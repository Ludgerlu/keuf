<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $table = 'permissions';
    
    protected $fillable = [
        'utilisateur_id',
        'module',
        'action',
        'projet_id',
        'autorise',
        'accorde_par'
    ];

    protected $casts = [
        'autorise' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relations
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function accordePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accorde_par');
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }
}