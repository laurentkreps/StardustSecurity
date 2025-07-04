<?php

// =============================================================================
// app/Models/Equipment.php - VERSION CORRIGÉE AVEC TOUTES LES RELATIONS
// =============================================================================

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
    protected $fillable = [
        'playground_id',
        'reference_code',
        'equipment_category',
        'equipment_type',
        'brand',
        'manufacturer_details',
        'supplier_details',
        'applicable_norms',
        'purchase_date',
        'installation_date',
        'height',
        'max_speed',
        'max_acceleration',
        'max_passengers',
        'voltage',
        'current',
        'protection_class',
        'ip_rating',
        'status',
    ];

    protected $casts = [
        'purchase_date'     => 'date',
        'installation_date' => 'date',
        'applicable_norms'  => 'array',
        'height'            => 'decimal:2',
        'max_speed'         => 'decimal:2',
        'max_acceleration'  => 'decimal:2',
        'voltage'           => 'decimal:1',
        'current'           => 'decimal:1',
    ];

    // =============================================================================
    // RELATIONS
    // =============================================================================

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

    public function certifications(): HasMany
    {
        return $this->hasMany(EquipmentCertification::class);
    }

    public function amusementRideInspections(): HasMany
    {
        return $this->hasMany(AmusementRideInspection::class);
    }

    public function electricalTests(): HasMany
    {
        return $this->hasMany(ElectricalSafetyTest::class);
    }

    public function technicalData(): HasOne
    {
        return $this->hasOne(AmusementRideTechnicalData::class);
    }

    // =============================================================================
    // ACCESSEURS ET ATTRIBUTS CALCULÉS
    // =============================================================================

    public function equipmentCategoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn()              => match ($this->equipment_category) {
                'playground_equipment' => 'Équipement aire de jeux',
                'amusement_ride'       => 'Manège/Attraction',
                'electrical_system'    => 'Système électrique',
                'infrastructure'       => 'Infrastructure',
                'safety_equipment'     => 'Équipement de sécurité',
                default                => $this->equipment_category
            }
        );
    }

    public function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn()        => match ($this->status) {
                'active'         => 'Actif',
                'maintenance'    => 'En maintenance',
                'out_of_service' => 'Hors service',
                default          => 'Statut inconnu'
            }
        );
    }

    public function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn()        => match ($this->status) {
                'active'         => 'green',
                'maintenance'    => 'yellow',
                'out_of_service' => 'red',
                default          => 'gray'
            }
        );
    }

    public function ageInYears(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->installation_date ?
            $this->installation_date->diffInYears(now()) : null
        );
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->reference_code . ' - ' . $this->equipment_type
        );
    }

    public function lastMaintenance(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintenanceChecks()
                ->where('status', 'completed')
                ->latest('completed_date')
                ->first()
        );
    }

    public function nextMaintenance(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintenanceChecks()
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date')
                ->first()
        );
    }

    public function lastInspectionDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->amusementRideInspections()
                ->latest('inspection_date')
                ->first()?->inspection_date
        );
    }

    public function operationAuthorized(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->equipment_category !== 'amusement_ride') {
                    return true;
                }

                $lastInspection = $this->amusementRideInspections()
                    ->latest('inspection_date')
                    ->first();

                return $lastInspection?->operation_authorized ?? false;
            }
        );
    }

    public function electricalTestDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->electricalTests()
                ->latest('test_date')
                ->first()?->test_date
        );
    }

    public function isElectricalTestDue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->equipment_category !== 'electrical_system') {
                    return false;
                }

                $lastTest = $this->electricalTests()
                    ->latest('test_date')
                    ->first();

                if (! $lastTest) {
                    return true;
                }

                return $lastTest->test_date->addYear()->isPast();
            }
        );
    }

    public function rideCategoryLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->equipment_category !== 'amusement_ride') {
                    return null;
                }

                // Déterminer la catégorie selon EN 13814 basée sur les caractéristiques
                if ($this->technicalData) {
                    return match ($this->technicalData->ride_category_from_data) {
                        'category_1' => 'Catégorie 1 - Manèges pour jeunes enfants',
                        'category_2' => 'Catégorie 2 - Manèges sans renversement',
                        'category_3' => 'Catégorie 3 - Manèges avec renversement',
                        'category_4' => 'Catégorie 4 - Attractions à sensations fortes',
                        default      => 'Catégorie non définie'
                    };
                }

                return 'Catégorie à déterminer';
            }
        );
    }

    // =============================================================================
    // SCOPES
    // =============================================================================

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

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('equipment_category', $category);
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

    public function scopeElectricalTestDue(Builder $query): Builder
    {
        return $query->where('equipment_category', 'electrical_system')
            ->where(function ($q) {
                $q->whereDoesntHave('electricalTests')
                    ->orWhereHas('electricalTests', function ($subQ) {
                        $subQ->whereRaw('test_date < DATE_SUB(NOW(), INTERVAL 1 YEAR)')
                            ->latest('test_date')
                            ->limit(1);
                    });
            });
    }

    public function scopeInspectionDue(Builder $query): Builder
    {
        return $query->where('equipment_category', 'amusement_ride')
            ->where(function ($q) {
                $q->whereDoesntHave('amusementRideInspections')
                    ->orWhereHas('amusementRideInspections', function ($subQ) {
                        $subQ->whereRaw('inspection_date < DATE_SUB(NOW(), INTERVAL 1 MONTH)')
                            ->latest('inspection_date')
                            ->limit(1);
                    });
            });
    }

    // =============================================================================
    // MÉTHODES UTILITAIRES
    // =============================================================================

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
        $nextCheck = $this->next_maintenance;
        return $nextCheck && $nextCheck->scheduled_date <= now();
    }

    public function hasHighRiskEvaluations(): bool
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->where('risk_category', '>=', 4)
            ->exists();
    }

    public function hasValidCertifications(): bool
    {
        return $this->certifications()
            ->where('status', 'valid')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            })
            ->exists();
    }

    public function requiresInspection(): bool
    {
        if ($this->equipment_category !== 'amusement_ride') {
            return false;
        }

        $lastInspection = $this->amusementRideInspections()
            ->latest('inspection_date')
            ->first();

        if (! $lastInspection) {
            return true;
        }

        // Inspection requise si plus de 30 jours
        return $lastInspection->inspection_date->addMonth()->isPast();
    }

    public function requiresElectricalTest(): bool
    {
        if ($this->equipment_category !== 'electrical_system') {
            return false;
        }

        return $this->is_electrical_test_due;
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

    public function getComplianceStatus(): array
    {
        return [
            'certifications'   => $this->hasValidCertifications(),
            'inspections'      => ! $this->requiresInspection(),
            'electrical_tests' => ! $this->requiresElectricalTest(),
            'risk_assessment'  => ! $this->hasHighRiskEvaluations(),
            'overall'          => $this->hasValidCertifications() &&
            ! $this->requiresInspection() &&
            ! $this->requiresElectricalTest() &&
            ! $this->hasHighRiskEvaluations(),
        ];
    }

    // =============================================================================
    // RÈGLES DE VALIDATION
    // =============================================================================

    public static function validationRules(): array
    {
        return [
            'playground_id'        => 'required|exists:playgrounds,id',
            'reference_code'       => 'required|string|max:50',
            'equipment_category'   => 'required|in:playground_equipment,amusement_ride,electrical_system,infrastructure,safety_equipment',
            'equipment_type'       => 'required|string|max:255',
            'brand'                => 'nullable|string|max:255',
            'manufacturer_details' => 'nullable|string',
            'supplier_details'     => 'nullable|string',
            'applicable_norms'     => 'nullable|array',
            'purchase_date'        => 'nullable|date|before_or_equal:today',
            'installation_date'    => 'nullable|date|before_or_equal:today|after_or_equal:purchase_date',
            'height'               => 'nullable|numeric|min:0',
            'max_speed'            => 'nullable|numeric|min:0',
            'max_acceleration'     => 'nullable|numeric|min:0',
            'max_passengers'       => 'nullable|integer|min:0',
            'voltage'              => 'nullable|numeric|min:0',
            'current'              => 'nullable|numeric|min:0',
            'protection_class'     => 'nullable|string',
            'ip_rating'            => 'nullable|string',
            'status'               => 'required|in:active,maintenance,out_of_service',
        ];
    }
}
