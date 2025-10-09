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
    $asunto = sanitizeInput($_POST['asunto'] ?? '');
    $mensaje = sanitizeInput($_POST['mensaje'] ?? '');
    
    // Validaciones
    if (empty($nombre)) {
        throw new Exception('El nombre es obligatorio');
    }
    
    if (empty($email) || !validateEmail($email)) {
        throw new Exception('El email es obligatorio y debe ser válido');
    }
    
    if (empty($asunto)) {
        throw new Exception('Debe seleccionar un asunto');
    }
    
    if (empty($mensaje)) {
        throw new Exception('El mensaje es obligatorio');
    }
    
    // Conectar a la base de datos
    $pdo = getDBConnection();
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Insertar en la base de datos
    $sql = "INSERT INTO contactos (nombre, email, telefono, asunto, mensaje, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre,
        $email,
        $telefono,
        $asunto,
        $mensaje,
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $contacto_id = $pdo->lastInsertId();
    
    // Preparar datos para el email
    $email_data = [
        'id' => $contacto_id,
        'nombre' => $nombre,
        'email' => $email,
        'telefono' => $telefono,
        'asunto' => $asunto,
        'mensaje' => $mensaje
    ];
    
    // Crear template de email
    $asunto_email = "Nuevo Mensaje de Contacto - CFA & Asociados";
    $mensaje_email = createContactoEmailTemplate($email_data);
    
    // Enviar email a ambos destinatarios
    $recipients = [EMAIL_TO_PRIMARY, EMAIL_TO_SECONDARY];
    $email_results = sendEmailToMultiple($recipients, $asunto_email, $mensaje_email);
    
    $email_enviado = true;
    foreach ($email_results as $result) {
        if (!$result) {
            $email_enviado = false;
            break;
        }
    }
    
    if ($email_enviado) {
        $response['success'] = true;
        $response['message'] = '¡Mensaje enviado correctamente! Te responderemos en breve.';
    } else {
        $response['success'] = true;
        $response['message'] = 'Tu mensaje ha sido registrado correctamente. Te contactaremos pronto.';
    }
    
} catch (Exception $e) {
    error_log("Error en process_contacto.php: " . $e->getMessage());
    $response['message'] = 'Hubo un error al enviar tu mensaje. Por favor, intentá nuevamente.';
}

// Enviar respuesta JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
