<?php
/**
 * API para obtener datos del cliente desde PostgreSQL
 * Busca cliente por PON/LOG y DNI
 */

// Cargar configuración
$config = require_once '../include/config.php';

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['pon_log'])) {
        throw new Exception('Datos de entrada inválidos: se requiere pon_log');
    }
    
    $ponLog = trim($input['pon_log']);
    
    // Conectar a PostgreSQL
    $dsn = "pgsql:host={$config['database']['host']};port={$config['database']['port']};dbname={$config['database']['dbname']}";
    $pdo = new PDO($dsn, $config['database']['user'], $config['database']['pass'], $config['database']['options']);
    
    // Buscar cliente por PON/LOG (que ya contiene HOST/PON/LOG)
    $sql = "SELECT 
                id,
                tipo_documento,
                dni_ruc,
                cliente,
                cod_suscri,
                telefono,
                movil,
                nombre_direccion,
                distrito,
                provincia,
                suscripcion,
                pon_log,
                troncal,
                odf,
                nro_hilo,
                zona_noc,
                name_mk,
                vlan,
                router,
                vlan_usuario,
                plan_vigente,
                cat_cliente,
                caja_ins,
                puerto_caja,
                mac_noc,
                tv_vigente,
                mac_tv,
                mac_tv_2,
                mac_tv_3,
                mac_tv_4,
                mac_tv_actual,
                subs_order,
                subs_count,
                correo_electronico,
                ubicacion,
                estado_sus,
                fecha_carga,
                fecha_actualizacion,
                archivo_origen
            FROM clientes 
            WHERE pon_log = :pon_log 
            LIMIT 1";
    
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':pon_log', $ponLog, PDO::PARAM_STR);
    $stmt->execute();
    
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        // Limpiar datos sensibles
        unset($cliente['id']);
        unset($cliente['archivo_origen']);
        
        echo json_encode([
            'success' => true,
            'cliente' => $cliente
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Cliente no encontrado',
            'cliente' => null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage(),
        'cliente' => null
    ]);
}
?>
