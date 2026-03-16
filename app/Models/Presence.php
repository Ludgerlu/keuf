<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presence extends Model
{
    protected $table = 'presences';
    
    protected $fillable = [
        'projet_id',
        'employe_id',
        'saisie_par',
        'date',
        'heure_debut',
        'heure_fin',
        'statut',
        'note'
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime:H:i:s',
        'heure_fin' => 'datetime:H:i:s',
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

    public function saisiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }
}