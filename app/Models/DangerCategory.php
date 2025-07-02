<?php

// =============================================================================
// app/Models/DangerCategory.php
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DangerCategory extends Model
{
    protected $fillable = [
        'code',
        'title',
        'description',
        'applies_to',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relations
    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlayground(Builder $query): Builder
    {
        return $query->where('applies_to', 'playground');
    }

    public function scopeForEquipment(Builder $query): Builder
    {
        return $query->where('applies_to', 'equipment');
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    // Accesseurs
    public function getFullTitleAttribute(): string
    {
        return $this->code . ' - ' . $this->title;
    }

    public function getAppliestoLabelAttribute(): string
    {
        return match ($this->applies_to) {
            'playground' => 'Aire de jeux',
            'equipment' => 'Équipement',
            default => 'Non défini'
        };
    }

    // Méthodes utiles
    public function isForPlayground(): bool
    {
        return $this->applies_to === 'playground';
    }

    public function isForEquipment(): bool
    {
        return $this->applies_to === 'equipment';
    }

    public function hasRiskEvaluations(): bool
    {
        return $this->riskEvaluations()->exists();
    }

    public function getActiveRiskEvaluationsCount(): int
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->count();
    }

    // Validation rules (pour les FormRequests)
    public static function validationRules(): array
    {
        return [
            'code'        => 'required|string|max:10|unique:danger_categories,code',
            'title'       => 'required|string|max:500',
            'description' => 'nullable|string',
            'applies_to'  => 'required|in:playground,equipment',
            'is_active'   => 'boolean',
        ];
    }
}
