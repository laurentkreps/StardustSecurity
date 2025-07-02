<?php
// config/risk_analysis.php

return [
    /*
    |--------------------------------------------------------------------------
    | Paramètres de la méthode Fine & Kinney
    |--------------------------------------------------------------------------
    */
    'fine_kinney'             => [
        'probability_values' => [
            10  => 'Presque sûr',
            6   => 'Fort possible',
            3   => 'Inhabituel mais possible',
            1   => 'Possible seulement à long terme',
            0.5 => 'Très improbable',
            0.2 => 'Presque impossible',
            0.1 => 'Impossible sauf avec l\'aide d\'adultes',
        ],

        'exposure_values'    => [
            10  => 'Pendant toute la durée de présence sur l\'aire de jeux',
            6   => 'Équipement de jeu utilisé en permanence',
            3   => 'Équipement de jeu utilisé occasionnellement',
            2   => 'Équipement de jeu utilisé rarement',
            1   => 'Équipement de jeu utilisé très rarement',
            0.5 => 'Équipement de jeu presque jamais utilisé',
        ],

        'gravity_values'     => [
            40 => 'Catastrophique - Décès multiple',
            15 => 'Très grave - Décès',
            7  => 'Grave - Blessure grave permanente',
            3  => 'Important - Blessure avec arrêt de travail',
            1  => 'Mineur - Blessure légère sans arrêt',
        ],

        'risk_categories'    => [
            5 => [
                'label'     => 'Très élevé',
                'color'     => 'red',
                'action'    => 'Envisager l\'arrêt de l\'activité',
                'min_value' => 320,
            ],
            4 => [
                'label'     => 'Élevé',
                'color'     => 'orange',
                'action'    => 'Mesures immédiates nécessaires',
                'min_value' => 160,
            ],
            3 => [
                'label'     => 'Important',
                'color'     => 'yellow',
                'action'    => 'Correction nécessaire',
                'min_value' => 70,
            ],
            2 => [
                'label'     => 'Possible',
                'color'     => 'blue',
                'action'    => 'Y porter attention',
                'min_value' => 20,
            ],
            1 => [
                'label'     => 'Faible',
                'color'     => 'green',
                'action'    => 'Le risque est peut-être acceptable',
                'min_value' => 0,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fréquences de contrôle recommandées
    |--------------------------------------------------------------------------
    */
    'maintenance_frequencies' => [
        'regular_verification' => [
            'label'       => 'Vérification régulière',
            'frequency'   => 'weekly',
            'description' => 'Contrôle visuel hebdomadaire pour détecter les dangers évidents',
        ],
        'maintenance'          => [
            'label'       => 'Entretien',
            'frequency'   => 'monthly',
            'description' => 'Entretien systématique mensuel pour garantir le bon fonctionnement',
        ],
        'periodic_control'     => [
            'label'       => 'Contrôle périodique',
            'frequency'   => 'yearly',
            'description' => 'Contrôle approfondi annuel du niveau de sécurité général',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Types d'équipements standards
    |--------------------------------------------------------------------------
    */
    'equipment_types'         => [
        'toboggan'   => 'Toboggan',
        'balancoire' => 'Balançoire',
        'portique'   => 'Portique',
        'bascule'    => 'Bascule',
        'tourniquet' => 'Tourniquet',
        'ressort'    => 'Jeu à ressort',
        'escalade'   => 'Structure d\'escalade',
        'cabane'     => 'Cabane/Maisonnette',
        'tunnel'     => 'Tunnel',
        'filet'      => 'Filet d\'escalade',
        'poutre'     => 'Poutre d\'équilibre',
        'multi_jeux' => 'Structure multi-jeux',
        'tyrolienne' => 'Tyrolienne',
        'autre'      => 'Autre',
    ],

    /*
    |--------------------------------------------------------------------------
    | Groupes d'âge
    |--------------------------------------------------------------------------
    */
    'age_groups'              => [
        '0-2'  => '0-2 ans (bambins)',
        '2-5'  => '2-5 ans (préscolaire)',
        '5-12' => '5-12 ans (scolaire)',
        '2-8'  => '2-8 ans (mixte jeune)',
        '5-14' => '5-14 ans (mixte)',
        '12+'  => '12 ans et plus (adolescents)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Matériaux d'équipements
    |--------------------------------------------------------------------------
    */
    'materials'               => [
        'metal'     => 'Métal',
        'wood'      => 'Bois',
        'plastic'   => 'Plastique/Polyéthylène',
        'composite' => 'Composite',
        'rope'      => 'Corde/Filet',
        'rubber'    => 'Caoutchouc',
        'mixed'     => 'Mixte',
    ],

    /*
    |--------------------------------------------------------------------------
    | Types de sol amortissant
    |--------------------------------------------------------------------------
    */
    'surface_types'           => [
        'grass'           => 'Gazon naturel',
        'sand'            => 'Sable',
        'rubber_tiles'    => 'Dalles caoutchouc',
        'rubber_pour'     => 'Caoutchouc coulé',
        'wood_chips'      => 'Copeaux de bois',
        'bark'            => 'Écorce',
        'gravel'          => 'Gravillon',
        'synthetic_grass' => 'Gazon synthétique',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuts et workflow
    |--------------------------------------------------------------------------
    */
    'statuses'                => [
        'playground'        => [
            'active'      => 'Actif',
            'inactive'    => 'Inactif',
            'maintenance' => 'En maintenance',
        ],
        'equipment'         => [
            'active'         => 'En service',
            'maintenance'    => 'En maintenance',
            'out_of_service' => 'Hors service',
        ],
        'maintenance_check' => [
            'scheduled'   => 'Programmé',
            'in_progress' => 'En cours',
            'completed'   => 'Terminé',
            'overdue'     => 'En retard',
            'cancelled'   => 'Annulé',
        ],
        'incident'          => [
            'reported'      => 'Signalé',
            'investigating' => 'En cours d\'investigation',
            'resolved'      => 'Résolu',
            'closed'        => 'Clôturé',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications et alertes
    |--------------------------------------------------------------------------
    */
    'notifications'           => [
        'risk_high_threshold'            => 160,                     // Risque élevé nécessitant notification
        'maintenance_overdue_days'       => 7,                       // Jours de retard avant alerte
        'analysis_expiry_months'         => 12,                      // Mois avant expiration analyse
        'incident_notification_severity' => ['serious', 'critical'], // Niveaux nécessitant notification immédiate
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de rapport
    |--------------------------------------------------------------------------
    */
    'reports'                 => [
        'default_logo'                  => 'images/logo.png',
        'risk_analysis_template'        => 'reports.risk_analysis',
        'maintenance_schedule_template' => 'reports.maintenance_schedule',
        'incident_report_template'      => 'reports.incident_report',
    ],

    /*
    |--------------------------------------------------------------------------
    | Réglementation de référence
    |--------------------------------------------------------------------------
    */
    'regulations'             => [
        'primary_belgium' => 'AR du 28 mars 2001 relatif à l\'exploitation des aires de jeux',
        'primary_france'  => 'Décret n° 2008-1458 du 30 décembre 2008 - Sécurité des manèges',

        'european_norms'  => [
            'EN 1176'  => [
                'title'       => 'Équipements d\'aires de jeux et sols de sécurité',
                'parts'       => [
                    'EN 1176-1'  => 'Exigences générales de sécurité et méthodes d\'essai',
                    'EN 1176-2'  => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les balançoires',
                    'EN 1176-3'  => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les toboggans',
                    'EN 1176-4'  => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les téléphériques',
                    'EN 1176-5'  => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les carrousels',
                    'EN 1176-6'  => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les bascules',
                    'EN 1176-7'  => 'Guide d\'installation, contrôle, maintenance et utilisation',
                    'EN 1176-10' => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les équipements de jeux entièrement clos',
                    'EN 1176-11' => 'Exigences de sécurité supplémentaires et méthodes d\'essai spécifiques pour les filets spatiaux',
                ],
                'application' => 'Aires de jeux pour enfants et adolescents',
            ],

            'EN 1177'  => [
                'title'       => 'Revêtements de sols d\'aires de jeux amortissant les chocs',
                'description' => 'Méthodes d\'essai pour la détermination de l\'atténuation des chocs',
                'application' => 'Sols de sécurité sous équipements aires de jeux',
            ],

            'EN 13814' => [
                'title'       => 'Machines et structures pour fêtes foraines et parcs d\'attraction - Sécurité',
                'parts'       => [
                    'EN 13814-1' => 'Conception et fabrication',
                    'EN 13814-2' => 'Exploitation, maintenance et utilisation',
                    'EN 13814-3' => 'Exigences relatives à l\'inspection',
                ],
                'categories'  => [
                    'Category 1' => 'Manèges pour jeunes enfants (< 8 ans)',
                    'Category 2' => 'Manèges sans renversement (≥ 8 ans)',
                    'Category 3' => 'Manèges avec renversement (≥ 12 ans)',
                    'Category 4' => 'Attractions à sensations fortes (≥ 14 ans)',
                ],
                'application' => 'Manèges, attractions foraines et parcs d\'attraction',
            ],

            'EN 60335' => [
                'title'              => 'Appareils électrodomestiques et analogues - Sécurité',
                'parts'              => [
                    'EN 60335-1'   => 'Exigences générales',
                    'EN 60335-2-X' => 'Exigences particulières par type d\'appareil',
                ],
                'protection_classes' => [
                    'Classe I'   => 'Isolation principale + mise à la terre',
                    'Classe II'  => 'Double isolation ou isolation renforcée',
                    'Classe III' => 'Très basse tension de sécurité (SELV)',
                ],
                'application'        => 'Sécurité électrique des équipements',
            ],
        ],

        'related_norms'   => [
            'EN 60204-1'   => 'Sécurité des machines - Équipement électrique des machines',
            'EN 62061'     => 'Sécurité des machines - Sécurité fonctionnelle des systèmes électriques',
            'EN ISO 12100' => 'Sécurité des machines - Principes généraux de conception',
            'EN 16630'     => 'Équipements d\'exercice en extérieur - Exigences de sécurité et méthodes d\'essai',
        ],

        'test_standards'  => [
            'EN 71'     => 'Sécurité des jouets',
            'EN 50075'  => 'Normes pour fiches et prises de courant domestiques',
            'IEC 61000' => 'Compatibilité électromagnétique (CEM)',
        ],
    ],
];
