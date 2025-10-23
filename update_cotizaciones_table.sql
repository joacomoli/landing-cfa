-- Script para actualizar la tabla cotizaciones con los nuevos campos de vehículo
-- Base de datos: cfasegur_contactos

-- Este script agrega las columnas nuevas si no existen
-- Ejecutalo solo si tu tabla ya existe y necesitas agregar los campos

-- Agregar campos de vehículo
ALTER TABLE cotizaciones 
ADD COLUMN IF NOT EXISTS marca_vehiculo VARCHAR(100) AFTER tipo_seguro,
ADD COLUMN IF NOT EXISTS modelo_vehiculo VARCHAR(150) AFTER marca_vehiculo,
ADD COLUMN IF NOT EXISTS anio_vehiculo VARCHAR(4) AFTER modelo_vehiculo,
ADD COLUMN IF NOT EXISTS tipo_poliza VARCHAR(50) AFTER anio_vehiculo;

-- Si tu versión de MySQL no soporta IF NOT EXISTS, usa este script alternativo:
/*
-- Verificar primero si las columnas existen antes de ejecutar:
ALTER TABLE cotizaciones ADD COLUMN marca_vehiculo VARCHAR(100) AFTER tipo_seguro;
ALTER TABLE cotizaciones ADD COLUMN modelo_vehiculo VARCHAR(150) AFTER marca_vehiculo;
ALTER TABLE cotizaciones ADD COLUMN anio_vehiculo VARCHAR(4) AFTER modelo_vehiculo;
ALTER TABLE cotizaciones ADD COLUMN tipo_poliza VARCHAR(50) AFTER anio_vehiculo;
*/

-- Eliminar la columna antigua marca_modelo si existe (ya no se usa)
-- ALTER TABLE cotizaciones DROP COLUMN IF EXISTS marca_modelo;

