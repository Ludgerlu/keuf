<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projet;
use App\Models\PhaseProjet;
use App\Models\HistoriquePhase;
use App\Models\RemarquePhase;
use App\Models\StockProjet;
use App\Models\MouvementStock;
use App\Models\Presence;
use App\Models\DetailPresence;
use App\Models\Signalement;
use App\Models\Permission;
use App\Models\Facture;
use App\Models\AffectationProjet;
use App\Models\Employe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ProjetController extends Controller
{
    /**
     * Liste des projets (chantiers uniquement, sauf demande explicite).
     */
    public function index(Request $request)
    {
        // Utiliser le scope chantiers() par défaut — exclut le stock central
        $query = $request->boolean('include_central')
            ? Projet::query()
            : Projet::chantiers();

        if ($request->filled('statut') && $request->statut !== 'all') {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('client')) {
            $query->where('client_nom', 'like', "%{$request->client}%");
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('client_nom', 'like', "%{$search}%");
            });
        }

        $projets = $query->withCount('phases')
            ->with('phaseActuelle')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $stats = [
            'actifs'       => Projet::chantiers()->actifs()->count(),
            'en_retard'    => Projet::chantiers()->where('statut', 'retard')->count(),
            'termines'     => Projet::chantiers()->where('statut', 'termine')->count(),
            'budget_total' => Projet::chantiers()->sum('budget'),
        ];

        if ($request->ajax()) {
            return response()->json(['projets' => $projets, 'stats' => $stats]);
        }

        return view('projets.index', compact('projets', 'stats'));
    }

    /**
     * Formulaire de création avec phases par défaut suggérées.
     */
    public function create()
    {
        $phasesDefaut = [
            ['nom' => 'Étude & conception',     'ordre' => 1],
            ['nom' => 'Terrassement',            'ordre' => 2],
            ['nom' => 'Fondations',              'ordre' => 3],
            ['nom' => 'Gros œuvre',              'ordre' => 4],
            ['nom' => 'Couverture & charpente',  'ordre' => 5],
            ['nom' => 'Second œuvre',            'ordre' => 6],
            ['nom' => 'Finitions',               'ordre' => 7],
            ['nom' => 'Réception & livraison',   'ordre' => 8],
        ];

        return view('projets.create', compact('phasesDefaut'));
    }

    /**
     * Enregistre un nouveau projet avec ses phases.
     * Ne crée PAS de ligne stock automatique : le stock s'alimente via les
     * mouvements (réception fournisseur ou transfert depuis le stock central).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'                          => 'required|string|max:255',
            'client_nom'                   => 'required_unless:est_stock_central,1|nullable|string|max:255',
            'client_contact'               => 'nullable|string|max:100',
            'client_telephone'             => 'nullable|string|max:30',
            'client_email'                 => 'nullable|email|max:255',
            'reference'                    => 'nullable|string|max:50|unique:projets,reference',
            'statut'                       => 'required|in:debut,en_cours,verification,retard,termine',
            'budget'                       => 'required|numeric|min:0',
            'localisation'                 => 'nullable|string|max:255',
            'date_debut'                   => 'nullable|date',
            'date_fin_prevue'              => 'nullable|date|after_or_equal:date_debut',
            'description'                  => 'nullable|string',
            'phases'                       => 'nullable|array',
            'phases.*.nom'                 => 'required|string|max:255',
            'phases.*.description'         => 'nullable|string',
            'phases.*.date_debut_prevue'   => 'nullable|date',
            'phases.*.date_fin_prevue'     => 'nullable|date|after_or_equal:phases.*.date_debut_prevue',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $projet = Projet::create([
                'nom'               => $request->nom,
                'est_stock_central' => $request->boolean('est_stock_central'),
                'client_nom'        => $request->client_nom,
                'client_contact'    => $request->client_contact,
                'client_telephone'  => $request->client_telephone,
                'client_email'      => $request->client_email,
                'reference'         => $request->reference ?? $this->generateReference(),
                'statut'            => $request->statut,
                'budget'            => $request->budget,
                'localisation'      => $request->localisation,
                'date_debut'        => $request->date_debut,
                'date_fin_prevue'   => $request->date_fin_prevue,
                'description'       => $request->description,
            ]);

            // Créer les phases et définir la première comme phase actuelle
            if ($request->filled('phases') && is_array($request->phases)) {
                $premierePhaseId = null;

                foreach ($request->phases as $index => $phaseData) {
                    $phase = PhaseProjet::create([
                        'projet_id'          => $projet->id,
                        'nom'                => $phaseData['nom'],
                        'description'        => $phaseData['description'] ?? null,
                        'ordre'              => $phaseData['ordre'] ?? ($index + 1),
                        'progression'        => 0,
                        'date_debut_prevue'  => $phaseData['date_debut_prevue'] ?? null,
                        'date_fin_prevue'    => $phaseData['date_fin_prevue']   ?? null,
                    ]);

                    if ($index === 0) {
                        $premierePhaseId = $phase->id;
                    }
                }

                if ($premierePhaseId) {
                    $projet->update(['phase_actuelle_id' => $premierePhaseId]);

                    // Enregistrer dans l'historique (démarrage du projet)
                    HistoriquePhase::create([
                        'projet_id'         => $projet->id,
                        'ancienne_phase_id' => null,
                        'nouvelle_phase_id' => $premierePhaseId,
                        'change_par'        => Auth::id(),
                        'date_changement'   => now(),
                        'note'              => 'Démarrage du projet',
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('projets.show', $projet)
                ->with('success', 'Projet créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Détail d'un projet : phases, présences (via sessions), stock, signalements.
     */
    public function show(Projet $projet)
    {
        $projet->load([
            'phases'       => fn($q) => $q->orderBy('ordre')->with('remarques.auteur'),
            'phaseActuelle',
            'affectations' => fn($q) => $q->with('employe')->whereNull('date_fin'),
            // Sessions de présence avec leurs détails employés
            'presences'    => fn($q) => $q->with(['details.employe', 'saisiePar'])->latest('date')->take(10),
            'signalements' => fn($q) => $q->with('auteur')->latest()->take(5),
            'factures'     => fn($q) => $q->latest()->take(5),
        ]);

        // Mouvements de stock (source ou destination = ce projet)
        $mouvementsStock = MouvementStock::where(function ($q) use ($projet) {
                $q->where('source_projet_id', $projet->id)
                  ->orWhere('dest_projet_id', $projet->id);
            })
            ->with(['materiau', 'effectuePar'])
            ->latest()
            ->take(10)
            ->get();

        // Remarques de toutes les phases (vue globale)
        $remarques = RemarquePhase::whereIn('phase_id', $projet->phases->pluck('id'))
            ->with(['auteur', 'phase'])
            ->latest()
            ->take(10)
            ->get();

        // Stats de présence : on compte dans detail_presences via les sessions du projet
        $sessionsProjet = Presence::where('projet_id', $projet->id);

        $statsPresences = [
            'total_sessions'      => (clone $sessionsProjet)->count(),
            'sessions_aujourdhui' => (clone $sessionsProjet)->whereDate('date', today())->count(),
            'presents_aujourdhui' => DetailPresence::where('statut', 'present')
                ->whereHas('presence', fn($q) => $q
                    ->where('projet_id', $projet->id)
                    ->whereDate('date', today())
                )
                ->count(),
            'absents_aujourdhui'  => DetailPresence::where('statut', 'absent')
                ->whereHas('presence', fn($q) => $q
                    ->where('projet_id', $projet->id)
                    ->whereDate('date', today())
                )
                ->count(),
        ];

        // Stock actuel du projet
        $stock = StockProjet::where('projet_id', $projet->id)
            ->with('materiau')
            ->get();

        // Permissions de l'utilisateur connecté sur ce projet
        $peutModifier       = Auth::user()->hasPermission('projets', 'modifier', $projet->id);
        $peutGererStock     = Auth::user()->hasPermission('stocks', 'modifier', $projet->id);
        $peutSaisirPresences = Auth::user()->hasPermission('presences', 'creer', $projet->id);

        return view('projets.show', compact(
            'projet',
            'mouvementsStock',
            'remarques',
            'statsPresences',
            'stock',
            'peutModifier',
            'peutGererStock',
            'peutSaisirPresences'
        ));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Projet $projet)
    {
        $projet->load('phases');
        return view('projets.edit', compact('projet'));
    }

    /**
     * Met à jour un projet. Si la phase actuelle change, insère dans historique_phases.
     */
    public function update(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'nom'               => 'required|string|max:255',
            'client_nom'        => 'required_unless:est_stock_central,1|nullable|string|max:255',
            'client_contact'    => 'nullable|string|max:100',
            'client_telephone'  => 'nullable|string|max:30',
            'client_email'      => 'nullable|email|max:255',
            'reference'         => 'nullable|string|max:50|unique:projets,reference,' . $projet->id,
            'statut'            => 'required|in:debut,en_cours,verification,retard,termine',
            'budget'            => 'required|numeric|min:0',
            'localisation'      => 'nullable|string|max:255',
            'date_debut'        => 'nullable|date',
            'date_fin_prevue'   => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle'   => 'nullable|date',
            'description'       => 'nullable|string',
            'phase_actuelle_id' => 'nullable|exists:phases_projet,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $anciennePhaseId = $projet->phase_actuelle_id;

            $projet->update([
                'nom'               => $request->nom,
                'client_nom'        => $request->client_nom,
                'client_contact'    => $request->client_contact,
                'client_telephone'  => $request->client_telephone,
                'client_email'      => $request->client_email,
                'reference'         => $request->reference,
                'statut'            => $request->statut,
                'budget'            => $request->budget,
                'localisation'      => $request->localisation,
                'date_debut'        => $request->date_debut,
                'date_fin_prevue'   => $request->date_fin_prevue,
                'date_fin_reelle'   => $request->date_fin_reelle,
                'description'       => $request->description,
                'phase_actuelle_id' => $request->phase_actuelle_id,
            ]);

            // Enregistrer le changement de phase dans l'historique
            if ($anciennePhaseId != $request->phase_actuelle_id && $request->filled('phase_actuelle_id')) {
                HistoriquePhase::create([
                    'projet_id'         => $projet->id,
                    'ancienne_phase_id' => $anciennePhaseId,
                    'nouvelle_phase_id' => $request->phase_actuelle_id,
                    'change_par'        => Auth::id(),
                    'date_changement'   => now(),
                    'note'              => $request->note_changement_phase ?? 'Mise à jour manuelle',
                ]);
            }

            DB::commit();

            return redirect()->route('projets.show', $projet)
                ->with('success', 'Projet mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprime un projet (SoftDelete) si aucune dépendance bloquante.
     * Bloque si des présences, mouvements de stock ou factures existent.
     * Le stock central ne peut jamais être supprimé.
     */
    public function destroy(Projet $projet)
    {
        try {
            if ($projet->est_stock_central) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le stock central ne peut pas être supprimé.',
                ], 422);
            }

            // Bloquer si des sessions de présence existent
            if ($projet->presences()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des présences enregistrées.',
                ], 422);
            }

            // Bloquer si des mouvements de stock sont liés (source ou destination)
            $aMouvements = MouvementStock::where('source_projet_id', $projet->id)
                ->orWhere('dest_projet_id', $projet->id)
                ->exists();

            if ($aMouvements) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des mouvements de stock.',
                ], 422);
            }

            if ($projet->factures()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des factures associées.',
                ], 422);
            }

            $projet->delete();

            return response()->json(['success' => true, 'message' => 'Projet supprimé avec succès.']);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ajoute une phase à un projet existant.
     */
    public function addPhase(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'nom'                => 'required|string|max:255',
            'description'        => 'nullable|string',
            'ordre'              => 'required|integer|min:1',
            'date_debut_prevue'  => 'nullable|date',
            'date_fin_prevue'    => 'nullable|date|after_or_equal:date_debut_prevue',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $phase = PhaseProjet::create([
            'projet_id'         => $projet->id,
            'nom'               => $request->nom,
            'description'       => $request->description,
            'ordre'             => $request->ordre,
            'progression'       => 0,
            'date_debut_prevue' => $request->date_debut_prevue,
            'date_fin_prevue'   => $request->date_fin_prevue,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phase ajoutée avec succès.',
            'phase'   => $phase,
        ]);
    }

    /**
     * Met à jour la progression d'une phase et recalcule la progression globale du projet.
     * Renseigne automatiquement date_debut_reelle et date_fin_reelle.
     */
    public function updatePhaseProgression(Request $request, PhaseProjet $phase)
    {
        $validator = Validator::make($request->all(), [
            'progression' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $phase->update([
                'progression'       => $request->progression,
                'date_debut_reelle' => $request->progression > 0 && ! $phase->date_debut_reelle
                    ? now()
                    : $phase->date_debut_reelle,
                'date_fin_reelle'   => $request->progression >= 100 ? now() : null,
            ]);

            // Recalculer la progression globale du projet (moyenne des phases)
            $projet = $phase->projet;
            $projet->update([
                'progression' => round($projet->phases()->avg('progression'), 2),
            ]);

            DB::commit();

            return response()->json([
                'success'             => true,
                'message'             => 'Progression mise à jour.',
                'progression_phase'   => $phase->progression,
                'progression_globale' => $projet->progression,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Ajoute une remarque sur une phase.
     * L'accès est contrôlé par la permission projets/modifier de l'utilisateur.
     */
    public function addRemark(Request $request, PhaseProjet $phase)
    {
        $validator = Validator::make($request->all(), [
            'contenu' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $remarque = RemarquePhase::create([
            'phase_id'  => $phase->id,
            'auteur_id' => Auth::id(),
            'contenu'   => $request->contenu,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Remarque ajoutée.',
            'remarque' => $remarque->load('auteur'),
        ]);
    }

    /**
     * Change la phase actuelle d'un projet et enregistre l'historique.
     * Endpoint dédié pour les changements de phase depuis la vue détail.
     */
    public function changerPhase(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'phase_id' => 'required|exists:phases_projet,id',
            'note'     => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que la phase appartient bien à ce projet
        $phase = PhaseProjet::where('id', $request->phase_id)
            ->where('projet_id', $projet->id)
            ->firstOrFail();

        DB::beginTransaction();
        try {
            $anciennePhaseId = $projet->phase_actuelle_id;

            $projet->update(['phase_actuelle_id' => $phase->id]);

            HistoriquePhase::create([
                'projet_id'         => $projet->id,
                'ancienne_phase_id' => $anciennePhaseId,
                'nouvelle_phase_id' => $phase->id,
                'change_par'        => Auth::id(),
                'date_changement'   => now(),
                'note'              => $request->note,
            ]);

            // Marquer la date de début réelle si la phase n'a pas encore démarré
            if (! $phase->date_debut_reelle) {
                $phase->update(['date_debut_reelle' => now()]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Phase changée avec succès.',
                'phase'   => $phase,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stats globales en JSON pour usage AJAX / dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total'          => Projet::chantiers()->count(),
            'par_statut'     => Projet::chantiers()
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get(),
            'budget_total'   => Projet::chantiers()->sum('budget'),
            'projets_retard' => Projet::chantiers()->where('statut', 'retard')->count(),
        ];

        return response()->json($stats);
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    /**
     * Génère une référence unique au format KEUF-YYYY-NNN.
     */
    private function generateReference(): string
    {
        $year  = now()->year;
        $count = Projet::whereYear('created_at', $year)->count() + 1;

        return 'KEUF-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}