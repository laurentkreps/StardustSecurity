<?php
// database/seeders/SpecializedNormsSeeder.php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializedNormsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Ajouter des catégories de dangers supplémentaires pour EN 13814 (manèges complexes)
        $additionalAmusementDangers = [
            [
                'code'                 => '3.9',
                'title'                => 'Dangers liés aux systèmes hydrauliques',
                'description'          => 'Risques dus aux systèmes hydrauliques haute pression',
                'applies_to'           => 'equipment',
                'sort_order'           => 39,
                'regulation_reference' => 'EN 13814 - Clause 4.6',
                'typical_examples'     => 'Rupture flexible, fuite huile, surpression',
                'default_measures'     => json_encode([
                    'Contrôle pression régulier',
                    'Remplacement préventif flexibles',
                    'Systèmes de sécurité surpression',
                ]),
            ],
            [
                'code'                 => '3.10',
                'title'                => 'Dangers liés aux systèmes pneumatiques',
                'description'          => 'Risques dus aux systèmes pneumatiques',
                'applies_to'           => 'equipment',
                'sort_order'           => 40,
                'regulation_reference' => 'EN 13814 - Clause 4.6',
                'typical_examples'     => 'Rupture circuit air, défaillance valve',
                'default_measures'     => json_encode([
                    'Maintenance compresseurs',
                    'Contrôle étanchéité',
                    'Soupapes de sécurité',
                ]),
            ],
            [
                'code'                 => '3.11',
                'title'                => 'Dangers liés aux effets spéciaux',
                'description'          => 'Risques dus aux effets pyrotechniques, fumée, lasers',
                'applies_to'           => 'equipment',
                'sort_order'           => 41,
                'regulation_reference' => 'EN 13814 + régl. spécifiques',
                'typical_examples'     => 'Pyrotechnie, lasers, effets de fumée',
                'default_measures'     => json_encode([
                    'Personnel spécialement formé',
                    'Équipements certifiés',
                    'Distances de sécurité',
                ]),
            ],
            [
                'code'                 => '3.12',
                'title'                => 'Dangers liés à l\'évacuation d\'urgence',
                'description'          => 'Risques lors des procédures d\'évacuation',
                'applies_to'           => 'equipment',
                'sort_order'           => 42,
                'regulation_reference' => 'EN 13814 - Clause 4.9',
                'typical_examples'     => 'Évacuation en hauteur, procédures complexes',
                'default_measures'     => json_encode([
                    'Procédures d\'évacuation documentées',
                    'Formation personnel secours',
                    'Équipements évacuation',
                ]),
            ],
        ];

        // Dangers spécifiques aux systèmes électriques avancés (EN 60335 + EN 60204)
        $additionalElectricalDangers = [
            [
                'code'                 => '4.8',
                'title'                => 'Dangers liés aux systèmes de commande',
                'description'          => 'Risques dus aux défaillances des systèmes de commande électroniques',
                'applies_to'           => 'equipment',
                'sort_order'           => 48,
                'regulation_reference' => 'EN 60335-1 + EN 62061',
                'typical_examples'     => 'Automate défaillant, capteur HS, logiciel bogué',
                'default_measures'     => json_encode([
                    'Redondance systèmes critiques',
                    'Tests logiciels',
                    'Mode dégradé sécurisé',
                ]),
            ],
            [
                'code'                 => '4.9',
                'title'                => 'Dangers liés aux batteries et accumulateurs',
                'description'          => 'Risques dus aux systèmes d\'alimentation autonome',
                'applies_to'           => 'equipment',
                'sort_order'           => 49,
                'regulation_reference' => 'EN 60335-1 + EN 62133',
                'typical_examples'     => 'Surchauffe batterie, explosion, fuite acide',
                'default_measures'     => json_encode([
                    'Système de gestion batterie (BMS)',
                    'Ventilation locale batterie',
                    'Procédures de remplacement',
                ]),
            ],
            [
                'code'                 => '4.10',
                'title'                => 'Dangers liés aux convertisseurs de fréquence',
                'description'          => 'Risques dus aux variateurs de vitesse électroniques',
                'applies_to'           => 'equipment',
                'sort_order'           => 50,
                'regulation_reference' => 'EN 60335-1 + EN 61800',
                'typical_examples'     => 'Harmoniques, surtensions, dysfonctionnements',
                'default_measures'     => json_encode([
                    'Filtres harmoniques',
                    'Protection surtensions',
                    'Câblage blindé',
                ]),
            ],
            [
                'code'                 => '4.11',
                'title'                => 'Dangers liés aux systèmes de sécurité fonctionnelle',
                'description'          => 'Risques dus aux défaillances des systèmes de sécurité',
                'applies_to'           => 'equipment',
                'sort_order'           => 51,
                'regulation_reference' => 'EN 62061 + EN ISO 13849',
                'typical_examples'     => 'Arrêt urgence inopérant, détecteur défaillant',
                'default_measures'     => json_encode([
                    'Architecture sécuritaire (SIL/PL)',
                    'Test périodique fonction sécurité',
                    'Diagnostic automatique',
                ]),
            ],
        ];

        // Dangers liés aux attractions aquatiques (spécifique)
        $aquaticDangers = [
            [
                'code'                 => '5.1',
                'title'                => 'Noyade et dangers aquatiques',
                'description'          => 'Risques de noyade dans les attractions aquatiques',
                'applies_to'           => 'equipment',
                'sort_order'           => 51,
                'regulation_reference' => 'EN 13814 + régl. piscines',
                'typical_examples'     => 'Bassins, toboggan aquatique, rivière lente',
                'default_measures'     => json_encode([
                    'Surveillance par maîtres-nageurs',
                    'Équipements de sauvetage',
                    'Signalisation profondeurs',
                ]),
            ],
            [
                'code'                 => '5.2',
                'title'                => 'Qualité de l\'eau',
                'description'          => 'Risques sanitaires liés à la qualité de l\'eau',
                'applies_to'           => 'equipment',
                'sort_order'           => 52,
                'regulation_reference' => 'EN 13814 + régl. sanitaire',
                'typical_examples'     => 'Contamination bactérienne, pH inadéquat',
                'default_measures'     => json_encode([
                    'Traitement eau automatisé',
                    'Analyses régulières',
                    'Nettoyage circuit eau',
                ]),
            ],
            [
                'code'                 => '5.3',
                'title'                => 'Glissement surfaces mouillées',
                'description'          => 'Risques de glissade sur surfaces humides',
                'applies_to'           => 'equipment',
                'sort_order'           => 53,
                'regulation_reference' => 'EN 13814',
                'typical_examples'     => 'Plages de piscine, rampes, escaliers',
                'default_measures'     => json_encode([
                    'Revêtements antidérapants',
                    'Évacuation eau efficace',
                    'Signalisation dangers',
                ]),
            ],
        ];

        // Insérer toutes les nouvelles catégories
        $allNewDangers = array_merge(
            $additionalAmusementDangers,
            $additionalElectricalDangers,
            $aquaticDangers
        );

        foreach ($allNewDangers as &$danger) {
            $danger['is_active']  = true;
            $danger['created_at'] = $now;
            $danger['updated_at'] = $now;
        }

        DB::table('danger_categories')->insert($allNewDangers);

        $this->command->info('Specialized norm danger categories seeded successfully!');
        $this->command->info('- Additional amusement ride dangers: ' . count($additionalAmusementDangers));
        $this->command->info('- Additional electrical dangers: ' . count($additionalElectricalDangers));
        $this->command->info('- Aquatic attraction dangers: ' . count($aquaticDangers));
        $this->command->info('- Total new dangers: ' . count($allNewDangers));

        // Ajouter des données de référence pour les tests électriques
        $this->seedElectricalTestStandards();

        // Ajouter des données pour les catégories de manèges
        $this->seedRideCategoryData();
    }

    /**
     * Ajouter les standards de test électrique
     */
    private function seedElectricalTestStandards(): void
    {
        $testStandards = [
            [
                'test_type'          => 'insulation_test',
                'voltage_class'      => 'LV', // Low Voltage
                'min_resistance'     => 1.0,  // MΩ
                'test_voltage'       => 500,  // V
                'standard_reference' => 'EN 60335-1 Clause 16',
            ],
            [
                'test_type'          => 'earth_continuity',
                'voltage_class'      => 'LV',
                'max_resistance'     => 0.1, // Ω
                'test_current'       => 10,  // A
                'standard_reference' => 'EN 60335-1 Clause 27',
            ],
            [
                'test_type'          => 'rcd_test',
                'voltage_class'      => 'LV',
                'max_trip_current'   => 30, // mA
                'max_trip_time'      => 40, // ms
                'standard_reference' => 'EN 61008/EN 61009',
            ],
        ];

        // Note: Ces données pourraient être stockées dans une table de configuration
        // ou directement dans le fichier config/risk_analysis.php
        $this->command->info('Electrical test standards data prepared');
    }

    /**
     * Ajouter les données des catégories de manèges
     */
    private function seedRideCategoryData(): void
    {
        $rideCategories = [
            [
                'category'             => 'category_1',
                'max_speed'            => 2.0, // m/s
                'max_height'           => 2.0, // m
                'max_acceleration'     => 2.0, // g
                'age_restriction'      => '< 8 years',
                'supervision_required' => true,
            ],
            [
                'category'          => 'category_2',
                'max_speed'         => 8.0,  // m/s
                'max_height'        => 15.0, // m
                'max_acceleration'  => 3.5,  // g
                'age_restriction'   => '>= 8 years',
                'inversion_allowed' => false,
            ],
            [
                'category'          => 'category_3',
                'max_speed'         => 15.0, // m/s
                'max_height'        => 50.0, // m
                'max_acceleration'  => 4.5,  // g
                'age_restriction'   => '>= 12 years',
                'inversion_allowed' => true,
            ],
            [
                'category'             => 'category_4',
                'max_speed'            => 25.0,  // m/s
                'max_height'           => 100.0, // m
                'max_acceleration'     => 6.0,   // g
                'age_restriction'      => '>= 14 years',
                'special_requirements' => true,
            ],
        ];

        $this->command->info('Ride category data prepared for ' . count($rideCategories) . ' categories');
    }
}
