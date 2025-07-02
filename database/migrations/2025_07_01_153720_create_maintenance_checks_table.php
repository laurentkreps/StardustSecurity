<?php
// database/migrations/2024_01_01_000005_create_maintenance_checks_table.php

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
        Schema::create('maintenance_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('check_type', [
                'regular_verification', // Vérification régulière
                'maintenance',          // Entretien
                'periodic_control',     // Contrôle périodique
            ])->comment('Type de contrôle');
            $table->date('scheduled_date')->comment('Date prévue');
            $table->date('completed_date')->nullable()->comment('Date de réalisation');
            $table->string('inspector_name')->nullable()->comment('Nom de l\'inspecteur');
            $table->string('inspector_qualification')->nullable()->comment('Qualification de l\'inspecteur');
            $table->text('observations')->nullable()->comment('Observations générales');
            $table->text('issues_found')->nullable()->comment('Problèmes identifiés');
            $table->text('actions_taken')->nullable()->comment('Actions réalisées');
            $table->text('recommendations')->nullable()->comment('Recommandations');
            $table->date('next_check_date')->nullable()->comment('Prochaine échéance');
            $table->enum('overall_condition', [
                'excellent', 'good', 'acceptable', 'poor', 'critical',
            ])->nullable()->comment('État général');
            $table->enum('status', [
                'scheduled',   // Programmé
                'in_progress', // En cours
                'completed',   // Terminé
                'overdue',     // En retard
                'cancelled',   // Annulé
            ])->default('scheduled');
            $table->decimal('duration_hours', 4, 2)->nullable()->comment('Durée en heures');
            $table->decimal('cost', 8, 2)->nullable()->comment('Coût de l\'intervention');
            $table->string('weather_conditions', 100)->nullable()->comment('Conditions météo');
            $table->json('checklist_items')->nullable()->comment('Éléments de contrôle détaillés');
            $table->json('photos')->nullable()->comment('Photos prises pendant le contrôle');
            $table->boolean('requires_follow_up')->default(false)->comment('Nécessite un suivi');
            $table->date('follow_up_date')->nullable()->comment('Date de suivi');
            $table->text('compliance_notes')->nullable()->comment('Notes de conformité');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['playground_id', 'check_type']);
            $table->index(['scheduled_date', 'status']);
            $table->index('completed_date');
            $table->index(['equipment_id', 'check_type']);
            $table->index(['next_check_date', 'status']);
            $table->index(['requires_follow_up', 'follow_up_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_checks');
    }
};
