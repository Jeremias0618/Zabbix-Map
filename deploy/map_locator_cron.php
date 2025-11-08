#!/usr/bin/env php
<?php
declare(strict_types=1);

use IntelliTrend\Zabbix\ZabbixApi;
use IntelliTrend\Zabbix\ZabbixApiException;

$projectRoot = dirname(__DIR__);

$config = require $projectRoot . '/include/config.php';

date_default_timezone_set($config['app']['timezone'] ?? 'UTC');

require_once $projectRoot . '/include/ZabbixApi.php';

$logFile = __DIR__ . '/map_locator.log';
$loopMode = in_array('--loop', $argv, true);

$cycleIntervalSeconds = 4;
$cyclesPerMinute = (int) floor(60 / $cycleIntervalSeconds);

try {
    $pdo = getDatabaseConnection($config['database']);
} catch (\PDOException $e) {
    fwrite(STDERR, '[' . date('Y-m-d H:i:s') . "] Error DB: " . $e->getMessage() . PHP_EOL);
    exit(1);
}

if ($loopMode) {
    for ($i = 0; $i < $cyclesPerMinute; $i++) {
        runCycle($config, $pdo, $logFile);
        if ($i < $cyclesPerMinute - 1) {
            sleep($cycleIntervalSeconds);
        }
    }
} else {
    runCycle($config, $pdo, $logFile);
}

exit(0);

function runCycle(array $config, \PDO $pdo, string $logFile): void
{
    try {
        $events = fetchAllEvents($config);
    } catch (Throwable $e) {
        fwrite(STDERR, '[' . date('Y-m-d H:i:s') . "] Error Zabbix: " . $e->getMessage() . PHP_EOL);
        return;
    }

    $records = [];

    foreach ($events as $event) {
        if (($event['STATUS'] ?? '') !== 'PROBLEM') {
            continue;
        }

        $host = trim((string) ($event['HOST'] ?? ''));
        $ponLog = trim((string) ($event['PON/LOG'] ?? ($event['GPON'] ?? '')));

        if ($host === '' || $ponLog === '' || strtoupper($ponLog) === 'N/A') {
            continue;
        }

        // Unificar formato host/slot/port/log
        $normalizedPon = $ponLog;
        if (str_starts_with($normalizedPon, $host . '/')) {
            $normalizedPon = substr($normalizedPon, strlen($host) + 1);
        }

        if (!isIndividualPon($normalizedPon) && !isIndividualPon($ponLog)) {
            continue;
        }

        $fullPonLog = str_contains($ponLog, '/') ? $ponLog : $host . '/' . $ponLog;
        if (!str_starts_with($fullPonLog, $host)) {
            $fullPonLog = $host . '/' . ltrim($normalizedPon, '/');
        }

        $clientInfo = fetchClientInfo($pdo, $fullPonLog);

        $record = [
            'host' => $host,
            'pon_log' => $fullPonLog,
            'tipo' => $event['TIPO'] ?? 'EQUIPO ALARMADO',
            'status' => $event['STATUS'] ?? 'PROBLEM',
            'timestamp' => formatTimestamp($event['TIME'] ?? ''),
            'dni' => $event['DNI'] ?? 'N/A',
            'descripcion' => $event['DESCRIPCION'] ?? '',
            'cliente' => $clientInfo['cliente'] ?? ($event['CLIENTE'] ?? null),
            'ubicacion' => $clientInfo['ubicacion'] ?? ($event['UBICACION'] ?? ''),
        ];

        if (!empty($clientInfo['dni_ruc']) && ($record['dni'] === 'N/A' || $record['dni'] === '' )) {
            $record['dni'] = $clientInfo['dni_ruc'];
        }

        $coords = extractLatLon($record['ubicacion']);
        if ($coords !== null) {
            $record['lat'] = $coords['lat'];
            $record['lon'] = $coords['lon'];
        }

        $records[] = $record;
    }

    usort($records, static function (array $a, array $b): int {
        return strcmp($a['pon_log'], $b['pon_log']);
    });

    writeLogFile($logFile, $records);
}

/**
 * Devuelve todos los eventos relevantes desde la API de Zabbix.
 *
 * @throws ZabbixApiException
 */
function fetchAllEvents(array $config): array
{
    $events = array_merge(
        fetchClientEvents($config),
        fetchThreadEvents($config)
    );

    usort($events, static function ($a, $b) {
        return strtotime($b['TIME'] ?? 'now') <=> strtotime($a['TIME'] ?? 'now');
    });

    return $events;
}

/**
 * Obtiene eventos de clientes (EQUIPO ALARMADO / PROBLEMAS DE POTENCIA).
 *
 * @throws ZabbixApiException
 */
