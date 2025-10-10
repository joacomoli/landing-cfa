<?php
// Test simple de envío de email - CFA & Asociados
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Email - CFA & Asociados</h2>";

// Test 1: Verificar función mail()
echo "<h3>1. Verificando función mail():</h3>";
if (function_exists('mail')) {
    echo "✅ Función mail() está disponible<br>";
} else {
    echo "❌ Función mail() NO está disponible<br>";
}

// Test 2: Verificar configuración SMTP
echo "<h3>2. Configuración PHP:</h3>";
echo "SMTP: " . ini_get('SMTP') . "<br>";
echo "smtp_port: " . ini_get('smtp_port') . "<br>";
echo "sendmail_from: " . ini_get('sendmail_from') . "<br>";

// Test 3: Intentar enviar email de prueba
echo "<h3>3. Enviando email de prueba:</h3>";

$to = "joaco.molinos.jm@gmail.com";
$subject = "Test Email - CFA & Asociados";
$message = "Este es un email de prueba desde CFA & Asociados.\n\nFecha: " . date('d/m/Y H:i:s');
$headers = "From: contacto@cfaseguros.com.ar\r\n";
$headers .= "Reply-To: contacto@cfaseguros.com.ar\r\n";

$result = mail($to, $subject, $message, $headers);

if ($result) {
    echo "✅ Email enviado correctamente a: $to<br>";
} else {
    echo "❌ Error al enviar email<br>";
}

// Test 4: Verificar logs de error
echo "<h3>4. Últimos errores PHP:</h3>";
$error_log = ini_get('error_log');
echo "Error log: $error_log<br>";

// Test 5: Información del servidor
echo "<h3>5. Información del servidor:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Server: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
echo "OS: " . php_uname() . "<br>";

echo "<h3>6. Test completado</h3>";
echo "<p>Si el email no llega, puede ser un problema de configuración del servidor.</p>";
?>
