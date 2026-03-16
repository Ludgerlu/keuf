<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fournisseur extends Model
{
    use SoftDeletes;

    protected $table = 'fournisseurs';
    
    protected $fillable = [
        'nom',
        'pays',
        'contact',
        'telephone',
        'email',
        'est_etranger',
        'devise_defaut'
    ];

    protected $casts = [
        'est_etranger' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function imports(): HasMany
    {
        return $this->hasMany(ImportInternational::class, 'fournisseur_id');
    }

    public function mouvementsStock(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'fournisseur_id');
    }

    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class, 'fournisseur_id');
    }
}