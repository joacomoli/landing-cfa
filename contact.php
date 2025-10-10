<?php
// Procesador de contacto simple - CFA & Asociados
error_reporting(0);
ini_set('display_errors', 0);

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Verificar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['mensaje'])) {
    http_response_code(400);
    exit;
}

// Sanitizar datos
$nombre = trim(htmlspecialchars($_POST['nombre']));
$email = trim($_POST['email']);
$telefono = !empty($_POST['telefono']) ? trim(htmlspecialchars($_POST['telefono'])) : '';
$asunto = !empty($_POST['asunto']) ? trim(htmlspecialchars($_POST['asunto'])) : '';
$mensaje = trim(htmlspecialchars($_POST['mensaje']));

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
    $sql = "INSERT INTO contactos (nombre, email, telefono, asunto, mensaje, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre, $email, $telefono, $asunto, $mensaje,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    $contactoId = $pdo->lastInsertId();
    
    // Enviar email
    $subject = "Nuevo mensaje - CFA & Asociados";
    $emailBody = "
Nuevo mensaje recibido:

Nombre: $nombre
Email: $email
Teléfono: $telefono
Asunto: $asunto

Mensaje:
$mensaje

Fecha: " . date('d/m/Y H:i:s') . "
IP: " . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . "
ID: $contactoId
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
        'message' => '¡Mensaje enviado correctamente! Te responderemos en breve.'
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
