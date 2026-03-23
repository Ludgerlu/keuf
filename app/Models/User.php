<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'actif'             => 'boolean',
        'derniere_connexion' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Constantes de rôles
    // -------------------------------------------------------------------------

    const ROLE_ADMIN               = 'admin';
    const ROLE_DIRECTEUR_TECHNIQUE = 'directeur_technique';
    const ROLE_CONTROLEUR          = 'controleur';
    const ROLE_GESTIONNAIRE_STOCK  = 'gestionnaire_stock';

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'utilisateur_id');
    }

    public function permissionsAccordees(): HasMany
    {
        return $this->hasMany(Permission::class, 'accorde_par');
    }

    /**
     * Sessions de pointage saisies par cet utilisateur (entêtes presences).
     */
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

    // -------------------------------------------------------------------------
    // Gestion des permissions
    // -------------------------------------------------------------------------

    /**
     * Vérifie si l'utilisateur a une permission sur un module/action.
     * L'admin a toujours accès à tout.
     * Si projet_id est passé, on cherche d'abord une permission spécifique au
     * projet, sinon on accepte une permission globale (projet_id = NULL).
     */
    public function hasPermission(string $module, string $action, ?int $projetId = null): bool
    {
        if ($this->role === self::ROLE_ADMIN) {
            return true;
        }

        $query = $this->permissions()
            ->where('module', $module)
            ->where('action', $action)
            ->where('autorise', true);

        if ($projetId) {
            $query->where(function ($q) use ($projetId) {
                $q->where('projet_id', $projetId)
                  ->orWhereNull('projet_id');
            });
        } else {
            $query->whereNull('projet_id');
        }

        return $query->exists();
    }

    /**
     * Raccourcis pour les vérifications de rôle.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isDirecteurTechnique(): bool
    {
        return $this->role === self::ROLE_DIRECTEUR_TECHNIQUE;
    }

    public function isControleur(): bool
    {
        return $this->role === self::ROLE_CONTROLEUR;
    }

    public function isGestionnaireStock(): bool
    {
        return $this->role === self::ROLE_GESTIONNAIRE_STOCK;
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->prenom, 0, 1) . substr($this->nom, 0, 1));
    }
}