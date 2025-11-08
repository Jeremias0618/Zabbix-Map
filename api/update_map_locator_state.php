<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$allowedStates = [
    'planned',
    'unplanned',
    'without_message',
    'no_visitors',
    'its_not_a_problem',
    'fieldwork',
];

try {
    $input = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    respond(false, 'Payload inválido');
}

$host = isset($input['host']) ? trim((string) $input['host']) : '';
$ponLog = isset($input['pon_log']) ? trim((string) $input['pon_log']) : '';
$state = isset($input['state_fp']) ? trim((string) $input['state_fp']) : '';

if ($host === '' || $ponLog === '' || !in_array($state, $allowedStates, true)) {
    respond(false, 'Parámetros inválidos');
}

$logFile = __DIR__ . '/../deploy/map_locator.log';

if (!file_exists($logFile)) {
    respond(false, 'Archivo de log no encontrado');
}

$handle = fopen($logFile, 'c+');
if ($handle === false) {
    respond(false, 'No se pudo abrir el archivo de log');
}

if (!flock($handle, LOCK_EX)) {
    fclose($handle);
    respond(false, 'No se pudo bloquear el archivo de log');
}

try {
    $content = stream_get_contents($handle);
    if ($content === false) {
        throw new RuntimeException('No se pudo leer el archivo de log');
    }

    [$generatedAt, $records] = decodeLogContent($content);

    $targetKey = buildStateKey($host, normalizePonForKey($host, $ponLog));

    $found = false;
    foreach ($records as &$record) {
        if (!is_array($record)) {
            continue;
        }

        $recordHost = trim((string) ($record['host'] ?? ''));
        $recordPon = trim((string) ($record['pon_log'] ?? ''));
        if ($recordHost === '' || $recordPon === '') {
            continue;
        }

        $recordKey = buildStateKey($recordHost, normalizePonForKey($recordHost, $recordPon));
        if ($recordKey === $targetKey) {
            $record['state_fp'] = $state;
            $found = true;
            break;
        }
    }
    unset($record);

    if (!$found) {
        throw new RuntimeException('Registro no encontrado');
    }

    $payload = [
        'generated_at' => date('c'),
        'records' => $records,
    ];

    $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('No se pudo generar JSON actualizado');
    }

    rewind($handle);
    if (!ftruncate($handle, 0)) {
        throw new RuntimeException('No se pudo limpiar el archivo de log');
    }

    if (fwrite($handle, $json . PHP_EOL) === false) {
        throw new RuntimeException('No se pudo escribir el archivo de log');
    }

    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    respond(true, 'Estado actualizado correctamente', ['state_fp' => $state]);
} catch (Throwable $e) {
    flock($handle, LOCK_UN);
    fclose($handle);
    respond(false, $e->getMessage());
}

function respond(bool $success, string $message, array $extra = []): void
{
    http_response_code($success ? 200 : 400);
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function decodeLogContent(string $content): array
{
    $content = trim($content);
    if ($content === '') {
        return [date('c'), []];
    }

    $decoded = json_decode($content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($decoded['records']) && is_array($decoded['records'])) {
            $generatedAt = isset($decoded['generated_at']) && is_string($decoded['generated_at'])
                ? $decoded['generated_at']
                : date('c');
            return [$generatedAt, $decoded['records']];
        }

        if (is_array($decoded)) {
            return [date('c'), $decoded];
        }
    }

    $records = [];
    $lines = preg_split('/\R+/', $content, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($lines as $line) {
        $item = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($item)) {
            $records[] = $item;
        }
    }

    return [date('c'), $records];
}

function normalizePonForKey(string $host, string $ponLog): string
{
    $normalized = trim($ponLog);
    if (str_starts_with($normalized, $host . '/')) {
        $normalized = substr($normalized, strlen($host) + 1);
    }

    return ltrim($normalized, '/');
}

function buildStateKey(string $host, string $normalizedPon): string
{
    return $host . '::' . ltrim($normalizedPon, '/');
}

