<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employe extends Model
{
    use SoftDeletes;

    protected $table = 'employes';
    
    protected $fillable = [
        'nom',
        'prenom',
        'telephone',
        'type',
        'role_metier',
        'actif'
    ];

    protected $casts = [
        'actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function affectations(): HasMany
    {
        return $this->hasMany(AffectationProjet::class, 'employe_id');
    }

    public function projets()
    {
        return $this->belongsToMany(Projet::class, 'affectation_projets', 'employe_id', 'projet_id')
                    ->withPivot('role_sur_projet', 'date_debut', 'date_fin')
                    ->withTimestamps();
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class, 'employe_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementEmploye::class, 'employe_id');
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->prenom} {$this->nom}";
    }
}