<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projet;
use App\Models\PhaseProjet;
use App\Models\RemarquePhase;
use App\Models\StockProjet;
use App\Models\MouvementStock;
use App\Models\Presence;
use App\Models\Signalement;
use App\Models\Permission;
use App\Models\Facture;
use App\Models\AffectationProjet;
use App\Models\Employe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class ProjetController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Projet::query();

        // Filter by status
        if ($request->has('statut') && $request->statut != 'all') {
            $query->where('statut', $request->statut);
        }

        // Filter by client
        if ($request->has('client') && !empty($request->client)) {
            $query->where('client_nom', 'like', "%{$request->client}%");
        }

        // Search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('reference', 'like', "%{$search}%")
                  ->orWhere('client_nom', 'like', "%{$search}%");
            });
        }

        // Exclude stock central from regular projects list unless specified
        if (!$request->has('include_central') || !$request->include_central) {
            $query->where('est_stock_central', 0);
        }

        $projets = $query->withCount('phases')
                        ->with(['phaseActuelle'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        // Statistiques pour les KPIs
        $stats = [
            'actifs' => Projet::where('est_stock_central', 0)
                              ->whereIn('statut', ['debut', 'en_cours', 'verification', 'retard'])
                              ->count(),
            'en_retard' => Projet::where('est_stock_central', 0)
                                 ->where('statut', 'retard')
                                 ->count(),
            'termines' => Projet::where('est_stock_central', 0)
                                ->where('statut', 'termine')
                                ->count(),
            'budget_total' => Projet::where('est_stock_central', 0)
                                    ->sum('budget'),
        ];

        if ($request->ajax()) {
            return response()->json([
                'projets' => $projets,
                'stats' => $stats
            ]);
        }

        return view('projets.index', compact('projets', 'stats'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        // Récupérer les phases par défaut ou laisser l'utilisateur les créer
        $phasesDefaut = [
            ['nom' => 'Étude & conception', 'ordre' => 1],
            ['nom' => 'Terrassement', 'ordre' => 2],
            ['nom' => 'Fondations', 'ordre' => 3],
            ['nom' => 'Gros œuvre', 'ordre' => 4],
            ['nom' => 'Couverture & charpente', 'ordre' => 5],
            ['nom' => 'Second œuvre', 'ordre' => 6],
            ['nom' => 'Finitions', 'ordre' => 7],
            ['nom' => 'Réception & livraison', 'ordre' => 8],
        ];

        return view('projets.create', compact('phasesDefaut'));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'client_nom' => 'required|string|max:255',
            'client_contact' => 'nullable|string|max:100',
            'client_telephone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'reference' => 'nullable|string|max:50|unique:projets,reference',
            'statut' => 'required|in:debut,en_cours,verification,retard,termine',
            'budget' => 'required|numeric|min:0',
            'localisation' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'description' => 'nullable|string',
            'phases' => 'nullable|array',
            'phases.*.nom' => 'required|string|max:255',
            'phases.*.description' => 'nullable|string',
            'phases.*.date_debut_prevue' => 'nullable|date',
            'phases.*.date_fin_prevue' => 'nullable|date|after_or_equal:phases.*.date_debut_prevue',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Générer une référence si non fournie
            if (empty($request->reference)) {
                $request->merge(['reference' => $this->generateReference()]);
            }

            // Créer le projet
            $projet = Projet::create([
                'nom' => $request->nom,
                'client_nom' => $request->client_nom,
                'client_contact' => $request->client_contact,
                'client_telephone' => $request->client_telephone,
                'client_email' => $request->client_email,
                'reference' => $request->reference,
                'statut' => $request->statut,
                'budget' => $request->budget,
                'localisation' => $request->localisation,
                'date_debut' => $request->date_debut,
                'date_fin_prevue' => $request->date_fin_prevue,
                'description' => $request->description,
                'est_stock_central' => $request->has('est_stock_central') ? 1 : 0,
            ]);

            // Créer les phases si fournies
            if ($request->has('phases') && is_array($request->phases)) {
                $premierePhase = null;
                foreach ($request->phases as $index => $phaseData) {
                    $phase = PhaseProjet::create([
                        'projet_id' => $projet->id,
                        'nom' => $phaseData['nom'],
                        'description' => $phaseData['description'] ?? null,
                        'ordre' => $phaseData['ordre'] ?? ($index + 1),
                        'date_debut_prevue' => $phaseData['date_debut_prevue'] ?? null,
                        'date_fin_prevue' => $phaseData['date_fin_prevue'] ?? null,
                        'progression' => 0,
                    ]);
                    
                    if ($index === 0) {
                        $premierePhase = $phase->id;
                    }
                }

                // Définir la première phase comme phase actuelle
                if ($premierePhase) {
                    $projet->phase_actuelle_id = $premierePhase;
                    $projet->save();
                }
            }

            // Créer le stock initial pour le projet
            StockProjet::create([
                'projet_id' => $projet->id,
                'materiau_id' => 1, // À ajuster selon votre logique
                'quantite' => 0,
                'seuil_alerte' => 10,
            ]);

            DB::commit();

            return redirect()->route('projets.show', $projet)
                ->with('success', 'Projet créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Projet $projet)
    {
        // Charger toutes les relations nécessaires
        $projet->load([
            'phases' => function($q) {
                $q->orderBy('ordre');
            },
            'phaseActuelle',
            'affectations' => function($q) {
                $q->with('employe')->whereNull('date_fin');
            },
            'presences' => function($q) {
                $q->with('employe')->latest('date')->take(10);
            },
            'signalements' => function($q) {
                $q->with('auteur')->latest()->take(5);
            },
            'mouvementsStock' => function($q) {
                $q->with(['materiau', 'effectuePar'])->latest()->take(10);
            },
            'factures' => function($q) {
                $q->latest()->take(5);
            },
        ]);

        // Récupérer les remarques des phases
        $remarques = RemarquePhase::whereIn('phase_id', $projet->phases->pluck('id'))
            ->with(['auteur', 'phase'])
            ->latest()
            ->take(10)
            ->get();

        // Statistiques de présence
        $statsPresences = [
            'total' => Presence::where('projet_id', $projet->id)->count(),
            'aujourdhui' => Presence::where('projet_id', $projet->id)
                ->whereDate('date', today())
                ->count(),
            'present_aujourdhui' => Presence::where('projet_id', $projet->id)
                ->whereDate('date', today())
                ->where('statut', 'present')
                ->count(),
        ];

        // Récupérer le stock actuel
        $stock = StockProjet::where('projet_id', $projet->id)
            ->with('materiau')
            ->get();

        // Vérifier les permissions pour les actions
        $peutModifier = $this->checkPermission('projets', 'modifier', $projet->id);
        $peutGererStock = $this->checkPermission('stocks', 'modifier', $projet->id);
        $peutSaisirPresences = $this->checkPermission('presences', 'creer', $projet->id);

        return view('projets.show', compact(
            'projet',
            'remarques',
            'statsPresences',
            'stock',
            'peutModifier',
            'peutGererStock',
            'peutSaisirPresences'
        ));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Projet $projet)
    {
        $projet->load('phases');
        return view('projets.edit', compact('projet'));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'client_nom' => 'required|string|max:255',
            'client_contact' => 'nullable|string|max:100',
            'client_telephone' => 'nullable|string|max:30',
            'client_email' => 'nullable|email|max:255',
            'reference' => 'nullable|string|max:50|unique:projets,reference,' . $projet->id,
            'statut' => 'required|in:debut,en_cours,verification,retard,termine',
            'budget' => 'required|numeric|min:0',
            'localisation' => 'nullable|string|max:255',
            'date_debut' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut',
            'date_fin_reelle' => 'nullable|date',
            'description' => 'nullable|string',
            'phase_actuelle_id' => 'nullable|exists:phases_projet,id,projet_id,' . $projet->id,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Sauvegarder l'ancienne phase pour l'historique
            $anciennePhaseId = $projet->phase_actuelle_id;

            // Mettre à jour le projet
            $projet->update([
                'nom' => $request->nom,
                'client_nom' => $request->client_nom,
                'client_contact' => $request->client_contact,
                'client_telephone' => $request->client_telephone,
                'client_email' => $request->client_email,
                'reference' => $request->reference,
                'statut' => $request->statut,
                'budget' => $request->budget,
                'localisation' => $request->localisation,
                'date_debut' => $request->date_debut,
                'date_fin_prevue' => $request->date_fin_prevue,
                'date_fin_reelle' => $request->date_fin_reelle,
                'description' => $request->description,
                'phase_actuelle_id' => $request->phase_actuelle_id,
            ]);

            // Si la phase actuelle a changé, enregistrer dans l'historique
            if ($anciennePhaseId != $request->phase_actuelle_id) {
                DB::table('historique_phases')->insert([
                    'projet_id' => $projet->id,
                    'ancienne_phase_id' => $anciennePhaseId,
                    'nouvelle_phase_id' => $request->phase_actuelle_id,
                    'change_par' => Auth::id(),
                    'date_changement' => now(),
                    'note' => 'Mise à jour manuelle',
                ]);
            }

            DB::commit();

            return redirect()->route('projets.show', $projet)
                ->with('success', 'Projet mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Projet $projet)
    {
        try {
            // Vérifier s'il y a des dépendances
            if ($projet->presences()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des présences enregistrées.'
                ], 422);
            }

            if ($projet->mouvementsStock()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des mouvements de stock.'
                ], 422);
            }

            if ($projet->factures()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un projet avec des factures associées.'
                ], 422);
            }

            $projet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Projet supprimé avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a phase to a project.
     */
    public function addPhase(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'ordre' => 'required|integer|min:1',
            'date_debut_prevue' => 'nullable|date',
            'date_fin_prevue' => 'nullable|date|after_or_equal:date_debut_prevue',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $phase = PhaseProjet::create([
            'projet_id' => $projet->id,
            'nom' => $request->nom,
            'description' => $request->description,
            'ordre' => $request->ordre,
            'date_debut_prevue' => $request->date_debut_prevue,
            'date_fin_prevue' => $request->date_fin_prevue,
            'progression' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Phase ajoutée avec succès.',
            'phase' => $phase
        ]);
    }

    /**
     * Update phase progression.
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
                'progression' => $request->progression,
                'date_debut_reelle' => $request->progression > 0 && !$phase->date_debut_reelle ? now() : $phase->date_debut_reelle,
                'date_fin_reelle' => $request->progression >= 100 ? now() : null,
            ]);

            // Recalculer la progression globale du projet
            $projet = $phase->projet;
            $progressionGlobale = $projet->phases()->avg('progression');
            $projet->progression = $progressionGlobale;
            $projet->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Progression mise à jour.',
                'progression_phase' => $phase->progression,
                'progression_globale' => $projet->progression
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Add a remark to a phase.
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
            'phase_id' => $phase->id,
            'auteur_id' => Auth::id(),
            'contenu' => $request->contenu,
        ]);

        $remarque->load('auteur');

        return response()->json([
            'success' => true,
            'message' => 'Remarque ajoutée.',
            'remarque' => $remarque
        ]);
    }

    /**
     * Get project statistics for dashboard.
     */
    public function getStats()
    {
        $stats = [
            'total' => Projet::where('est_stock_central', 0)->count(),
            'par_statut' => Projet::where('est_stock_central', 0)
                ->select('statut', DB::raw('count(*) as total'))
                ->groupBy('statut')
                ->get(),
            'budget_total' => Projet::where('est_stock_central', 0)->sum('budget'),
            'projets_retard' => Projet::where('est_stock_central', 0)
                ->where('statut', 'retard')
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Generate a unique project reference.
     */
    private function generateReference()
    {
        $year = date('Y');
        $month = date('m');
        $count = Projet::whereYear('created_at', $year)->count() + 1;
        
        return 'KEUF-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Check user permission.
     */
    private function checkPermission($module, $action, $projetId = null)
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();
        
        // Admin a tous les droits
        if ($user->role === 'admin') {
            return true;
        }

        $query = Permission::where('utilisateur_id', $user->id)
            ->where('module', $module)
            ->where('action', $action)
            ->where('autorise', true);

        if ($projetId) {
            $query->where(function($q) use ($projetId) {
                $q->whereNull('projet_id')
                  ->orWhere('projet_id', $projetId);
            });
        }

        return $query->exists();
    }
}
