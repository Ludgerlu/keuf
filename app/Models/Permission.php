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
    
    /**
     * Get all available modules.
     */
    public static function getModules()
    {
        return [
            'projets',
            'employes',
            'stocks',
            'presences',
            'signalements',
            // 'facturation',
            // 'imports'
        ];
    }

    /**
     * Get all available actions.
     */
    public static function getActions()
    {
        return [
            'creer',
            'lire',
            'modifier',
            'supprimer',
            // 'exporter'
        ];
    }

    /**
     * Get module label in French.
     */
    public static function getModuleLabel($module)
    {
        $labels = [
            'projets' => 'Projets',
            'employes' => 'Employés',
            'stocks' => 'Stocks',
            'presences' => 'Présences',
            'signalements' => 'Signalements',
            'facturation' => 'Facturation',
            'imports' => 'Imports',
        ];
        
        return $labels[$module] ?? ucfirst($module);
    }

    /**
     * Get action label in French.
     */
    public static function getActionLabel($action)
    {
        $labels = [
            'creer' => 'Créer',
            'lire' => 'Lire',
            'modifier' => 'Modifier',
            'supprimer' => 'Supprimer',
            'exporter' => 'Exporter',
        ];
        
        return $labels[$action] ?? ucfirst($action);
    }
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