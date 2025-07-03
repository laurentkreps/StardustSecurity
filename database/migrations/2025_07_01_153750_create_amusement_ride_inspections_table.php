<?php
// =============================================================================
// MIGRATION MANQUANTE 1: amusement_ride_inspections
// =============================================================================

// Créer le fichier: database/migrations/2025_07_01_153750_create_amusement_ride_inspections_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amusement_ride_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('inspection_type', [
                'assembly_inspection',      // Contrôle de montage
                'commissioning_test',       // Essai de mise en service
                'daily_check',              // Contrôle quotidien
                'periodic_inspection',      // Inspection périodique
                'extraordinary_inspection', // Contrôle extraordinaire
                'dismantling_check',        // Contrôle de démontage
            ])->comment('Type d\'inspection selon EN 13814');
            $table->string('inspector_name')->comment('Nom de l\'inspecteur');
            $table->string('inspector_qualification')->comment('Qualification de l\'inspecteur');
            $table->string('inspection_body')->nullable()->comment('Organisme de contrôle');
            $table->date('inspection_date')->comment('Date de l\'inspection');
            $table->time('start_time')->nullable()->comment('Heure de début');
            $table->time('end_time')->nullable()->comment('Heure de fin');

            // Conditions d'inspection
            $table->json('weather_conditions')->nullable()->comment('Conditions météorologiques');
            $table->decimal('wind_speed', 5, 1)->nullable()->comment('Vitesse du vent (km/h)');

            // Contrôles selon EN 13814
            $table->json('structural_checks')->nullable()->comment('Contrôles structurels');
            $table->json('mechanical_checks')->nullable()->comment('Contrôles mécaniques');
            $table->json('electrical_checks')->nullable()->comment('Contrôles électriques');
            $table->json('safety_system_checks')->nullable()->comment('Systèmes de sécurité');
            $table->json('restraint_system_checks')->nullable()->comment('Systèmes de retenue');

            // Tests de fonctionnement
            $table->boolean('test_run_performed')->default(false)->comment('Test de fonctionnement effectué');
            $table->integer('test_cycles')->nullable()->comment('Nombre de cycles de test');
            $table->decimal('max_speed_recorded', 6, 2)->nullable()->comment('Vitesse max enregistrée (km/h)');
            $table->decimal('max_acceleration_recorded', 5, 2)->nullable()->comment('Accélération max (m/s²)');

            // Résultats
            $table->enum('overall_result', [
                'conformite',                // Conforme
                'non_conformite_mineure',    // Non-conformité mineure
                'non_conformite_majeure',    // Non-conformité majeure
                'interdiction_exploitation', // Interdiction d'exploitation
            ])->comment('Résultat global de l\'inspection');
            $table->text('observations')->nullable()->comment('Observations générales');
            $table->text('defects_found')->nullable()->comment('Défauts constatés');
            $table->text('corrective_actions')->nullable()->comment('Actions correctives');
            $table->date('next_inspection_date')->nullable()->comment('Prochaine inspection');
            $table->boolean('operation_authorized')->default(true)->comment('Autorisation d\'exploitation');
            $table->json('operating_restrictions')->nullable()->comment('Restrictions d\'exploitation');
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index(['equipment_id', 'inspection_type']);
            $table->index(['inspection_date', 'operation_authorized']);
            $table->index('overall_result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amusement_ride_inspections');
    }
};
