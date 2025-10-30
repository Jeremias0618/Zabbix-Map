<?php
header('Content-Type: application/json');

$config = require_once __DIR__ . '/../include/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['pon_prefix'])) {
        throw new Exception('Datos de entrada inválidos: se requiere pon_prefix');
    }

    $ponPrefix = trim($input['pon_prefix']);

    $db = $config['database'];
    $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['dbname']}";
    $pdo = new PDO($dsn, $db['user'], $db['pass'], $db['options']);

    // Buscar todos los clientes cuyo pon_log empieza con el prefijo dado seguido de '/'
    $sql = "SELECT 
                dni_ruc,
                cliente,
                nombre_direccion,
                distrito,
                provincia,
                pon_log,
                troncal,
                odf,
                nro_hilo,
                ubicacion
            FROM clientes
            WHERE pon_log LIKE :prefixLike OR pon_log = :prefixExact
            LIMIT 1000"; // límite de seguridad

    $stmt = $pdo->prepare($sql);
    $prefixLike = $ponPrefix . '/%';
    $stmt->bindParam(':prefixLike', $prefixLike, PDO::PARAM_STR);
    $stmt->bindParam(':prefixExact', $ponPrefix, PDO::PARAM_STR);
    $stmt->execute();

    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'clientes' => $clientes]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}