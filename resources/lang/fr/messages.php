<?php

return [
    'navigation_label' => 'Joueurs',
    
    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Nom d\'utilisateur',
        'status' => 'Statut',
        'world' => 'Monde',
        'online' => 'En ligne',
        'offline' => 'Hors ligne',
        'op' => 'Operateur',
    ],

    'filters' => [
        'all' => 'Tous',
        'online' => 'En ligne',
        'offline' => 'Hors linge',
        'op' => 'OP',
        'banned' => 'Bannis',
    ],

    'sections' => [
        'identity' => 'Identity',
        'statistics' => 'Statistiques',
        'statistics_desc' => 'Donnees historiques des statistiques du monde',
        'live_status' => 'Statut',
        'live_status_desc' => 'Donnees en temps reel du serveur',
        'offline_status_desc' => 'Hors ligne - Affichage des donnees de la derniere sauvegarde',
        'rcon_disabled_status_desc' => 'RCON desactive - Affichage des donnees de la sauvegarde',
        'inventory' => 'Inventaire',
        'management' => 'Management',
        'management_desc' => 'Perform actions on this player',
        'enderchest' => 'Ender Chest',
    ],

    'fields' => [
        'username' => 'Username',
        'current_status' => 'Current Status',
        'uuid' => 'UUID',
        'play_time' => 'Play Time',
        'distance_walked' => 'Distance Walked',
        'mobs_killed' => 'Mobs Killed',
        'deaths' => 'Deaths',
        'status' => 'Status',
        'xp_level' => 'XP Level',
        'gamemode' => 'Gamemode',
        'visual_inventory' => 'Inventaire',
    ],

    'stats' => [
        'health' => 'Vie',
        'food' => 'Nouriture',
    ],

    'actions' => [
        'view' => 'View',
        'op' => [
            'label_op' => 'OP',
            'label_deop' => 'DEOP',
            'heading_op' => 'Grant Operator Status',
            'heading_deop' => 'Revoke Operator Status',
            'desc_op' => 'Are you sure you want to make this player an Operator (OP)?',
            'desc_deop' => 'Are you sure you want to remove OP privileges from this player?',
            'notify_op' => 'OP Command Sent',
            'notify_deop' => 'DEOP Command Sent',
        ],
        'clear_inventory' => [
            'label' => 'Clear Inv',
            'desc' => 'Are you sure you want to clear this player\'s inventory? This cannot be undone.',
            'notify' => 'Clear Inventory Command Sent',
        ],
        'kick' => [
            'label' => 'Kick',
            'reason' => 'Reason',
            'default_reason' => 'Kicked by operator',
            'notify' => 'Kick Command Sent',
        ],
        'ban' => [
            'label_ban' => 'Ban',
            'label_unban' => 'Unban',
            'reason' => 'Reason',
            'default_reason' => 'Banned by Operator',
            'notify_ban' => 'Ban command sent',
            'notify_unban' => 'Unban command sent',
        ],
    ],

    'widget' => [
        'online_players' => 'Online Players',
        'motd' => 'MOTD',
        'map' => 'Map Name',
        'units' => [
            'mins' => 'mins',
        ],
    ],

    'pages' => [
        'list' => 'Player List',
        'view' => 'View Player',
    ],

    'values' => [
        'survival' => 'Survival',
        'creative' => 'Creative',
        'adventure' => 'Adventure',
        'spectator' => 'Spectator',
        'online' => 'Online',
        'offline' => 'Offline',
        'offline_data_source' => 'Offline (Last Saved Data)',
    ],

    'units' => [
        'mins' => 'mins',
    ],
    'settings' => [
        'rcon_enabled' => 'Enable RCON / Live Status',
        'rcon_enabled_helper' => 'Enables real-time data fetching (Inventory, Health, etc.) via RCON. Requires RCON to be enabled in server.properties.',
        'nav_sort' => 'Navigation Order',
        'nav_sort_helper' => 'Sort order in the side menu. Lower numbers appear higher. (Default: 2)',
        'saved' => 'Settings saved successfully.',
    ],
];
