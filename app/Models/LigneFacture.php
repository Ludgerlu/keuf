<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneFacture extends Model
{
    protected $table = 'lignes_factures';
    
    public $timestamps = false;
    
    protected $fillable = [
        'facture_id',
        'description',
        'quantite',
        'unite',
        'prix_unitaire',
        'ordre'
    ];

    protected $casts = [
        'quantite' => 'decimal:3',
        'prix_unitaire' => 'decimal:2',
        'ordre' => 'integer',
    ];

    protected $appends = ['total'];

    // Relations
    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }

    // Accessors
    public function getTotalAttribute(): float
    {
        return $this->quantite * $this->prix_unitaire;
    }
}