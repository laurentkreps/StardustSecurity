<?php
// database/migrations/2024_01_01_000003_create_danger_categories_table.php

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
        Schema::create('danger_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique()->comment('Code du danger (ex: 1.1, 1.2, 2.1, etc.)');
            $table->string('title', 500)->comment('Titre du danger');
            $table->text('description')->nullable()->comment('Description détaillée');
            $table->enum('applies_to', ['playground', 'equipment'])->comment('S\'applique à l\'aire ou aux équipements');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0)->comment('Ordre d\'affichage');
            $table->string('regulation_reference')->nullable()->comment('Référence réglementaire');
            $table->text('typical_examples')->nullable()->comment('Exemples typiques');
            $table->json('default_measures')->nullable()->comment('Mesures préventives par défaut');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['applies_to', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('danger_categories');
    }
};
