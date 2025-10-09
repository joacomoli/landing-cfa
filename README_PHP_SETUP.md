# CFA Seguros - Configuración PHP

## Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx)
- Extensión PHP PDO MySQL

## Instalación

### 1. Configuración de la Base de Datos

1. Crear la base de datos MySQL:
```sql
mysql -u cfasegur_usuario -p < database_setup.sql
```

2. O ejecutar manualmente en MySQL:
```sql
CREATE DATABASE cfasegur_contactos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cfasegur_contactos;
-- Ejecutar las consultas del archivo database_setup.sql
```

### 2. Configuración de PHP

1. Editar el archivo `config/database.php` (ya configurado):
```php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cfasegur_contactos');
define('DB_USER', 'cfasegur_usuario');
define('DB_PASS', 'RudolfSteiner98*');
```

2. Configurar el email (opcional, para usar SMTP):
```php
// En config/database.php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'info@cfaseguros.com.ar');
define('SMTP_PASSWORD', 'tu_password_gmail');
```

### 3. Configuración del Servidor

#### Apache (.htaccess)
Crear archivo `.htaccess` en la raíz:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([^\.]+)$ $1.php [NC,L]

# Seguridad
<Files "config/*">
    Order allow,deny
    Deny from all
</Files>
```

#### Nginx
Agregar al archivo de configuración:
```nginx
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}

location ~ /config/ {
    deny all;
}
```

### 4. Permisos de Archivos

```bash
chmod 644 *.php
chmod 644 config/*.php
chmod 644 *.html
chmod 644 *.css
chmod 644 *.js
chmod 755 .
```

## Funcionalidades

### Formularios

1. **Formulario de Cotización** (`index.html`)
   - Guarda datos en tabla `cotizaciones`
   - Envía email a `info@cfaseguros.com.ar` y `joaco.molinos.jm@gmail.com`
   - Validación completa de datos

2. **Formulario de Contacto** (`contacto.html`)
   - Guarda datos en tabla `contactos`
   - Envía email a ambos destinatarios
   - Validación de campos obligatorios

### Base de Datos

#### Tabla `cotizaciones`
- id (AUTO_INCREMENT)
- nombre, email, telefono, ciudad
- tipo_seguro, marca_modelo, direccion_hogar
- mensaje, fecha_creacion, ip_address, user_agent

#### Tabla `contactos`
- id (AUTO_INCREMENT)
- nombre, email, telefono, asunto
- mensaje, fecha_creacion, ip_address, user_agent

### Emails

- Formato HTML con estilos CSS
- Headers personalizados
- Reply-To configurado con el email del usuario
- Envío a múltiples destinatarios

## Seguridad

- Sanitización de datos de entrada
- Validación de emails
- Protección contra inyección SQL (prepared statements)
- Logs de errores
- Validación de método HTTP (solo POST)

## Testing

1. Probar formulario de cotización:
   - Completar todos los campos
   - Verificar que se guarde en BD
   - Confirmar recepción de emails

2. Probar formulario de contacto:
   - Enviar mensaje completo
   - Verificar almacenamiento
   - Confirmar envío de emails

## Troubleshooting

### Error de conexión a BD
- Verificar credenciales en `config/database.php`
- Confirmar que MySQL esté ejecutándose
- Verificar que la base de datos existe

### Emails no se envían
- Verificar configuración SMTP
- Revisar logs de PHP
- Confirmar que la función `mail()` esté habilitada

### Formularios no funcionan
- Verificar que los archivos PHP estén en el servidor
- Confirmar permisos de archivos
- Revisar logs de errores del servidor

## Archivos Principales

- `index.html` - Página principal con formulario de cotización
- `contacto.html` - Página de contacto
- `process_cotizacion.php` - Procesador del formulario de cotización
- `process_contacto.php` - Procesador del formulario de contacto
- `config/database.php` - Configuración de BD y funciones
- `database_setup.sql` - Script de creación de BD
- `script.js` - JavaScript actualizado para PHP
- `styles.css` - Estilos CSS

## Soporte

Para problemas técnicos, revisar:
1. Logs de PHP (`/var/log/php/error.log`)
2. Logs del servidor web
3. Logs de MySQL
4. Console del navegador (F12)
