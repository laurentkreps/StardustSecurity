<?php

// =============================================================================
// app/Models/Equipment.php - VERSION CORRIGÉE SYNCHRONISÉE AVEC MIGRATION
// =============================================================================

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
    // 🔧 FILLABLE CORRIGÉ - SYNCHRONISÉ AVEC TOUTE LA MIGRATION
    protected $fillable = [
        // Champs de base
        'playground_id',
        'reference_code',
        'equipment_category',
        'equipment_type',
        'ride_category',
        'brand',
        'manufacturer_details',
        'supplier_details',
        'applicable_norms',
        'ce_marking',
        'declaration_of_conformity',
        'purchase_date',
        'installation_date',
        'verification_frequency',
        'risk_analysis_certificate',
        'status',
        'description',
        'material',
        'height',
        'max_speed',
        'max_acceleration',
        'max_passengers',
        'min_height_requirement',
        'max_weight_limit',
        'age_group',
        'dimensions',
        'requires_fall_protection',
        'fall_height',
        'safety_features',

        // Spécifique aux manèges (EN 13814)
        'is_mobile',
        'setup_time_hours',
        'power_consumption_kw',
        'structural_calculations_ref',
        'weather_operating_limits',
        'requires_operator',
        'operator_qualification',

        // Spécifique électrique (EN 60335)
        'voltage',
        'current',
        'protection_class',
        'ip_rating',
        'requires_earth_connection',
        'electrical_test_date',
        'electrical_certificate',
    ];

    // 🔧 CASTS CORRIGÉS - SYNCHRONISÉS AVEC MIGRATION
    protected $casts = [
        'purchase_date'             => 'date',
        'installation_date'         => 'date',
        'electrical_test_date'      => 'date',
        'applicable_norms'          => 'array',
        'dimensions'                => 'array',
        'weather_operating_limits'  => 'array',
        'height'                    => 'decimal:2',
        'max_speed'                 => 'decimal:2',
        'max_acceleration'          => 'decimal:2',
        'min_height_requirement'    => 'decimal:2',
        'max_weight_limit'          => 'decimal:2',
        'setup_time_hours'          => 'decimal:1',
        'power_consumption_kw'      => 'decimal:2',
        'voltage'                   => 'decimal:2',
        'current'                   => 'decimal:2',
        'requires_fall_protection'  => 'boolean',
        'is_mobile'                 => 'boolean',
        'requires_operator'         => 'boolean',
        'requires_earth_connection' => 'boolean',
    ];

    // =============================================================================
    // RELATIONS (inchangées)
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

    public function rideCategoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn()        => match ($this->ride_category) {
                'category_1'     => 'Catégorie 1 - Manèges pour jeunes enfants',
                'category_2'     => 'Catégorie 2 - Manèges sans renversement',
                'category_3'     => 'Catégorie 3 - Manèges avec renversement',
                'category_4'     => 'Catégorie 4 - Attractions à sensations fortes',
                'not_applicable' => 'Non applicable',
                default          => $this->ride_category
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

    public function hasCeMarking(): Attribute
    {
        return Attribute::make(
            get: fn() => ! empty($this->ce_marking)
        );
    }

    public function hasDeclarationOfConformity(): Attribute
    {
        return Attribute::make(
            get: fn() => ! empty($this->declaration_of_conformity)
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

    public function isElectricalTestDue(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->equipment_category !== 'electrical_system') {
                    return false;
                }

                if (! $this->electrical_test_date) {
                    return true;
                }

                return $this->electrical_test_date->addYear()->isPast();
            }
        );
    }

    public function requiresOperatorQualification(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->requires_operator && ! empty($this->operator_qualification)
        );
    }

    // =============================================================================
    // SCOPES (inchangés mais étendus)
    // =============================================================================

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('equipment_category', $category);
    }

    public function scopeByRideCategory(Builder $query, string $rideCategory): Builder
    {
        return $query->where('ride_category', $rideCategory);
    }

    public function scopeMobileEquipment(Builder $query): Builder
    {
        return $query->where('is_mobile', true);
    }

    public function scopeRequiringOperator(Builder $query): Builder
    {
        return $query->where('requires_operator', true);
    }

    public function scopeElectricalTestDue(Builder $query): Builder
    {
        return $query->where('equipment_category', 'electrical_system')
            ->where(function ($q) {
                $q->whereNull('electrical_test_date')
                    ->orWhere('electrical_test_date', '<', now()->subYear());
            });
    }

    public function scopeWithExpiredCertifications(Builder $query): Builder
    {
        return $query->whereHas('certifications', function ($q) {
            $q->where('expiry_date', '<', now())
                ->where('status', 'valid');
        });
    }

    // =============================================================================
    // MÉTHODES UTILITAIRES ÉTENDUES
    // =============================================================================

    public function isAmusementRide(): bool
    {
        return $this->equipment_category === 'amusement_ride';
    }

    public function isElectricalSystem(): bool
    {
        return $this->equipment_category === 'electrical_system';
    }

    public function isMobile(): bool
    {
        return $this->is_mobile ?? false;
    }

    public function needsOperator(): bool
    {
        return $this->requires_operator ?? false;
    }

    public function getComplianceStatus(): array
    {
        return [
            'ce_marking'             => $this->has_ce_marking,
            'declaration_conformity' => $this->has_declaration_of_conformity,
            'certifications'         => $this->hasValidCertifications(),
            'inspections'            => ! $this->requiresInspection(),
            'electrical_tests'       => ! $this->requiresElectricalTest(),
            'risk_assessment'        => ! $this->hasHighRiskEvaluations(),
            'overall'                => $this->calculateOverallCompliance(),
        ];
    }

    private function calculateOverallCompliance(): bool
    {
        $status = $this->getComplianceStatus();
        unset($status['overall']); // Éviter la récursion

        return ! in_array(false, $status, true);
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
        if (! $this->isAmusementRide()) {
            return false;
        }

        $lastInspection = $this->amusementRideInspections()
            ->latest('inspection_date')
            ->first();

        if (! $lastInspection) {
            return true;
        }

        return $lastInspection->inspection_date->addMonth()->isPast();
    }

    public function requiresElectricalTest(): bool
    {
        return $this->isElectricalSystem() && $this->is_electrical_test_due;
    }

    public function hasHighRiskEvaluations(): bool
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->where('risk_category', '>=', 4)
            ->exists();
    }

    // =============================================================================
    // RÈGLES DE VALIDATION ÉTENDUES
    // =============================================================================

    public static function validationRules(): array
    {
        return [
            'playground_id'               => 'required|exists:playgrounds,id',
            'reference_code'              => 'required|string|max:50',
            'equipment_category'          => 'required|in:playground_equipment,amusement_ride,electrical_system,infrastructure,safety_equipment',
            'equipment_type'              => 'required|string|max:255',
            'ride_category'               => 'nullable|in:category_1,category_2,category_3,category_4,not_applicable',
            'brand'                       => 'nullable|string|max:255',
            'manufacturer_details'        => 'nullable|string',
            'supplier_details'            => 'nullable|string',
            'applicable_norms'            => 'nullable|array',
            'ce_marking'                  => 'nullable|string|max:255',
            'declaration_of_conformity'   => 'nullable|string|max:255',
            'purchase_date'               => 'nullable|date|before_or_equal:today',
            'installation_date'           => 'nullable|date|before_or_equal:today|after_or_equal:purchase_date',
            'verification_frequency'      => 'nullable|string|max:100',
            'risk_analysis_certificate'   => 'nullable|string',
            'status'                      => 'required|in:active,maintenance,out_of_service',
            'description'                 => 'nullable|string',
            'material'                    => 'nullable|string|max:100',
            'height'                      => 'nullable|numeric|min:0',
            'max_speed'                   => 'nullable|numeric|min:0',
            'max_acceleration'            => 'nullable|numeric|min:0',
            'max_passengers'              => 'nullable|integer|min:0',
            'min_height_requirement'      => 'nullable|numeric|min:0',
            'max_weight_limit'            => 'nullable|numeric|min:0',
            'age_group'                   => 'nullable|string|max:50',
            'dimensions'                  => 'nullable|array',
            'requires_fall_protection'    => 'boolean',
            'fall_height'                 => 'nullable|numeric|min:0',
            'safety_features'             => 'nullable|string',
            'is_mobile'                   => 'boolean',
            'setup_time_hours'            => 'nullable|numeric|min:0',
            'power_consumption_kw'        => 'nullable|numeric|min:0',
            'structural_calculations_ref' => 'nullable|string',
            'weather_operating_limits'    => 'nullable|array',
            'requires_operator'           => 'boolean',
            'operator_qualification'      => 'nullable|string',
            'voltage'                     => 'nullable|numeric|min:0',
            'current'                     => 'nullable|numeric|min:0',
            'protection_class'            => 'nullable|string',
            'ip_rating'                   => 'nullable|string',
            'requires_earth_connection'   => 'boolean',
            'electrical_test_date'        => 'nullable|date|before_or_equal:today',
            'electrical_certificate'      => 'nullable|string',
        ];
    }
}
