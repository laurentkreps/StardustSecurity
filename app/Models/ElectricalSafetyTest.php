<?php

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
