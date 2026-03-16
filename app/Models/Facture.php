<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facture extends Model
{
    use SoftDeletes;

    protected $table = 'factures';
    
    protected $fillable = [
        'projet_id',
        'fournisseur_id',
        'import_id',
        'client_nom',
        'numero',
        'type',
        'montant_ht',
        'tva',
        'statut',
        'date_emission',
        'date_echeance',
        'date_paiement',
        'notes'
    ];

    protected $casts = [
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = ['montant_ttc'];

    // Relations
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ImportInternational::class, 'import_id');
    }

    public function lignes(): HasMany
    {
        return $this->hasMany(LigneFacture::class, 'facture_id')->orderBy('ordre');
    }

    public function paiementsEmployes(): HasMany
    {
        return $this->hasMany(PaiementEmploye::class, 'facture_id');
    }

    // Accessors
    public function getMontantTtcAttribute(): float
    {
        return $this->montant_ht * (1 + $this->tva / 100);
    }
}