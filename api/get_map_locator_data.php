<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$logFile = __DIR__ . '/../deploy/map_locator.log';

$response = [
    'success' => true,
    'records' => [],
];

$allowedStates = ['planned', 'unplanned', 'without_message', 'no_visitors', 'its_not_a_problem', 'fieldwork'];

if (!file_exists($logFile)) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$handle = @fopen($logFile, 'r');
if ($handle === false) {
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo abrir el archivo de log.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$content = '';
if (flock($handle, LOCK_SH)) {
    $content = stream_get_contents($handle);
    flock($handle, LOCK_UN);
}
fclose($handle);

$content = trim((string) $content);
if ($content === '') {
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $records = [];
    $lines = preg_split('/\R+/', $content, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        $item = json_decode($line, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($item)) {
            $records[] = $item;
        }
    }

    if (empty($records)) {
        echo json_encode([
            'success' => false,
            'message' => 'El formato del log no es válido JSON.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $decoded = $records;
}

if (isset($decoded['records']) && is_array($decoded['records'])) {
    $decoded = $decoded['records'];
}

if (!is_array($decoded)) {
    echo json_encode([
        'success' => false,
        'message' => 'El contenido del log no es una lista de registros.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$cleanRecords = [];
foreach ($decoded as $record) {
    if (is_array($record)) {
        $state = $record['state_fp'] ?? 'unplanned';
        if (!in_array($state, $allowedStates, true)) {
            $state = 'unplanned';
        }
        $record['state_fp'] = $state;
        $cleanRecords[] = $record;
    }
}

$response['records'] = $cleanRecords;

echo json_encode($response, JSON_UNESCAPED_UNICODE);

