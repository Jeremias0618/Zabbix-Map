<?php
return [
    'zabbix' => [
        'ip' => '',
        'port' => '80',
        'token' => '',
        'url' => '',
        'timeout' => 30,
        'connect_timeout' => 10,
        'ssl_verify_peer' => false,
        'ssl_verify_host' => false,
        'use_gzip' => true,
        'debug' => false
    ],
    'database' => [
        'host' => '',
        'port' => '5432',
        'dbname' => '',
        'user' => '',
        'pass' => '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],
    'app' => [
        'name' => 'Zabbix Map',
        'version' => '1.0.0',
        'timezone' => 'America/Lima',
        'debug' => false,
        'maintenance_mode' => false,
        'session_lifetime' => 3600,
        'max_execution_time' => 30,
        'memory_limit' => '512M'
    ],
    'update' => [
        'interval_seconds' => 4,
        'max_events' => 1000,
        'cache_duration' => 60,
        'enable_statistics' => false,
        'statistics_dir' => __DIR__ . '/../api/statistics/'
    ],
    'filters' => [
        'default_date_range' => 7,
        'max_date_range' => 30,
        'enable_export' => true,
        'export_max_records' => 10000
    ],
    'security' => [
        'encrypt_tokens' => true,
        'session_encryption_key' => 'zabbix_realtime_map_2024',
        'csrf_protection' => true,
        'rate_limiting' => [
            'enabled' => true,
            'max_requests_per_minute' => 60
        ]
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'INFO',
        'log_file' => __DIR__ . '/../logs/app.log',
        'max_file_size' => '10MB',
        'max_files' => 5
    ],
    'map' => [
        'default_zoom' => 10,
        'default_center' => [
            'lat' => -12.0464,
            'lng' => -77.0428
        ],
        'enable_clustering' => true,
        'cluster_distance' => 50
    ]
];