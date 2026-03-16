<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materiau extends Model
{
    use SoftDeletes;

    protected $table = 'materiaux';
    
    protected $fillable = [
        'nom',
        'unite',
        'categorie'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function stocks(): HasMany
    {
        return $this->hasMany(StockProjet::class, 'materiau_id');
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvementStock::class, 'materiau_id');
    }
}