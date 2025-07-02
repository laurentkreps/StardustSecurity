<?php
// app/Models/EquipmentCertification.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCertification extends Model
{
    protected $fillable = [
        'equipment_id', 'certification_type', 'norm_reference', 'certificate_number',
        'issuing_body', 'issue_date', 'expiry_date', 'status', 'scope',
        'restrictions', 'document_path', 'technical_data',
    ];

    protected $casts = [
        'issue_date'     => 'date',
        'expiry_date'    => 'date',
        'technical_data' => 'array',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function certificationTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn()                => match ($this->certification_type) {
                'ce_marking'             => 'Marquage CE',
                'declaration_conformity' => 'Déclaration de conformité',
                'type_examination'       => 'Examen de type',
                'production_quality'     => 'Assurance qualité production',
                'electrical_safety'      => 'Sécurité électrique',
                'structural_calculation' => 'Calculs de structure',
                'installation_approval'  => 'Agrément d\'installation',
                'operational_permit'     => 'Permis d\'exploitation',
                default                  => $this->certification_type
            }
        );
    }

    public function isExpiringSoon(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->expiry_date && $this->expiry_date->diffInDays(now()) <= 60
        );
    }

    public function isValid(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'valid' && (! $this->expiry_date || $this->expiry_date->isFuture())
        );
    }

    // Scopes
    public function scopeValid($query)
    {
        return $query->where('status', 'valid')
            ->where(function ($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>', now());
            });
    }

    public function scopeExpiringSoon($query, $days = 60)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeByNorm($query, $norm)
    {
        return $query->where('norm_reference', 'like', "%{$norm}%");
    }
}

// app/Models/AmusementRideInspection.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmusementRideInspection extends Model
{
    protected $fillable = [
        'equipment_id', 'inspection_type', 'inspector_name', 'inspector_qualification',
        'inspection_body', 'inspection_date', 'start_time', 'end_time',
        'weather_conditions', 'wind_speed', 'structural_checks', 'mechanical_checks',
        'electrical_checks', 'safety_system_checks', 'restraint_system_checks',
        'test_run_performed', 'test_cycles', 'max_speed_recorded', 'max_acceleration_recorded',
        'overall_result', 'observations', 'defects_found', 'corrective_actions',
        'next_inspection_date', 'operation_authorized', 'operating_restrictions',
    ];

    protected $casts = [
        'inspection_date'           => 'date',
        'next_inspection_date'      => 'date',
        'start_time'                => 'datetime:H:i',
        'end_time'                  => 'datetime:H:i',
        'weather_conditions'        => 'array',
        'structural_checks'         => 'array',
        'mechanical_checks'         => 'array',
        'electrical_checks'         => 'array',
        'safety_system_checks'      => 'array',
        'restraint_system_checks'   => 'array',
        'operating_restrictions'    => 'array',
        'test_run_performed'        => 'boolean',
        'operation_authorized'      => 'boolean',
        'wind_speed'                => 'decimal:1',
        'max_speed_recorded'        => 'decimal:2',
        'max_acceleration_recorded' => 'decimal:2',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function inspectionTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn()                  => match ($this->inspection_type) {
                'assembly_inspection'      => 'Contrôle de montage',
                'commissioning_test'       => 'Essai de mise en service',
                'daily_check'              => 'Contrôle quotidien',
                'periodic_inspection'      => 'Inspection périodique',
                'extraordinary_inspection' => 'Contrôle extraordinaire',
                'dismantling_check'        => 'Contrôle de démontage',
                default                    => $this->inspection_type
            }
        );
    }

    public function overallResultLabel(): Attribute
    {
        return Attribute::make(
            get: fn()                   => match ($this->overall_result) {
                'conformite'                => 'Conforme',
                'non_conformite_mineure'    => 'Non-conformité mineure',
                'non_conformite_majeure'    => 'Non-conformité majeure',
                'interdiction_exploitation' => 'Interdiction d\'exploitation',
                default                     => $this->overall_result
            }
        );
    }

    public function resultColor(): Attribute
    {
        return Attribute::make(
            get: fn()                   => match ($this->overall_result) {
                'conformite'                => 'green',
                'non_conformite_mineure'    => 'yellow',
                'non_conformite_majeure'    => 'orange',
                'interdiction_exploitation' => 'red',
                default                     => 'gray'
            }
        );
    }

    public function inspectionDuration(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->start_time && $this->end_time
            ? $this->start_time->diffInMinutes($this->end_time)
            : null
        );
    }

    // Scopes
    public function scopeAuthorizedForOperation($query)
    {
        return $query->where('operation_authorized', true);
    }

    public function scopeNonCompliant($query)
    {
        return $query->whereIn('overall_result', ['non_conformite_majeure', 'interdiction_exploitation']);
    }

    public function scopeRecentInspections($query, $days = 30)
    {
        return $query->where('inspection_date', '>=', now()->subDays($days));
    }
}

