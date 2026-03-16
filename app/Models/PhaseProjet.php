<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhaseProjet extends Model
{
    protected $table = 'phases_projet';
    
    protected $fillable = [
        'projet_id',
        'nom',
        'description',
        'ordre',
        'progression',
        'date_debut_prevue',
        'date_fin_prevue',
        'date_debut_reelle',
        'date_fin_reelle'
    ];

    protected $casts = [
        'progression' => 'decimal:2',
        'ordre' => 'integer',
        'date_debut_prevue' => 'date',
        'date_fin_prevue' => 'date',
        'date_debut_reelle' => 'date',
        'date_fin_reelle' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function remarques(): HasMany
    {
        return $this->hasMany(RemarquePhase::class, 'phase_id');
    }

    public function historiqueAncienne(): HasMany
    {
        return $this->hasMany(HistoriquePhase::class, 'ancienne_phase_id');
    }

    public function historiqueNouvelle(): HasMany
    {
        return $this->hasMany(HistoriquePhase::class, 'nouvelle_phase_id');
    }
}