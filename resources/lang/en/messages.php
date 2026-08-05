<?php

return [
    'navigation_label' => 'Game Players',
    
    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Username',
        'status' => 'Status',
        'world' => 'World',
        'online' => 'Online',
        'offline' => 'Offline',
        'op' => 'Operator',
    ],

    'filters' => [
        'all' => 'All',
        'online' => 'Online',
        'offline' => 'Offline',
        'op' => 'OP',
        'banned' => 'Banned',
    ],

    'sections' => [
        'identity' => 'Identity',
        'statistics' => 'Statistics',
        'statistics_desc' => 'Historical data from world stats',
        'live_status' => 'Status',
        'live_status_desc' => 'Real-time data from server',
        'offline_status_desc' => 'Offline - Displaying data from last save file',
        'rcon_disabled_status_desc' => 'RCON disabled - Displaying data from save file',
        'inventory' => 'Inventory',
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
        'visual_inventory' => 'Visual Inventory',
    ],

    'stats' => [
        'health' => 'Health',
        'food' => 'Food',
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
