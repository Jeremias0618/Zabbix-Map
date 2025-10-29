<?php
// Endpoint para obtener datos de eventos Zabbix sin bloquear otras páginas
// También funciona como cron job para recolectar estadísticas cada 3 segundos

// Incluir configuración principal
$config = require_once(__DIR__ . "/../include/config.php");

// Configurar zona horaria desde configuración
date_default_timezone_set($config['app']['timezone']);

// Detectar si es llamada desde cron o desde web
$isCronJob = (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST']));

// Si no es cron job, cerrar sesión para evitar bloqueos
if (!$isCronJob) {
    session_write_close();
    
    // Headers para evitar cache
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Content-Type: application/json; charset=utf-8');
}

// Configurar límites de tiempo y memoria desde configuración
ini_set('max_execution_time', $config['app']['max_execution_time']);
ini_set('memory_limit', $config['app']['memory_limit']);
set_time_limit($config['app']['max_execution_time']);

// Incluir clases
require_once(__DIR__ . "/../include/ZabbixApi.php");

use IntelliTrend\Zabbix\ZabbixApi;
use IntelliTrend\Zabbix\ZabbixApiException;

// Función para obtener datos de clientes (dashboard_client.php)
function getClientData($config) {
    $zabConfig = $config['zabbix'];
    
    $zabUrl = $zabConfig['url'];
    $zabToken = $zabConfig['token'];
    $groupName = 'OLT';
    
    try {
        $zbx = new ZabbixApi();
        $zbx->loginToken($zabUrl, $zabToken);

        // 1) Obtener ID del grupo "OLT"
        $groups = $zbx->call('hostgroup.get', [
            'filter' => ['name' => [$groupName]],
            'output' => ['groupid']
        ]);
        if (empty($groups)) {
            return [];
        }
        $groupid = $groups[0]['groupid'];

        // 2) Obtener hosts del grupo
        $hosts = $zbx->call('host.get', [
            'output'   => ['hostid','host','name'],
            'groupids' => [$groupid]
        ]);
        if (empty($hosts)) {
            return [];
        }
        
        $hostMap = [];
        foreach ($hosts as $h) {
            $hostMap[$h['hostid']] = $h['host'];
        }

        // 3) Obtener filtros de tags para clientes
        $tagFilters = [
            // Filtros para equipos alarmados
            [
                ['tag' => 'OLT', 'value' => 'HUAWEI'],
                ['tag' => 'ONU', 'value' => 'DESCONEXIÓN'],
                ['tag' => 'ONU', 'value' => 'EQUIPO ALARMADO'],
                ['tag' => 'ONU', 'value' => 'ESTADO']
            ],
            // Filtros para problemas de potencia
            [
                ['tag' => 'OLT', 'value' => 'HUAWEI'],
                ['tag' => 'ONU', 'value' => 'POTENCIA TX'],
                ['tag' => 'ONU', 'value' => 'POTENCIA RX'],
                ['tag' => 'ONU', 'value' => 'PROBLEMAS DE POTENCIA']
            ]
        ];

        // 4) Recoger problemas activos y resueltos con múltiples filtros de tags
        $allProblems = [];
        
        foreach ($hostMap as $hid => $hostName) {
            foreach ($tagFilters as $tagFilterSet) {
                $probs = $zbx->call('problem.get', [
                    'output'    => ['eventid','name','severity','clock','r_clock'],
                    'hostids'   => [$hid],
                    'tags'      => $tagFilterSet,
                    'recent'    => true,
                    'selectTags'=> ['tag','value'],
                ]);
                
                foreach ($probs as $p) {
                    $p['hostid'] = $hid;
                    $p['status'] = !empty($p['r_clock']) ? 'RESOLVED' : 'PROBLEM';
                    $allProblems[] = $p;
                }
            }
        }

        if (empty($allProblems)) {
            return [];
        }

        // 5) Eliminar duplicados basado en eventid
        $uniqueProblems = [];
        $seenEventIds = [];
        foreach ($allProblems as $p) {
            if (!in_array($p['eventid'], $seenEventIds)) {
                $uniqueProblems[] = $p;
                $seenEventIds[] = $p['eventid'];
            }
        }

        // 6) Ordenar por 'clock' descendente (más recientes primero)
        usort($uniqueProblems, fn($a, $b) => $b['clock'] <=> $a['clock']);

        // 7) Transformar a formato solicitado
        $jsonProblems = [];
        foreach ($uniqueProblems as $p) {
            $hid = $p['hostid'];
            $hostName = $hostMap[$hid];
            
            // Extraer PON/LOG
            $ponLogInfo = null;
            if (preg_match('/\((\d+\/\d+\/\d+)\)/', $p['name'], $matches)) {
                $ponLogInfo = $matches[1];
            }
            
            // Extraer DNI
            $dniInfo = null;
            if (preg_match('/DNI\s+\(([^)]+)\)/', $p['name'], $matches)) {
                $dniInfo = $matches[1];
            }
            
            // Limpiar descripción
            $description = $p['name'];
            if (preg_match('/PROBLEMAS DE POTENCIA\s+(\d+)\s+([\d.]+)\s*km/', $p['name'], $matches)) {
                $description = 'DNI: ' . $matches[1] . ' - Distancia: ' . $matches[2] . ' km';
            }
            
            // Determinar tipo de problema
            $problemType = 'OTRO';
            if (strpos($p['name'], 'EQUIPO ALARMADO') !== false) {
                $problemType = 'EQUIPO ALARMADO';
            } elseif (strpos($p['name'], 'PROBLEMAS DE POTENCIA') !== false) {
                $problemType = 'PROBLEMAS DE POTENCIA';
            }
            
            // Calcular TIME con zona horaria de Perú
            $timeAdjusted = $p['clock'];
            
            $jsonProblems[] = [
                'HOST' => $hostName,
                'PON/LOG' => $ponLogInfo ?: 'N/A',
                'DNI' => $dniInfo ?: 'N/A',
                'TIPO' => $problemType,
                'STATUS' => $p['status'],
                'TIME' => date('Y-m-d g:i:s A', $timeAdjusted),
                'DESCRIPCION' => $description
            ];
        }

        return $jsonProblems;

    } catch (Exception $e) {
        error_log("Error Zabbix Clientes: " . $e->getMessage());
        return [];
    }
}

// Función para obtener datos de threads (dashboard_thread.php)
function getThreadData($config) {
    $zabConfig = $config['zabbix'];
    
    $zabUrl = $zabConfig['url'];
    $zabToken = $zabConfig['token'];
    $groupName = 'OLT';
    
    // Tag filter para caída de hilo
    $tagFilter = [
        'tag' => 'PON',
        'value' => 'CAIDA DE HILO'
    ];
    
    try {
        $zbx = new ZabbixApi();
        $zbx->loginToken($zabUrl, $zabToken);

        // 1) Obtener ID del grupo "OLT"
        $groups = $zbx->call('hostgroup.get', [
            'filter' => ['name' => [$groupName]],
            'output' => ['groupid']
        ]);
        if (empty($groups)) {
            return [];
        }
        $groupid = $groups[0]['groupid'];

        // 2) Obtener hosts del grupo
        $hosts = $zbx->call('host.get', [
            'output'   => ['hostid','host','name'],
            'groupids' => [$groupid]
        ]);
        if (empty($hosts)) {
            return [];
        }
        
        $hostMap = [];
        foreach ($hosts as $h) {
            $hostMap[$h['hostid']] = $h['host'];
        }

        // 3) Recoger problemas activos y resueltos
        $allProblems = [];
        foreach ($hostMap as $hid => $hostName) {
            $probs = $zbx->call('problem.get', [
                'output'    => ['eventid','name','severity','clock','r_clock'],
                'hostids'   => [$hid],
                'tags'      => [$tagFilter],
                'recent'    => true,
                'selectTags'=> ['tag','value'],
            ]);
            foreach ($probs as $p) {
                $p['hostid'] = $hid;
                $p['status'] = !empty($p['r_clock']) ? 'RESOLVED' : 'PROBLEM';
                $allProblems[] = $p;
            }
        }

        if (empty($allProblems)) {
            return [];
        }

        // 4) Ordenar por 'clock' descendente (más recientes primero)
        usort($allProblems, fn($a, $b) => $b['clock'] <=> $a['clock']);

        // 5) Transformar a formato solicitado
        $jsonProblems = [];
        foreach ($allProblems as $p) {
            $hid = $p['hostid'];
            $hostName = $hostMap[$hid];
            
            // Extraer GPON
            $gponInfo = null;
            if (preg_match('/GPON\s+(\d+)\/(\d+)\/(\d+)/', $p['name'], $matches)) {
                $gponInfo = $matches[2] . '/' . $matches[3]; // Y/Z
            }
            
            // Limpiar descripción
            $description = '';
            if (preg_match('/\(:([^)]+)\)/', $p['name'], $matches)) {
                $description = $matches[1];
            } elseif (preg_match('/GPON\s+\d+\/\d+\/\d+\s+(.*)/', $p['name'], $matches)) {
                $desc = trim($matches[1]);
                $desc = preg_replace('/^\(:?([^)]*)\)?$/', '$1', $desc);
                $description = $desc;
            }
            
            // Determinar tipo de problema
            $problemType = 'CAIDA DE HILO';
            if (strpos($p['name'], 'CAIDA DE HILO') === false) {
                $problemType = 'OTRO';
            }
            
            // Calcular TIME con zona horaria de Perú
            $timeAdjusted = $p['clock'];
            
            if ($gponInfo !== null) {
                $jsonProblems[] = [
                    'HOST' => $hostName,
                    'GPON' => $gponInfo,
                    'DNI' => 'N/A', // Los threads no tienen DNI
                    'TIPO' => $problemType,
                    'STATUS' => $p['status'],
                    'TIME' => date('Y-m-d g:i:s A', $timeAdjusted),
                    'DESCRIPCION' => $description ?: ''
                ];
            }
        }

        return $jsonProblems;

    } catch (Exception $e) {
        error_log("Error Zabbix Threads: " . $e->getMessage());
        return [];
    }
}

// Función principal para obtener todos los datos
function getAllEventsData($config) {
    $clientData = getClientData($config);
    $threadData = getThreadData($config);
    
    // Combinar ambos datasets
    $allEvents = array_merge($clientData, $threadData);
    
    // Ordenar por tiempo (más recientes primero)
    usort($allEvents, function($a, $b) {
        return strtotime($b['TIME']) - strtotime($a['TIME']);
    });
    
    return $allEvents;
}

// Función para deduplicar eventos y contar únicos
function deduplicateEvents($events) {
    $uniqueEvents = [];
    $eventMap = [];
    
    foreach ($events as $event) {
        // Crear clave única basada en HOST + PON/LOG/GPON + DNI + TIPO
        $ponLog = isset($event['PON/LOG']) ? $event['PON/LOG'] : $event['GPON'];
        $eventKey = $event['HOST'] . '|' . $ponLog . '|' . $event['DNI'] . '|' . $event['TIPO'];
        
        $eventTime = strtotime($event['TIME']);
        
        if (!isset($eventMap[$eventKey])) {
            // Primer evento de este tipo
            $eventMap[$eventKey] = [
                'event' => $event,
                'first_time' => $eventTime,
                'last_status' => $event['STATUS'],
                'last_time' => $eventTime
            ];
            $uniqueEvents[] = $event;
        } else {
            // Evento ya existe, verificar si es actualización
            $existing = $eventMap[$eventKey];
            $timeDiff = abs($eventTime - $existing['last_time']);
            
            // Si la diferencia es menor a 60 segundos y solo cambió el STATUS, no contar como nuevo
            if ($timeDiff < 60 && 
                $existing['last_status'] === 'PROBLEM' && 
                $event['STATUS'] === 'RESOLVED') {
                // Es resolución del mismo evento, actualizar pero no agregar como nuevo
                $eventMap[$eventKey]['last_status'] = $event['STATUS'];
                $eventMap[$eventKey]['last_time'] = $eventTime;
            } else {
                // Es un evento nuevo (diferente tiempo o reincidencia)
                $eventMap[$eventKey] = [
                    'event' => $event,
                    'first_time' => $eventTime,
                    'last_status' => $event['STATUS'],
                    'last_time' => $eventTime
                ];
                $uniqueEvents[] = $event;
            }
        }
    }
    
    return $uniqueEvents;
}

// Función para guardar estadísticas históricas
function saveStatistics($events, $total, $individual, $threadFalls, $config) {
    $statisticsDir = $config['update']['statistics_dir'];
    if (!is_dir($statisticsDir)) {
        mkdir($statisticsDir, 0755, true);
    }
    
    $now = new DateTime();
    $timestamp = $now->format('Y-m-d H:i:s');
    $dateKey = $now->format('Y-m-d');
    $hourKey = $now->format('H');
    
    // Deduplicar eventos para obtener conteos reales
    $uniqueEvents = deduplicateEvents($events);
    $uniqueTotal = count($uniqueEvents);
    $uniqueIndividual = count(array_filter($uniqueEvents, function($e) { return $e['DNI'] !== 'N/A'; }));
    $uniqueThreadFalls = count(array_filter($uniqueEvents, function($e) { return $e['DNI'] === 'N/A'; }));
    
    // Datos a guardar
    $statsData = [
        'timestamp' => $timestamp,
        'total' => $total, // Total bruto de la consulta
        'individual' => $individual, // Individual bruto
        'caida_hilos' => $threadFalls, // Caídas brutas
        'unique_total' => $uniqueTotal, // Total únicos (sin duplicados)
        'unique_individual' => $uniqueIndividual, // Individual únicos
        'unique_threads' => $uniqueThreadFalls, // Caídas únicas
        'hour' => $now->format('H:i:s'),
        'date' => $dateKey,
        'events_sample' => array_slice($uniqueEvents, 0, 10) // Muestra de eventos únicos
    ];
    
    // 1. Guardar solo cada minuto (no cada 3 segundos) para evitar archivos gigantes
    $minuteKey = $now->format('H:i');
    $dailyFile = $statisticsDir . "stats_$dateKey.json";
    $dailyStats = [];
    
    if (file_exists($dailyFile)) {
        $dailyStats = json_decode(file_get_contents($dailyFile), true) ?: [];
    }
    
    // Solo guardar si es una nueva entrada por minuto (evitar 20 entradas por minuto)
    $shouldSave = true;
    if (!empty($dailyStats)) {
        $lastEntry = end($dailyStats);
        $lastMinute = date('H:i', strtotime($lastEntry['timestamp']));
        if ($lastMinute === $minuteKey) {
            // Ya hay entrada para este minuto, actualizar en lugar de agregar
            $dailyStats[count($dailyStats) - 1] = $statsData;
            $shouldSave = true;
        } else {
            // Nueva entrada para nuevo minuto
            $dailyStats[] = $statsData;
            $shouldSave = true;
        }
    } else {
        // Primera entrada
        $dailyStats[] = $statsData;
        $shouldSave = true;
    }
    
    if ($shouldSave) {
        // Mantener solo las últimas 1440 entradas (24 horas × 60 minutos)
        if (count($dailyStats) > 1440) {
            $dailyStats = array_slice($dailyStats, -1440);
        }
        
        file_put_contents($dailyFile, json_encode($dailyStats, JSON_PRETTY_PRINT));
    }
    
    // 2. Guardar resumen por horas optimizado (en lugar de eventos completos)
    $hourlyFile = $statisticsDir . "hourly_$dateKey.json";
    $hourlyStats = [];
    
    if (file_exists($hourlyFile)) {
        $hourlyStats = json_decode(file_get_contents($hourlyFile), true) ?: [];
    }
    
    // Crear hash de eventos únicos para esta recopilación
    $eventHashes = [];
    foreach ($uniqueEvents as $event) {
        $ponLog = isset($event['PON/LOG']) ? $event['PON/LOG'] : ($event['GPON'] ?? 'N/A');
        $eventHash = md5($event['HOST'] . '|' . $ponLog . '|' . $event['DNI'] . '|' . $event['TIPO']);
        $eventHashes[] = $eventHash;
    }
    
    // Actualizar datos de la hora actual
    if (!isset($hourlyStats[$hourKey])) {
        $hourlyStats[$hourKey] = [
            'hour' => $hourKey,
            'unique_events' => [],
            'individual_hashes' => [],
            'thread_hashes' => [],
            'last_update' => $timestamp
        ];
    }
    
    // Separar hashes por tipo para mantener el desglose
    $individualHashes = [];
    $threadHashes = [];
    
    foreach ($uniqueEvents as $event) {
        $ponLog = isset($event['PON/LOG']) ? $event['PON/LOG'] : ($event['GPON'] ?? 'N/A');
        $eventHash = md5($event['HOST'] . '|' . $ponLog . '|' . $event['DNI'] . '|' . $event['TIPO']);
        
        if ($event['DNI'] !== 'N/A') {
            $individualHashes[] = $eventHash;
        } else {
            $threadHashes[] = $eventHash;
        }
    }
    
    // Inicializar arrays si no existen (migración de estructura antigua)
    if (!isset($hourlyStats[$hourKey]['individual_hashes'])) {
        $hourlyStats[$hourKey]['individual_hashes'] = [];
    }
    if (!isset($hourlyStats[$hourKey]['thread_hashes'])) {
        $hourlyStats[$hourKey]['thread_hashes'] = [];
    }
    
    // Agregar nuevos hashes únicos por categoría (evitar duplicados)
    foreach ($eventHashes as $hash) {
        if (!in_array($hash, $hourlyStats[$hourKey]['unique_events'])) {
            $hourlyStats[$hourKey]['unique_events'][] = $hash;
        }
    }
    
    foreach ($individualHashes as $hash) {
        if (!in_array($hash, $hourlyStats[$hourKey]['individual_hashes'])) {
            $hourlyStats[$hourKey]['individual_hashes'][] = $hash;
        }
    }
    
    foreach ($threadHashes as $hash) {
        if (!in_array($hash, $hourlyStats[$hourKey]['thread_hashes'])) {
            $hourlyStats[$hourKey]['thread_hashes'][] = $hash;
        }
    }
    
    $hourlyStats[$hourKey]['last_update'] = $timestamp;
    
    file_put_contents($hourlyFile, json_encode($hourlyStats, JSON_PRETTY_PRINT));
    
    // 3. Log de texto simple (backup)
    $logFile = $statisticsDir . "events_log_$dateKey.txt";
    $logEntry = "$timestamp - TOTAL: $total ($uniqueTotal únicos), INDIVIDUAL: $individual ($uniqueIndividual únicos), CAIDA DE HILOS: $threadFalls ($uniqueThreadFalls únicos)\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    
    return true;
}

// Ejecutar y devolver resultado
try {
    $events = getAllEventsData($config);
    $total = count($events);
    $individual = count(array_filter($events, function($e) { return $e['DNI'] !== 'N/A'; }));
    $threadFalls = count(array_filter($events, function($e) { return $e['DNI'] === 'N/A'; }));
    
    // Guardar estadísticas (siempre, tanto para cron como web)
    if ($config['update']['enable_statistics']) {
        saveStatistics($events, $total, $individual, $threadFalls, $config);
    }
    
    // Respuesta según el tipo de ejecución
    if ($isCronJob) {
        // Para cron job, solo log
        echo "[" . date('Y-m-d H:i:s') . "] Estadísticas guardadas - TOTAL: $total, INDIVIDUAL: $individual, CAÍDA DE HILOS: $threadFalls\n";
    } else {
        // Para web, JSON response
        echo json_encode([
            'success' => true,
            'events' => $events,
            'total' => $total,
            'timestamp' => date('Y-m-d H:i:s'),
            'client_count' => $individual,
            'thread_count' => $threadFalls
        ]);
    }
    
} catch (Exception $e) {
    error_log("Error general en get_events_data.php: " . $e->getMessage());
    
    if ($isCronJob) {
        echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener datos de eventos',
            'events' => [],
            'total' => 0,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
?>
