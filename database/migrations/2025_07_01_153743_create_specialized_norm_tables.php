<?php
// database/migrations/2024_01_01_000008_create_specialized_norm_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Table pour les certifications et conformités
        Schema::create('equipment_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('certification_type', [
                'ce_marking',             // Marquage CE
                'declaration_conformity', // Déclaration de conformité
                'type_examination',       // Examen de type
                'production_quality',     // Assurance qualité production
                'electrical_safety',      // Sécurité électrique
                'structural_calculation', // Calculs de structure
                'installation_approval',  // Agrément d'installation
                'operational_permit',     // Permis d'exploitation
            ])->comment('Type de certification');
            $table->string('norm_reference')->comment('Référence norme (EN 1176, EN 13814, etc.)');
            $table->string('certificate_number')->nullable()->comment('Numéro de certificat');
            $table->string('issuing_body')->comment('Organisme émetteur');
            $table->date('issue_date')->comment('Date d\'émission');
            $table->date('expiry_date')->nullable()->comment('Date d\'expiration');
            $table->enum('status', ['valid', 'expired', 'suspended', 'revoked'])->default('valid');
            $table->text('scope')->nullable()->comment('Domaine d\'application');
            $table->text('restrictions')->nullable()->comment('Restrictions d\'usage');
            $table->string('document_path')->nullable()->comment('Chemin vers le document');
            $table->json('technical_data')->nullable()->comment('Données techniques certifiées');
            $table->timestamps();

            $table->index(['equipment_id', 'certification_type']);
            $table->index(['norm_reference', 'status']);
            $table->index('expiry_date');
        });

        // Table pour les contrôles spécialisés selon EN 13814
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
            ])->comment('Type d\'inspection EN 13814');
            $table->string('inspector_name')->comment('Nom de l\'inspecteur');
            $table->string('inspector_qualification')->comment('Qualification inspecteur');
            $table->string('inspection_body')->nullable()->comment('Organisme de contrôle');
            $table->date('inspection_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->json('weather_conditions')->nullable()->comment('Conditions météo');
            $table->decimal('wind_speed', 5, 1)->nullable()->comment('Vitesse du vent (km/h)');

            // Contrôles techniques spécifiques
            $table->json('structural_checks')->nullable()->comment('Vérifications structurelles');
            $table->json('mechanical_checks')->nullable()->comment('Vérifications mécaniques');
            $table->json('electrical_checks')->nullable()->comment('Vérifications électriques');
            $table->json('safety_system_checks')->nullable()->comment('Vérifications systèmes sécurité');
            $table->json('restraint_system_checks')->nullable()->comment('Vérifications systèmes retenue');

            // Tests de fonctionnement
            $table->boolean('test_run_performed')->default(false)->comment('Essai de fonctionnement effectué');
            $table->integer('test_cycles')->nullable()->comment('Nombre de cycles d\'essai');
            $table->decimal('max_speed_recorded', 6, 2)->nullable()->comment('Vitesse max enregistrée');
            $table->decimal('max_acceleration_recorded', 5, 2)->nullable()->comment('Accélération max enregistrée');

            // Résultats
            $table->enum('overall_result', [
                'conformite',                // Conforme
                'non_conformite_mineure',    // Non-conformité mineure
                'non_conformite_majeure',    // Non-conformité majeure
                'interdiction_exploitation', // Interdiction d'exploitation
            ])->comment('Résultat global');
            $table->text('observations')->nullable();
            $table->text('defects_found')->nullable()->comment('Défauts constatés');
            $table->text('corrective_actions')->nullable()->comment('Actions correctives');
            $table->date('next_inspection_date')->nullable();
            $table->boolean('operation_authorized')->default(true)->comment('Exploitation autorisée');
            $table->json('operating_restrictions')->nullable()->comment('Restrictions d\'exploitation');

            $table->timestamps();

            $table->index(['equipment_id', 'inspection_type']);
            $table->index(['inspection_date', 'overall_result']);
            $table->index(['operation_authorized', 'next_inspection_date']);
        });

        // Table pour les données techniques spécifiques aux manèges
        Schema::create('amusement_ride_technical_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');

            // Caractéristiques dynamiques
            $table->decimal('max_design_speed', 6, 2)->nullable()->comment('Vitesse max de conception (km/h)');
            $table->decimal('max_operating_speed', 6, 2)->nullable()->comment('Vitesse max d\'exploitation (km/h)');
            $table->decimal('max_acceleration', 5, 2)->nullable()->comment('Accélération maximale (g)');
            $table->decimal('max_deceleration', 5, 2)->nullable()->comment('Décélération maximale (g)');
            $table->decimal('cycle_time', 6, 2)->nullable()->comment('Durée du cycle (secondes)');

            // Limites dimensionnelles
            $table->decimal('min_passenger_height', 4, 1)->nullable()->comment('Taille min passager (cm)');
            $table->decimal('max_passenger_height', 4, 1)->nullable()->comment('Taille max passager (cm)');
            $table->decimal('min_passenger_weight', 5, 1)->nullable()->comment('Poids min passager (kg)');
            $table->decimal('max_passenger_weight', 5, 1)->nullable()->comment('Poids max passager (kg)');
            $table->integer('min_passenger_age')->nullable()->comment('Âge minimum');
            $table->integer('max_passengers_per_unit')->nullable()->comment('Passagers max par unité');
            $table->integer('total_passenger_capacity')->nullable()->comment('Capacité totale');

            // Données structurelles
            $table->decimal('total_weight', 8, 2)->nullable()->comment('Poids total (kg)');
            $table->decimal('foundation_load', 8, 2)->nullable()->comment('Charge sur fondations (kN)');
            $table->decimal('wind_resistance', 5, 1)->nullable()->comment('Résistance au vent (km/h)');
            $table->string('structural_material')->nullable()->comment('Matériau structure principale');
            $table->json('foundation_requirements')->nullable()->comment('Exigences fondations');

            // Données électriques
            $table->decimal('power_consumption', 8, 2)->nullable()->comment('Consommation électrique (kW)');
            $table->decimal('supply_voltage', 6, 1)->nullable()->comment('Tension d\'alimentation (V)');
            $table->decimal('supply_current', 6, 1)->nullable()->comment('Courant d\'alimentation (A)');
            $table->string('protection_class')->nullable()->comment('Classe de protection');
            $table->string('ip_rating')->nullable()->comment('Indice de protection IP');

            // Systèmes de sécurité
            $table->json('safety_systems')->nullable()->comment('Systèmes de sécurité');
            $table->json('restraint_systems')->nullable()->comment('Systèmes de retenue');
            $table->json('emergency_systems')->nullable()->comment('Systèmes d\'urgence');
            $table->integer('emergency_stop_distance', false, true)->nullable()->comment('Distance arrêt urgence (m)');

            // Conditions d'exploitation
            $table->decimal('max_wind_speed_operation', 5, 1)->nullable()->comment('Vent max exploitation (km/h)');
            $table->decimal('min_temperature_operation', 4, 1)->nullable()->comment('Température min (°C)');
            $table->decimal('max_temperature_operation', 4, 1)->nullable()->comment('Température max (°C)');
            $table->boolean('rain_operation_allowed')->default(false)->comment('Exploitation sous la pluie');
            $table->json('weather_restrictions')->nullable()->comment('Restrictions météo');

            $table->timestamps();

            $table->unique('equipment_id');
        });

        // Table pour les tests électriques selon EN 60335
        Schema::create('electrical_safety_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
            $table->enum('test_type', [
                'initial_verification', // Vérification initiale
                'routine_test',         // Essai de routine
                'periodic_test',        // Essai périodique
                'pat_test',             // Test d'appareils portables
                'insulation_test',      // Test d'isolement
                'earth_continuity',     // Continuité de terre
                'rcd_test',             // Test différentiel
                'polarity_test',        // Test de polarité
                'load_test',            // Test en charge
            ])->comment('Type de test électrique');
            $table->string('tester_name')->comment('Nom du testeur');
            $table->string('tester_qualification')->comment('Qualification testeur');
            $table->date('test_date');
            $table->string('test_equipment_used')->nullable()->comment('Équipements de test utilisés');

            // Résultats des tests
            $table->decimal('insulation_resistance', 8, 2)->nullable()->comment('Résistance d\'isolement (MΩ)');
            $table->decimal('earth_resistance', 6, 3)->nullable()->comment('Résistance de terre (Ω)');
            $table->decimal('rcd_trip_current', 6, 1)->nullable()->comment('Courant déclenchement RCD (mA)');
            $table->decimal('rcd_trip_time', 6, 1)->nullable()->comment('Temps déclenchement RCD (ms)');
            $table->boolean('polarity_correct')->nullable()->comment('Polarité correcte');
            $table->decimal('load_test_current', 6, 2)->nullable()->comment('Courant test en charge (A)');
            $table->json('voltage_measurements')->nullable()->comment('Mesures de tension');

            // Conditions de test
            $table->decimal('ambient_temperature', 4, 1)->nullable()->comment('Température ambiante (°C)');
            $table->decimal('relative_humidity', 4, 1)->nullable()->comment('Humidité relative (%)');
            $table->text('test_conditions')->nullable()->comment('Conditions de test');

            // Résultats
            $table->enum('test_result', [
                'pass',            // Réussi
                'fail',            // Échec
                'conditional',     // Conditionnel
                'retest_required', // Nouveau test requis
            ])->comment('Résultat du test');
            $table->text('observations')->nullable();
            $table->text('defects_found')->nullable()->comment('Défauts trouvés');
            $table->text('recommendations')->nullable()->comment('Recommandations');
            $table->date('next_test_date')->nullable();
            $table->boolean('safe_to_use')->default(true)->comment('Sûr à utiliser');

            $table->timestamps();

            $table->index(['equipment_id', 'test_type']);
            $table->index(['test_date', 'test_result']);
            $table->index(['safe_to_use', 'next_test_date']);
        });

        // Table pour les opérateurs qualifiés (EN 13814)
        Schema::create('qualified_operators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date')->nullable();
            $table->string('employee_id')->nullable()->comment('Numéro employé');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Qualifications
            $table->json('equipment_qualifications')->nullable()->comment('Équipements qualifiés à opérer');
            $table->json('certifications')->nullable()->comment('Certifications détenues');
            $table->date('training_completion_date')->nullable();
            $table->date('certification_expiry')->nullable();
            $table->boolean('medical_fitness_valid')->default(true)->comment('Aptitude médicale valide');
            $table->date('medical_check_date')->nullable();
            $table->date('medical_check_expiry')->nullable();

            // Expérience
            $table->integer('years_experience')->nullable()->comment('Années d\'expérience');
            $table->text('previous_experience')->nullable();
            $table->json('languages_spoken')->nullable()->comment('Langues parlées');

            // Statut
            $table->enum('status', [
                'active',    // Actif
                'inactive',  // Inactif
                'suspended', // Suspendu
                'training',  // En formation
            ])->default('active');
            $table->date('employment_start')->nullable();
            $table->date('employment_end')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['playground_id', 'status']);
            $table->index('certification_expiry');
            $table->index('medical_check_expiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qualified_operators');
        Schema::dropIfExists('electrical_safety_tests');
        Schema::dropIfExists('amusement_ride_technical_data');
        Schema::dropIfExists('amusement_ride_inspections');
        Schema::dropIfExists('equipment_certifications');
    }
};
