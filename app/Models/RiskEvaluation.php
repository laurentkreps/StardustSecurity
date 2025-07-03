<?php

// =============================================================================
// MODÈLE ELOQUENT CORRIGÉ: RiskEvaluation.php
// =============================================================================

// Pour gérer les calculs automatiques sans colonnes calculées SQL

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskEvaluation extends Model
{
    protected $fillable = [
        'playground_id', 'equipment_id', 'danger_category_id', 'evaluation_type',
        'is_present', 'risk_description', 'probability_value', 'exposure_value',
        'gravity_value', 'risk_value', 'risk_category', 'preventive_measures',
        'implemented_measures', 'target_date', 'measure_status', 'evaluator_name',
        'evaluation_date', 'next_review_date', 'comments',
    ];

    protected $casts = [
        'evaluation_date'   => 'date',
        'next_review_date'  => 'date',
        'target_date'       => 'date',
        'is_present'        => 'boolean',
        'probability_value' => 'decimal:1',
        'exposure_value'    => 'decimal:1',
        'gravity_value'     => 'decimal:1',
        'risk_value'        => 'decimal:2',
    ];

    // Boot method pour calculer automatiquement risk_value et risk_category
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($risk) {
            $risk->calculateRiskValues();
        });
    }

    public function calculateRiskValues(): void
    {
        if ($this->probability_value && $this->exposure_value && $this->gravity_value) {
            $this->risk_value    = $this->probability_value * $this->exposure_value * $this->gravity_value;
            $this->risk_category = $this->calculateRiskCategory($this->risk_value);
        }
    }

    private function calculateRiskCategory($riskValue): int
    {
        if ($riskValue > 320) {
            return 5;
        }

        if ($riskValue > 160) {
            return 4;
        }

        if ($riskValue > 70) {
            return 3;
        }

        if ($riskValue > 20) {
            return 2;
        }

        return 1;
    }

    // Relations
    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function dangerCategory(): BelongsTo
    {
        return $this->belongsTo(DangerCategory::class);
    }

    // Accesseurs
    public function getRiskCategoryLabelAttribute(): string
    {
        return match ($this->risk_category) {
            5 => 'Très élevé',
            4 => 'Élevé',
            3 => 'Important',
            2 => 'Possible',
            1 => 'Faible',
            default => 'Non évalué'
        };
    }

    public function getRiskCategoryColorAttribute(): string
    {
        return match ($this->risk_category) {
            5 => 'red',
            4 => 'orange',
            3 => 'yellow',
            2 => 'blue',
            1 => 'green',
            default => 'gray'
        };
    }

    public function getActionRequiredAttribute(): string
    {
        return match ($this->risk_category) {
            5 => "Envisager l'arrêt de l'activité",
            4 => 'Mesures immédiates nécessaires',
            3 => 'Correction nécessaire',
            2 => 'Y porter attention',
            1 => 'Le risque est peut-être acceptable',
            default => 'Évaluation requise'
        };
    }
}
