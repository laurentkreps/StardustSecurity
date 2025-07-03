<?php

// =============================================================================
// MIGRATION MANQUANTE 2: electrical_safety_tests
// =============================================================================

// Créer le fichier: database/migrations/2025_07_01_153755_create_electrical_safety_tests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            ])->comment('Type de test selon EN 60335');
            $table->string('tester_name')->comment('Nom du testeur');
            $table->string('tester_qualification')->comment('Qualification du testeur');
            $table->date('test_date')->comment('Date du test');
            $table->string('test_equipment_used')->nullable()->comment('Équipement de test utilisé');

            // Mesures électriques
            $table->decimal('insulation_resistance', 8, 2)->nullable()->comment('Résistance isolement (MΩ)');
            $table->decimal('earth_resistance', 8, 3)->nullable()->comment('Résistance de terre (Ω)');
            $table->decimal('rcd_trip_current', 6, 1)->nullable()->comment('Courant déclenchement RCD (mA)');
            $table->decimal('rcd_trip_time', 6, 1)->nullable()->comment('Temps déclenchement RCD (ms)');
            $table->boolean('polarity_correct')->nullable()->comment('Polarité correcte');
            $table->decimal('load_test_current', 8, 2)->nullable()->comment('Courant test en charge (A)');
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
            $table->text('observations')->nullable()->comment('Observations');
            $table->text('defects_found')->nullable()->comment('Défauts constatés');
            $table->text('recommendations')->nullable()->comment('Recommandations');
            $table->date('next_test_date')->nullable()->comment('Prochain test');
            $table->boolean('safe_to_use')->default(true)->comment('Sûr à utiliser');
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index(['equipment_id', 'test_type']);
            $table->index(['test_date', 'safe_to_use']);
            $table->index('test_result');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('electrical_safety_tests');
    }
};
