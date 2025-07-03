<?php
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
