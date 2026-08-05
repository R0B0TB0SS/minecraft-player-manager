<?php

return [
    'navigation_label' => 'Joueurs du jeu',

    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Nom d’utilisateur',
        'status' => 'Statut',
        'world' => 'Monde',
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'op' => 'Opérateur',
    ],

    'filters' => [
        'all' => 'Tous',
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'op' => 'OP',
        'banned' => 'Bannis',
    ],

    'sections' => [
        'identity' => 'Identité',
        'statistics' => 'Statistiques',
        'statistics_desc' => 'Données historiques provenant des statistiques du monde',
        'live_status' => 'Statut',
        'live_status_desc' => 'Données en temps réel provenant du serveur',
        'offline_status_desc' => 'Hors ligne — affichage des données du dernier fichier sauvegardé',
        'rcon_disabled_status_desc' => 'RCON désactivé — affichage des données du fichier de sauvegarde',
        'inventory' => 'Inventaire',
        'management' => 'Gestion',
        'management_desc' => 'Effectuer des actions sur ce joueur',
        'enderchest' => 'Coffre de l’Ender',
    ],

    'fields' => [
        'username' => 'Nom d’utilisateur',
        'current_status' => 'Statut actuel',
        'uuid' => 'UUID',
        'play_time' => 'Temps de jeu',
        'distance_walked' => 'Distance parcourue',
        'mobs_killed' => 'Créatures tuées',
        'deaths' => 'Morts',
        'status' => 'Statut',
        'xp_level' => 'Niveau d’XP',
        'gamemode' => 'Mode de jeu',
        'visual_inventory' => 'Inventaire visuel',
    ],

    'stats' => [
        'health' => 'Vie',
        'food' => 'Faim',
    ],

    'actions' => [
        'view' => 'Voir',

        'op' => [
            'label_op' => 'OP',
            'label_deop' => 'DEOP',
            'heading_op' => 'Accorder le statut d’opérateur',
            'heading_deop' => 'Révoquer le statut d’opérateur',
            'desc_op' => 'Êtes-vous sûr de vouloir faire de ce joueur un opérateur (OP) ?',
            'desc_deop' => 'Êtes-vous sûr de vouloir retirer les privilèges OP de ce joueur ?',
            'notify_op' => 'Commande OP envoyée',
            'notify_deop' => 'Commande DEOP envoyée',
        ],

        'clear_inventory' => [
            'label' => 'Vider l’inventaire',
            'desc' => 'Êtes-vous sûr de vouloir vider l’inventaire de ce joueur ? Cette action est irréversible.',
            'notify' => 'Commande de suppression de l’inventaire envoyée',
        ],

        'kick' => [
            'label' => 'Expulser',
            'reason' => 'Raison',
            'default_reason' => 'Expulsé par un opérateur',
            'notify' => 'Commande d’expulsion envoyée',
        ],

        'ban' => [
            'label_ban' => 'Bannir',
            'label_unban' => 'Débannir',
            'reason' => 'Raison',
            'default_reason' => 'Banni par un opérateur',
            'notify_ban' => 'Commande de bannissement envoyée',
            'notify_unban' => 'Commande de débannissement envoyée',
        ],
    ],

    'widget' => [
        'online_players' => 'Joueurs en ligne',
        'motd' => 'MOTD',
        'map' => 'Nom de la carte',
        'units' => [
            'mins' => 'min',
        ],
    ],

    'pages' => [
        'list' => 'Liste des joueurs',
        'view' => 'Voir le joueur',
    ],

    'values' => [
        'survival' => 'Survie',
        'creative' => 'Créatif',
        'adventure' => 'Aventure',
        'spectator' => 'Spectateur',
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'offline_data_source' => 'Hors ligne — dernières données sauvegardées',
    ],

    'units' => [
        'mins' => 'min',
    ],

    'settings' => [
        'rcon_enabled' => 'Activer RCON / le statut en temps réel',
        'rcon_enabled_helper' => 'Active la récupération des données en temps réel (inventaire, vie, etc.) via RCON. RCON doit être activé dans le fichier server.properties.',
        'nav_sort' => 'Ordre de navigation',
        'nav_sort_helper' => 'Définit l’ordre d’affichage dans le menu latéral. Les nombres les plus petits apparaissent en premier. (Par défaut : 2)',
        'saved' => 'Paramètres enregistrés avec succès.',
    ],
];