function fetchClientEvents(array $config): array
{
    $zabConfig = $config['zabbix'];

    $zbx = new ZabbixApi();
    $zbx->loginToken($zabConfig['url'], $zabConfig['token']);

    $groupName = 'OLT';

    $groups = $zbx->call('hostgroup.get', [
        'filter' => ['name' => [$groupName]],
        'output' => ['groupid'],
    ]);

    if (empty($groups)) {
        return [];
    }

    $groupId = $groups[0]['groupid'];

    $hosts = $zbx->call('host.get', [
        'output'   => ['hostid', 'host', 'name'],
        'groupids' => [$groupId],
    ]);

    if (empty($hosts)) {
        return [];
    }

    $hostMap = [];
    foreach ($hosts as $host) {
        $hostMap[$host['hostid']] = $host['host'];
    }

    $tagFilters = [
        [
            ['tag' => 'OLT', 'value' => 'HUAWEI'],
            ['tag' => 'ONU', 'value' => 'DESCONEXIÓN'],
            ['tag' => 'ONU', 'value' => 'EQUIPO ALARMADO'],
            ['tag' => 'ONU', 'value' => 'ESTADO'],
        ],
        [
            ['tag' => 'OLT', 'value' => 'HUAWEI'],
            ['tag' => 'ONU', 'value' => 'POTENCIA TX'],
            ['tag' => 'ONU', 'value' => 'POTENCIA RX'],
            ['tag' => 'ONU', 'value' => 'PROBLEMAS DE POTENCIA'],
        ],
    ];

    $problems = [];

    foreach ($hostMap as $hostId => $hostName) {
        foreach ($tagFilters as $tags) {
            $data = $zbx->call('problem.get', [
                'output'    => ['eventid', 'name', 'severity', 'clock', 'r_clock'],
                'hostids'   => [$hostId],
                'tags'      => $tags,
                'recent'    => true,
                'selectTags'=> ['tag', 'value'],
            ]);

            foreach ($data as $item) {
                $item['hostid'] = $hostId;
                $item['status'] = !empty($item['r_clock']) ? 'RESOLVED' : 'PROBLEM';
                $problems[] = $item;
            }
        }
    }

    if (empty($problems)) {
        return [];
    }

    $unique = [];
    $seen = [];

    foreach ($problems as $problem) {
        if (isset($seen[$problem['eventid']])) {
            continue;
        }
        $seen[$problem['eventid']] = true;

        $hostName = $hostMap[$problem['hostid']] ?? 'UNKNOWN';

        $ponLog = null;
        if (preg_match('/\((\d+\/\d+\/\d+)\)/', $problem['name'], $matches)) {
            $ponLog = $matches[1];
        }

        $dni = null;
        if (preg_match('/DNI\s+\(([^)]+)\)/', $problem['name'], $matches)) {
            $dni = $matches[1];
        }

        $problemType = 'OTRO';
        if (str_contains($problem['name'], 'EQUIPO ALARMADO')) {
            $problemType = 'EQUIPO ALARMADO';
        } elseif (str_contains($problem['name'], 'PROBLEMAS DE POTENCIA')) {
            $problemType = 'PROBLEMAS DE POTENCIA';
        }

        $unique[] = [
            'HOST' => $hostName,
            'PON/LOG' => $ponLog ?? 'N/A',
            'DNI' => $dni ?? 'N/A',
            'TIPO' => $problemType,
            'STATUS' => $problem['status'],
            'TIME' => date('Y-m-d g:i:s A', (int) $problem['clock']),
            'DESCRIPCION' => $problem['name'],
        ];
    }

    return $unique;
}

/**
 * Obtiene eventos de tipo CAÍDA DE HILO.
 *
 * @throws ZabbixApiException
 */
function fetchThreadEvents(array $config): array
{
    $zabConfig = $config['zabbix'];

    $zbx = new ZabbixApi();
    $zbx->loginToken($zabConfig['url'], $zabConfig['token']);

    $groupName = 'OLT';
    $tagFilter = [
        ['tag' => 'PON', 'value' => 'CAIDA DE HILO'],
    ];

    $groups = $zbx->call('hostgroup.get', [
        'filter' => ['name' => [$groupName]],
        'output' => ['groupid'],
    ]);

    if (empty($groups)) {
        return [];
    }

    $groupId = $groups[0]['groupid'];

    $hosts = $zbx->call('host.get', [
        'output'   => ['hostid', 'host', 'name'],
        'groupids' => [$groupId],
    ]);

    if (empty($hosts)) {
        return [];
    }

    $hostMap = [];
    foreach ($hosts as $host) {
        $hostMap[$host['hostid']] = $host['host'];
    }

    $problems = [];

    foreach ($hostMap as $hostId => $hostName) {
        $data = $zbx->call('problem.get', [
            'output'    => ['eventid', 'name', 'severity', 'clock', 'r_clock'],
            'hostids'   => [$hostId],
            'tags'      => $tagFilter,
            'recent'    => true,
            'selectTags'=> ['tag', 'value'],
        ]);

        foreach ($data as $item) {
            $item['hostid'] = $hostId;
            $item['status'] = !empty($item['r_clock']) ? 'RESOLVED' : 'PROBLEM';
            $problems[] = $item;
        }
    }

    if (empty($problems)) {
        return [];
    }

    $events = [];

    foreach ($problems as $problem) {
        $hostName = $hostMap[$problem['hostid']] ?? 'UNKNOWN';

        $gpon = null;
        if (preg_match('/GPON\s+(\d+)\/(\d+)\/(\d+)/', $problem['name'], $matches)) {
            $gpon = $matches[2] . '/' . $matches[3];
        }

        $description = '';
        if (preg_match('/\(:([^)]+)\)/', $problem['name'], $matches)) {
            $description = $matches[1];
        }

        if ($gpon !== null) {
            $events[] = [
                'HOST' => $hostName,
                'GPON' => $gpon,
                'DNI' => 'N/A',
                'TIPO' => 'CAIDA DE HILO',
                'STATUS' => $problem['status'],
                'TIME' => date('Y-m-d g:i:s A', (int) $problem['clock']),
                'DESCRIPCION' => $description,
            ];
        }
    }

    return $events;
}

