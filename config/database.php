<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cfasegur_contactos');
define('DB_USER', 'cfasegur_usuario');
define('DB_PASS', 'RudolfSteiner98*');
define('DB_CHARSET', 'utf8mb4');

// Configuración de email
define('SMTP_HOST', 'mail.cfaseguros.com.ar');
define('SMTP_PORT', 465);
define('SMTP_USERNAME', 'info@cfaseguros.com.ar');
define('SMTP_PASSWORD', '*azul8665**');
define('SMTP_FROM_EMAIL', 'info@cfaseguros.com.ar');
define('SMTP_FROM_NAME', 'CFA & Asociados');

// Emails de destino
define('EMAIL_TO_PRIMARY', 'info@cfaseguros.com.ar');
define('EMAIL_TO_SECONDARY', 'joaco.molinos.jm@gmail.com');

// Función para conectar a la base de datos
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return null;
    }
}

// Función para crear las tablas si no existen
function createTables() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // Tabla para cotizaciones
        $sql_cotizaciones = "
        CREATE TABLE IF NOT EXISTS cotizaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefono VARCHAR(50),
            ciudad VARCHAR(100),
            tipo_seguro VARCHAR(100),
            marca_modelo VARCHAR(255),
            direccion_hogar VARCHAR(255),
            mensaje TEXT,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT
        )";
        
        // Tabla para contactos
        $sql_contactos = "
        CREATE TABLE IF NOT EXISTS contactos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            telefono VARCHAR(50),
            asunto VARCHAR(100),
            mensaje TEXT NOT NULL,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            ip_address VARCHAR(45),
            user_agent TEXT
        )";
        
        $pdo->exec($sql_cotizaciones);
        $pdo->exec($sql_contactos);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error creando tablas: " . $e->getMessage());
        return false;
    }
}

// Función para obtener la IP del cliente
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Función para validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Función para sanitizar datos
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
