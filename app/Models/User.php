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

    // Relations
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'utilisateur_id');
    }

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
}