/**
 * Crea y devuelve la conexión PDO a Quanttel.
 *
 * @throws PDOException
 */
function getDatabaseConnection(array $dbConfig): \PDO
{
    $dsn = sprintf(
        'pgsql:host=%s;port=%d;dbname=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['dbname']
    );

    $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new \PDO($dsn, $dbConfig['user'], $dbConfig['pass'], $options);

    $charset = $dbConfig['charset'] ?? 'utf8';
    if ($charset !== '') {
        $pdo->exec("SET NAMES '" . strtoupper(preg_replace('/[^a-zA-Z0-9_\-]/', '', $charset)) . "'");
    }

    return $pdo;
}

/**
 * Devuelve la fila de la tabla clientes para el PON/LOG indicado.
 */
function fetchClientInfo(\PDO $pdo, string $ponLogFull): ?array
{
    static $stmt = null;

    if ($stmt === null) {
        $stmt = $pdo->prepare("
            SELECT 
                cliente,
                dni_ruc,
                pon_log,
                ubicacion
            FROM clientes
            WHERE pon_log = :pon_log
            LIMIT 1
        ");
    }

    $stmt->execute([':pon_log' => $ponLogFull]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    return $row;
}

/**
 * Convierte formatos de hora conocidos al formato requerido (Y/m/d h:iA).
 */
function formatTimestamp(string $time): string
{
    if ($time === '') {
        return '';
    }

    $timestamp = strtotime($time);
    if ($timestamp === false) {
        $pattern = '/(\d{4})[\/-](\d{2})[\/-](\d{2})\s+(\d{1,2}):(\d{2})(?::(\d{2}))?\s*(AM|PM)?/i';
        if (preg_match($pattern, $time, $matches)) {
            $year = $matches[1];
            $month = $matches[2];
            $day = $matches[3];
            $hour = (int) $matches[4];
            $minute = $matches[5];
            $ampm = strtoupper($matches[7] ?? 'AM');
            $hour = $hour % 12 + ($ampm === 'PM' ? 12 : 0);
            $timestamp = strtotime(sprintf('%s-%s-%s %02d:%s', $year, $month, $day, $hour, $minute));
        } else {
            return $time;
        }
    }

    return date('Y/m/d h:ia', $timestamp);
}

/**
 * Determina si un PON corresponde a una ONU individual (SLOT/PORT/LOG).
 */
function isIndividualPon(string $pon): bool
{
    $parts = array_filter(explode('/', $pon), fn($part) => $part !== '');
    return count($parts) >= 3;
}

/**
 * Extrae latitud/longitud de una URL o texto de Google Maps.
 */
function extractLatLon(?string $value): ?array
{
    if ($value === null || trim($value) === '') {
        return null;
    }

    $text = trim($value);

    $patterns = [
        '/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
        '/3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/',
        '/@(-?\d+\.\d+),(-?\d+\.\d+)/',
        '/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/',
        '/[?&]ll=(-?\d+\.\d+),(-?\d+\.\d+)/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $text, $match)) {
            $lat = (float) $match[1];
            $lon = (float) $match[2];
            if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                return ['lat' => $lat, 'lon' => $lon];
            }
        }
    }

    if (preg_match('/(-?\d+\.\d+)[,\s]+(-?\d+\.\d+)/', $text, $match)) {
        $lat = (float) $match[1];
        $lon = (float) $match[2];
        if ($lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
            return ['lat' => $lat, 'lon' => $lon];
        }
    }

    return null;
}

/**
 * Escribe el archivo JSON consumido por el mapa.
 */
function writeLogFile(string $path, array $records): void
{
    $payload = [
        'generated_at' => date('c'),
        'records' => $records,
    ];

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    file_put_contents($path, $json . PHP_EOL, LOCK_EX);
}

