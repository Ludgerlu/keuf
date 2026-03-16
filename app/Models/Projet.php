<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Projet extends Model
{
    use SoftDeletes;

    protected $table = 'projets';
    
    protected $fillable = [
        'est_stock_central',
        'client_nom',
        'client_contact',
        'client_telephone',
        'client_email',
        'nom',
        'reference',
        'statut',
        'progression',
        'budget',
        'localisation',
        'date_debut',
        'date_fin_prevue',
        'date_fin_reelle',
        'description',
        'phase_actuelle_id'
    ];

    protected $casts = [
        'est_stock_central' => 'boolean',
        'progression' => 'decimal:2',
        'budget' => 'decimal:2',
        'date_debut' => 'date',
        'date_fin_prevue' => 'date',
        'date_fin_reelle' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function phaseActuelle(): BelongsTo
    {
        return $this->belongsTo(PhaseProjet::class, 'phase_actuelle_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(PhaseProjet::class, 'projet_id')->orderBy('ordre');
    }

    public function employes()
    {
        return $this->belongsToMany(Employe::class, 'affectation_projets', 'projet_id', 'employe_id')
                    ->withPivot('role_sur_projet', 'date_debut', 'date_fin')
                    ->withTimestamps();
    }

    public function affectations(): HasMany
    {
        return $this->hasMany(AffectationProjet::class, 'projet_id');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class, 'projet_id');
    }

    public function signalements(): HasMany
    {
        return $this->hasMany(Signalement::class, 'projet_id');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(StockProjet::class, 'projet_id');
    }

    public function mouvementsSource(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'source_projet_id');
    }

    public function mouvementsDestination(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'dest_projet_id');
    }

    public function imports(): HasMany
    {
        return $this->hasMany(ImportInternational::class, 'projet_id');
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'projet_id');
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(PaiementEmploye::class, 'projet_id');
    }

    public function historiquePhases(): HasMany
    {
        return $this->hasMany(HistoriquePhase::class, 'projet_id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'projet_id');
    }
}