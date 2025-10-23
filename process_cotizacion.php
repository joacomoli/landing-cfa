<?php
// Procesador de cotización simple - CFA & Asociados
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
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cotizaciones WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
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
    echo json_encode(['success' => true, 'message' => 'Gracias, hemos recibido tu solicitud.']);
    exit;
}

// Verificar datos requeridos
if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['tipo-seguro'])) {
    http_response_code(400);
    exit;
}

// Sanitizar datos (ya tenemos algunos valores)
$nombre = htmlspecialchars($nombre);
$email = trim($email);
$telefono = !empty($_POST['telefono']) ? trim(htmlspecialchars($_POST['telefono'])) : '';
$ciudad = !empty($_POST['ciudad']) ? trim(htmlspecialchars($_POST['ciudad'])) : '';
$tipo_seguro = trim(htmlspecialchars($_POST['tipo-seguro']));

// Campos específicos de vehículo
$marca_vehiculo = !empty($_POST['marca-vehiculo']) ? trim(htmlspecialchars($_POST['marca-vehiculo'])) : '';
$modelo_vehiculo = !empty($_POST['modelo-vehiculo']) ? trim(htmlspecialchars($_POST['modelo-vehiculo'])) : '';
$anio_vehiculo = !empty($_POST['anio-vehiculo']) ? trim(htmlspecialchars($_POST['anio-vehiculo'])) : '';
$tipo_poliza = !empty($_POST['tipo-poliza']) ? trim(htmlspecialchars($_POST['tipo-poliza'])) : '';

// Campos de otros tipos de seguro
$direccion_hogar = !empty($_POST['direccion-hogar']) ? trim(htmlspecialchars($_POST['direccion-hogar'])) : '';
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
    $sql = "INSERT INTO cotizaciones (nombre, email, telefono, ciudad, tipo_seguro, marca_vehiculo, modelo_vehiculo, anio_vehiculo, tipo_poliza, direccion_hogar, mensaje, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nombre, $email, $telefono, $ciudad, $tipo_seguro, 
        $marca_vehiculo, $modelo_vehiculo, $anio_vehiculo, $tipo_poliza,
        $direccion_hogar, $mensaje,
        $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);
    
    $cotizacionId = $pdo->lastInsertId();
    
    // Preparar email
    $tipo_seguro_nombres = [
        'art' => 'ART',
        'incendio' => 'Incendio',
        'hogar' => 'Hogar',
        'objetos-personales' => 'Objetos personales',
        'integral-comercio' => 'Integrales de comercio',
        'automotor' => 'Auto',
        'moto' => 'Moto',
        'caucion' => 'Caución',
        'accidentes' => 'Accidentes personales',
        'vida' => 'Vida',
        'embarcaciones' => 'Embarcaciones',
        'turismo' => 'Turismo'
    ];
    
    $tipo_seguro_nombre = $tipo_seguro_nombres[$tipo_seguro] ?? $tipo_seguro;
    
    // Nombres de tipos de póliza
    $tipo_poliza_nombres = [
        'rc' => 'Responsabilidad Civil',
        'rc-plus' => 'RC + Robo Total/Parcial + Incendio Total/Parcial + Daños Totales por Accidente',
        'rc-premium' => 'RC + Robo + Incendio + Daños Totales + Vidrios/Parabrisas + Granizo',
        'todo-riesgo' => 'Todo Riesgo con Franquicia',
        'no-se' => 'No lo sé aún'
    ];
    
    // Forzar UTF-8 en cabeceras y contenido
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
        mb_language('uni');
    }
    
    // Configurar headers UTF-8
    header('Content-Type: application/json; charset=UTF-8');
    
    $subject = 'Nueva Solicitud de Cotización - CFA & Asociados';
    $encodedSubject = function_exists('mb_encode_mimeheader') ? mb_encode_mimeheader($subject, 'UTF-8', 'B') : '=?UTF-8?B?'.base64_encode($subject).'?=';
    
    $emailBody = "Nueva solicitud de cotización recibida:\r\n\r\n".
                 "Nombre: $nombre\r\n".
                 "Email: $email\r\n".
                 "Teléfono: $telefono\r\n".
                 "Ciudad: $ciudad\r\n".
                 "Tipo de Seguro: $tipo_seguro_nombre";

    // Información específica de vehículo
    if (!empty($marca_vehiculo) || !empty($modelo_vehiculo) || !empty($anio_vehiculo)) {
        $emailBody .= "\r\n\r\nDATOS DEL VEHÍCULO:";
        if (!empty($marca_vehiculo)) {
            $emailBody .= "\r\nMarca: $marca_vehiculo";
        }
        if (!empty($modelo_vehiculo)) {
            $emailBody .= "\r\nModelo: $modelo_vehiculo";
        }
        if (!empty($anio_vehiculo)) {
            $emailBody .= "\r\nAño: $anio_vehiculo";
        }
        if (!empty($tipo_poliza)) {
            $tipo_poliza_nombre = $tipo_poliza_nombres[$tipo_poliza] ?? $tipo_poliza;
            $emailBody .= "\r\nTipo de Póliza: $tipo_poliza_nombre";
        }
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
        'message' => '¡Gracias por confiar en nosotros! 😊 En breve recibirás en tu email las opciones de seguro cotizadas. Ante cualquier duda, estamos para ayudarte.'
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
