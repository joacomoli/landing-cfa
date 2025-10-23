<?php
// Procesador de contacto - CFA & Asociados
error_reporting(0);
ini_set('display_errors', 0);

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Anti-spam: múltiples verificaciones
$honeypot = isset($_POST['website']) ? trim($_POST['website']) : '';
$fpTs = isset($_POST['fp_timestamp']) ? (int)$_POST['fp_timestamp'] : 0; // ms desde JS
$tooFast = ($fpTs > 0) ? (time() - (int)floor($fpTs / 1000)) < 3 : false; // <3s desde carga

// Verificación adicional: campos vacíos sospechosos
$suspiciousFields = ['url', 'website', 'link', 'href'];
$hasSuspiciousContent = false;
foreach ($suspiciousFields as $field) {
    if (isset($_POST[$field]) && !empty(trim($_POST[$field]))) {
        $hasSuspiciousContent = true;
        break;
    }
}

// Verificación de IP repetida (máximo 3 envíos por hora)
$clientIP = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
$recentSubmissions = 0;
try {
    $pdo = new PDO('mysql:host=localhost;dbname=cfasegur_contactos;charset=utf8mb4', 'cfasegur_usuario', 'RudolfSteiner98*');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contactos WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
    $stmt->execute([$clientIP]);
    $recentSubmissions = $stmt->fetchColumn();
} catch (Exception $e) {
    // Si hay error en BD, continuar pero con precaución
}

// Obtener datos básicos para verificación anti-spam
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$mensaje = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';

// Verificación de contenido sospechoso en campos de texto
$suspiciousKeywords = ['viagra', 'casino', 'loan', 'credit', 'bitcoin', 'crypto', 'investment', 'profit'];
$textContent = strtolower($nombre . ' ' . $email . ' ' . $mensaje);
$hasSuspiciousKeywords = false;
foreach ($suspiciousKeywords as $keyword) {
    if (strpos($textContent, $keyword) !== false) {
        $hasSuspiciousKeywords = true;
        break;
    }
}

if ($honeypot !== '' || $tooFast || $hasSuspiciousContent || $recentSubmissions >= 3 || $hasSuspiciousKeywords) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado correctamente.']);
    exit;
}

// Verificar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['asunto']) || empty($_POST['mensaje'])) {
    http_response_code(400);
    exit;
}

// Sanitizar datos (ya tenemos algunos valores)
$nombre = htmlspecialchars($nombre);
$email = trim($email);
$telefono = !empty($_POST['telefono']) ? trim(htmlspecialchars($_POST['telefono'])) : '';
$asunto = trim(htmlspecialchars($_POST['asunto']));
$mensaje = htmlspecialchars($mensaje);

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
    
    // Preparar email
    $asunto_nombres = [
        'cotizacion' => 'Solicitar Cotización',
        'siniestro' => 'Reportar Siniestro',
        'consulta' => 'Consulta General',
        'reclamo' => 'Reclamo',
        'otro' => 'Otro'
    ];
    
    $asunto_nombre = $asunto_nombres[$asunto] ?? $asunto;
    
    // Forzar UTF-8 en cabeceras y contenido
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
        mb_language('uni');
    }
    
    // Configurar headers UTF-8
    header('Content-Type: application/json; charset=UTF-8');
    
    $subject = 'Nuevo Mensaje de Contacto - CFA & Asociados';
    $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8', 'B') : '=?UTF-8?B?'.base64_encode($subject).'?=';
    $emailBody = "Nuevo mensaje de contacto recibido:\r\n\r\n".
                 "Nombre: $nombre\r\n".
                 "Email: $email\r\n".
                 "Teléfono: $telefono\r\n".
                 "Asunto: $asunto_nombre\r\n\r\n".
                 "Mensaje:\r\n$mensaje\r\n\r\n".
                 "Fecha: " . date('d/m/Y H:i:s') . "\r\n".
                 "IP: " . ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1') . "\r\n".
                 "ID: $contactoId\r\n";
    
    $headers = "From: contacto@cfaseguros.com.ar\r\n";
    $headers .= "Reply-To: contacto@cfaseguros.com.ar\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    $headers .= "X-Priority: 3\r\n";
    
    // Enviar emails a todos los destinatarios
    $emails = [
        'info@cfaseguros.com.ar',
        'joaco.molinos.jm@gmail.com'
    ];
    
    foreach ($emails as $emailDestino) {
        @mail($emailDestino, $encodedSubject, $emailBody, $headers);
    }
    
    // Respuesta JSON
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success' => true,
        'message' => '¡Mensaje enviado correctamente! Te responderemos en breve.'
    ]);
    
} catch (Exception $e) {
    header('Content-Type: application/json; charset=UTF-8');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Ha ocurrido un error. Por favor, intenta nuevamente.'
    ]);
}
?>
