<?php

// =============================================================================
// ÉTAPE 1: Créer le seeder
// =============================================================================

// Commande à exécuter :
// php artisan make:seeder DangerCategorySeeder

// =============================================================================
// CONTENU COMPLET: database/seeders/DangerCategorySeeder.php
// =============================================================================

namespace Database\Seeders;

use App\Models\DangerCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DangerCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création des catégories de dangers réglementaires...');

        // Supprimer les catégories existantes pour éviter les doublons
        DangerCategory::truncate();

        // Dangers généraux de l'aire de jeux (applies_to: playground)
        $playgroundDangers = [
            [
                'code'                 => '1.1',
                'title'                => 'Impact avec le sol ou autre surface',
                'description'          => 'Risque de chute et d\'impact avec le sol ou toute autre surface dur suite à une chute depuis un équipement.',
                'typical_examples'     => 'Chute du toboggan, chute de la balançoire, glissade sur surface mouillée',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 5',
                'default_measures'     => json_encode([
                    'Revêtement amortissant conforme EN 1177',
                    'Hauteur de chute libre respectée',
                    'Zone de sécurité délimitée',
                    'Entretien régulier des surfaces',
                ]),
            ],
            [
                'code'                 => '1.2',
                'title'                => 'Impact avec l\'équipement ou parties d\'équipement',
                'description'          => 'Risque de collision avec des parties fixes ou mobiles de l\'équipement.',
                'typical_examples'     => 'Collision avec poteau, choc contre structure, impact avec partie mobile',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.9',
                'default_measures'     => json_encode([
                    'Espacement suffisant entre équipements',
                    'Angles et arêtes arrondis',
                    'Signalisation des zones dangereuses',
                    'Protection des parties saillantes',
                ]),
            ],
            [
                'code'                 => '1.3',
                'title'                => 'Impact avec autres usagers',
                'description'          => 'Risque de collision entre usagers lors de l\'utilisation simultanée des équipements.',
                'typical_examples'     => 'Collision entre enfants sur toboggan, choc sur balançoire',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.8',
                'default_measures'     => json_encode([
                    'Limitation du nombre d\'utilisateurs simultanés',
                    'Zones de circulation clairement définies',
                    'Surveillance appropriée',
                    'Signalisation des règles d\'usage',
                ]),
            ],
            [
                'code'                 => '1.4',
                'title'                => 'Impact avec objets jetés ou qui tombent',
                'description'          => 'Risque d\'être heurté par des objets jetés ou tombant depuis les équipements.',
                'typical_examples'     => 'Chute d\'objets depuis plateforme, projection de matériaux',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.10',
                'default_measures'     => json_encode([
                    'Interdiction d\'objets en hauteur',
                    'Inspection régulière des fixations',
                    'Zone de protection sous équipements',
                    'Règlement d\'usage affiché',
                ]),
            ],
            [
                'code'                 => '2.1',
                'title'                => 'Coincement de la tête et du cou',
                'description'          => 'Risque de coincement de la tête ou du cou dans les ouvertures de l\'équipement.',
                'typical_examples'     => 'Ouvertures entre barreaux, espaces dans structures',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.2',
                'default_measures'     => json_encode([
                    'Ouvertures < 89mm ou > 230mm',
                    'Contrôle dimensionnel régulier',
                    'Gabarit de vérification',
                    'Formation du personnel',
                ]),
            ],
            [
                'code'                 => '2.2',
                'title'                => 'Coincement des doigts, mains, bras',
                'description'          => 'Risque de coincement des membres supérieurs dans les mécanismes ou ouvertures.',
                'typical_examples'     => 'Espaces entre pièces mobiles, ouvertures dans structures',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.3',
                'default_measures'     => json_encode([
                    'Espaces < 8mm ou > 25mm pour doigts',
                    'Protection des mécanismes',
                    'Surfaces lisses sans aspérités',
                    'Contrôle des jeux et tolérances',
                ]),
            ],
            [
                'code'                 => '2.3',
                'title'                => 'Coincement des pieds et jambes',
                'description'          => 'Risque de coincement des membres inférieurs dans les ouvertures ou mécanismes.',
                'typical_examples'     => 'Espaces sous équipements, ouvertures dans sols',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.4',
                'default_measures'     => json_encode([
                    'Ouvertures < 89mm ou > 230mm',
                    'Protection des mécanismes en partie basse',
                    'Inspection des espaces au sol',
                    'Maintenance préventive',
                ]),
            ],
            [
                'code'                 => '2.4',
                'title'                => 'Coincement des cheveux, vêtements, cordons',
                'description'          => 'Risque d\'accrochage ou de coincement d\'éléments vestimentaires.',
                'typical_examples'     => 'Parties rugueuses, éléments saillants, espaces étroits',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.5',
                'default_measures'     => json_encode([
                    'Surfaces lisses et continues',
                    'Élimination des parties saillantes',
                    'Contrôle régulier de l\'état des surfaces',
                    'Sensibilisation aux vêtements appropriés',
                ]),
            ],
            [
                'code'                 => '3.1',
                'title'                => 'Chute depuis une hauteur',
                'description'          => 'Risque de chute depuis une plateforme, toboggan ou tout équipement en hauteur.',
                'typical_examples'     => 'Chute de plateforme, glissade sur toboggan, chute de structure d\'escalade',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.1',
                'default_measures'     => json_encode([
                    'Garde-corps conformes (hauteur min 700mm)',
                    'Surfaces antidérapantes',
                    'Revêtement amortissant adapté à la hauteur',
                    'Inspection des systèmes de protection',
                ]),
            ],
            [
                'code'                 => '3.2',
                'title'                => 'Chute dans des excavations',
                'description'          => 'Risque de chute dans des trous ou excavations autour de l\'aire de jeux.',
                'typical_examples'     => 'Fosses techniques, canalisations, trous dans le sol',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 8',
                'default_measures'     => json_encode([
                    'Comblement ou protection des excavations',
                    'Signalisation des zones dangereuses',
                    'Inspection régulière du terrain',
                    'Maintenance des abords',
                ]),
            ],
            [
                'code'                 => '4.1',
                'title'                => 'Secousses et vibrations',
                'description'          => 'Risque lié aux secousses et vibrations des équipements à bascule ou ressort.',
                'typical_examples'     => 'Jeux à ressort, bascules, tourniquets',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.11',
                'default_measures'     => json_encode([
                    'Limitation des amplitudes de mouvement',
                    'Amortisseurs et butées',
                    'Zone de sécurité adaptée',
                    'Maintenance des systèmes mobiles',
                ]),
            ],
            [
                'code'                 => '4.2',
                'title'                => 'Instabilité des équipements',
                'description'          => 'Risque lié à l\'instabilité ou au basculement des équipements mal fixés.',
                'typical_examples'     => 'Équipements mal ancrés, fondations insuffisantes',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.12',
                'default_measures'     => json_encode([
                    'Ancrage conforme aux spécifications',
                    'Vérification des fondations',
                    'Contrôle de la stabilité',
                    'Maintenance des systèmes de fixation',
                ]),
            ],
            [
                'code'                 => '5.1',
                'title'                => 'Manque de surveillance',
                'description'          => 'Risques liés à l\'absence de surveillance appropriée des enfants.',
                'typical_examples'     => 'Usage non conforme, comportements dangereux',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 12',
                'default_measures'     => json_encode([
                    'Définition des niveaux de surveillance',
                    'Formation du personnel d\'encadrement',
                    'Procédures d\'urgence',
                    'Signalisation des responsabilités',
                ]),
            ],
            [
                'code'                 => '5.2',
                'title'                => 'Usage non conforme',
                'description'          => 'Risques liés à un usage non approprié ou non conforme des équipements.',
                'typical_examples'     => 'Utilisation par mauvaise tranche d\'âge, détournement d\'usage',
                'regulation_reference' => 'EN 1176-1 - Section 4.1',
                'default_measures'     => json_encode([
                    'Signalisation claire des tranches d\'âge',
                    'Règlement d\'usage visible',
                    'Formation des utilisateurs',
                    'Contrôle de l\'usage approprié',
                ]),
            ],
            [
                'code'                 => '6.1',
                'title'                => 'Conditions météorologiques',
                'description'          => 'Risques liés aux conditions météorologiques (glace, neige, vent fort).',
                'typical_examples'     => 'Surfaces glissantes par gel, équipements inutilisables par vent',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 9',
                'default_measures'     => json_encode([
                    'Procédures météorologiques',
                    'Fermeture temporaire si nécessaire',
                    'Déneigement et déglaçage',
                    'Surveillance météorologique',
                ]),
            ],
            [
                'code'                 => '6.2',
                'title'                => 'Pollution et contamination',
                'description'          => 'Risques liés à la pollution de l\'air, du sol ou à la contamination bactérienne.',
                'typical_examples'     => 'Pollution chimique, contamination biologique, déchets dangereux',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 10',
                'default_measures'     => json_encode([
                    'Nettoyage et désinfection réguliers',
                    'Contrôle de la qualité de l\'environnement',
                    'Gestion des déchets',
                    'Surveillance sanitaire',
                ]),
            ],
            [
                'code'                 => '7.1',
                'title'                => 'Actes de vandalisme',
                'description'          => 'Risques liés aux dégradations volontaires des équipements.',
                'typical_examples'     => 'Équipements détériorés, graffitis, destruction volontaire',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 11',
                'default_measures'     => json_encode([
                    'Surveillance et sécurisation',
                    'Réparation immédiate des dégradations',
                    'Éclairage et visibilité',
                    'Sensibilisation du public',
                ]),
            ],
            [
                'code'                 => '7.2',
                'title'                => 'Présence d\'intrus ou d\'animaux',
                'description'          => 'Risques liés à la présence d\'intrus ou d\'animaux dangereux sur l\'aire de jeux.',
                'typical_examples'     => 'Animaux errants, personnes non autorisées, nuisibles',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 13',
                'default_measures'     => json_encode([
                    'Clôture et contrôle d\'accès',
                    'Inspection régulière',
                    'Élimination des attractions pour nuisibles',
                    'Procédures d\'intervention',
                ]),
            ],
        ];

        // Dangers spécifiques aux équipements (applies_to: equipment)
        $equipmentDangers = [
            [
                'code'                 => 'E1.1',
                'title'                => 'Défaillance structurelle',
                'description'          => 'Risque de rupture ou défaillance de la structure portante de l\'équipement.',
                'typical_examples'     => 'Rupture de soudure, fissure de matériau, déformation excessive',
                'regulation_reference' => 'EN 1176-1 - Section 4.3',
                'default_measures'     => json_encode([
                    'Contrôle non destructif régulier',
                    'Vérification des assemblages',
                    'Surveillance des déformations',
                    'Maintenance préventive structurelle',
                ]),
            ],
            [
                'code'                 => 'E1.2',
                'title'                => 'Corrosion et dégradation',
                'description'          => 'Risque lié à la corrosion des éléments métalliques ou dégradation des matériaux.',
                'typical_examples'     => 'Corrosion des structures métalliques, dégradation du bois, usure des plastiques',
                'regulation_reference' => 'EN 1176-1 - Section 5',
                'default_measures'     => json_encode([
                    'Traitement anticorrosion',
                    'Inspection visuelle régulière',
                    'Remplacement des éléments dégradés',
                    'Protection contre les intempéries',
                ]),
            ],
            [
                'code'                 => 'E1.3',
                'title'                => 'Usure des pièces mobiles',
                'description'          => 'Risque lié à l\'usure excessive des pièces mobiles (roulements, pivots, etc.).',
                'typical_examples'     => 'Usure des roulements, jeu excessif dans pivots, blocage de mécanismes',
                'regulation_reference' => 'EN 1176-1 - Section 4.4',
                'default_measures'     => json_encode([
                    'Lubrification régulière',
                    'Contrôle des jeux fonctionnels',
                    'Remplacement préventif',
                    'Vérification des mouvements',
                ]),
            ],
            [
                'code'                 => 'E2.1',
                'title'                => 'Surfaces rugueuses ou coupantes',
                'description'          => 'Risque de blessures par des surfaces rugueuses, échardes ou arêtes vives.',
                'typical_examples'     => 'Échardes sur bois, arêtes métalliques, surfaces abrasives',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.6',
                'default_measures'     => json_encode([
                    'Ponçage et finition des surfaces',
                    'Protection des arêtes vives',
                    'Contrôle tactile régulier',
                    'Remplacement des éléments détériorés',
                ]),
            ],
            [
                'code'                 => 'E2.2',
                'title'                => 'Éléments saillants',
                'description'          => 'Risque de blessures par des éléments qui dépassent (boulons, écrous, etc.).',
                'typical_examples'     => 'Boulons dépassants, éléments de fixation saillants, parties pointues',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.7',
                'default_measures'     => json_encode([
                    'Protection ou suppression des saillies',
                    'Utilisation de fixations affleurantes',
                    'Contrôle régulier des assemblages',
                    'Remplacement si nécessaire',
                ]),
            ],
            [
                'code'                 => 'E2.3',
                'title'                => 'Points de cisaillement',
                'description'          => 'Risque de cisaillement entre pièces mobiles.',
                'typical_examples'     => 'Mécanismes de rotation, pièces coulissantes, articulations',
                'regulation_reference' => 'EN 1176-1 - Section 4.2.13',
                'default_measures'     => json_encode([
                    'Protection des mécanismes',
                    'Limitation des forces de cisaillement',
                    'Contrôle des vitesses de mouvement',
                    'Maintenance des protections',
                ]),
            ],
            [
                'code'                 => 'E3.1',
                'title'                => 'Instabilité de l\'équipement',
                'description'          => 'Risque de basculement ou d\'instabilité de l\'équipement.',
                'typical_examples'     => 'Équipement mal équilibré, ancrage insuffisant, surcharge',
                'regulation_reference' => 'EN 1176-1 - Section 4.3.1',
                'default_measures'     => json_encode([
                    'Vérification de la stabilité',
                    'Contrôle des charges admissibles',
                    'Inspection des ancrages',
                    'Test de stabilité périodique',
                ]),
            ],
            [
                'code'                 => 'E3.2',
                'title'                => 'Ancrage défaillant',
                'description'          => 'Risque lié à un ancrage au sol défaillant ou insuffisant.',
                'typical_examples'     => 'Fondations détériorées, boulons d\'ancrage desserrés, tassement du sol',
                'regulation_reference' => 'EN 1176-1 - Section 4.3.2',
                'default_measures'     => json_encode([
                    'Inspection des fondations',
                    'Contrôle du serrage des ancrages',
                    'Vérification du sol support',
                    'Renforcement si nécessaire',
                ]),
            ],
            [
                'code'                 => 'E4.1',
                'title'                => 'Conformité aux normes',
                'description'          => 'Non-conformité aux normes EN 1176-1177 en vigueur.',
                'typical_examples'     => 'Dimensions non conformes, matériaux non agréés, performances insuffisantes',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 4',
                'default_measures'     => json_encode([
                    'Vérification de conformité',
                    'Mise à jour selon nouvelles normes',
                    'Documentation technique',
                    'Certification par organisme agréé',
                ]),
            ],
            [
                'code'                 => 'E4.2',
                'title'                => 'Maintenance insuffisante',
                'description'          => 'Risque lié à une maintenance insuffisante ou inadéquate.',
                'typical_examples'     => 'Absence de plan de maintenance, interventions différées, contrôles non effectués',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 14',
                'default_measures'     => json_encode([
                    'Plan de maintenance préventive',
                    'Formation du personnel',
                    'Traçabilité des interventions',
                    'Contrôles périodiques',
                ]),
            ],
            [
                'code'                 => 'E5.1',
                'title'                => 'Accessibilité inadéquate',
                'description'          => 'Problèmes d\'accessibilité pour les personnes à mobilité réduite.',
                'typical_examples'     => 'Absence d\'accès PMR, obstacles sur parcours, signalisation insuffisante',
                'regulation_reference' => 'Décret accessibilité - Article 2',
                'default_measures'     => json_encode([
                    'Aménagement d\'accès PMR',
                    'Signalisation adaptée',
                    'Formation du personnel',
                    'Équipements inclusifs',
                ]),
            ],
            [
                'code'                 => 'E5.2',
                'title'                => 'Signalisation manquante',
                'description'          => 'Absence ou défaillance de la signalisation de sécurité.',
                'typical_examples'     => 'Panneaux manquants, signalisation illisible, informations obsolètes',
                'regulation_reference' => 'AR du 28 mars 2001 - Article 15',
                'default_measures'     => json_encode([
                    'Installation signalisation complète',
                    'Maintenance des panneaux',
                    'Mise à jour des informations',
                    'Contrôle de visibilité',
                ]),
            ],
        ];

        // Insertion des données avec gestion d'erreurs
        DB::beginTransaction();

        try {
            $playgroundCount = 0;
            foreach ($playgroundDangers as $danger) {
                DangerCategory::create(array_merge($danger, [
                    'applies_to' => 'playground',
                    'is_active'  => true,
                    'sort_order' => $playgroundCount++,
                ]));
            }

            $equipmentCount = 0;
            foreach ($equipmentDangers as $danger) {
                DangerCategory::create(array_merge($danger, [
                    'applies_to' => 'equipment',
                    'is_active'  => true,
                    'sort_order' => $equipmentCount++,
                ]));
            }

            DB::commit();

            $this->command->info('✅ Catégories de dangers créées avec succès !');
            $this->command->info('📊 Total playground dangers: ' . count($playgroundDangers));
            $this->command->info('📊 Total equipment dangers: ' . count($equipmentDangers));
            $this->command->info('🎯 Total catégories: ' . (count($playgroundDangers) + count($equipmentDangers)));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de la création des catégories: ' . $e->getMessage());
            throw $e;
        }
    }
}

