<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Projet;
use App\Models\Materiau;
use App\Models\StockProjet;
use App\Models\MouvementStock;
use App\Models\Fournisseur;
use App\Models\PhaseProjet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Display stock management for a project.
     */
    public function index(Projet $projet)
    {
        // Get current stock
        $stock = StockProjet::where('projet_id', $projet->id)
            ->with('materiau')
            ->get()
            ->keyBy('materiau_id');

        // Get all materials
        $materiaux = Materiau::orderBy('nom')->get();

        // Get recent movements
        $recentMovements = MouvementStock::where(function($q) use ($projet) {
                $q->where('source_projet_id', $projet->id)
                  ->orWhere('dest_projet_id', $projet->id);
            })
            ->with(['materiau', 'effectuePar', 'fournisseur', 'sourceProjet', 'destProjet'])
            ->latest()
            ->take(20)
            ->get();

        // Calculate statistics
        $stats = [
            'alerte' => $stock->filter(function($item) {
                return $item->quantite <= $item->seuil_alerte;
            })->count(),
            'ok' => $stock->filter(function($item) {
                return $item->quantite > $item->seuil_alerte;
            })->count(),
            'mouvements_mois' => MouvementStock::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where(function($q) use ($projet) {
                    $q->where('source_projet_id', $projet->id)
                      ->orWhere('dest_projet_id', $projet->id);
                })
                ->count(),
        ];

        // Get active projects for transfers (excluding current project)
        $activeProjects = Projet::where('id', '!=', $projet->id)
            ->where('est_stock_central', 0)
            ->whereIn('statut', ['debut', 'en_cours', 'verification'])
            ->orderBy('nom')
            ->get();

        // Get project phases
        $phases = PhaseProjet::where('projet_id', $projet->id)
            ->orderBy('ordre')
            ->get();

        return view('stocks.index', compact(
            'projet',
            'stock',
            'materiaux',
            'recentMovements',
            'stats',
            'activeProjects',
            'phases'
        ));
    }

    /**
     * Receive materials (add to stock).
     */
    public function receive(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'materiau_id' => 'required|exists:materiaux,id',
            'quantite' => 'required',
            'prix_unitaire' => 'nullable|numeric|min:0',
            'fournisseur_id' => 'nullable|exists:fournisseurs,id',
            'fournisseur_nom' => 'required_without:fournisseur_id|max:255',
            'reference_doc' => 'nullable|string|max:100',
            'note' => 'nullable|string|max:500',
            'date_reception' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Create or get fournisseur
            if ($request->filled('fournisseur_id')) {
                $fournisseurId = $request->fournisseur_id;
            } else {
                $fournisseur = Fournisseur::firstOrCreate(
                    ['nom' => $request->fournisseur_nom],
                    ['actif' => true]
                );
                $fournisseurId = $fournisseur->id;
            }

            // Create mouvement
            $mouvement = MouvementStock::create([
                'materiau_id' => $request->materiau_id,
                'effectue_par' => Auth::id(),
                'fournisseur_id' => $fournisseurId,
                'type' => 'entree',
                'dest_projet_id' => $projet->id,
                'quantite' => $request->quantite,
                'prix_unitaire' => $request->prix_unitaire,
                'reference_doc' => $request->reference_doc,
                'note' => $request->note,
                'created_at' => $request->date_reception ?: now(),
            ]);

            // Update or create stock
            $stock = StockProjet::firstOrNew([
                'projet_id' => $projet->id,
                'materiau_id' => $request->materiau_id,
            ]);

            if (!$stock->seuil_alerte) {
                $stock->seuil_alerte = 10; // Default threshold
            }

            $stock->updated_at = now();
            $stock->save();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Réception enregistrée avec succès.',
                    'mouvement' => $mouvement->load(['materiau', 'fournisseur']),
                    'stock' => $stock->load('materiau')
                ]);
            }
            return redirect()->route('stocks.index', $projet)
                    ->with('success', 'Réception enregistrée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Transfer materials to another project.
     */
    public function transfer(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'materiau_id' => 'required|exists:materiaux,id',
            'quantite' => 'required|numeric|min:0.001',
            'dest_projet_id' => 'required|exists:projets,id|different:projet.id',
            'phase_id' => 'nullable|exists:phases_projet,id',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if enough stock
        $stock = StockProjet::where('projet_id', $projet->id)
            ->where('materiau_id', $request->materiau_id)
            ->first();

        if (!$stock || $stock->quantite < $request->quantite) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant pour ce transfert.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create mouvement
            $mouvement = MouvementStock::create([
                'materiau_id' => $request->materiau_id,
                'effectue_par' => Auth::id(),
                'type' => 'transfert',
                'source_projet_id' => $projet->id,
                'dest_projet_id' => $request->dest_projet_id,
                'quantite' => $request->quantite,
                'phase_id' => $request->phase_id,
                'note' => $request->note,
            ]);

            // Decrease source stock
            $stock->quantite -= $request->quantite;
            $stock->save();

            // Increase destination stock
            $destStock = StockProjet::firstOrNew([
                'projet_id' => $request->dest_projet_id,
                'materiau_id' => $request->materiau_id,
            ]);
            $destStock->quantite += $request->quantite;
            if (!$destStock->seuil_alerte) {
                $destStock->seuil_alerte = $stock->seuil_alerte;
            }
            $destStock->updated_at = now();
            $destStock->save();

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transfert effectué avec succès.',
                    'mouvement' => $mouvement->load(['materiau', 'destProjet'])
                ]);
            }
            return redirect()->route('stocks.index', $projet)
                    ->with('success', 'Transfert effectué avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Consume materials (used in project).
     */
    public function consume(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'materiau_id' => 'required|exists:materiaux,id',
            'quantite' => 'required|numeric|min:0.001',
            'phase_id' => 'required|exists:phases_projet,id',
            'note' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check stock
        $stock = StockProjet::where('projet_id', $projet->id)
            ->where('materiau_id', $request->materiau_id)
            ->first();

        if (!$stock || $stock->quantite < $request->quantite) {
            return response()->json([
                'success' => false,
                'message' => 'Stock insuffisant.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create mouvement
            $mouvement = MouvementStock::create([
                'materiau_id' => $request->materiau_id,
                'effectue_par' => Auth::id(),
                'type' => 'consommation',
                'source_projet_id' => $projet->id,
                'quantite' => $request->quantite,
                'phase_id' => $request->phase_id,
                'note' => $request->note,
            ]);

            // Decrease stock
            $stock->quantite -= $request->quantite;
            $stock->save();

            DB::commit();

            
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Consommation enregistrée.',
                    'mouvement' => $mouvement->load(['materiau', 'phase'])
                ]);
            }
            return redirect()->route('stocks.index', $projet)
                    ->with('success', 'Consommation enregistrée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                             ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Update stock threshold.
     */
    public function updateThreshold(Request $request, StockProjet $stock)
    {
        $validator = Validator::make($request->all(), [
            'seuil_alerte' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $stock->seuil_alerte = $request->seuil_alerte;
            $stock->save();

            return response()->json([
                'success' => true,
                'message' => 'Seuil d\'alerte mis à jour.',
                'est_en_alerte' => $stock->est_en_alerte
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock history for a material.
     */
    public function history(Projet $projet, Materiau $materiau)
    {
        $movements = MouvementStock::where(function($q) use ($projet, $materiau) {
                $q->where('source_projet_id', $projet->id)
                  ->orWhere('dest_projet_id', $projet->id);
            })
            ->where('materiau_id', $materiau->id)
            ->with(['effectuePar', 'sourceProjet', 'destProjet', 'phase', 'fournisseur'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($movements);
    }

    /**
     * Create new material.
     */
    // public function createMaterial(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'nom' => 'required|string|max:255|unique:materiaux,nom',
    //         'unite' => 'required|string|max:50',
    //         'categorie' => 'nullable|string|max:100',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     try {
    //         $materiau = Materiau::create([
    //             'nom' => $request->nom,
    //             'unite' => $request->unite,
    //             'categorie' => $request->categorie,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Matériau créé avec succès.',
    //             'materiau' => $materiau
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erreur: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Export stock report.
     */
    public function export(Projet $projet, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'format' => 'required|in:pdf,excel,csv',
            'include_mouvements' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Export logic here
        return back()->with('success', 'Export en cours de génération...');
    }


    /**
 * Show receive form (full page).
 */
public function receiveForm(Request $request, Projet $projet)
{
    $stock      = StockProjet::where('projet_id', $projet->id)
                    ->with('materiau')->get()->keyBy('materiau_id');
    $materiaux  = Materiau::orderBy('nom')->get();
    $fournisseurs = Fournisseur::orderBy('nom')->get();

    return view('stocks.receive', [
        'projet'              => $projet,
        'stock'               => $stock,
        'materiaux'           => $materiaux,
        'fournisseurs'        => $fournisseurs,
        'preselectedMateriau' => $request->query('materiau_id'),
    ]);
}

/**
 * Show transfer form (full page).
 */
public function transferForm(Request $request, Projet $projet)
{
    $stock = StockProjet::where('projet_id', $projet->id)
               ->with('materiau')->get()->keyBy('materiau_id');

    $activeProjects = Projet::where('id', '!=', $projet->id)
                       ->whereIn('statut', ['debut', 'en_cours', 'verification'])
                       ->orderBy('nom')->get();

    $phases = PhaseProjet::where('projet_id', $projet->id)->orderBy('ordre')->get();

    return view('stocks.transfer', [
        'projet'              => $projet,
        'stock'               => $stock,
        'activeProjects'      => $activeProjects,
        'phases'              => $phases,
        'preselectedMateriau' => $request->query('materiau_id'),
    ]);
}

/**
 * Show consume form (full page).
 */
public function consumeForm(Request $request, Projet $projet)
{
    $stock  = StockProjet::where('projet_id', $projet->id)
                ->with('materiau')->get()->keyBy('materiau_id');
    $phases = PhaseProjet::where('projet_id', $projet->id)->orderBy('ordre')->get();

    return view('stocks.consume', [
        'projet'              => $projet,
        'stock'               => $stock,
        'phases'              => $phases,
        'preselectedMateriau' => $request->query('materiau_id'),
    ]);
}

/**
 * Show create material form (full page).
 */
public function createMaterialForm(Request $request)
{
    $projet = null;
    if ($request->query('projet_id')) {
        $projet = Projet::find($request->query('projet_id'));
    }
    return view('stocks.create-material', compact('projet'));
}

/**
 * Store new material (override existing method to handle redirect).
 */
public function createMaterial(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nom'               => 'required|string|max:255|unique:materiaux,nom',
        'unite'             => 'required|string|max:50',
        'categorie'         => 'nullable|string|max:100',
        'seuil_alerte'      => 'required|numeric|min:0',
        'quantite_initiale' => 'nullable|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    DB::beginTransaction();
    try {
        $materiau = Materiau::create([
            'nom'       => $request->nom,
            'unite'     => $request->unite,
            'categorie' => $request->categorie,
        ]);

        // If a projet_id was passed AND an initial qty, create a stock entry
        if ($request->filled('projet_id') && $request->quantite_initiale > 0) {
            StockProjet::create([
                'projet_id'    => $request->projet_id,
                'materiau_id'  => $materiau->id,
                'quantite'     => $request->quantite_initiale,
                'seuil_alerte' => $request->seuil_alerte,
            ]);
        }

        DB::commit();

        $redirect = $request->filled('projet_id')
            ? route('stocks.index', $request->projet_id)
            : route('projets.index');

        return redirect($redirect)
            ->with('success', 'Matériau "' . $materiau->nom . '" créé avec succès.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->with('error', 'Erreur lors de la création : ' . $e->getMessage());
    }
}
}
