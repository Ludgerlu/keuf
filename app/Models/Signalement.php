<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signalement extends Model
{
    protected $table = 'signalements';
    
    protected $fillable = [
        'projet_id',
        'auteur_id',
        'type',
        'description',
        'photo_url',
        'gps_lat',
        'gps_lng',
        'priorite',
        'statut'
    ];

    protected $casts = [
        'gps_lat' => 'decimal:7',
        'gps_lng' => 'decimal:7',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function auteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}