<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Presence extends Model
{
    protected $table = 'presences';

    protected $fillable = [
        'projet_id',
        'saisie_par',
        'date',
        'note',
    ];

    protected $casts = [
        'date'       => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function saisiePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saisie_par');
    }

    /**
     * Détails du pointage de chaque employé pour cette session.
     */
    public function details(): HasMany
    {
        return $this->hasMany(DetailPresence::class, 'presence_id');
    }

    // -------------------------------------------------------------------------
    // Scopes utiles
    // -------------------------------------------------------------------------

    /**
     * Filtrer par projet.
     */
    public function scopeProjet($query, int $projetId)
    {
        return $query->where('projet_id', $projetId);
    }

    /**
     * Filtrer par date exacte.
     */
    public function scopeDate($query, string $date)
    {
        return $query->whereDate('date', $date);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Nombre de présents dans cette session.
     */
    public function getNbPresentsAttribute(): int
    {
        return $this->details()->where('statut', 'present')->count();
    }

    /**
     * Nombre d'absents dans cette session.
     */
    public function getNbAbsentsAttribute(): int
    {
        return $this->details()->where('statut', 'absent')->count();
    }
}