<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';
    
    protected $fillable = [
        'materiau_id',
        'effectue_par',
        'fournisseur_id',
        'import_id',
        'type',
        'source_projet_id',
        'dest_projet_id',
        'quantite',
        'prix_unitaire',
        'reference_doc',
        'note'
    ];

    protected $casts = [
        'quantite' => 'decimal:3',
        'prix_unitaire' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function materiau(): BelongsTo
    {
        return $this->belongsTo(Materiau::class, 'materiau_id');
    }

    public function effectuePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'effectue_par');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class, 'fournisseur_id');
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ImportInternational::class, 'import_id');
    }

    public function sourceProjet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'source_projet_id');
    }

    public function destProjet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'dest_projet_id');
    }
}