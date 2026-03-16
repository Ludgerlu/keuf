<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoriquePhase extends Model
{
    protected $table = 'historique_phases';
    
    public $timestamps = false;
    
    protected $fillable = [
        'projet_id',
        'ancienne_phase_id',
        'nouvelle_phase_id',
        'change_par',
        'date_changement',
        'note'
    ];

    protected $casts = [
        'date_changement' => 'datetime',
    ];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function anciennePhase(): BelongsTo
    {
        return $this->belongsTo(PhaseProjet::class, 'ancienne_phase_id');
    }

    public function nouvellePhase(): BelongsTo
    {
        return $this->belongsTo(PhaseProjet::class, 'nouvelle_phase_id');
    }

    public function changePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'change_par');
    }
}