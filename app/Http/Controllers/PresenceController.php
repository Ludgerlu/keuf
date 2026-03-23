<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\DetailPresence;
use App\Models\Projet;
use App\Models\Employe;
use App\Models\AffectationProjet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PresenceController extends Controller
{
    /**
     * Affiche la page de gestion des présences pour un projet à une date donnée.
     * Charge la session (entête) du jour si elle existe déjà.
     */
    public function index(Projet $projet, Request $request)
    {
        $date = $request->date ? $request->date : today()->toDateString();

        // Session de pointage du jour pour ce projet (entête)
        $session = Presence::where('projet_id', $projet->id)
            ->whereDate('date', $date)
            ->with(['details.employe', 'saisiePar'])
            ->first();

        // Détails indexés par employe_id pour accès rapide dans la vue
        $detailsParEmploye = $session
            ? $session->details->keyBy('employe_id')
            : collect();

        // Tous les employés affectés au projet (actifs)
        $assignedEmployees = Employe::whereHas('affectations', function ($q) use ($projet) {
            $q->where('projet_id', $projet->id)
              ->whereNull('date_fin');
        })->actifs()->get();

        $stats = [
            'total'        => $detailsParEmploye->count(),
            'present'      => $detailsParEmploye->where('statut', 'present')->count(),
            'absent'       => $detailsParEmploye->where('statut', 'absent')->count(),
            'demi_journee' => $detailsParEmploye->where('statut', 'demi_journee')->count(),
        ];

        return view('presences.index', compact(
            'projet',
            'session',
            'detailsParEmploye',
            'assignedEmployees',
            'stats',
            'date'
        ));
    }

    /**
     * Crée ou met à jour la session de pointage et tous ses détails employés.
     * Une seule session par projet par jour (uq_presence_session).
     * Les détails sont upsertés individuellement (uq_detail_presence).
     */
    public function store(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'date'                          => 'sometimes|date|before_or_equal:today',
            'note_session'                  => 'nullable|string|max:500',
            'presences'                     => 'required|array|min:1',
            'presences.*.employe_id'        => 'required|exists:employes,id',
            'presences.*.statut'            => 'required|in:present,absent,demi_journee',
            'presences.*.heure_debut'       => 'nullable|date_format:H:i',
            'presences.*.heure_fin'         => 'nullable|date_format:H:i|after:presences.*.heure_debut',
            'presences.*.note'              => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date ?: today()->toDateString();

        DB::beginTransaction();
        try {
            // 1. Créer ou récupérer la session de pointage (entête)
            $session = Presence::updateOrCreate(
                [
                    'projet_id' => $projet->id,
                    'date'      => $date,
                ],
                [
                    'saisie_par' => Auth::id(),
                    'note'       => $request->note_session ?? null,
                ]
            );

            // 2. Upsert des détails par employé
            foreach ($request->presences as $data) {
                DetailPresence::updateOrCreate(
                    [
                        'presence_id' => $session->id,
                        'employe_id'  => $data['employe_id'],
                    ],
                    [
                        'statut'      => $data['statut'],
                        'heure_debut' => $data['heure_debut'] ?? null,
                        'heure_fin'   => $data['heure_fin']   ?? null,
                        'note'        => $data['note']        ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Présences enregistrées avec succès.',
                'stats'   => $this->getSessionStats($session->id),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Historique des sessions de pointage d'un projet.
     * Chaque session inclut ses détails employés.
     */
    public function history(Projet $projet, Request $request)
    {
        $query = Presence::where('projet_id', $projet->id)
            ->with(['details.employe', 'saisiePar']);

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // Filtrer par employé : on passe par les détails
        if ($request->filled('employe_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('employe_id', $request->employe_id);
            });
        }

        $sessions = $query->orderBy('date', 'desc')->paginate(20);

        if ($request->ajax()) {
            return response()->json($sessions);
        }

        // Liste des employés ayant au moins un détail sur ce projet
        $employes = Employe::whereHas('detailPresences.presence', function ($q) use ($projet) {
            $q->where('projet_id', $projet->id);
        })->get();

        return view('presences.history', compact('projet', 'sessions', 'employes'));
    }

    /**
     * Mise à jour d'un détail de présence individuel (statut, heures, note).
     */
    public function updateDetail(Request $request, DetailPresence $detail)
    {
        $this->authorize('update', $detail->presence);

        $validator = Validator::make($request->all(), [
            'statut'      => 'required|in:present,absent,demi_journee',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin'   => 'nullable|date_format:H:i|after:heure_debut',
            'note'        => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $detail->update($request->only(['statut', 'heure_debut', 'heure_fin', 'note']));

            return response()->json([
                'success' => true,
                'message' => 'Présence mise à jour.',
                'detail'  => $detail->load('employe'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime une session de pointage complète (cascade sur les détails).
     */
    public function destroy(Presence $presence)
    {
        $this->authorize('delete', $presence);

        try {
            // Les détails sont supprimés en cascade (ON DELETE CASCADE en BDD)
            $presence->delete();

            return response()->json([
                'success' => true,
                'message' => 'Session de présence supprimée.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Supprime un détail de présence individuel sans toucher la session.
     */
    public function destroyDetail(DetailPresence $detail)
    {
        $this->authorize('delete', $detail->presence);

        try {
            $detail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Ligne de présence supprimée.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export des présences sur une période.
     */
    public function export(Projet $projet, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from'   => 'required|date',
            'to'     => 'required|date|after_or_equal:from',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // TODO: implémenter avec Laravel Excel ou Barryvdh PDF
        return back()->with('success', 'Export en cours de génération...');
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    /**
     * Statistiques d'une session depuis ses détails.
     */
    private function getSessionStats(int $sessionId): array
    {
        $details = DetailPresence::where('presence_id', $sessionId)->get();

        return [
            'total'        => $details->count(),
            'present'      => $details->where('statut', 'present')->count(),
            'absent'       => $details->where('statut', 'absent')->count(),
            'demi_journee' => $details->where('statut', 'demi_journee')->count(),
        ];
    }
}