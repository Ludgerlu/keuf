<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    protected $table = 'utilisateurs';
    
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'password',
        'role',
        'actif',
        'derniere_connexion',
        'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'derniere_connexion' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    // Roles constants
    const ROLE_ADMIN = 'admin';
    const ROLE_DIRECTEUR_TECHNIQUE = 'directeur_technique';
    const ROLE_CONTROLEUR = 'controleur';
    const ROLE_GESTIONNAIRE_STOCK = 'gestionnaire_stock';

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'utilisateur_id');
    }

    public function hasPermission($module, $action, $projetId = null)
    {
        // Admin has all permissions
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        // Check specific permission
        $query = $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->where('autorise', true);

        if ($projetId) {
            $query->where(function($q) use ($projetId) {
                $q->where('projet_id', $projetId)
                  ->orWhereNull('projet_id');
            });
        } else {
            $query->whereNull('projet_id');
        }

        return $query->exists();
    }

    
    // Relations
    public function grantedPermissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'accorde_par');
    }

    public function presencesSaisies(): HasMany
    {
        return $this->hasMany(Presence::class, 'saisie_par');
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class, 'auteur_id');
    }

    public function remarquesPhases(): HasMany
    {
        return $this->hasMany(RemarquePhase::class, 'auteur_id');
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'effectue_par');
    }

    public function logsActivites(): HasMany
    {
        return $this->hasMany(LogActivite::class, 'utilisateur_id');
    }

    public function historiquePhases(): HasMany
    {
        return $this->hasMany(HistoriquePhase::class, 'change_par');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
    public function getInitialsAttribute()
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }
}