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
        'actif',
        'email',
        'date_embauche',
        'notes',
    ];

    protected $casts = [
        'actif'        => 'boolean',
        'date_embauche'=> 'date',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function affectations(): HasMany
    {
        return $this->hasMany(AffectationProjet::class, 'employe_id');
    }

    public function projets(): BelongsToMany
    {
        return $this->belongsToMany(Projet::class, 'affectation_projets', 'employe_id', 'projet_id')
                    ->withPivot('role_sur_projet', 'date_debut', 'date_fin')
                    ->withTimestamps();
    }

    /**
     * Détails de présence individuels (via detail_presences).
     * Pour accéder aux sessions complètes, passer par Presence::with('details').
     */
    public function detailPresences(): HasMany
    {
        return $this->hasMany(DetailPresence::class, 'employe_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementEmploye::class, 'employe_id');
    }

    // -------------------------------------------------------------------------
    // Scopes utiles
    // -------------------------------------------------------------------------

    public function scopeActifs($query)
    {
        return $query->where('actif', true);
    }

    public function scopeInternes($query)
    {
        return $query->where('type', 'interne');
    }

    public function scopeSousTraitants($query)
    {
        return $query->where('type', 'sous_traitant');
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