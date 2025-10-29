<?php
/**
 * Panel de Configuración - Zabbix Real-Time Map Monitor
 * 
 * Permite configurar:
 * - Conexión a Zabbix (IP, Puerto, Token)
 * - Conexión a PostgreSQL (IP, Puerto, Base de datos, Usuario, Clave)
 * - Configuraciones generales de la aplicación
 */

// Iniciar sesión
session_start();

// Cargar configuración principal
$config = require_once 'include/config.php';

// Leer versión del sistema desde archivo VERSION
$version = trim(file_get_contents('VERSION'));

// Configurar zona horaria
date_default_timezone_set($config['app']['timezone']);

// Incluir clases necesarias
require_once(__DIR__ . '/include/ZabbixApi.php');
use IntelliTrend\Zabbix\ZabbixApi;

// Incluir configuración actual
$configFile = __DIR__ . '/include/config.php';
$currentConfig = file_exists($configFile) ? require($configFile) : [];

// Procesar formulario de configuración
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    try {
        // Validar datos de entrada
        $zabbixIp = filter_var($_POST['zabbix_ip'] ?? '', FILTER_VALIDATE_IP);
        $zabbixPort = filter_var($_POST['zabbix_port'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        $zabbixToken = trim($_POST['zabbix_token'] ?? '');
        
        $dbIp = filter_var($_POST['db_ip'] ?? '', FILTER_VALIDATE_IP);
        $dbPort = filter_var($_POST['db_port'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        $dbName = trim($_POST['db_name'] ?? '');
        $dbUser = trim($_POST['db_user'] ?? '');
        $dbPass = $_POST['db_pass'] ?? '';
        
        // Validaciones
        if (!$zabbixIp) {
            throw new Exception('IP de Zabbix no válida');
        }
        if (!$zabbixPort) {
            throw new Exception('Puerto de Zabbix no válido');
        }
        if (empty($zabbixToken)) {
            throw new Exception('Token de Zabbix es requerido');
        }
        if (!$dbIp) {
            throw new Exception('IP de PostgreSQL no válida');
        }
        if (!$dbPort) {
            throw new Exception('Puerto de PostgreSQL no válido');
        }
        if (empty($dbName)) {
            throw new Exception('Nombre de base de datos es requerido');
        }
        if (empty($dbUser)) {
            throw new Exception('Usuario de base de datos es requerido');
        }
        
        // Construir URL de Zabbix
        $protocol = ($zabbixPort == 443) ? 'https' : 'http';
        $zabbixUrl = $protocol . '://' . $zabbixIp . ':' . $zabbixPort . '/zabbix';
        
        // Crear nueva configuración
        $newConfig = [
            'zabbix' => [
                'ip' => $zabbixIp,
                'port' => $zabbixPort,
                'token' => $zabbixToken,
                'url' => $zabbixUrl,
                'timeout' => 30,
                'connect_timeout' => 10,
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
                'use_gzip' => true,
                'debug' => false
            ],
            'database' => [
                'host' => $dbIp,
                'port' => $dbPort,
                'dbname' => $dbName,
                'user' => $dbUser,
                'pass' => $dbPass,
                'charset' => 'utf8',
                'options' => [
                    'PDO::ATTR_ERRMODE' => 'PDO::ERRMODE_EXCEPTION',
                    'PDO::ATTR_DEFAULT_FETCH_MODE' => 'PDO::FETCH_ASSOC',
                    'PDO::ATTR_EMULATE_PREPARES' => false,
                ]
            ],
            'app' => [
                'name' => 'Zabbix Real-Time Map Monitor',
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
                'enable_statistics' => true,
                'statistics_dir' => __DIR__ . '/api/statistics/'
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
                'log_file' => __DIR__ . '/logs/app.log',
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
        
        // Generar contenido del archivo de configuración
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Configuración de la aplicación Zabbix Real-Time Map Monitor\n";
        $configContent .= " * Generado automáticamente el " . date('Y-m-d H:i:s') . "\n";
        $configContent .= " */\n\n";
        $configContent .= "return " . var_export($newConfig, true) . ";\n";
        
        // Escribir archivo de configuración
        if (file_put_contents($configFile, $configContent) === false) {
            throw new Exception('No se pudo escribir el archivo de configuración. Verifique permisos.');
        }
        
        // Probar conexión a Zabbix
        try {
            $zbx = new ZabbixApi();
            $zbx->loginToken($zabbixUrl, $zabbixToken);
            $version = $zbx->getApiVersion();
            
            $message = "Configuración guardada exitosamente. Zabbix API v{$version} conectada correctamente.";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Configuración guardada, pero error al conectar con Zabbix: " . $e->getMessage();
            $messageType = 'warning';
        }
        
        // Probar conexión a PostgreSQL
        try {
            $dsn = "pgsql:host={$dbIp};port={$dbPort};dbname={$dbName};charset=utf8";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            
            $message .= " PostgreSQL conectada correctamente.";
        } catch (Exception $e) {
            $message .= " Error al conectar con PostgreSQL: " . $e->getMessage();
            $messageType = 'warning';
        }
        
        // Recargar configuración actual
        $currentConfig = $newConfig;
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Obtener valores actuales para el formulario
$zabbixIp = $currentConfig['zabbix']['ip'] ?? '';
$zabbixPort = $currentConfig['zabbix']['port'] ?? '80';
$zabbixToken = $currentConfig['zabbix']['token'] ?? '';
$dbIp = $currentConfig['database']['host'] ?? '';
$dbPort = $currentConfig['database']['port'] ?? '5432';
$dbName = $currentConfig['database']['dbname'] ?? '';
$dbUser = $currentConfig['database']['user'] ?? '';
$dbPass = $currentConfig['database']['pass'] ?? '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?= htmlspecialchars($config['app']['name']) ?></title>
    <link rel="icon" type="image/x-icon" href="include/ico/bms.ico">
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Material Design Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(148, 163, 184, 0.1);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .input-field {
            background: rgba(30, 41, 59, 0.6);
            border: 1px solid rgba(148, 163, 184, 0.2);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background: rgba(30, 41, 59, 0.8);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #64748b 0%, #475569 100%);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(100, 116, 139, 0.3);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl mb-4">
                <span class="mdi mdi-cog text-2xl text-white"></span>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Configuración del Sistema</h1>
            <p class="text-gray-400"><?= htmlspecialchars($config['app']['name']) ?> v<?= htmlspecialchars($version) ?></p>
        </div>

        <!-- Mensaje de estado -->
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-500/20 border border-green-500/30 text-green-300' : ($messageType === 'warning' ? 'bg-yellow-500/20 border border-yellow-500/30 text-yellow-300' : 'bg-red-500/20 border border-red-500/30 text-red-300') ?>">
            <div class="flex items-center">
                <span class="mdi mdi-<?= $messageType === 'success' ? 'check-circle' : ($messageType === 'warning' ? 'alert' : 'close-circle') ?> mr-2"></span>
                <?= htmlspecialchars($message) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario de configuración -->
        <form method="POST" class="space-y-8">
            <!-- Configuración de Zabbix -->
            <div class="glass-card p-8 rounded-2xl">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center mr-4">
                        <span class="mdi mdi-chart-line text-white"></span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Configuración de Zabbix</h2>
                        <p class="text-gray-400 text-sm">Datos de conexión a la API de Zabbix</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-server mr-1"></span>
                            IP del Servidor
                        </label>
                        <input type="text" name="zabbix_ip" value="<?= htmlspecialchars($zabbixIp) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="192.168.1.100" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-network-outline mr-1"></span>
                            Puerto
                        </label>
                        <input type="number" name="zabbix_port" value="<?= htmlspecialchars($zabbixPort) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="80" min="1" max="65535" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-key mr-1"></span>
                            Token de API
                        </label>
                        <input type="password" name="zabbix_token" value="<?= htmlspecialchars($zabbixToken) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="Token de autenticación" required>
                    </div>
                </div>
            </div>

            <!-- Configuración de PostgreSQL -->
            <div class="glass-card p-8 rounded-2xl">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center mr-4">
                        <span class="mdi mdi-database text-white"></span>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">Configuración de PostgreSQL</h2>
                        <p class="text-gray-400 text-sm">Datos de conexión a la base de datos</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-server mr-1"></span>
                            IP del Servidor
                        </label>
                        <input type="text" name="db_ip" value="<?= htmlspecialchars($dbIp) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="192.168.1.100" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-network-outline mr-1"></span>
                            Puerto
                        </label>
                        <input type="number" name="db_port" value="<?= htmlspecialchars($dbPort) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="5432" min="1" max="65535" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-database mr-1"></span>
                            Nombre de Base de Datos
                        </label>
                        <input type="text" name="db_name" value="<?= htmlspecialchars($dbName) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="zabbix_db" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-account mr-1"></span>
                            Usuario
                        </label>
                        <input type="text" name="db_user" value="<?= htmlspecialchars($dbUser) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="zabbix_user" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <span class="mdi mdi-lock mr-1"></span>
                            Contraseña
                        </label>
                        <input type="password" name="db_pass" value="<?= htmlspecialchars($dbPass) ?>" 
                               class="w-full px-4 py-3 rounded-lg input-field text-white placeholder-gray-400" 
                               placeholder="Contraseña" required>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button type="submit" name="save_config" 
                        class="btn-primary px-8 py-3 rounded-lg text-white font-semibold flex items-center justify-center">
                    <span class="mdi mdi-content-save mr-2"></span>
                    Guardar Configuración
                </button>
                
                <a href="events_zabbix.php" 
                   class="btn-secondary px-8 py-3 rounded-lg text-white font-semibold flex items-center justify-center">
                    <span class="mdi mdi-arrow-left mr-2"></span>
                    Volver a Eventos
                </a>
            </div>
        </form>

        <!-- Información adicional -->
        <div class="mt-8 text-center text-gray-400 text-sm">
            <p>La configuración se guarda en <code class="bg-gray-800 px-2 py-1 rounded">include/config.php</code></p>
            <p class="mt-2">Se realizarán pruebas de conexión automáticas al guardar</p>
        </div>
    </div>
</body>
</html>
