<?php

return [
    'navigation_label' => 'Jugadores',
    
    'columns' => [
        'avatar' => 'Avatar',
        'name' => 'Usuario',
        'status' => 'Estado',
        'world' => 'Mundo',
        'online' => 'En línea',
        'offline' => 'Desconectado',
        'op' => 'Operador',
    ],

    'filters' => [
        'all' => 'Todo',
        'online' => 'En línea',
        'offline' => 'Desconectado',
        'op' => 'OP',
        'banned' => 'Baneado',
    ],

    'sections' => [
        'identity' => 'Identidad',
        'statistics' => 'Estadísticas',
        'statistics_desc' => 'Historial de las estadísticas del mundo',
        'live_status' => 'Estado',
        'live_status_desc' => 'Datos en tiempo real del servidor',
        'offline_status_desc' => 'Desconectado - Mostrar datos del último archivo guardado',
        'rcon_disabled_status_desc' => 'RCON desactivado - Mostrar datos del archivo guardado',
        'inventory' => 'Inventario',
        'management' => 'Administración',
        'management_desc' => 'Realizar acciones sobre este jugador',
        'enderchest' => 'Ender Chest',
    ],

    'fields' => [
        'username' => 'Usuario',
        'current_status' => 'Estado Actual',
        'uuid' => 'UUID',
        'play_time' => 'Tiempo Jugado',
        'distance_walked' => 'Distancia Recorrida',
        'mobs_killed' => 'Mobs Asesinados',
        'deaths' => 'Muertes',
        'status' => 'Estado',
        'xp_level' => 'Nivel de XP',
        'gamemode' => 'Modo de juego',
        'visual_inventory' => 'Inventario Visual',
    ],

    'stats' => [
        'health' => 'Vida',
        'food' => 'Muslitos',
    ],

    'actions' => [
        'view' => 'Ver',
        'op' => [
            'label_op' => 'OP',
            'label_deop' => 'DEOP',
            'heading_op' => 'Otorgar Operador',
            'heading_deop' => 'Revocar Operador',
            'desc_op' => '¿Estás seguro de que quieres otorgar Operador (OP) a este jugador?',
            'desc_deop' => '¿Estás seguro de que quieres revocar los privilegios de OP de este jugador?',
            'notify_op' => 'Comando OP enviado',
            'notify_deop' => 'Comando DEOP enviado',
        ],
        'clear_inventory' => [
            'label' => 'Borrar Inventario',
            'desc' => '¿Estás seguro de que quieres borrar el inventario de este jugador? Esto no se puede deshacer.',
            'notify' => 'Comando de borrar inventario enviado',
        ],
        'kick' => [
            'label' => 'Expulsar',
            'reason' => 'Razón',
            'default_reason' => 'Expulsado por un operador',
            'notify' => 'Comando de expulsión enviado',
        ],
        'ban' => [
            'label_ban' => 'Banear',
            'label_unban' => 'Desbanear',
            'reason' => 'Razón',
            'default_reason' => 'Baneado por un operador',
            'notify_ban' => 'Comando de baneo enviado',
            'notify_unban' => 'Comando de desbaneo enviado',
        ],
    ],

    'widget' => [
        'online_players' => 'Jugadores en línea',
        'motd' => 'MOTD',
        'map' => 'Nombre de mapa',
        'units' => [
            'mins' => 'mins',
        ],
    ],

    'pages' => [
        'list' => 'Lista de Jugadores',
        'view' => 'Ver Jugador',
    ],

    'values' => [
        'survival' => 'Supervivencia',
        'creative' => 'Creativo',
        'adventure' => 'Aventura',
        'spectator' => 'Espectador',
        'online' => 'En línea',
        'offline' => 'Desconectado',
        'offline_data_source' => 'Desconectado (Últimos datos guardados)',
    ],

    'units' => [
        'mins' => 'mins',
    ],
    'settings' => [
        'rcon_enabled' => 'Activar RCON / Tiempo Real',
        'rcon_enabled_helper' => 'Activa los datos en tiempo real (inventario, Vida, etc.) a través de RCON. RCON necesita estar habilitado en server.properties.',
        'nav_sort' => 'Orden de Navegación',
        'nav_sort_helper' => 'Orden en el menú lateral. Mientras menor sea el número más arriba aparece. (por defecto: 2)',
        'saved' => 'Configuración guardada correctamente.',
        'custom_files' => 'URL de texturas personalizadas',
        'custom_files_helper' => 'Si quieres un resource pack personalizado, especifica la URL aquí. Para más información, consulta https://github.com/R0B0TB0SS/minecraft-player-manager/blob/main/CUSTOM_TEXTURES.md',
        'custom_files_placeholder' => 'Dejar en blanco para usar el CDN por defecto',
    ],
];
