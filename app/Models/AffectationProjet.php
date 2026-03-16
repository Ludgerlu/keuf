<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectationProjet extends Model
{
    protected $table = 'affectation_projets';
    
    protected $fillable = [
        'projet_id',
        'employe_id',
        'role_sur_projet',
        'date_debut',
        'date_fin'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }
}