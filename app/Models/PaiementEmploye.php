<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaiementEmploye extends Model
{
    protected $table = 'paiements_employes';
    
    protected $fillable = [
        'employe_id',
        'projet_id',
        'facture_id',
        'montant',
        'periode',
        'type',
        'statut',
        'date_paiement',
        'note'
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function employe(): BelongsTo
    {
        return $this->belongsTo(Employe::class, 'employe_id');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function facture(): BelongsTo
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }
}