<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employe;
use App\Models\Permission;
use App\Models\Projet;
use App\Models\User;
use App\Models\AffectationProjet;
use App\Models\Presence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeController extends Controller
{
    /**
     * Display a listing of employees.
     */
    public function index(Request $request)
    {
        $query = Employe::query();

        // Filter by type
        if ($request->has('type') && $request->type != 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('statut') && $request->statut != 'all') {
            $query->where('actif', $request->statut === 'actif');
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('role_metier', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $employes = $query->withCount('presences')->get();

        // Statistiques
        $stats = [
            'total' => Employe::count(),
            'actifs' => Employe::where('actif', true)->count(),
            'internes' => Employe::where('type', 'interne')->count(),
            'sous_traitants' => Employe::where('type', 'sous_traitant')->count(),
            'presences_aujourdhui' => Presence::whereDate('date', today())
                ->where('statut', 'present')
                ->count(),
            'en_conge' => Employe::where('actif', false)->count(), // You might want a specific field for congé
        ];

        if ($request->ajax()) {
            return response()->json([
                'employes' => $employes,
                'stats' => $stats
            ]);
        }

        return view('employes.index', compact('employes', 'stats'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        $projets = Projet::where('est_stock_central', 0)->get();
        $modules = Permission::getModules();
        $actions = Permission::getActions();

        return view('employes.create', compact('projets', 'modules', 'actions'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:employes,email',
            'type' => 'required|in:interne,sous_traitant',
            'role_metier' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'date_embauche' => 'nullable|date',
            'adresse' => 'nullable|string',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_telephone' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
            'avatar_couleur' => 'nullable|string',
            'actif' => 'boolean',
            'permissions' => 'nullable|array',
            // 'permissions.*.module' => 'required|in:projets,employes,stocks,presences,signalements,facturation,imports',
            // 'permissions.*.action' => 'required|in:creer,lire,modifier,supprimer,exporter',
            // 'permissions.*.projet_id' => 'nullable|exists:projets,id',
            // 'permissions.*.autorise' => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            
            // Créer l'employé
            $employe = Employe::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'type' => $request->type,
                'role_metier' => $request->role_metier,
                // 'date_naissance' => $request->date_naissance,
                // 'adresse' => $request->adresse,
                // 'urgence_nom' => $request->urgence_nom,
                // 'urgence_telephone' => $request->urgence_telephone,
                'notes' => $request->notes,
                'actif' => $request->has('actif') ? $request->actif : true,
            ]);

            // Créer les permissions si l'employé est aussi un utilisateur système
            if ($request->has('permissions') && $request->type === 'interne') {
                // Créer un compte utilisateur pour cet employé
                $user = User::create([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'email' => $request->email ?? $this->generateEmail($request->prenom, $request->nom),
                    'password' => bcrypt('password'), // Mot de passe par défaut
                    'role' => $this->determineRoleFromPermissions($request->permissions),
                    'actif' => true,
                ]);

                // dd($request->permissions);
                // Assigner les permissions
                if (!empty($request->permissions) && is_array($request->permissions)) {
                    $permissions = [];
                    $now = now();
                    $userId = Auth::id();
                    
                    foreach ($request->permissions as $perm => $modules) {
                        if (is_array($modules)) {
                            foreach ($modules as $module => $actions) {
                                if (is_array($actions)) {
                                    foreach ($actions as $action => $details) {
                                        $permissions[] = [
                                            'utilisateur_id' => $user->id,
                                            'module' => $perm,
                                            'action' => $module,
                                            'projet_id' => $details['projet_id'] ?? null,
                                            'autorise' => $details['autorise'] ?? true,
                                            'accorde_par' => $userId,
                                            'created_at' => $now,
                                            'updated_at' => $now,
                                        ];
                                    }
                                }
                            }
                        }
                    }
                    
                    // Insertion en lot seulement s'il y a des permissions
                    if (!empty($permissions)) {
                        Permission::insert($permissions);
                    }
                }

                $employe->id = $user->id;
                $employe->save();
            }

            DB::commit();

            $message = 'Employé créé avec succès.';
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'employe' => $employe
                ]);
            }

            return redirect()->route('employes.show', $employe)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $message = 'Erreur: ' . $e->getMessage();
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }
            return back()->with('error', $message)->withInput();
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employe $employe)
    {
        $employe->load([
            'affectations' => function($q) {
                $q->with('projet')->latest('date_debut');
            },
            'presences' => function($q) {
                $q->with('projet')->latest('date')->take(30);
            }
        ]);

        // Récupérer les permissions si l'employé a un compte utilisateur
        $permissions = [];
        if ($employe->id) {
            $permissions = Permission::where('utilisateur_id', $employe->id)
                ->with('projet')
                ->get()
                ->groupBy('module');
        }

        // dd($permissions);
        // Statistiques de présence
        $statsPresences = [
            'total' => $employe->presences->count(),
            'present' => $employe->presences->where('statut', 'present')->count(),
            'absent' => $employe->presences->where('statut', 'absent')->count(),
            'demi_journee' => $employe->presences->where('statut', 'demi_journee')->count(),
        ];

        // Projets en cours
        $projetsEnCours = $employe->affectations()
            ->whereNull('date_fin')
            ->with('projet')
            ->get();

        return view('employes.show', compact('employe', 'permissions', 'statsPresences', 'projetsEnCours'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employe $employe)
    {
        $projets = Projet::where('est_stock_central', 0)->get();
        $modules = Permission::getModules();
        $actions = Permission::getActions();

        // Récupérer les permissions existantes
        $permissions = [];
        if ($employe->user_id) {
            $permissions = Permission::where('utilisateur_id', $employe->user_id)
                ->get()
                ->keyBy(function($item) {
                    return $item->module . '_' . $item->action . '_' . ($item->projet_id ?? 'global');
                });
        }

        return view('employes.edit', compact('employe', 'projets', 'modules', 'actions', 'permissions'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employe $employe)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255|unique:employes,email,' . $employe->id,
            'type' => 'required|in:interne,sous_traitant',
            'role_metier' => 'nullable|string|max:100',
            'date_naissance' => 'nullable|date',
            'date_embauche' => 'nullable|date',
            'adresse' => 'nullable|string',
            'urgence_nom' => 'nullable|string|max:255',
            'urgence_telephone' => 'nullable|string|max:30',
            'notes' => 'nullable|string',
            'avatar_couleur' => 'nullable|string',
            'actif' => 'boolean',
            'permissions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            // Mettre à jour l'employé
            $employe->update([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'telephone' => $request->telephone,
                'email' => $request->email,
                'type' => $request->type,
                'role_metier' => $request->role_metier,
                'date_naissance' => $request->date_naissance,
                'date_embauche' => $request->date_embauche,
                'adresse' => $request->adresse,
                'urgence_nom' => $request->urgence_nom,
                'urgence_telephone' => $request->urgence_telephone,
                'notes' => $request->notes,
                'avatar_couleur' => $request->avatar_couleur ?? $employe->avatar_couleur,
                'actif' => $request->has('actif') ? $request->actif : $employe->actif,
            ]);

            // Mettre à jour les permissions si nécessaire
            if ($request->has('permissions') && $employe->user_id) {
                // Supprimer les anciennes permissions
                Permission::where('utilisateur_id', $employe->user_id)->delete();

                // Créer les nouvelles
                foreach ($request->permissions as $perm) {
                    Permission::create([
                        'utilisateur_id' => $employe->user_id,
                        'module' => $perm['module'],
                        'action' => $perm['action'],
                        'projet_id' => $perm['projet_id'] ?? null,
                        'autorise' => $perm['autorise'] ?? true,
                        'accorde_par' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('employes.show', $employe)
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employe $employe)
    {
        try {
            // Vérifier s'il y a des présences récentes
            if ($employe->presences()->where('date', '>=', now()->subDays(30))->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un employé avec des présences récentes.'
                ], 422);
            }

            // Supprimer l'utilisateur associé si existe
            if ($employe->user_id) {
                User::find($employe->user_id)?->delete();
            }

            $employe->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employé supprimé avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign employee to a project.
     */
    public function assignProject(Request $request, Employe $employe)
    {
        $validator = Validator::make($request->all(), [
            'projet_id' => 'required|exists:projets,id',
            'role_sur_projet' => 'nullable|string|max:100',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $affectation = AffectationProjet::create([
            'projet_id' => $request->projet_id,
            'employe_id' => $employe->id,
            'role_sur_projet' => $request->role_sur_projet ?? $employe->role_metier,
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Employé affecté au projet avec succès.',
            'affectation' => $affectation->load('projet')
        ]);
    }

    /**
     * Get employee presence history.
     */
    public function getPresences(Employe $employe, Request $request)
    {
        $query = $employe->presences()->with('projet');

        if ($request->has('mois')) {
            $query->whereYear('date', substr($request->mois, 0, 4))
                  ->whereMonth('date', substr($request->mois, 5, 2));
        }

        $presences = $query->latest('date')->paginate(30);

        return response()->json($presences);
    }

    /**
     * Generate a unique email for the employee.
     */
    private function generateEmail($prenom, $nom)
    {
        $base = strtolower(Str::ascii($prenom . '.' . $nom)) . '@keuf.cm';
        $email = $base;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = str_replace('@keuf.cm', $counter . '@keuf.cm', $base);
            $counter++;
        }

        return $email;
    }

    /**
     * Determine user role based on permissions.
     */
    private function determineRoleFromPermissions($permissions)
    {
        // Logique pour déterminer le rôle en fonction des permissions
        $hasFullAccess = collect($permissions)->where('module', 'all')->isNotEmpty();
        
        if ($hasFullAccess) {
            return 'admin';
        }

        $modules = collect($permissions)->pluck('module')->unique();
        
        if ($modules->contains('stocks') && $modules->count() === 1) {
            return 'gestionnaire_stock';
        }

        if ($modules->contains('presences') && $modules->contains('signalements')) {
            return 'controleur';
        }

        if ($modules->contains('projets') && $modules->contains('employes')) {
            return 'directeur_technique';
        }

        return 'controleur'; // Rôle par défaut
    }
}
