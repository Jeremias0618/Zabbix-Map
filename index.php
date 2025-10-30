<?php
// Cargar configuración principal
$config = require_once 'include/config.php';

// Leer versión del sistema desde archivo VERSION
$version = trim(file_get_contents('VERSION'));

// Configurar zona horaria
date_default_timezone_set($config['app']['timezone']);
// Ruta actual para activar navegación
$currentPage = basename($_SERVER['PHP_SELF']);
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
    <style>
      body { background-color: #0B1120; }
    </style>
</head>
<body class="min-h-screen bg-[#0a0e17] bg-gradient-to-br from-gray-950 via-slate-900 to-gray-950 text-white font-sans overflow-x-hidden">
    <header class="h-16 bg-gradient-to-r from-cyber-dark/40 to-cyber-dark/30 backdrop-blur-md border-b border-white/10 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto h-full flex items-center justify-between px-6">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center">
                    <span class="mdi mdi-react text-xl text-white"></span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-white"><?= htmlspecialchars($config['app']['name']) ?></h1>
                    <p class="text-xs text-gray-400">Panel de Control v<?= htmlspecialchars($version) ?></p>
                </div>
            </div>
            <nav class="flex items-center gap-2">
                <a href="index.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='index.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                    <span class="mdi mdi-home"></span>
                    <span>Inicio</span>
                </a>
                <a href="events_zabbix.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='events_zabbix.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                    <span class="mdi mdi-chart-line"></span>
                    <span>Eventos</span>
                </a>
                <a href="basic_data.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='basic_data.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                    <span class="mdi mdi-database"></span>
                    <span>Datos</span>
                </a>
                <a href="map_locator.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='map_locator.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                    <span class="mdi mdi-map"></span>
                    <span>Mapa</span>
                </a>
                <a href="configuration.php" class="px-4 py-2 rounded-lg transition-all duration-200 text-sm border-b-2 <?= $currentPage==='configuration.php' ? 'border-cyan-400 text-white' : 'border-transparent text-gray-300 hover:text-white hover:bg-cyber-blue/10' ?> flex items-center gap-2">
                    <span class="mdi mdi-wrench"></span>
                    <span>Configuración</span>
                </a>
        </nav>
        </div>
    </header>

    <main class="px-6 py-10">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-10">
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-orange-300 via-amber-300 to-yellow-300 mb-3"><?= htmlspecialchars($config['app']['name']) ?></h1>
                <p class="text-sm text-gray-400 mb-2">v<?= htmlspecialchars($version) ?></p>
                <p class="text-gray-300 max-w-3xl mx-auto">Sistema de monitoreo en tiempo real que integra la API de Zabbix con PostgreSQL para visualizar eventos, clientes y ubicaciones en un mapa interactivo.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <a href="events_zabbix.php" class="group block rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all duration-300 p-5 shadow-lg hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-600/20 flex items-center justify-center">
                            <span class="mdi mdi-chart-line text-2xl text-blue-400"></span>
                </div>
                        <div>
                            <h3 class="text-lg font-semibold">Eventos Zabbix</h3>
                            <p class="text-sm text-gray-400">Listado y análisis de eventos en tiempo real</p>
            </div>
                    </div>
                </a>

                <a href="basic_data.php" class="group block rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all duration-300 p-5 shadow-lg hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-purple-600/20 flex items-center justify-center">
                            <span class="mdi mdi-database text-2xl text-purple-400"></span>
                </div>
                        <div>
                            <h3 class="text-lg font-semibold">Datos Básicos</h3>
                            <p class="text-sm text-gray-400">Cruce de eventos con clientes de PostgreSQL</p>
                </div>
            </div>
                </a>

                <a href="configuration.php" class="group block rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all duration-300 p-5 shadow-lg hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-600/20 flex items-center justify-center">
                            <span class="mdi mdi-cog text-2xl text-green-400"></span>
                </div>
                        <div>
                            <h3 class="text-lg font-semibold">Configuración</h3>
                            <p class="text-sm text-gray-400">Parámetros de Zabbix, PostgreSQL y app</p>
                </div>
            </div>
                </a>

                <a href="map_locator.php" class="group block rounded-2xl border border-white/10 bg-white/5 hover:bg-white/10 transition-all duration-300 p-5 shadow-lg hover:-translate-y-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-red-600/20 flex items-center justify-center">
                            <span class="mdi mdi-map-marker-radius text-2xl text-red-400"></span>
                </div>
                        <div>
                            <h3 class="text-lg font-semibold">Mapa (Zabbix)</h3>
                            <p class="text-sm text-gray-400">Marcadores de eventos y clientes en OSM</p>
                        </div>
                    </div>
                </a>
                    </div>
                </div>
    </main>
</body>
</html>
