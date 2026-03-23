<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employe;
use App\Models\Permission;
use App\Models\Projet;
use App\Models\User;
use App\Models\AffectationProjet;
use App\Models\DetailPresence;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeController extends Controller
{
    /**
     * Liste des employés avec filtres et stats.
     */
    public function index(Request $request)
    {
        $query = Employe::query();

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut') && $request->statut !== 'all') {
            $query->where('actif', $request->statut === 'actif');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('prenom', 'like', "%{$search}%")
                  ->orWhere('role_metier', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        // Compte les détails de présence (via detail_presences, pas presences directement)
        $employes = $query->withCount('detailPresences')->get();

        $stats = [
            'total'               => Employe::count(),
            'actifs'              => Employe::where('actif', true)->count(),
            'internes'            => Employe::where('type', 'interne')->count(),
            'sous_traitants'      => Employe::where('type', 'sous_traitant')->count(),
            // Présents aujourd'hui : on cherche dans detail_presences via les sessions du jour
            'presences_aujourdhui' => DetailPresence::where('statut', 'present')
                ->whereHas('presence', fn($q) => $q->whereDate('date', today()))
                ->count(),
            'inactifs'            => Employe::where('actif', false)->count(),
        ];

        if ($request->ajax()) {
            return response()->json(['employes' => $employes, 'stats' => $stats]);
        }

        return view('employes.index', compact('employes', 'stats'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $projets  = Projet::chantiers()->get();
        $modules  = Permission::getModules();
        $actions  = Permission::getActions();

        return view('employes.create', compact('projets', 'modules', 'actions'));
    }

    /**
     * Enregistre un nouvel employé.
     * Si des permissions sont fournies, crée aussi un compte utilisateur.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom'          => 'required|string|max:100',
            'prenom'       => 'required|string|max:100',
            'telephone'    => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255|unique:employes,email',
            'type'         => 'required|in:interne,sous_traitant',
            'role_metier'  => 'nullable|string|max:100',
            'date_embauche'=> 'nullable|date',
            'notes'        => 'nullable|string',
            'actif'        => 'boolean',
            'permissions'  => 'nullable|array',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $employe = Employe::create([
                'nom'          => $request->nom,
                'prenom'       => $request->prenom,
                'telephone'    => $request->telephone,
                'email'        => $request->email,
                'type'         => $request->type,
                'role_metier'  => $request->role_metier,
                'date_embauche'=> $request->date_embauche,
                'notes'        => $request->notes,
                'actif'        => $request->boolean('actif', true),
            ]);

            // Si des permissions sont fournies → créer un compte utilisateur lié
            if ($request->filled('permissions') && $request->type === 'interne') {
                $user = User::create([
                    'nom'      => $request->nom,
                    'prenom'   => $request->prenom,
                    'email'    => $request->email ?? $this->generateEmail($request->prenom, $request->nom),
                    'password' => bcrypt('password'), // à changer à la première connexion
                    'role'     => $this->determineRoleFromPermissions($request->permissions),
                    'actif'    => true,
                ]);

                $this->syncPermissions($user->id, $request->permissions);
            }

            DB::commit();

            $message = 'Employé créé avec succès.';

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => $message, 'employe' => $employe]);
            }

            return redirect()->route('employes.show', $employe)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            $message = 'Erreur : ' . $e->getMessage();

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 500);
            }

            return back()->with('error', $message)->withInput();
        }
    }

    /**
     * Détail d'un employé avec ses présences récentes (via detail_presences)
     * et ses projets en cours.
     */
    public function show(Employe $employe)
    {
        $employe->load([
            'affectations.projet',
        ]);

        // 30 derniers détails de présence avec la session parente (date, projet)
        $derniersDetails = $employe->detailPresences()
            ->with(['presence.projet'])
            ->whereHas('presence', fn($q) => $q->latest('date'))
            ->latest('created_at')
            ->take(30)
            ->get();

        // Statistiques de présence depuis les détails
        $statsPresences = [
            'total'        => $employe->detailPresences()->count(),
            'present'      => $employe->detailPresences()->where('statut', 'present')->count(),
            'absent'       => $employe->detailPresences()->where('statut', 'absent')->count(),
            'demi_journee' => $employe->detailPresences()->where('statut', 'demi_journee')->count(),
        ];

        // Compte utilisateur lié (même email)
        $user = $employe->email
            ? User::where('email', $employe->email)->first()
            : null;

        $permissions = $user
            ? Permission::where('utilisateur_id', $user->id)->with('projet')->get()->groupBy('module')
            : collect();

        $projetsEnCours = $employe->affectations()
            ->whereNull('date_fin')
            ->with('projet')
            ->get();

        return view('employes.show', compact(
            'employe',
            'derniersDetails',
            'statsPresences',
            'permissions',
            'projetsEnCours',
            'user'
        ));
    }

    /**
     * Formulaire d'édition.
     */
    public function edit(Employe $employe)
    {
        $projets = Projet::chantiers()->get();
        $modules = Permission::getModules();
        $actions = Permission::getActions();

        $user = $employe->email
            ? User::where('email', $employe->email)->first()
            : null;

        $permissions = $user
            ? Permission::where('utilisateur_id', $user->id)
                ->get()
                ->keyBy(fn($item) => $item->module . '_' . $item->action . '_' . ($item->projet_id ?? 'global'))
            : collect();

        return view('employes.edit', compact('employe', 'projets', 'modules', 'actions', 'permissions', 'user'));
    }

    /**
     * Met à jour un employé et ses permissions si un compte utilisateur existe.
     */
    public function update(Request $request, Employe $employe)
    {
        $validator = Validator::make($request->all(), [
            'nom'          => 'required|string|max:100',
            'prenom'       => 'required|string|max:100',
            'telephone'    => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255|unique:employes,email,' . $employe->id,
            'type'         => 'required|in:interne,sous_traitant',
            'role_metier'  => 'nullable|string|max:100',
            'date_embauche'=> 'nullable|date',
            'notes'        => 'nullable|string',
            'actif'        => 'boolean',
            'permissions'  => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $employe->update([
                'nom'          => $request->nom,
                'prenom'       => $request->prenom,
                'telephone'    => $request->telephone,
                'email'        => $request->email,
                'type'         => $request->type,
                'role_metier'  => $request->role_metier,
                'date_embauche'=> $request->date_embauche,
                'notes'        => $request->notes,
                'actif'        => $request->boolean('actif', $employe->actif),
            ]);

            // Sync permissions si un compte utilisateur est lié
            if ($request->filled('permissions')) {
                $user = $employe->email
                    ? User::where('email', $employe->email)->first()
                    : null;

                if ($user) {
                    Permission::where('utilisateur_id', $user->id)->delete();
                    $this->syncPermissions($user->id, $request->permissions);
                }
            }

            DB::commit();

            return redirect()->route('employes.show', $employe)
                ->with('success', 'Employé mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Supprime un employé (SoftDelete).
     * Refusé si des présences récentes existent dans detail_presences.
     */
    public function destroy(Employe $employe)
    {
        try {
            $aDesPresencesRecentes = $employe->detailPresences()
                ->whereHas('presence', fn($q) => $q->where('date', '>=', now()->subDays(30)))
                ->exists();

            if ($aDesPresencesRecentes) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer un employé avec des présences dans les 30 derniers jours.',
                ], 422);
            }

            // Supprimer le compte utilisateur lié si existant
            if ($employe->email) {
                User::where('email', $employe->email)->first()?->delete();
            }

            $employe->delete();

            return response()->json(['success' => true, 'message' => 'Employé supprimé avec succès.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Affecte un employé à un projet.
     */
    public function assignProject(Request $request, Employe $employe)
    {
        $validator = Validator::make($request->all(), [
            'projet_id'       => 'required|exists:projets,id',
            'role_sur_projet' => 'nullable|string|max:100',
            'date_debut'      => 'required|date',
            'date_fin'        => 'nullable|date|after:date_debut',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $affectation = AffectationProjet::create([
            'projet_id'       => $request->projet_id,
            'employe_id'      => $employe->id,
            'role_sur_projet' => $request->role_sur_projet ?? $employe->role_metier,
            'date_debut'      => $request->date_debut,
            'date_fin'        => $request->date_fin,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'Employé affecté au projet avec succès.',
            'affectation' => $affectation->load('projet'),
        ]);
    }

    /**
     * Historique des présences d'un employé (via detail_presences).
     */
    public function getPresences(Employe $employe, Request $request)
    {
        $query = $employe->detailPresences()
            ->with(['presence.projet']);

        if ($request->filled('mois')) {
            $query->whereHas('presence', function ($q) use ($request) {
                $q->whereYear('date', substr($request->mois, 0, 4))
                  ->whereMonth('date', substr($request->mois, 5, 2));
            });
        }

        $details = $query->latest('created_at')->paginate(30);

        return response()->json($details);
    }

    // -------------------------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------------------------

    /**
     * Insère en lot les permissions pour un utilisateur.
     */
    private function syncPermissions(int $userId, array $permissions): void
    {
        $rows = [];
        $now  = now();

        foreach ($permissions as $perm => $modules) {
            if (! is_array($modules)) {
                continue;
            }
            foreach ($modules as $module => $actions) {
                if (! is_array($actions)) {
                    continue;
                }
                foreach ($actions as $action => $details) {
                    $rows[] = [
                        'utilisateur_id' => $userId,
                        'module'         => $perm,
                        'action'         => $module,
                        'projet_id'      => $details['projet_id'] ?? null,
                        'autorise'       => $details['autorise']  ?? true,
                        'accorde_par'    => Auth::id(),
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            Permission::insert($rows);
        }
    }

    /**
     * Génère un email unique @keuf.cm depuis prénom.nom.
     */
    private function generateEmail(string $prenom, string $nom): string
    {
        $base    = strtolower(Str::ascii("{$prenom}.{$nom}")) . '@keuf.cm';
        $email   = $base;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $email = str_replace('@keuf.cm', "{$counter}@keuf.cm", $base);
            $counter++;
        }

        return $email;
    }

    /**
     * Détermine le rôle utilisateur à partir des modules accordés.
     */
    private function determineRoleFromPermissions(array $permissions): string
    {
        $modules = collect($permissions)->keys();

        if ($modules->contains('all')) {
            return User::ROLE_ADMIN;
        }
        if ($modules->contains('stocks') && $modules->count() === 1) {
            return User::ROLE_GESTIONNAIRE_STOCK;
        }
        if ($modules->contains('presences') && $modules->contains('signalements')) {
            return User::ROLE_CONTROLEUR;
        }
        if ($modules->contains('projets') && $modules->contains('employes')) {
            return User::ROLE_DIRECTEUR_TECHNIQUE;
        }

        return User::ROLE_CONTROLEUR;
    }
}