<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportInternational extends Model
{
    protected $table = 'imports_internationaux';
    
    protected $fillable = [
        'fournisseur_id',
        'projet_id',
        'numero_declaration',
        'pays_origine',
        'devise',
        'taux_change',
        'montant_devise',
        'montant_fcfa',
        'droits_douane',
        'autres_taxes',
        'statut',
        'date_commande',
        'date_arrivee_prevue',
        'date_arrivee_reelle',
        'notes'
    ];

    protected $casts = [
        'taux_change' => 'decimal:4',
        'montant_devise' => 'decimal:2',
        'montant_fcfa' => 'decimal:2',
        'droits_douane' => 'decimal:2',
        'autres_taxes' => 'decimal:2',
        'date_commande' => 'date',
        'date_arrivee_prevue' => 'date',
        'date_arrivee_reelle' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'import_id');
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'import_id');
    }
}