// app/Models/AmusementRideTechnicalData.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmusementRideTechnicalData extends Model
{
    protected $fillable = [
        'equipment_id', 'max_design_speed', 'max_operating_speed', 'max_acceleration',
        'max_deceleration', 'cycle_time', 'min_passenger_height', 'max_passenger_height',
        'min_passenger_weight', 'max_passenger_weight', 'min_passenger_age',
        'max_passengers_per_unit', 'total_passenger_capacity', 'total_weight',
        'foundation_load', 'wind_resistance', 'structural_material', 'foundation_requirements',
        'power_consumption', 'supply_voltage', 'supply_current', 'protection_class',
        'ip_rating', 'safety_systems', 'restraint_systems', 'emergency_systems',
        'emergency_stop_distance', 'max_wind_speed_operation', 'min_temperature_operation',
        'max_temperature_operation', 'rain_operation_allowed', 'weather_restrictions',
    ];

    protected $casts = [
        'foundation_requirements'   => 'array',
        'safety_systems'            => 'array',
        'restraint_systems'         => 'array',
        'emergency_systems'         => 'array',
        'weather_restrictions'      => 'array',
        'rain_operation_allowed'    => 'boolean',
        'max_design_speed'          => 'decimal:2',
        'max_operating_speed'       => 'decimal:2',
        'max_acceleration'          => 'decimal:2',
        'max_deceleration'          => 'decimal:2',
        'cycle_time'                => 'decimal:2',
        'min_passenger_height'      => 'decimal:1',
        'max_passenger_height'      => 'decimal:1',
        'min_passenger_weight'      => 'decimal:1',
        'max_passenger_weight'      => 'decimal:1',
        'total_weight'              => 'decimal:2',
        'foundation_load'           => 'decimal:2',
        'wind_resistance'           => 'decimal:1',
        'power_consumption'         => 'decimal:2',
        'supply_voltage'            => 'decimal:1',
        'supply_current'            => 'decimal:1',
        'max_wind_speed_operation'  => 'decimal:1',
        'min_temperature_operation' => 'decimal:1',
        'max_temperature_operation' => 'decimal:1',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function operatingConditionsOk(): Attribute
    {
        return Attribute::make(
            get: function () {
                                                              // Exemple de vérification des conditions d'exploitation
                $currentWeather = $this->getCurrentWeather(); // À implémenter

                if (! $currentWeather) {
                    return true;
                }
                // Si pas de données météo, on autorise

                return $currentWeather['wind_speed'] <= $this->max_wind_speed_operation &&
                $currentWeather['temperature'] >= $this->min_temperature_operation &&
                $currentWeather['temperature'] <= $this->max_temperature_operation &&
                    ($this->rain_operation_allowed || ! $currentWeather['rain']);
            }
        );
    }

    public function rideCategoryFromData(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Déterminer la catégorie EN 13814 selon les caractéristiques
                if ($this->max_operating_speed <= 2.0 && $this->equipment->height <= 2.0) {
                    return 'category_1';
                } elseif ($this->max_acceleration <= 3.5) {
                    return 'category_2';
                } elseif ($this->max_acceleration <= 4.5) {
                    return 'category_3';
                } else {
                    return 'category_4';
                }
            }
        );
    }

    private function getCurrentWeather()
    {
        // À implémenter : récupérer les données météo actuelles
        // Peut être intégré avec une API météo
        return null;
    }
}

