<?php

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
