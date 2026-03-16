<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user account is active
            if (!$user->actif) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'email' => ['Votre compte est inactif. Veuillez contacter l\'administrateur.'],
                ]);
            }

            // Update last connection timestamp
            $user->derniere_connexion = now();
            $user->save();

            $request->session()->regenerate();
            
            // Log the login action (optional - if you have LogActivite model)
            // $this->logLoginActivity($user);
            
            return redirect()->intended('dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['Les identifiants fournis ne correspondent pas à nos enregistrements.'],
        ]);
    }

    /**
     * Redirect user to their appropriate dashboard
     */
    public function redirectToDashboard()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return redirect()->route($this->getUserDashboardRoute($user->role));
        }

        return redirect()->route('login');
    }

    /**
     * Get dashboard route based on user role
     */
    private function getUserDashboardRoute($userRole)
    {
        if (!$userRole) {
            return 'login';
        }

        // Mapping des rôles KEUF Engineering vers les routes de dashboard
        $roleRoutes = [
            'admin' => 'admin.dashboard',
            'directeur_technique' => 'dt.dashboard',
            'controleur' => 'controleur.dashboard',
            'gestionnaire_stock' => 'stock.dashboard',
        ];

        return $roleRoutes[$userRole] ?? 'dashboard';
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        
        // Log the logout action (optional)
        // if ($user) {
        //     $this->logLogoutActivity($user);
        // }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login');
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Get user permissions
        $permissions = $user->permissions()->with('projet')->get();
        
        // Get recent activities (if using LogActivite model)
        // $recentActivities = $user->logsActivites()->latest()->take(10)->get();

        return view('profile.profil', [
            'user' => $user,
            'userRole' => $user->role,
            'permissions' => $permissions,
            // 'recentActivities' => $recentActivities,
            'dashboardRoute' => $this->getUserDashboardRoute($user->role),
        ]);
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        return view('profile.password', [
            'user' => $user,
            'userRole' => $user->role,
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // Validation rules with password complexity requirements
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
            ],
        ], [
            'new_password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&).',
            'new_password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'new_password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Le mot de passe actuel est incorrect.'
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Log password change (optional)
        // $this->logPasswordChange($user);

        return back()->with('success', 'Mot de passe modifié avec succès!');
    }

    /**
     * Reset password to default (admin function - should be protected by middleware)
     */
    public function resetPassword(User $user)
    {
        // Check if current user has permission to reset passwords
        // This should be protected by middleware or policy
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $defaultPassword = 'Keuf@2026'; // Or generate random password
        
        $user->password = Hash::make($defaultPassword);
        $user->save();

        return back()->with('success', "Mot de passe réinitialisé avec succès! Nouveau mot de passe: {$defaultPassword}");
    }

    /**
     * Optional: Log login activity
     */
    private function logLoginActivity($user)
    {
        // If you have the LogActivite model
        // LogActivite::create([
        //     'utilisateur_id' => $user->id,
        //     'action' => 'LOGIN',
        //     'table_cible' => 'utilisateurs',
        //     'enregistrement_id' => $user->id,
        //     'ip' => request()->ip(),
        //     'created_at' => now(),
        // ]);
    }

    /**
     * Optional: Log logout activity
     */
    private function logLogoutActivity($user)
    {
        // If you have the LogActivite model
        // LogActivite::create([
        //     'utilisateur_id' => $user->id,
        //     'action' => 'LOGOUT',
        //     'table_cible' => 'utilisateurs',
        //     'enregistrement_id' => $user->id,
        //     'ip' => request()->ip(),
        //     'created_at' => now(),
        // ]);
    }
}