// app/Models/ElectricalSafetyTest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectricalSafetyTest extends Model
{
    protected $fillable = [
        'equipment_id', 'test_type', 'tester_name', 'tester_qualification',
        'test_date', 'test_equipment_used', 'insulation_resistance', 'earth_resistance',
        'rcd_trip_current', 'rcd_trip_time', 'polarity_correct', 'load_test_current',
        'voltage_measurements', 'ambient_temperature', 'relative_humidity',
        'test_conditions', 'test_result', 'observations', 'defects_found',
        'recommendations', 'next_test_date', 'safe_to_use',
    ];

    protected $casts = [
        'test_date'             => 'date',
        'next_test_date'        => 'date',
        'voltage_measurements'  => 'array',
        'polarity_correct'      => 'boolean',
        'safe_to_use'           => 'boolean',
        'insulation_resistance' => 'decimal:2',
        'earth_resistance'      => 'decimal:3',
        'rcd_trip_current'      => 'decimal:1',
        'rcd_trip_time'         => 'decimal:1',
        'load_test_current'     => 'decimal:2',
        'ambient_temperature'   => 'decimal:1',
        'relative_humidity'     => 'decimal:1',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function testTypeLabel(): Attribute
    {
        return Attribute::make(
            get: fn()              => match ($this->test_type) {
                'initial_verification' => 'Vérification initiale',
                'routine_test'         => 'Essai de routine',
                'periodic_test'        => 'Essai périodique',
                'pat_test'             => 'Test d\'appareils portables',
                'insulation_test'      => 'Test d\'isolement',
                'earth_continuity'     => 'Continuité de terre',
                'rcd_test'             => 'Test différentiel',
                'polarity_test'        => 'Test de polarité',
                'load_test'            => 'Test en charge',
                default                => $this->test_type
            }
        );
    }

    public function testResultLabel(): Attribute
    {
        return Attribute::make(
            get: fn()         => match ($this->test_result) {
                'pass'            => 'Réussi',
                'fail'            => 'Échec',
                'conditional'     => 'Conditionnel',
                'retest_required' => 'Nouveau test requis',
                default           => $this->test_result
            }
        );
    }

    public function resultColor(): Attribute
    {
        return Attribute::make(
            get: fn()         => match ($this->test_result) {
                'pass'            => 'green',
                'fail'            => 'red',
                'conditional'     => 'yellow',
                'retest_required' => 'orange',
                default           => 'gray'
            }
        );
    }

    public function complianceStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Vérifier la conformité selon EN 60335-1
                $standards = config('risk_analysis.electrical_standards');

                return match ($this->test_type) {
                    'insulation_test'  => $this->insulation_resistance >= 1.0,                         // MΩ
                    'earth_continuity' => $this->earth_resistance <= 0.1,                              // Ω
                    'rcd_test'         => $this->rcd_trip_current <= 30 && $this->rcd_trip_time <= 40, // mA, ms
                    default            => $this->test_result === 'pass'
                };
            }
        );
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('test_result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('test_result', 'fail');
    }

    public function scopeSafeToUse($query)
    {
        return $query->where('safe_to_use', true);
    }

    public function scopeDueForRetest($query)
    {
        return $query->where('next_test_date', '<=', now());
    }
}

// app/Models/QualifiedOperator.php
namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualifiedOperator extends Model
{
    protected $fillable = [
        'playground_id', 'first_name', 'last_name', 'birth_date', 'employee_id',
        'phone', 'email', 'equipment_qualifications', 'certifications',
        'training_completion_date', 'certification_expiry', 'medical_fitness_valid',
        'medical_check_date', 'medical_check_expiry', 'years_experience',
        'previous_experience', 'languages_spoken', 'status', 'employment_start',
        'employment_end', 'notes',
    ];

    protected $casts = [
        'birth_date'               => 'date',
        'training_completion_date' => 'date',
        'certification_expiry'     => 'date',
        'medical_check_date'       => 'date',
        'medical_check_expiry'     => 'date',
        'employment_start'         => 'date',
        'employment_end'           => 'date',
        'equipment_qualifications' => 'array',
        'certifications'           => 'array',
        'languages_spoken'         => 'array',
        'medical_fitness_valid'    => 'boolean',
    ];

    public function playground(): BelongsTo
    {
        return $this->belongsTo(Playground::class);
    }

    public function fullName(): Attribute
    {
        return Attribute::make(
            get: fn() => "{$this->first_name} {$this->last_name}"
        );
    }

    public function age(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->birth_date ? $this->birth_date->age : null
        );
    }

    public function isCertificationExpiring(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->certification_expiry && $this->certification_expiry->diffInDays(now()) <= 60
        );
    }

    public function isMedicalCheckExpiring(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->medical_check_expiry && $this->medical_check_expiry->diffInDays(now()) <= 30
        );
    }

    public function isQualifiedFor(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->status === 'active' &&
            $this->medical_fitness_valid &&
            (! $this->certification_expiry || $this->certification_expiry->isFuture())
        );
    }

    public function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn()   => match ($this->status) {
                'active'    => 'Actif',
                'inactive'  => 'Inactif',
                'suspended' => 'Suspendu',
                'training'  => 'En formation',
                default     => $this->status
            }
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeQualified($query)
    {
        return $query->where('status', 'active')
            ->where('medical_fitness_valid', true)
            ->where(function ($q) {
                $q->whereNull('certification_expiry')
                    ->orWhere('certification_expiry', '>', now());
            });
    }

    public function scopeQualifiedForEquipment($query, $equipmentType)
    {
        return $query->qualified()
            ->whereJsonContains('equipment_qualifications', $equipmentType);
    }

    public function scopeExpiringCertifications($query, $days = 60)
    {
        return $query->where('certification_expiry', '<=', now()->addDays($days))
            ->where('certification_expiry', '>', now());
    }
}
