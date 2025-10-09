<?php
require_once 'database.php';

// Función para enviar email usando SMTP
function sendEmailSMTP($to, $subject, $message, $from_email = null, $from_name = null) {
    // Usar configuración por defecto si no se especifica
    $from_email = $from_email ?: SMTP_FROM_EMAIL;
    $from_name = $from_name ?: SMTP_FROM_NAME;
    
    // Headers del email
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
        'Reply-To: ' . $from_email,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // Intentar enviar usando la función mail() de PHP
    // Nota: Para usar SMTP real, necesitarías una librería como PHPMailer
    $result = mail($to, $subject, $message, implode("\r\n", $headers));
    
    return $result;
}

// Función para enviar email a múltiples destinatarios
function sendEmailToMultiple($recipients, $subject, $message, $from_email = null, $from_name = null) {
    $results = [];
    
    foreach ($recipients as $email) {
        $results[$email] = sendEmailSMTP($email, $subject, $message, $from_email, $from_name);
    }
    
    return $results;
}

// Función para crear template de email de cotización
function createCotizacionEmailTemplate($data) {
    $tipo_seguro_nombres = [
        'automotor' => 'Seguro Automotor',
        'hogar' => 'Seguro de Hogar',
        'accidentes' => 'Accidentes Personales',
        'empresarial' => 'Seguros Empresariales',
        'vida' => 'Seguro de Vida',
        'otro' => 'Otro'
    ];
    
    $tipo_seguro_nombre = $tipo_seguro_nombres[$data['tipo_seguro']] ?? $data['tipo_seguro'];
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #1e3a8a; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
            .highlight { background: #e3f2fd; padding: 10px; border-left: 4px solid #1e3a8a; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nueva Solicitud de Cotización</h2>
                <p>CFA & Asociados - Broker de Seguros</p>
            </div>
            
            <div class='content'>
                <div class='highlight'>
                    <strong>ID de Cotización:</strong> #{$data['id']}<br>
                    <strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "
                </div>
                
                <div class='field'>
                    <span class='label'>Nombre:</span> {$data['nombre']}
                </div>
                
                <div class='field'>
                    <span class='label'>Email:</span> {$data['email']}
                </div>
                
                <div class='field'>
                    <span class='label'>Teléfono:</span> {$data['telefono']}
                </div>
                
                <div class='field'>
                    <span class='label'>Ciudad:</span> {$data['ciudad']}
                </div>
                
                <div class='field'>
                    <span class='label'>Tipo de Seguro:</span> {$tipo_seguro_nombre}
                </div>";
    
    if (!empty($data['marca_modelo'])) {
        $html .= "
                <div class='field'>
                    <span class='label'>Marca y Modelo:</span> {$data['marca_modelo']}
                </div>";
    }
    
    if (!empty($data['direccion_hogar'])) {
        $html .= "
                <div class='field'>
                    <span class='label'>Dirección del Hogar:</span> {$data['direccion_hogar']}
                </div>";
    }
    
    if (!empty($data['mensaje'])) {
        $html .= "
                <div class='field'>
                    <span class='label'>Mensaje:</span><br>
                    " . nl2br($data['mensaje']) . "
                </div>";
    }
    
    $html .= "
            </div>
            
            <div class='footer'>
                <p>Este email fue enviado desde el formulario de cotización de CFA & Asociados</p>
                <p>Responder a: {$data['email']}</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}

// Función para crear template de email de contacto
function createContactoEmailTemplate($data) {
    $asunto_nombres = [
        'cotizacion' => 'Solicitar Cotización',
        'siniestro' => 'Reportar Siniestro',
        'consulta' => 'Consulta General',
        'reclamo' => 'Reclamo',
        'otro' => 'Otro'
    ];
    
    $asunto_nombre = $asunto_nombres[$data['asunto']] ?? $data['asunto'];
    
    $html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 0 auto; background: #fff; }
            .header { background: #1e3a8a; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #1e3a8a; }
            .footer { background: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; }
            .highlight { background: #e3f2fd; padding: 10px; border-left: 4px solid #1e3a8a; margin: 15px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Nuevo Mensaje de Contacto</h2>
                <p>CFA & Asociados - Broker de Seguros</p>
            </div>
            
            <div class='content'>
                <div class='highlight'>
                    <strong>ID de Contacto:</strong> #{$data['id']}<br>
                    <strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "
                </div>
                
                <div class='field'>
                    <span class='label'>Nombre:</span> {$data['nombre']}
                </div>
                
                <div class='field'>
                    <span class='label'>Email:</span> {$data['email']}
                </div>
                
                <div class='field'>
                    <span class='label'>Teléfono:</span> {$data['telefono']}
                </div>
                
                <div class='field'>
                    <span class='label'>Asunto:</span> {$asunto_nombre}
                </div>
                
                <div class='field'>
                    <span class='label'>Mensaje:</span><br>
                    " . nl2br($data['mensaje']) . "
                </div>
            </div>
            
            <div class='footer'>
                <p>Este email fue enviado desde el formulario de contacto de CFA & Asociados</p>
                <p>Responder a: {$data['email']}</p>
            </div>
        </div>
    </body>
    </html>";
    
    return $html;
}
?>
