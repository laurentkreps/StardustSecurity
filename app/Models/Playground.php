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
