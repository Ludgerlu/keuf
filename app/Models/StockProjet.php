<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockProjet extends Model
{
    protected $table = 'stock_projets';
    
    public $timestamps = false;
    
    protected $fillable = [
        'projet_id',
        'materiau_id',
        'quantite',
        'seuil_alerte',
        'updated_at'
    ];

    protected $casts = [
        'quantite' => 'decimal:3',
        'seuil_alerte' => 'decimal:3',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function materiau(): BelongsTo
    {
        return $this->belongsTo(Materiau::class, 'materiau_id');
    }

    // Accessors
    public function getEstEnAlerteAttribute(): bool
    {
        return $this->quantite <= $this->seuil_alerte;
    }
}