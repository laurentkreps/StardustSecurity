<?php
// =============================================================================
// MIGRATION CORRIGÉE 2: incident_reports (SIMPLIFIÉE)
// =============================================================================

// Remplacer le contenu de : 2025_07_01_153726_create_incident_reports_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->id();
            $table->string('incident_number', 20)->nullable()->unique()->comment('Numéro unique d\'incident');
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained()->onDelete('set null');
            $table->dateTime('incident_date')->comment('Date et heure de l\'incident');
            $table->enum('incident_type', [
                'accident',         // Accident
                'serious_incident', // Incident grave
                'damage',           // Dégâts matériels
                'near_miss',        // Presque accident
                'vandalism',        // Vandalisme
                'other',            // Autre
            ])->comment('Type d\'incident');
            $table->enum('severity', [
                'minor',    // Mineur
                'moderate', // Modéré
                'serious',  // Grave
                'critical', // Critique
            ])->comment('Gravité');
            $table->text('description')->comment('Description détaillée');
            $table->text('circumstances')->nullable()->comment('Circonstances de l\'incident');
            $table->text('persons_involved')->nullable()->comment('Personnes impliquées (JSON as text)');
            $table->text('witnesses')->nullable()->comment('Témoins (JSON as text)');
            $table->text('injuries_description')->nullable()->comment('Description des blessures');
            $table->boolean('medical_assistance_required')->default(false)->comment('Assistance médicale requise');
            $table->text('immediate_actions')->nullable()->comment('Actions immédiates prises');
            $table->text('preventive_measures')->nullable()->comment('Mesures préventives proposées');
            $table->boolean('reported_to_authorities')->default(false)->comment('Signalé aux autorités');
            $table->date('authority_report_date')->nullable()->comment('Date de signalement aux autorités');
            $table->string('authority_reference')->nullable()->comment('Référence du signalement');
            $table->string('reporter_name')->comment('Nom du déclarant');
            $table->string('reporter_contact')->nullable()->comment('Contact du déclarant');
            $table->string('reporter_function')->nullable()->comment('Fonction du déclarant');
            $table->enum('status', [
                'reported',      // Signalé
                'investigating', // En cours d\'investigation
                'resolved',      // Résolu
                'closed',        // Clôturé
            ])->default('reported');
            $table->text('investigation_notes')->nullable()->comment('Notes d\'investigation');
            $table->text('corrective_actions')->nullable()->comment('Actions correctives (JSON as text)');
            $table->date('closure_date')->nullable()->comment('Date de clôture');
            $table->string('closed_by')->nullable()->comment('Clôturé par');
            $table->text('lessons_learned')->nullable()->comment('Enseignements tirés');
            $table->text('attachments')->nullable()->comment('Pièces jointes (JSON as text)');
            $table->string('weather_conditions', 100)->nullable()->comment('Conditions météorologiques');
            $table->integer('visitor_count_estimate')->nullable()->comment('Estimation du nombre de visiteurs');
            $table->time('incident_time')->nullable()->comment('Heure précise de l\'incident');
            $table->decimal('temperature', 4, 1)->nullable()->comment('Température en °C');
            $table->boolean('requires_equipment_shutdown')->default(false)->comment('Nécessite arrêt équipement');
            $table->date('equipment_restart_date')->nullable()->comment('Date de remise en service');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['playground_id', 'incident_type']);
            $table->index(['incident_date', 'severity']);
            $table->index(['status', 'reported_to_authorities']);
            $table->index('equipment_id');
            $table->index(['requires_equipment_shutdown', 'equipment_restart_date']);
            $table->index('closure_date');
            $table->index('incident_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};
