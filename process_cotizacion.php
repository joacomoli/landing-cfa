<?php
require_once 'config/database.php';
require_once 'config/email.php';

// Verificar que la petición sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Inicializar respuesta
$response = ['success' => false, 'message' => ''];

try {
    // Crear tablas si no existen
    createTables();
    
    // Obtener y validar datos
    $nombre = sanitizeInput($_POST['nombre'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $telefono = sanitizeInput($_POST['telefono'] ?? '');
    $ciudad = sanitizeInput($_POST['ciudad'] ?? '');
    $tipo_seguro = sanitizeInput($_POST['tipo-seguro'] ?? '');
    $marca_modelo = sanitizeInput($_POST['marca-modelo'] ?? '');
    $direccion_hogar = sanitizeInput($_POST['direccion-hogar'] ?? '');
    $mensaje = sanitizeInput($_POST['mensaje'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if (empty($email) || !validateEmail($email)) {
        throw new Exception('El email es obligatorio y debe ser válido');
    }
    
    if (empty($tipo_seguro)) {
        throw new Exception('Debe seleccionar un tipo de seguro');
    }
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Insertar en la base de datos
    $sql = "INSERT INTO cotizaciones (nombre, email, telefono, ciudad, tipo_seguro, marca_modelo, direccion_hogar, mensaje, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre,
        $email,
        $telefono,
        $ciudad,
        $tipo_seguro,
        $marca_modelo,
        $direccion_hogar,
        $mensaje,
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $cotizacion_id = $pdo->lastInsertId();
    
    // Preparar datos para el email
    $email_data = [
        'id' => $cotizacion_id,
        'nombre' => $nombre,
        'email' => $email,
        'telefono' => $telefono,
        'ciudad' => $ciudad,
        'tipo_seguro' => $tipo_seguro,
        'marca_modelo' => $marca_modelo,
        'direccion_hogar' => $direccion_hogar,
        'mensaje' => $mensaje
    ];
    
    // Crear template de email
    $asunto = "Nueva Solicitud de Cotización - CFA & Asociados";
    $mensaje_email = createCotizacionEmailTemplate($email_data);
    
    // Enviar email a ambos destinatarios
    $recipients = [EMAIL_TO_PRIMARY, EMAIL_TO_SECONDARY];
    $email_results = sendEmailToMultiple($recipients, $asunto, $mensaje_email);
    
    $email_enviado = true;
    foreach ($email_results as $result) {
        if (!$result) {
            $email_enviado = false;
            break;
        }
    }
    
    if ($email_enviado) {
        $response['success'] = true;
        $response['message'] = '¡Gracias por confiar en nosotros! 😊 En breve recibirás en tu email las opciones de seguro cotizadas. Ante cualquier duda, estamos para ayudarte.';
    } else {
        $response['success'] = true;
        $response['message'] = 'Tu solicitud ha sido registrada correctamente. Te contactaremos pronto.';
    }
    
} catch (Exception $e) {
    error_log("Error en process_cotizacion.php: " . $e->getMessage());
    $response['message'] = 'Hubo un error al procesar tu solicitud. Por favor, intentá nuevamente.';
}

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
