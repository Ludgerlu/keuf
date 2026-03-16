<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemarquePhase extends Model
{
    use SoftDeletes;

    protected $table = 'remarques_phases';
    
    protected $fillable = [
        'phase_id',
        'auteur_id',
        'contenu'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function phase(): BelongsTo
    {
        return $this->belongsTo(PhaseProjet::class, 'phase_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}