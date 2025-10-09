-- Crear tablas para CFA Seguros
-- Base de datos: cfasegur_contactos

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
