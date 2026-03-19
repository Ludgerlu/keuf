<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Projet;
use App\Models\Employe;
use App\Models\AffectationProjet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class PresenceController extends Controller
{
    /**
     * Display presence management page for a specific project.
     */
    public function index(Projet $projet)
    {
        // Get today's presences
        $todayPresences = Presence::where('projet_id', $projet->id)
            ->whereDate('date', today())
            ->with(['employe', 'saisiePar'])
            ->get()
            ->keyBy('employe_id');

        // Get all employees assigned to this project (active assignments)
        $assignedEmployees = Employe::get();

        // Get external prestataires (unique list)
        $prestataires = [];


        // Get prestataire employees
        $prestataireEmployees = [];

        $stats = [
            'total' => $todayPresences->count(),
            'present' => $todayPresences->where('statut', 'present')->count(),
            'absent' => $todayPresences->where('statut', 'absent')->count(),
            'retard' => $todayPresences->where('statut', 'en_retard')->count(),
        ];

        return view('presences.index', compact(
            'projet',
            'todayPresences',
            'assignedEmployees',
            'prestataires',
            'prestataireEmployees',
            'stats'
        ));
    }

    /**
     * Store or update presences for a project.
     */
    public function store(Request $request, Projet $projet)
    {
        $validator = Validator::make($request->all(), [
            'presences' => 'required|array',
            'presences.*.employe_id' => 'required|exists:employes,id',
            'presences.*.statut' => 'required|in:present,absent,en_retard,absent_justifie',
            'presences.*.heure_debut' => 'nullable|date_format:H:i',
            'presences.*.heure_fin' => 'nullable|date_format:H:i|after:presences.*.heure_debut',
            'presences.*.heures_sup' => 'nullable|numeric|min:0|max:24',
            'presences.*.note' => 'nullable|string|max:255',
            'date' => 'sometimes|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $date = $request->date ?: today();

        DB::beginTransaction();
        try {
            foreach ($request->presences as $presenceData) {
                Presence::updateOrCreate(
                    [
                        'projet_id' => $projet->id,
                        'employe_id' => $presenceData['employe_id'],
                        'date' => $date,
                    ],
                    [
                        'saisie_par' => Auth::id(),
                        'heure_debut' => $presenceData['heure_debut'] ?? null,
                        'heure_fin' => $presenceData['heure_fin'] ?? null,
                        'statut' => $presenceData['statut'],
                        'heures_sup' => $presenceData['heures_sup'] ?? 0,
                        'note' => $presenceData['note'] ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Présences enregistrées avec succès.',
                'stats' => $this->getPresenceStats($projet->id, $date)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get presence history for a project.
     */
    public function history(Projet $projet, Request $request)
    {
        $query = Presence::where('projet_id', $projet->id)
            ->with(['employe', 'saisiePar']);

        // Filter by date range
        if ($request->has('from')) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->has('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        // Filter by employee
        if ($request->has('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        $presences = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($request->ajax()) {
            return response()->json($presences);
        }

        $employes = Employe::whereHas('presences', function($q) use ($projet) {
            $q->where('projet_id', $projet->id);
        })->get();

        return view('presences.history', compact('projet', 'presences', 'employes'));
    }

    /**
     * Update a specific presence record.
     */
    public function update(Request $request, Presence $presence)
    {
        $this->authorize('update', $presence);

        $validator = Validator::make($request->all(), [
            'statut' => 'required|in:present,absent,en_retard,absent_justifie',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i|after:heure_debut',
            'heures_sup' => 'nullable|numeric|min:0|max:24',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $presence->update($request->only([
                'statut', 'heure_debut', 'heure_fin', 'heures_sup', 'note'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Présence mise à jour.',
                'presence' => $presence->load('employe', 'saisiePar')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a presence record.
     */
    public function destroy(Presence $presence)
    {
        $this->authorize('delete', $presence);

        try {
            $presence->delete();
            return response()->json([
                'success' => true,
                'message' => 'Présence supprimée.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get presence statistics.
     */
    private function getPresenceStats($projetId, $date)
    {
        $presences = Presence::where('projet_id', $projetId)
            ->whereDate('date', $date)
            ->get();

        return [
            'total' => $presences->count(),
            'present' => $presences->where('statut', 'present')->count(),
            'absent' => $presences->where('statut', 'absent')->count(),
            'en_retard' => $presences->where('statut', 'en_retard')->count(),
            'absent_justifie' => $presences->where('statut', 'absent_justifie')->count(),
        ];
    }

    /**
     * Export presence report.
     */
    public function export(Projet $projet, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        // Export logic here (using Laravel Excel or similar)
        // This would generate a report of presences for the period
        
        return back()->with('success', 'Export en cours de génération...');
    }
}
