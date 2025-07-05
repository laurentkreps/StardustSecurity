<?php

// =============================================================================
// 1. app/Models/DangerCategory.php - CORRIGÉ
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DangerCategory extends Model
{
    // 🔧 FILLABLE CORRIGÉ - Champs manquants ajoutés
    protected $fillable = [
        'code',
        'title',
        'description',
        'applies_to',
        'is_active',
        // 🆕 AJOUTÉS - manquaient dans l'original
        'sort_order',
        'regulation_reference',
        'typical_examples',
        'default_measures',
    ];

    // 🔧 CASTS CORRIGÉS
    protected $casts = [
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
        'default_measures' => 'array', // 🆕 AJOUTÉ pour JSON
    ];

    // Relations (inchangées)
    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    // Scopes (inchangés)
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

    // 🆕 SCOPE AJOUTÉ pour tri
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    // Accesseurs existants + nouveaux
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

    // 🆕 ACCESSEURS AJOUTÉS
    public function getHasExamplesAttribute(): bool
    {
        return ! empty($this->typical_examples);
    }

    public function getHasDefaultMeasuresAttribute(): bool
    {
        return ! empty($this->default_measures);
    }

    public function getRegulationReferenceShortAttribute(): ?string
    {
        if (! $this->regulation_reference) {
            return null;
        }

        // Extraire la référence courte (ex: "EN 1176-1 Clause 4.2.1" -> "EN 1176-1")
        return explode(' ', $this->regulation_reference)[0] ?? $this->regulation_reference;
    }

    // Méthodes utiles (inchangées + nouvelles)
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

    // 🆕 MÉTHODES AJOUTÉES
    public function getDefaultMeasuresList(): array
    {
        return $this->default_measures ?? [];
    }

    public function addDefaultMeasure(string $measure): void
    {
        $measures               = $this->getDefaultMeasuresList();
        $measures[]             = $measure;
        $this->default_measures = $measures;
        $this->save();
    }

    // Validation rules (mises à jour)
    public static function validationRules(): array
    {
        return [
            'code'                 => 'required|string|max:10|unique:danger_categories,code',
            'title'                => 'required|string|max:500',
            'description'          => 'nullable|string',
            'applies_to'           => 'required|in:playground,equipment',
            'is_active'            => 'boolean',
            'sort_order'           => 'integer|min:0',           // 🆕
            'regulation_reference' => 'nullable|string|max:255', // 🆕
            'typical_examples'     => 'nullable|string',         // 🆕
            'default_measures'     => 'nullable|array',          // 🆕
        ];
    }
}
