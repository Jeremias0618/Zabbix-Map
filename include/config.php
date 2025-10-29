<?php
/**
 * Template de configuración de la aplicación Zabbix Map
 * 
 * Este archivo contiene el template de configuración para:
 * - Conexión a Zabbix API (configurar desde configuration.php)
 * - Conexión a base de datos PostgreSQL (configurar desde configuration.php)
 * - Configuraciones generales de la aplicación
 */

return [
    // Configuración de Zabbix
    'zabbix' => [
        'ip' => '',
        'port' => '80',
        'token' => '',
        'url' => '', // Construido automáticamente
        'timeout' => 30,
        'connect_timeout' => 10,
        'ssl_verify_peer' => false,
        'ssl_verify_host' => false,
        'use_gzip' => true,
        'debug' => false
    ],
    
    // Configuración de base de datos PostgreSQL
    'database' => [
        'host' => '',
        'port' => '5432',
        'dbname' => '',
        'user' => '',
        'pass' => '',
        'charset' => 'utf8',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    
            // Configuración de la aplicación
            'app' => [
                'name' => 'Zabbix Map',
        'version' => '1.0.0',
        'timezone' => 'America/Lima',
        'debug' => false,
        'maintenance_mode' => false,
        'session_lifetime' => 3600, // 1 hora
        'max_execution_time' => 30,
        'memory_limit' => '512M'
    ],
    
    // Configuración de actualización de datos
    'update' => [
        'interval_seconds' => 4, // Intervalo de actualización automática
        'max_events' => 1000, // Máximo de eventos a mostrar
        'cache_duration' => 60, // Duración del cache en segundos
        'enable_statistics' => true, // Habilitar guardado de estadísticas
        'statistics_dir' => __DIR__ . '/../api/statistics/'
    ],
    
    // Configuración de filtros
    'filters' => [
        'default_date_range' => 7, // Días por defecto para filtrar
        'max_date_range' => 30, // Máximo de días permitidos
        'enable_export' => true, // Habilitar exportación a Excel
        'export_max_records' => 10000 // Máximo de registros para exportar
    ],
    
    // Configuración de seguridad
    'security' => [
        'encrypt_tokens' => true,
        'session_encryption_key' => 'zabbix_realtime_map_2024',
        'csrf_protection' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60
        ]
    ],
    
    // Configuración de logging
    'logging' => [
        'enabled' => true,
        'level' => 'INFO', // DEBUG, INFO, WARNING, ERROR
        'log_file' => __DIR__ . '/../logs/app.log',
        'max_file_size' => '10MB',
        'max_files' => 5
    ],
    
    // Configuración de mapas (para futuras implementaciones)
    'map' => [
        'default_zoom' => 10,
        'default_center' => [
            'lat' => -12.0464, // Lima, Perú
            'lng' => -77.0428
        ],
        'enable_clustering' => true,
        'cluster_distance' => 50
    ]
];