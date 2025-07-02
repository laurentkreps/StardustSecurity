<?php

// =============================================================================
// app/Models/Equipment.php
// =============================================================================

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $fillable = [
        'playground_id',
        'reference_code',
        'equipment_type',
        'brand',
        'manufacturer_details',
        'supplier_details',
        'applicable_norm',
        'purchase_date',
        'installation_date',
        'verification_frequency',
        'risk_analysis_certificate',
        'status',
    ];

    protected $casts = [
        'purchase_date'     => 'date',
        'installation_date' => 'date',
    ];

    // Relations
    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    public function maintenanceChecks(): HasMany
    {
        return $this->hasMany(MaintenanceCheck::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInMaintenance(Builder $query): Builder
    {
        return $query->where('status', 'maintenance');
    }

    public function scopeOutOfService(Builder $query): Builder
    {
        return $query->where('status', 'out_of_service');
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('equipment_type', $type);
    }

    public function scopeByBrand(Builder $query, string $brand): Builder
    {
        return $query->where('brand', $brand);
    }

    public function scopeNeedingMaintenance(Builder $query): Builder
    {
        return $query->whereHas('maintenanceChecks', function ($q) {
            $q->where('status', 'overdue')
                ->orWhere('next_check_date', '<=', now());
        });
    }

    // Accesseurs
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active' => 'Actif',
            'maintenance' => 'En maintenance',
            'out_of_service' => 'Hors service',
            default => 'Statut inconnu'
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active' => 'green',
            'maintenance' => 'yellow',
            'out_of_service' => 'red',
            default => 'gray'
        };
    }

    public function getAgeInYearsAttribute(): ?int
    {
        return $this->installation_date ?
        $this->installation_date->diffInYears(now()) : null;
    }

    public function getFullNameAttribute(): string
    {
        return $this->reference_code . ' - ' . $this->equipment_type;
    }

    public function getLastMaintenanceAttribute(): ?MaintenanceCheck
    {
        return $this->maintenanceChecks()
            ->where('status', 'completed')
            ->latest('completed_date')
            ->first();
    }

    public function getNextMaintenanceAttribute(): ?MaintenanceCheck
    {
        return $this->maintenanceChecks()
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date')
            ->first();
    }

    // Méthodes utiles
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    public function isOutOfService(): bool
    {
        return $this->status === 'out_of_service';
    }

    public function needsMaintenance(): bool
    {
        $nextCheck = $this->getNextMaintenanceAttribute();
        return $nextCheck && $nextCheck->scheduled_date <= now();
    }

    public function hasHighRiskEvaluations(): bool
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->whereRaw('(probability_value * exposure_value * gravity_value) > 160')
            ->exists();
    }

    public function getRiskSummary(): array
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->selectRaw('risk_category, COUNT(*) as count')
            ->groupBy('risk_category')
            ->pluck('count', 'risk_category')
            ->toArray();
    }

    public function scheduleNextMaintenance(Carbon $date, string $type = 'regular_verification'): MaintenanceCheck
    {
        return $this->maintenanceChecks()->create([
            'playground_id'  => $this->playground_id,
            'check_type'     => $type,
            'scheduled_date' => $date,
            'status'         => 'scheduled',
        ]);
    }

    // Validation rules
    public static function validationRules(): array
    {
        return [
            'playground_id'             => 'required|exists:playgrounds,id',
            'reference_code'            => 'required|string|max:50|unique:equipment,reference_code',
            'equipment_type'            => 'required|string|max:255',
            'brand'                     => 'nullable|string|max:255',
            'manufacturer_details'      => 'nullable|string',
            'supplier_details'          => 'nullable|string',
            'applicable_norm'           => 'nullable|string|max:255',
            'purchase_date'             => 'nullable|date|before_or_equal:today',
            'installation_date'         => 'nullable|date|before_or_equal:today',
            'verification_frequency'    => 'nullable|string|max:100',
            'risk_analysis_certificate' => 'nullable|string',
            'status'                    => 'required|in:active,maintenance,out_of_service',
        ];
    }
}
