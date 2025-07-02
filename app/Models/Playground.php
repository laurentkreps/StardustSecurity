<?php
// app/Models/Playground.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playground extends Model
{
    protected $fillable = [
        'name', 'address', 'city', 'postal_code', 'manager_name',
        'manager_contact', 'installation_date', 'last_analysis_date',
        'facility_type', 'status', 'total_surface', 'capacity', 'age_range',
        'opening_hours', 'is_fenced', 'has_lighting', 'is_permanent',
        'operating_license', 'license_expiry', 'max_wind_speed',
        'weather_restrictions', 'electrical_installation_cert', 'notes',
    ];

    protected $casts = [
        'installation_date'    => 'date',
        'last_analysis_date'   => 'date',
        'license_expiry'       => 'date',
        'opening_hours'        => 'array',
        'weather_restrictions' => 'array',
        'is_fenced'            => 'boolean',
        'has_lighting'         => 'boolean',
        'is_permanent'         => 'boolean',
        'max_wind_speed'       => 'decimal:1',
    ];

    // Relations
    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class);
    }

    public function playgroundEquipment(): HasMany
    {
        return $this->hasMany(Equipment::class)->where('equipment_category', 'playground_equipment');
    }

    public function amusementRides(): HasMany
    {
        return $this->hasMany(Equipment::class)->where('equipment_category', 'amusement_ride');
    }

    public function electricalSystems(): HasMany
    {
        return $this->hasMany(Equipment::class)->where('equipment_category', 'electrical_system');
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

    public function qualifiedOperators(): HasMany
    {
        return $this->hasMany(QualifiedOperator::class);
    }

    // Accessors
    public function facilityTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => config('risk_analysis.facility_types')[$this->facility_type]['label'] ?? $this->facility_type
        );
    }

    public function applicableNorms(): Attribute
    {
        return Attribute::make(
            get: fn() => config('risk_analysis.facility_types')[$this->facility_type]['applicable_norms'] ?? []
        );
    }

    public function isLicenseExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->license_expiry && $this->license_expiry->diffInDays(now()) <= 30
        );
    }

    public function riskCategorySummary(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->riskEvaluations()
                ->selectRaw('risk_category, COUNT(*) as count')
                ->where('is_present', true)
                ->groupBy('risk_category')
                ->pluck('count', 'risk_category')
                ->toArray()
        );
    }

    public function highRiskCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->riskEvaluations()
                ->where('is_present', true)
                ->where('risk_category', '>=', 4)
                ->count()
        );
    }

    public function overdueMaintenanceCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->maintenanceChecks()
                ->where('status', 'overdue')
                ->count()
        );
    }

    public function requiresOperatorQualification(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->facility_type, ['amusement_park', 'fairground'])
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByFacilityType($query, $type)
    {
        return $query->where('facility_type', $type);
    }

    public function scopeRequiringInspection($query)
    {
        return $query->whereDate('last_analysis_date', '<', now()->subYear())
            ->orWhereNull('last_analysis_date');
    }

    public function scopeWithExpiredLicense($query)
    {
        return $query->where('license_expiry', '<', now());
    }
}

// app/Models/Equipment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
    protected $fillable = [
        'playground_id', 'reference_code', 'equipment_category', 'equipment_type',
        'ride_category', 'brand', 'manufacturer_details', 'supplier_details',
        'applicable_norms', 'ce_marking', 'declaration_of_conformity',
        'purchase_date', 'installation_date', 'verification_frequency',
        'risk_analysis_certificate', 'status', 'description', 'material',
        'height', 'max_speed', 'max_acceleration', 'max_passengers',
        'min_height_requirement', 'max_weight_limit', 'age_group', 'dimensions',
        'requires_fall_protection', 'fall_height', 'safety_features',
        'is_mobile', 'setup_time_hours', 'power_consumption_kw',
        'structural_calculations_ref', 'weather_operating_limits',
        'requires_operator', 'operator_qualification', 'voltage', 'current',
        'protection_class', 'ip_rating', 'requires_earth_connection',
        'electrical_test_date', 'electrical_certificate',
    ];

    protected $casts = [
        'purchase_date'             => 'date',
        'installation_date'         => 'date',
        'electrical_test_date'      => 'date',
        'applicable_norms'          => 'array',
        'dimensions'                => 'array',
        'weather_operating_limits'  => 'array',
        'requires_fall_protection'  => 'boolean',
        'is_mobile'                 => 'boolean',
        'requires_operator'         => 'boolean',
        'requires_earth_connection' => 'boolean',
        'height'                    => 'decimal:2',
        'max_speed'                 => 'decimal:2',
        'max_acceleration'          => 'decimal:2',
        'min_height_requirement'    => 'decimal:2',
        'max_weight_limit'          => 'decimal:2',
        'fall_height'               => 'decimal:2',
        'setup_time_hours'          => 'decimal:1',
        'power_consumption_kw'      => 'decimal:2',
        'voltage'                   => 'decimal:2',
        'current'                   => 'decimal:2',
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

    public function certifications(): HasMany
    {
        return $this->hasMany(EquipmentCertification::class);
    }

    public function amusementRideInspections(): HasMany
    {
        return $this->hasMany(AmusementRideInspection::class);
    }

    public function technicalData(): HasOne
    {
        return $this->hasOne(AmusementRideTechnicalData::class);
    }

    public function electricalTests(): HasMany
    {
        return $this->hasMany(ElectricalSafetyTest::class);
    }

    // Accessors
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
            get: fn() => $this->ride_category !== 'not_applicable'
            ? config('risk_analysis.ride_categories')[$this->ride_category]['label'] ?? $this->ride_category
            : null
        );
    }

    public function requiresSpecializedInspection(): Attribute
    {
        return Attribute::make(
            get: fn() => in_array($this->equipment_category, ['amusement_ride', 'electrical_system'])
        );
    }

    public function isElectricalTestDue(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->equipment_category === 'electrical_system' &&
            (! $this->electrical_test_date || $this->electrical_test_date->addYear()->isPast())
        );
    }

    public function lastInspectionDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->amusementRideInspections()
                ->orderBy('inspection_date', 'desc')
                ->first()?->inspection_date
        );
    }

    public function operationAuthorized(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->amusementRideInspections()
                ->orderBy('inspection_date', 'desc')
                ->first()?->operation_authorized ?? true
        );
    }

    public function currentRiskLevel(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->riskEvaluations()
                ->where('is_present', true)
                ->max('risk_category') ?? 0
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('equipment_category', $category);
    }

    public function scopeRequiringInspection($query)
    {
        return $query->where('equipment_category', 'amusement_ride')
            ->whereDoesntHave('amusementRideInspections', function ($subQuery) {
                $subQuery->where('inspection_date', '>=', now()->subMonth());
            });
    }

    public function scopeElectricalTestDue($query)
    {
        return $query->where('equipment_category', 'electrical_system')
            ->where(function ($q) {
                $q->whereNull('electrical_test_date')
                    ->orWhere('electrical_test_date', '<', now()->subYear());
            });
    }

    public function scopeHighRisk($query)
    {
        return $query->whereHas('riskEvaluations', function ($subQuery) {
            $subQuery->where('is_present', true)->where('risk_category', '>=', 4);
        });
    }
}

