<?php

// =============================================================================
// MIGRATION MANQUANTE 3: amusement_ride_technical_data
// =============================================================================

// Créer le fichier: database/migrations/2025_07_01_153760_create_amusement_ride_technical_data_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amusement_ride_technical_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');

            // Données techniques de performance
            $table->decimal('max_design_speed', 6, 2)->nullable()->comment('Vitesse max de conception (km/h)');
            $table->decimal('max_operating_speed', 6, 2)->nullable()->comment('Vitesse max d\'exploitation (km/h)');
            $table->decimal('max_acceleration', 5, 2)->nullable()->comment('Accélération max (m/s²)');
            $table->decimal('max_deceleration', 5, 2)->nullable()->comment('Décélération max (m/s²)');
            $table->decimal('cycle_time', 6, 2)->nullable()->comment('Temps de cycle (s)');

            // Restrictions passagers
            $table->decimal('min_passenger_height', 4, 1)->nullable()->comment('Taille min passager (cm)');
            $table->decimal('max_passenger_height', 4, 1)->nullable()->comment('Taille max passager (cm)');
            $table->decimal('min_passenger_weight', 5, 1)->nullable()->comment('Poids min passager (kg)');
            $table->decimal('max_passenger_weight', 5, 1)->nullable()->comment('Poids max passager (kg)');
            $table->integer('min_passenger_age')->nullable()->comment('Âge minimum');
            $table->integer('max_passengers_per_unit')->nullable()->comment('Passagers max par unité');
            $table->integer('total_passenger_capacity')->nullable()->comment('Capacité totale passagers');

            // Données structurelles
            $table->decimal('total_weight', 8, 2)->nullable()->comment('Poids total (kg)');
            $table->decimal('foundation_load', 8, 2)->nullable()->comment('Charge sur fondations (kN)');
            $table->decimal('wind_resistance', 5, 1)->nullable()->comment('Résistance au vent (km/h)');
            $table->string('structural_material')->nullable()->comment('Matériau de structure');
            $table->json('foundation_requirements')->nullable()->comment('Exigences fondations');

            // Données électriques
            $table->decimal('power_consumption', 8, 2)->nullable()->comment('Consommation électrique (kW)');
            $table->decimal('supply_voltage', 6, 1)->nullable()->comment('Tension d\'alimentation (V)');
            $table->decimal('supply_current', 8, 2)->nullable()->comment('Courant d\'alimentation (A)');
            $table->string('protection_class')->nullable()->comment('Classe de protection');
            $table->string('ip_rating')->nullable()->comment('Indice de protection IP');

            // Systèmes de sécurité
            $table->json('safety_systems')->nullable()->comment('Systèmes de sécurité');
            $table->json('restraint_systems')->nullable()->comment('Systèmes de retenue');
            $table->json('emergency_systems')->nullable()->comment('Systèmes d\'urgence');
            $table->decimal('emergency_stop_distance', 6, 2)->nullable()->comment('Distance d\'arrêt d\'urgence (m)');

            // Conditions d'exploitation
            $table->decimal('max_wind_speed_operation', 5, 1)->nullable()->comment('Vent max exploitation (km/h)');
            $table->decimal('min_temperature_operation', 4, 1)->nullable()->comment('Température min (°C)');
            $table->decimal('max_temperature_operation', 4, 1)->nullable()->comment('Température max (°C)');
            $table->boolean('rain_operation_allowed')->default(false)->comment('Exploitation sous la pluie');
            $table->json('weather_restrictions')->nullable()->comment('Restrictions météo');
            $table->timestamps();

            // Index
            $table->index('equipment_id');
            $table->index(['max_operating_speed', 'max_acceleration']);
            $table->index('rain_operation_allowed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amusement_ride_technical_data');
    }
};
