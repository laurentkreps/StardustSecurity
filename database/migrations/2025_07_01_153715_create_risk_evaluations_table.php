<?php
// =============================================================================
// MIGRATION CORRIGÉE 1: risk_evaluations (COMPATIBLE SQLite/MySQL)
// =============================================================================

// Remplacer le contenu de : 2025_07_01_153715_create_risk_evaluations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('danger_category_id')->constrained()->onDelete('cascade');
            $table->enum('evaluation_type', ['initial', 'post_measures', 'periodic_review'])
                ->default('initial')
                ->comment('Type d\'évaluation');
            $table->boolean('is_present')->default(false)->comment('Danger présent/applicable');
            $table->text('risk_description')->nullable()->comment('Description du risque identifié');
            $table->decimal('probability_value', 3, 1)->nullable()->comment('Probabilité (0.1 à 10)');
            $table->decimal('exposure_value', 3, 1)->nullable()->comment('Exposition (0.5 à 10)');
            $table->decimal('gravity_value', 4, 1)->nullable()->comment('Gravité (1 à 40)');

            // Colonnes pour les valeurs calculées (compatibles toutes BDD)
            $table->decimal('risk_value', 8, 2)->nullable()->comment('Valeur de risque calculée (P × E × G)');
            $table->tinyInteger('risk_category')->nullable()->comment('Catégorie de risque (1-5)');

            $table->text('preventive_measures')->nullable()->comment('Mesures préventives à mettre en place');
            $table->text('implemented_measures')->nullable()->comment('Mesures déjà mises en place');
            $table->date('target_date')->nullable()->comment('Date cible pour la mise en place des mesures');
            $table->enum('measure_status', ['planned', 'in_progress', 'completed', 'not_applicable'])
                ->default('planned')
                ->comment('Statut des mesures');
            $table->string('evaluator_name');
            $table->date('evaluation_date');
            $table->date('next_review_date')->nullable()->comment('Prochaine révision');
            $table->text('comments')->nullable();
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index(['playground_id', 'evaluation_type']);
            $table->index(['equipment_id', 'is_present']);
            $table->index('risk_category');
            $table->index('evaluation_date');
            $table->index(['target_date', 'measure_status']);
        });

        // Ajouter des triggers ou utiliser des observers pour calculer automatiquement
        // risk_value et risk_category (voir modèle Eloquent)
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_evaluations');
    }
};