// app/Models/RiskEvaluation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskEvaluation extends Model
{
    protected $fillable = [
        'playground_id', 'equipment_id', 'danger_category_id', 'evaluation_type',
        'is_present', 'risk_description', 'probability_value', 'exposure_value',
        'gravity_value', 'preventive_measures', 'implemented_measures',
        'target_date', 'measure_status', 'evaluator_name', 'evaluation_date',
        'next_review_date', 'comments',
    ];

    protected $casts = [
        'evaluation_date'   => 'date',
        'target_date'       => 'date',
        'next_review_date'  => 'date',
        'is_present'        => 'boolean',
        'probability_value' => 'decimal:1',
        'exposure_value'    => 'decimal:1',
        'gravity_value'     => 'decimal:1',
    ];

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

    // Accessors - Les colonnes calculées sont déjà dans la migration
    public function riskCategoryLabel(): Attribute
    {
        return Attribute::make(
            get: fn() => config('risk_analysis.fine_kinney.risk_categories')[$this->risk_category]['label'] ?? 'Non évalué'
        );
    }

    public function riskCategoryColor(): Attribute
    {
        return Attribute::make(
            get: fn() => config('risk_analysis.fine_kinney.risk_categories')[$this->risk_category]['color'] ?? 'gray'
        );
    }

    public function actionRequired(): Attribute
    {
        return Attribute::make(
            get: fn() => config('risk_analysis.fine_kinney.risk_categories')[$this->risk_category]['action'] ?? 'Évaluation requise'
        );
    }

    public function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->target_date && $this->target_date->isPast() && $this->measure_status !== 'completed'
        );
    }

    public function applicableNorm(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->dangerCategory?->regulation_reference
        );
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('is_present', true);
    }

    public function scopeHighRisk($query)
    {
        return $query->where('risk_category', '>=', 4);
    }

    public function scopeByNorm($query, $norm)
    {
        return $query->whereHas('dangerCategory', function ($subQuery) use ($norm) {
            $subQuery->where('regulation_reference', 'like', "%{$norm}%");
        });
    }

    public function scopePendingMeasures($query)
    {
        return $query->where('measure_status', '!=', 'completed')
            ->whereNotNull('target_date');
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_date', '<', now())
            ->where('measure_status', '!=', 'completed');
    }
}

// app/Models/DangerCategory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DangerCategory extends Model
{
    protected $fillable = [
        'code', 'title', 'description', 'applies_to', 'is_active',
        'sort_order', 'regulation_reference', 'typical_examples', 'default_measures',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'default_measures' => 'array',
    ];

    // Relations
    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    // Accessors
    public function appliesLabel(): Attribute
    {
        return Attribute::make(
            get: fn()    => match ($this->applies_to) {
                'playground' => 'Aire de jeux',
                'equipment'  => 'Équipement',
                default      => $this->applies_to
            }
        );
    }

    public function normCategory(): Attribute
    {
        return Attribute::make(
            get: fn()                                             => match (true) {
                str_contains($this->regulation_reference, 'EN 1176')  => 'EN 1176',
                str_contains($this->regulation_reference, 'EN 1177')  => 'EN 1177',
                str_contains($this->regulation_reference, 'EN 13814') => 'EN 13814',
                str_contains($this->regulation_reference, 'EN 60335') => 'EN 60335',
                default                                               => 'Autre'
            }
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlayground($query)
    {
        return $query->where('applies_to', 'playground');
    }

    public function scopeForEquipment($query)
    {
        return $query->where('applies_to', 'equipment');
    }

    public function scopeByNorm($query, $norm)
    {
        return $query->where('regulation_reference', 'like', "%{$norm}%");
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
