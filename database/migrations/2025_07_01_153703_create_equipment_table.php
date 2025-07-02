<?php
// database/migrations/2024_01_01_000002_create_equipment_table.php

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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('playground_id')->constrained()->onDelete('cascade');
            $table->string('reference_code', 50)->comment('Référence alphanumérique sur l\'aire');
            $table->enum('equipment_category', [
                'playground_equipment', // Équipement aire de jeux (EN 1176)
                'amusement_ride',       // Manège/attraction (EN 13814)
                'electrical_system',    // Système électrique (EN 60335)
                'infrastructure',       // Infrastructure support
                'safety_equipment',     // Équipement de sécurité
            ])->default('playground_equipment')->comment('Catégorie d\'équipement');
            $table->string('equipment_type')->comment('Type de produit');
            $table->enum('ride_category', [
                'category_1',     // Manèges pour jeunes enfants
                'category_2',     // Manèges sans renversement
                'category_3',     // Manèges avec renversement
                'category_4',     // Attractions à sensations fortes
                'not_applicable', // Non applicable
            ])->default('not_applicable')->comment('Catégorie manège selon EN 13814');
            $table->string('brand')->nullable()->comment('Marque');
            $table->text('manufacturer_details')->nullable()->comment('Coordonnées fabricant');
            $table->text('supplier_details')->nullable()->comment('Coordonnées fournisseur');
            $table->json('applicable_norms')->nullable()->comment('Normes applicables (EN 1176, EN 13814, EN 60335)');
            $table->string('ce_marking')->nullable()->comment('Marquage CE');
            $table->string('declaration_of_conformity')->nullable()->comment('Déclaration de conformité');
            $table->date('purchase_date')->nullable();
            $table->date('installation_date')->nullable();
            $table->string('verification_frequency', 100)->nullable()->comment('Périodicité selon fabricant');
            $table->text('risk_analysis_certificate')->nullable()->comment('Certificat ou fiche d\'évaluation');
            $table->enum('status', ['active', 'maintenance', 'out_of_service'])->default('active');
            $table->text('description')->nullable();
            $table->string('material', 100)->nullable()->comment('Matériau principal');
            $table->decimal('height', 5, 2)->nullable()->comment('Hauteur en mètres');
            $table->decimal('max_speed', 6, 2)->nullable()->comment('Vitesse maximale (km/h) - pour manèges');
            $table->decimal('max_acceleration', 5, 2)->nullable()->comment('Accélération max (g) - pour manèges');
            $table->integer('max_passengers')->nullable()->comment('Nombre max de passagers simultanés');
            $table->decimal('min_height_requirement', 4, 2)->nullable()->comment('Taille minimale requise (cm)');
            $table->decimal('max_weight_limit', 6, 2)->nullable()->comment('Poids maximum autorisé (kg)');
            $table->string('age_group', 50)->nullable()->comment('Groupe d\'âge recommandé');
            $table->json('dimensions')->nullable()->comment('Dimensions détaillées');
            $table->boolean('requires_fall_protection')->default(false);
            $table->decimal('fall_height', 5, 2)->nullable()->comment('Hauteur de chute libre en mètres');
            $table->text('safety_features')->nullable()->comment('Équipements de sécurité');

            // Spécifique aux manèges (EN 13814)
            $table->boolean('is_mobile')->default(false)->comment('Équipement mobile/transportable');
            $table->decimal('setup_time_hours', 4, 1)->nullable()->comment('Temps de montage en heures');
            $table->decimal('power_consumption_kw', 8, 2)->nullable()->comment('Consommation électrique (kW)');
            $table->string('structural_calculations_ref')->nullable()->comment('Référence calculs structurels');
            $table->json('weather_operating_limits')->nullable()->comment('Limites météo d\'exploitation');
            $table->boolean('requires_operator')->default(false)->comment('Nécessite un opérateur');
            $table->string('operator_qualification')->nullable()->comment('Qualification requise opérateur');

            // Spécifique électrique (EN 60335)
            $table->decimal('voltage', 6, 2)->nullable()->comment('Tension nominale (V)');
            $table->decimal('current', 6, 2)->nullable()->comment('Courant nominal (A)');
            $table->string('protection_class')->nullable()->comment('Classe de protection électrique');
            $table->string('ip_rating')->nullable()->comment('Indice de protection IP');
            $table->boolean('requires_earth_connection')->default(false)->comment('Nécessite connexion terre');
            $table->date('electrical_test_date')->nullable()->comment('Date dernier test électrique');
            $table->string('electrical_certificate')->nullable()->comment('Certificat conformité électrique');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['playground_id', 'status']);
            $table->index('equipment_type');
            $table->unique(['playground_id', 'reference_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
