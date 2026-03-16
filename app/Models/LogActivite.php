<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogActivite extends Model
{
    protected $table = 'logs_activites';
    
    public $timestamps = false;
    
    protected $fillable = [
        'utilisateur_id',
        'action',
        'table_cible',
        'enregistrement_id',
        'avant',
        'apres',
        'ip',
        'created_at'
    ];

    protected $casts = [
        'avant' => 'array',
        'apres' => 'array',
        'created_at' => 'datetime',
    ];

    // Relations
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}