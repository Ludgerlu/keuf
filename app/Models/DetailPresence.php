<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPresence extends Model
{
    protected $table = 'detail_presences';

    protected $fillable = [
        'presence_id',
        'employe_id',
        'heure_debut',
        'heure_fin',
        'statut',
        'note',
    ];

    protected $casts = [
        'heure_debut' => 'datetime:H:i:s',
        'heure_fin'   => 'datetime:H:i:s',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    /**
     * Session de pointage parente (projet + date).
     */
    public function presence(): BelongsTo
    {
        return $this->belongsTo(Presence::class, 'presence_id');
    }

    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    // -------------------------------------------------------------------------
    // Scopes utiles
    // -------------------------------------------------------------------------

    public function scopePresents($query)
    {
        return $query->where('statut', 'present');
    }

    public function scopeAbsents($query)
    {
        return $query->where('statut', 'absent');
    }

    public function scopeDemiJournee($query)
    {
        return $query->where('statut', 'demi_journee');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Durée travaillée en heures (null si heures non renseignées).
     */
    public function getDureeHeuresAttribute(): ?float
    {
        if (! $this->heure_debut || ! $this->heure_fin) {
            return null;
        }

        return round(
            $this->heure_fin->diffInMinutes($this->heure_debut) / 60,
            2
        );
    }

    /**
     * Vérifie si l'employé est présent (present ou demi_journee).
     */
    public function getEstPresentAttribute(): bool
    {
        return in_array($this->statut, ['present', 'demi_journee']);
    }
}