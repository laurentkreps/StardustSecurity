<?php
// database/migrations/2024_01_01_000001_create_playgrounds_table.php

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
        Schema::create('playgrounds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('manager_name')->nullable();
            $table->string('manager_contact')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('last_analysis_date')->nullable();
            $table->enum('facility_type', [
                'playground',     // Aire de jeux traditionnelle (EN 1176/1177)
                'amusement_park', // Parc d'attractions (EN 13814)
                'fairground',     // Fête foraine itinérante (EN 13814)
                'mixed_facility', // Installation mixte
            ])->default('playground')->comment('Type d\'installation');
            $table->enum('status', ['active', 'inactive', 'maintenance', 'seasonal_closure'])->default('active');
            $table->text('notes')->nullable();
            $table->decimal('total_surface', 8, 2)->nullable()->comment('Surface totale en m²');
            $table->integer('capacity')->nullable()->comment('Capacité max d\'utilisateurs');
            $table->string('age_range', 50)->nullable()->comment('Tranche d\'âge (ex: 3-12 ans)');
            $table->json('opening_hours')->nullable()->comment('Horaires d\'ouverture');
            $table->boolean('is_fenced')->default(false)->comment('Installation clôturée');
            $table->boolean('has_lighting')->default(false)->comment('Éclairage présent');
            $table->boolean('is_permanent')->default(true)->comment('Installation permanente ou temporaire');
            $table->string('operating_license')->nullable()->comment('Licence d\'exploitation');
            $table->date('license_expiry')->nullable()->comment('Expiration de la licence');
            $table->decimal('max_wind_speed', 5, 1)->nullable()->comment('Vitesse vent max autorisée (km/h)');
            $table->json('weather_restrictions')->nullable()->comment('Restrictions météorologiques');
            $table->string('electrical_installation_cert')->nullable()->comment('Certificat installation électrique');
            $table->timestamps();

            // Index pour optimiser les recherches
            $table->index(['city', 'status']);
            $table->index('last_analysis_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playgrounds');
    }
};
