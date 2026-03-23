<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Projet;
use App\Models\Presence;
use App\Models\DetailPresence;
use App\Models\StockProjet;
use App\Models\Signalement;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\PhaseProjet;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    /**
     * Redirige vers le dashboard correspondant au rôle de l'utilisateur connecté.
     */
    public function index()
    {
        $user = Auth::user();

        return match ($user->role) {
            'admin'                => $this->adminDashboard(),
            'directeur_technique'  => $this->directeurTechniqueDashboard(),
            'controleur'           => $this->controleurDashboard(),
            'gestionnaire_stock'   => $this->gestionnaireStockDashboard(),
            default                => $this->defaultDashboard(),
        };
    }

    // -------------------------------------------------------------------------
    // Dashboard Admin
    // -------------------------------------------------------------------------

    private function adminDashboard()
    {
        $totalProjets  = Projet::chantiers()->count();
        $projetsActifs = Projet::chantiers()->actifs()->count();

        $projetsParStatut = Projet::chantiers()
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        // Présences du jour : on compte dans detail_presences via les sessions du jour
        $presencesToday = DetailPresence::where('statut', 'present')
            ->whereHas('presence', fn($q) => $q->whereDate('date', today()))
            ->count();

        $absentsToday = DetailPresence::where('statut', 'absent')
            ->whereHas('presence', fn($q) => $q->whereDate('date', today()))
            ->count();

        // Alertes stocks (tous projets, central inclus)
        $stockAlerts = StockProjet::with(['projet', 'materiau'])
            ->whereRaw('quantite <= seuil_alerte')
            ->where('seuil_alerte', '>', 0)
            ->get();

        $stockAlertsCount = $stockAlerts->count();

        // Signalements ouverts
        $signalementsEnAttente = Signalement::whereIn('statut', ['ouvert', 'en_cours'])
            ->with(['projet', 'auteur'])
            ->latest()
            ->take(5)
            ->get();

        $signalementsCount = Signalement::whereIn('statut', ['ouvert', 'en_cours'])->count();

        // Projets en cours
        $projetsEnCours = Projet::chantiers()
            ->actifs()
            ->with('phaseActuelle')
            ->orderBy('date_fin_prevue')
            ->take(5)
            ->get();

        // Alertes combinées pour le panneau de notifications
        $alertes = collect();

        foreach ($stockAlerts as $stock) {
            $alertes->push([
                'type'        => 'stock',
                'icon'        => 'box',
                'color'       => 'red',
                'title'       => 'Stock critique — ' . $stock->materiau->nom,
                'description' => number_format($stock->quantite, 0) . ' ' . $stock->materiau->unite
                                 . ' · Seuil : ' . number_format($stock->seuil_alerte, 0),
                'projet'      => $stock->projet->nom,
                'date'        => $stock->updated_at?->format('d/m/Y'),
                'link'        => '#',
            ]);
        }

        foreach ($signalementsEnAttente as $sig) {
            $alertes->push([
                'type'        => 'signalement',
                'icon'        => 'exclamation-triangle',
                'color'       => 'orange',
                'title'       => 'Signalement ' . $sig->type,
                'description' => Str::limit($sig->description, 50),
                'projet'      => $sig->projet->nom,
                'date'        => $sig->created_at,
                'link'        => '#',
            ]);
        }

        $alertes = $alertes->sortByDesc('date')->take(6);

        // Finances
        $chiffreAffairesMois = Facture::where('type', 'client')
            ->whereMonth('date_emission', now()->month)
            ->whereYear('date_emission', now()->year)
            ->sum(DB::raw('montant_ht * (1 + tva / 100)'));

        $facturesEnAttente = Facture::whereIn('statut', ['envoyee', 'en_retard'])->count();
        $budgetTotal       = Projet::chantiers()->sum('budget');

        return view('dashboards.admin', compact(
            'totalProjets',
            'projetsActifs',
            'projetsParStatut',
            'presencesToday',
            'absentsToday',
            'stockAlertsCount',
            'stockAlerts',
            'signalementsCount',
            'signalementsEnAttente',
            'projetsEnCours',
            'alertes',
            'chiffreAffairesMois',
            'facturesEnAttente',
            'budgetTotal'
        ));
    }

    // -------------------------------------------------------------------------
    // Dashboard Directeur Technique
    // -------------------------------------------------------------------------

    private function directeurTechniqueDashboard()
    {
        $projets = Projet::chantiers()
            ->with(['phases' => fn($q) => $q->orderBy('ordre'), 'phaseActuelle'])
            ->get();

        $projetsRetard       = Projet::chantiers()->where('statut', 'retard')->count();
        $progressionMoyenne  = Projet::chantiers()->avg('progression');

        $phasesEnCours = PhaseProjet::whereHas('projet', fn($q) => $q->chantiers())
            ->whereNotNull('date_debut_reelle')
            ->whereNull('date_fin_reelle')
            ->count();

        $signalementsTechniques = Signalement::whereIn('type', ['avancement', 'materiaux', 'securite'])
            ->whereIn('statut', ['ouvert', 'en_cours'])
            ->with(['projet', 'auteur'])
            ->latest()
            ->take(10)
            ->get();

        return view('dashboards.directeur-technique', compact(
            'projets',
            'projetsRetard',
            'progressionMoyenne',
            'phasesEnCours',
            'signalementsTechniques'
        ));
    }

    // -------------------------------------------------------------------------
    // Dashboard Contrôleur
    // -------------------------------------------------------------------------

    private function controleurDashboard()
    {
        $today = today()->toDateString();

        // Sessions de pointage du jour avec leurs détails
        $sessionsAujourdhui = Presence::whereDate('date', $today)
            ->with(['details.employe', 'projet'])
            ->get();

        // Totaux calculés depuis les détails
        $presencesCount = DetailPresence::where('statut', 'present')
            ->whereHas('presence', fn($q) => $q->whereDate('date', $today))
            ->count();

        $absentsCount = DetailPresence::where('statut', 'absent')
            ->whereHas('presence', fn($q) => $q->whereDate('date', $today))
            ->count();

        $signalementsOuverts = Signalement::whereIn('statut', ['ouvert', 'en_cours'])
            ->with(['projet', 'auteur'])
            ->latest()
            ->get();

        $signalementsByPriority = $signalementsOuverts->groupBy('priorite')->map->count();

        $projetsAVerifier = Projet::chantiers()
            ->where('statut', 'verification')
            ->with('phases')
            ->get();

        return view('dashboards.controleur', compact(
            'sessionsAujourdhui',
            'presencesCount',
            'absentsCount',
            'signalementsOuverts',
            'signalementsByPriority',
            'projetsAVerifier'
        ));
    }

    // -------------------------------------------------------------------------
    // Dashboard Gestionnaire de Stock
    // -------------------------------------------------------------------------

    private function gestionnaireStockDashboard()
    {
        $stockCentral = Projet::stockCentral()->first();

        $stockAlerts = StockProjet::with(['projet', 'materiau'])
            ->whereRaw('quantite <= seuil_alerte')
            ->where('seuil_alerte', '>', 0)
            ->get();

        $mouvementsRecents = DB::table('mouvements_stock')
            ->join('materiaux', 'materiaux.id', '=', 'mouvements_stock.materiau_id')
            ->join('utilisateurs', 'utilisateurs.id', '=', 'mouvements_stock.effectue_par')
            ->leftJoin('projets as source', 'source.id', '=', 'mouvements_stock.source_projet_id')
            ->leftJoin('projets as dest',   'dest.id',   '=', 'mouvements_stock.dest_projet_id')
            ->select(
                'mouvements_stock.*',
                'materiaux.nom as materiau_nom',
                'materiaux.unite',
                'utilisateurs.nom as user_nom',
                'utilisateurs.prenom as user_prenom',
                'source.nom as source_nom',
                'dest.nom as dest_nom'
            )
            ->latest('mouvements_stock.created_at')
            ->take(20)
            ->get();

        $statsParCategorie = DB::table('stock_projets')
            ->join('materiaux', 'materiaux.id', '=', 'stock_projets.materiau_id')
            ->select(
                'materiaux.categorie',
                DB::raw('count(*) as total_materiaux'),
                DB::raw('sum(quantite) as quantite_totale')
            )
            ->groupBy('materiaux.categorie')
            ->get();

        $fournisseursActifs = \App\Models\Fournisseur::count();

        return view('dashboards.gestionnaire-stock', compact(
            'stockCentral',
            'stockAlerts',
            'mouvementsRecents',
            'statsParCategorie',
            'fournisseursActifs'
        ));
    }

    // -------------------------------------------------------------------------
    // Fallback
    // -------------------------------------------------------------------------

    private function defaultDashboard()
    {
        return view('dashboards.default');
    }

    // -------------------------------------------------------------------------
    // API : stats rapides (polling AJAX)
    // -------------------------------------------------------------------------

    /**
     * Retourne les 4 KPIs principaux en JSON pour le rafraîchissement en temps réel.
     */
    public function getQuickStats()
    {
        $stats = [
            'projets_actifs'      => Projet::chantiers()->actifs()->count(),
            'presences_aujourdhui' => DetailPresence::where('statut', 'present')
                ->whereHas('presence', fn($q) => $q->whereDate('date', today()))
                ->count(),
            'alertes_stock'       => StockProjet::whereRaw('quantite <= seuil_alerte')
                ->where('seuil_alerte', '>', 0)
                ->count(),
            'signalements_ouverts' => Signalement::whereIn('statut', ['ouvert', 'en_cours'])->count(),
        ];

        return response()->json($stats);
    }
}