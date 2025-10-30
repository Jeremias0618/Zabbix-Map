<?php
// Cargar configuración principal
$config = require_once 'include/config.php';

// Leer versión del sistema desde archivo VERSION
$version = trim(file_get_contents('VERSION'));

// Configurar zona horaria
date_default_timezone_set($config['app']['timezone']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['app']['name']) ?></title>
    <link rel="icon" type="image/x-icon" href="include/ico/bms.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap">
</head>
<body class="bg-gradient-to-br from-gray-900 via-blue-900 to-purple-900 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-white mb-2"><?= htmlspecialchars($config['app']['name']) ?></h1>
            <p class="text-sm text-gray-400 mb-2">v<?= htmlspecialchars($version) ?></p>
            <p class="text-gray-300 mb-8">Sistema de monitoreo en tiempo real de eventos Zabbix</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <a href="events_zabbix.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200">
                    <div class="flex items-center justify-center gap-3">
                        <span class="mdi mdi-chart-line text-2xl"></span>
                        <span>Eventos Zabbix</span>
                    </div>
                </a>
                
                <a href="basic_data.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200">
                    <div class="flex items-center justify-center gap-3">
                        <span class="mdi mdi-database text-2xl"></span>
                        <span>Datos Básicos</span>
            </div>
                </a>
                
                <a href="configuration.php" class="bg-green-600 hover:bg-green-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200">
                    <div class="flex items-center justify-center gap-3">
                        <span class="mdi mdi-cog text-2xl"></span>
                        <span>Configuración</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
