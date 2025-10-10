<?php
// Procesador de cotización simple - CFA & Asociados
error_reporting(0);
ini_set('display_errors', 0);

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Verificar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['tipo-seguro'])) {
    http_response_code(400);
    exit;
}

// Sanitizar datos
$nombre = trim(htmlspecialchars($_POST['nombre']));
$email = trim($_POST['email']);
$telefono = !empty($_POST['telefono']) ? trim(htmlspecialchars($_POST['telefono'])) : '';
$ciudad = !empty($_POST['ciudad']) ? trim(htmlspecialchars($_POST['ciudad'])) : '';
$tipo_seguro = trim(htmlspecialchars($_POST['tipo-seguro']));
$marca_modelo = !empty($_POST['marca-modelo']) ? trim(htmlspecialchars($_POST['marca-modelo'])) : '';
$direccion_hogar = !empty($_POST['direccion-hogar']) ? trim(htmlspecialchars($_POST['direccion-hogar'])) : '';
$mensaje = !empty($_POST['mensaje']) ? trim(htmlspecialchars($_POST['mensaje'])) : '';

// Validar email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit;
}

// Conectar a BD
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cfasegur_contactos;charset=utf8mb4', 'cfasegur_usuario', 'RudolfSteiner98*');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Insertar en BD
    $sql = "INSERT INTO cotizaciones (nombre, email, telefono, ciudad, tipo_seguro, marca_modelo, direccion_hogar, mensaje, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre, $email, $telefono, $ciudad, $tipo_seguro, $marca_modelo, $direccion_hogar, $mensaje,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    $cotizacionId = $pdo->lastInsertId();
    
    // Preparar email
    $tipo_seguro_nombres = [
        'automotor' => 'Seguro Automotor',
        'hogar' => 'Seguro de Hogar',
        'accidentes' => 'Accidentes Personales',
        'empresarial' => 'Seguros Empresariales',
        'vida' => 'Seguro de Vida',
        'otro' => 'Otro'
    ];
    
    $tipo_seguro_nombre = $tipo_seguro_nombres[$tipo_seguro] ?? $tipo_seguro;
    
    $subject = "Nueva Solicitud de Cotización - CFA & Asociados";
    $emailBody = "
Nueva solicitud de cotización recibida:

Nombre: $nombre
Email: $email
Teléfono: $telefono
Ciudad: $ciudad
Tipo de Seguro: $tipo_seguro_nombre";

    if (!empty($marca_modelo)) {
        $emailBody .= "
Marca y Modelo: $marca_modelo";
    }
    
    if (!empty($direccion_hogar)) {
        $emailBody .= "
Dirección del Hogar: $direccion_hogar";
    }
    
    if (!empty($mensaje)) {
        $emailBody .= "

Mensaje:
$mensaje";
    }
    
    $emailBody .= "

Fecha: " . date('d/m/Y H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . "
ID: $cotizacionId
";
    
    $headers = "From: contacto@cfaseguros.com.ar\r\n";
    $headers .= "Reply-To: contacto@cfaseguros.com.ar\r\n";
    
    // Enviar emails a todos los destinatarios
    $emails = [
        'info@cfaseguros.com.ar',
        'joaco.molinos.jm@gmail.com'
    ];
    
    foreach ($emails as $emailDestino) {
        mail($emailDestino, $subject, $emailBody, $headers);
    }
    
    // Respuesta JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => '¡Gracias por confiar en nosotros! 😊 En breve recibirás en tu email las opciones de seguro cotizadas. Ante cualquier duda, estamos para ayudarte.'
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ha ocurrido un error. Por favor, intenta nuevamente.'
    ]);
}
?>