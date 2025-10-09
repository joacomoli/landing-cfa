-- Crear base de datos para CFA Seguros
CREATE DATABASE IF NOT EXISTS cfasegur_contactos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE cfasegur_contactos;

-- Tabla para cotizaciones
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
    user_agent TEXT,
    INDEX idx_email (email),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_tipo_seguro (tipo_seguro)
);

-- Tabla para contactos
CREATE TABLE IF NOT EXISTS contactos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    asunto VARCHAR(100),
    mensaje TEXT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_email (email),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_asunto (asunto)
);

-- Insertar datos de ejemplo (opcional)
-- INSERT INTO cotizaciones (nombre, email, telefono, ciudad, tipo_seguro, mensaje) 
-- VALUES ('Juan Pérez', 'juan@email.com', '+54 9 11 1234-5678', 'Buenos Aires', 'automotor', 'Necesito cotización para mi auto 2020');

-- INSERT INTO contactos (nombre, email, telefono, asunto, mensaje) 
-- VALUES ('María García', 'maria@email.com', '+54 9 11 9876-5432', 'consulta', 'Quiero información sobre seguros de hogar');
