<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Projet;
use App\Models\Presence;
use App\Models\StockProjet;
use App\Models\Signalement;
use App\Models\Facture;
use App\Models\Fournisseurs;
use App\Models\Phase;

use Illuminate\Support\Str;


class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get role-specific dashboard data
        switch ($user->role) {
            case 'admin':
                return $this->adminDashboard();
            case 'directeur_technique':
                return $this->directeurTechniqueDashboard();
            case 'controleur':
                return $this->controleurDashboard();
            case 'gestionnaire_stock':
                return $this->gestionnaireStockDashboard();
            default:
                return $this->defaultDashboard();
        }
    }

    /**
     * Admin Dashboard
     */
    private function adminDashboard()
    {
        // KPIs
        $totalProjets = Projet::where('est_stock_central', 0)->count();
        $projetsActifs = Projet::where('est_stock_central', 0)
            ->whereIn('statut', ['en_cours', 'verification', 'retard'])
            ->count();
        
        $projetsParStatut = Projet::where('est_stock_central', 0)
            ->select('statut', DB::raw('count(*) as total'))
            ->groupBy('statut')
            ->pluck('total', 'statut')
            ->toArray();

        $presencesToday = Presence::whereDate('date', today())
            ->where('statut', 'present')
            ->count();
        
        $absentsToday = Presence::whereDate('date', today())
            ->where('statut', 'absent')
            ->count();

        // Stock alerts
        $stockAlerts = StockProjet::with(['projet', 'materiau'])
            ->whereRaw('quantite <= seuil_alerte')
            ->where('seuil_alerte', '>', 0)
            ->get();
        
        $stockAlertsCount = $stockAlerts->count();

        // Signalements en attente
        $signalementsEnAttente = Signalement::whereIn('statut', ['ouvert', 'en_cours'])
            ->with(['projet', 'auteur'])
            ->latest()
            ->take(5)
            ->get();
        
        $signalementsCount = Signalement::whereIn('statut', ['ouvert', 'en_cours'])->count();

        // Projets en cours avec progression
        $projetsEnCours = Projet::where('est_stock_central', 0)
            ->whereIn('statut', ['en_cours', 'verification', 'retard'])
            ->with('phaseActuelle')
            ->orderBy('date_fin_prevue')
            ->take(5)
            ->get();

        // Alertes combinées pour les notifications
        $alertes = collect();
        
        // Ajouter les alertes stock
        foreach ($stockAlerts as $stock) {
            $alertes->push([
                'type' => 'stock',
                'icon' => 'box',
                'color' => 'red',
                'title' => 'Stock critique - ' . $stock->materiau->nom,
                'description' => number_format($stock->quantite, 0) . ' ' . $stock->materiau->unite . ' · Seuil : ' . number_format($stock->seuil_alerte, 0),
                'projet' => $stock->projet->nom,
                'date' => $stock->updated_at?->format('d/m/Y'),
                'link' =>  '#'
            ]);
        }

        // Ajouter les signalements récents
        foreach ($signalementsEnAttente as $sig) {
            $alertes->push([
                'type' => 'signalement',
                'icon' => 'exclamation-triangle',
                'color' => 'orange',
                'title' => $sig->type === 'securite' ? 'Signalement sécurité' : 'Signalement ' . $sig->type,
                'description' => Str::limit($sig->description, 50),
                'projet' => $sig->projet->nom,
                'date' => $sig->created_at,
                'link' => '#'
            ]);
        }

        // Trier par date (récent d'abord)
        $alertes = $alertes->sortByDesc('date')->take(6);

        // Statistiques financières
        $chiffreAffairesMois = Facture::where('type', 'client')
            ->whereMonth('date_emission', now()->month)
            ->whereYear('date_emission', now()->year)
            ->sum(DB::raw('montant_ht * (1 + tva/100)'));

        $facturesEnAttente = Facture::whereIn('statut', ['envoyee', 'en_retard'])
            ->count();

        // Budget total des projets
        $budgetTotal = Projet::where('est_stock_central', 0)->sum('budget');

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

    /**
     * Directeur Technique Dashboard
     */
    private function directeurTechniqueDashboard()
    {
        // Focus on project progress, phases, and technical aspects
        $projets = Projet::where('est_stock_central', 0)
            ->with(['phases' => function($q) {
                $q->orderBy('ordre');
            }, 'phaseActuelle'])
            ->get();

        $projetsRetard = Projet::where('est_stock_central', 0)
            ->where('statut', 'retard')
            ->count();

        $progressionMoyenne = Projet::where('est_stock_central', 0)
            ->avg('progression');

        $phasesEnCours = DB::table('phases_projet')
            ->join('projets', 'projets.id', '=', 'phases_projet.projet_id')
            ->where('projets.est_stock_central', 0)
            ->whereNull('phases_projet.date_fin_reelle')
            ->whereNotNull('phases_projet.date_debut_reelle')
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

    /**
     * Contrôleur Dashboard
     */
    private function controleurDashboard()
    {
        // Focus on quality control, signalements, presences
        $today = now()->toDateString();

        $presencesAujourdhui = Presence::whereDate('date', $today)
            ->with(['employe', 'projet'])
            ->get();

        $presencesCount = $presencesAujourdhui->count();
        $absentsCount = $presencesAujourdhui->where('statut', 'absent')->count();

        $signalementsOuverts = Signalement::whereIn('statut', ['ouvert', 'en_cours'])
            ->with(['projet', 'auteur'])
            ->latest()
            ->get();

        $signalementsByPriority = $signalementsOuverts->groupBy('priorite')->map->count();

        $projetsAVerifier = Projet::where('statut', 'verification')
            ->with('phases')
            ->get();

        return view('dashboards.controleur', compact(
            'presencesAujourdhui',
            'presencesCount',
            'absentsCount',
            'signalementsOuverts',
            'signalementsByPriority',
            'projetsAVerifier'
        ));
    }

    /**
     * Gestionnaire de Stock Dashboard
     */
    private function gestionnaireStockDashboard()
    {
        // Focus on inventory management
        $stockCentral = Projet::where('est_stock_central', 1)->first();
        
        $stockAlerts = StockProjet::with(['projet', 'materiau'])
            ->whereRaw('quantite <= seuil_alerte')
            ->where('seuil_alerte', '>', 0)
            ->get();

        $mouvementsRecents = DB::table('mouvements_stock')
            ->join('materiaux', 'materiaux.id', '=', 'mouvements_stock.materiau_id')
            ->join('utilisateurs', 'utilisateurs.id', '=', 'mouvements_stock.effectue_par')
            ->leftJoin('projets as source', 'source.id', '=', 'mouvements_stock.source_projet_id')
            ->leftJoin('projets as dest', 'dest.id', '=', 'mouvements_stock.dest_projet_id')
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

        // $fournisseursActifs = Fournisseurs::get()->count();
        $fournisseursActifs = 2;

        return view('dashboards.gestionnaire-stock', compact(
            'stockCentral',
            'stockAlerts',
            'mouvementsRecents',
            'statsParCategorie',
            'fournisseursActifs'
        ));
    }

    /**
     * Default Dashboard (fallback)
     */
    private function defaultDashboard()
    {
        return view('dashboards.default');
    }

    /**
     * Get quick stats for AJAX calls
     */
    public function getQuickStats()
    {
        $stats = [
            'projets_actifs' => Projet::where('est_stock_central', 0)
                ->whereIn('statut', ['en_cours', 'verification', 'retard'])
                ->count(),
            'presences_aujourdhui' => Presence::whereDate('date', today())
                ->where('statut', 'present')
                ->count(),
            'alertes_stock' => StockProjet::whereRaw('quantite <= seuil_alerte')
                ->where('seuil_alerte', '>', 0)
                ->count(),
            'signalements_ouverts' => Signalement::whereIn('statut', ['ouvert', 'en_cours'])
                ->count(),
        ];

        return response()->json($stats);
    }
}