// =============================================================================
// MODÈLE DangerCategory.php (À CRÉER SI MANQUANT)
// =============================================================================

// Créer le fichier: app/Models/DangerCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DangerCategory extends Model
{
    protected $fillable = [
        'code', 'title', 'description', 'applies_to', 'is_active',
        'sort_order', 'regulation_reference', 'typical_examples', 'default_measures',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
        'default_measures' => 'array',
    ];

    // Relations
    public function riskEvaluations(): HasMany
    {
        return $this->hasMany(RiskEvaluation::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForPlayground(Builder $query): Builder
    {
        return $query->where('applies_to', 'playground');
    }

    public function scopeForEquipment(Builder $query): Builder
    {
        return $query->where('applies_to', 'equipment');
    }

    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', $code);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('code');
    }

    // Accesseurs
    public function getFullTitleAttribute(): string
    {
        return $this->code . ' - ' . $this->title;
    }

    public function getAppliesToLabelAttribute(): string
    {
        return match ($this->applies_to) {
            'playground' => 'Aire de jeux',
            'equipment' => 'Équipement',
            default => 'Non défini'
        };
    }

    // Méthodes utiles
    public function isForPlayground(): bool
    {
        return $this->applies_to === 'playground';
    }

    public function isForEquipment(): bool
    {
        return $this->applies_to === 'equipment';
    }

    public function hasRiskEvaluations(): bool
    {
        return $this->riskEvaluations()->exists();
    }

    public function getActiveRiskEvaluationsCount(): int
    {
        return $this->riskEvaluations()
            ->where('is_present', true)
            ->count();
    }

    public function getDefaultMeasuresListAttribute(): array
    {
        return $this->default_measures ?? [];
    }
}

// =============================================================================
// MISE À JOUR DatabaseSeeder.php
// =============================================================================

// Dans database/seeders/DatabaseSeeder.php, ajouter:

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DangerCategorySeeder::class,
            // Ajouter d'autres seeders ici si nécessaire
        ]);
    }
}
