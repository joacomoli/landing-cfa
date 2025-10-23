# Actualización del Formulario de Cotización - CFA & Asociados

## 📋 Resumen de Cambios

Se ha actualizado el formulario de cotización para capturar información más detallada cuando el usuario selecciona **Seguro Automotor**.

---

## ✨ Nuevas Funcionalidades

### 1. **Campos Específicos para Vehículos**

Cuando el usuario selecciona "Seguro Automotor", ahora se solicitan:

- ✅ **Marca del vehículo** (Ej: Toyota)
- ✅ **Modelo completo** (Ej: Corolla XLI)
- ✅ **Año del vehículo** (Ej: 2020)
- ✅ **Tipo de póliza**:
  1. Responsabilidad Civil
  2. RC + Robo Total/Parcial + Incendio Total/Parcial + Daños Totales por Accidente
  3. RC + Robo + Incendio + Daños Totales + Vidrios/Parabrisas + Granizo
  4. Todo Riesgo con Franquicia
  5. No lo sé aún

### 2. **Validación Condicional**

- Los campos de vehículo solo se muestran cuando se selecciona "Seguro Automotor"
- Estos campos son **obligatorios** cuando están visibles
- El usuario no puede avanzar sin completarlos correctamente

### 3. **Sección de Contacto Simplificada**

- ✅ Se eliminó la sección de "Oficina" de la página de contacto
- Solo se muestran: Teléfono, WhatsApp y Email

---

## 📁 Archivos Modificados

### **1. `index.html`**

**Cambios:**
- Se separaron los campos del vehículo (marca, modelo, año) que antes estaban combinados
- Se agregó el selector de tipo de póliza
- Los campos ahora se organizan en dos filas (2 columnas cada una)

**Código relevante (líneas 385-421):**
```html
<!-- Campos específicos para Automotor -->
<div id="detalles-automotor" style="display: none;">
    <div class="form-row">
        <div class="form-group">
            <label for="marca-vehiculo">Marca del vehículo *</label>
            <input type="text" id="marca-vehiculo" name="marca-vehiculo" placeholder="Ej: Toyota">
        </div>
        <div class="form-group">
            <label for="modelo-vehiculo">Modelo completo *</label>
            <input type="text" id="modelo-vehiculo" name="modelo-vehiculo" placeholder="Ej: Corolla XLI">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="anio-vehiculo">Año del vehículo *</label>
            <input type="number" id="anio-vehiculo" name="anio-vehiculo" min="1980" max="2026" placeholder="Ej: 2020">
        </div>
        <div class="form-group">
            <label for="tipo-poliza">Tipo de póliza *</label>
            <select id="tipo-poliza" name="tipo-poliza">
                <option value="">Seleccioná el tipo de cobertura</option>
                <option value="rc">Responsabilidad Civil</option>
                <option value="rc-plus">RC + Robo Total/Parcial + Incendio Total/Parcial + Daños Totales por Accidente</option>
                <option value="rc-premium">RC + Robo + Incendio + Daños Totales + Vidrios/Parabrisas + Granizo</option>
                <option value="todo-riesgo">Todo Riesgo con Franquicia</option>
                <option value="no-se">No lo sé aún</option>
            </select>
        </div>
    </div>
</div>
```

---

### **2. `script.js`**

**Cambios:**
- Se agregó validación condicional para los campos de vehículo
- Se creó función `showFieldError()` para mostrar errores personalizados
- La validación se ejecuta en el paso 2 cuando el tipo de seguro es "automotor"

**Código relevante (líneas 189-215):**
```javascript
// Validación especial para campos de automotor si está seleccionado
if (currentStep === 2) {
    const tipoSeguro = document.getElementById('tipo-seguro');
    if (tipoSeguro.value === 'automotor') {
        const marcaVehiculo = document.getElementById('marca-vehiculo');
        const modeloVehiculo = document.getElementById('modelo-vehiculo');
        const anioVehiculo = document.getElementById('anio-vehiculo');
        const tipoPoliza = document.getElementById('tipo-poliza');
        
        if (!marcaVehiculo.value.trim()) {
            showFieldError(marcaVehiculo, 'Por favor ingresá la marca del vehículo');
            isValid = false;
        }
        if (!modeloVehiculo.value.trim()) {
            showFieldError(modeloVehiculo, 'Por favor ingresá el modelo del vehículo');
            isValid = false;
        }
        if (!anioVehiculo.value.trim()) {
            showFieldError(anioVehiculo, 'Por favor ingresá el año del vehículo');
            isValid = false;
        }
        if (!tipoPoliza.value) {
            showFieldError(tipoPoliza, 'Por favor seleccioná el tipo de póliza');
            isValid = false;
        }
    }
}
```

---

### **3. `process_cotizacion.php`**

**Cambios:**
- Se agregaron variables para capturar los nuevos campos del vehículo
- Se actualizó la query SQL para incluir las nuevas columnas
- Se mejoró el template del email para mostrar los datos del vehículo de forma organizada
- Se agregó un array con los nombres descriptivos de los tipos de póliza

**Código relevante (líneas 25-33):**
```php
// Campos específicos de vehículo
$marca_vehiculo = !empty($_POST['marca-vehiculo']) ? trim(htmlspecialchars($_POST['marca-vehiculo'])) : '';
$modelo_vehiculo = !empty($_POST['modelo-vehiculo']) ? trim(htmlspecialchars($_POST['modelo-vehiculo'])) : '';
$anio_vehiculo = !empty($_POST['anio-vehiculo']) ? trim(htmlspecialchars($_POST['anio-vehiculo'])) : '';
$tipo_poliza = !empty($_POST['tipo-poliza']) ? trim(htmlspecialchars($_POST['tipo-poliza'])) : '';
```

**Email actualizado (líneas 90-112):**
```php
// Información específica de vehículo
if (!empty($marca_vehiculo) || !empty($modelo_vehiculo) || !empty($anio_vehiculo)) {
    $emailBody .= "

DATOS DEL VEHÍCULO:";
    if (!empty($marca_vehiculo)) {
        $emailBody .= "
Marca: $marca_vehiculo";
    }
    if (!empty($modelo_vehiculo)) {
        $emailBody .= "
Modelo: $modelo_vehiculo";
    }
    if (!empty($anio_vehiculo)) {
        $emailBody .= "
Año: $anio_vehiculo";
    }
    if (!empty($tipo_poliza)) {
        $tipo_poliza_nombre = $tipo_poliza_nombres[$tipo_poliza] ?? $tipo_poliza;
        $emailBody .= "
Tipo de Póliza: $tipo_poliza_nombre";
    }
}
```

---

### **4. `contacto.html`**

**Cambios:**
- Se eliminó completamente la sección de "Oficina" (líneas 112-121)
- Ahora solo se muestran 3 métodos de contacto: Teléfono, WhatsApp y Email

---

### **5. Base de Datos: `create_tables.sql`**

**Cambios:**
- Se actualizó la estructura de la tabla `cotizaciones`
- Se reemplazó el campo `marca_modelo` por campos separados
- Se agregaron 4 nuevas columnas:
  - `marca_vehiculo` VARCHAR(100)
  - `modelo_vehiculo` VARCHAR(150)
  - `anio_vehiculo` VARCHAR(4)
  - `tipo_poliza` VARCHAR(50)

**Nueva estructura:**
```sql
CREATE TABLE IF NOT EXISTS cotizaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50),
    ciudad VARCHAR(100),
    tipo_seguro VARCHAR(100),
    -- Campos específicos de vehículo
    marca_vehiculo VARCHAR(100),
    modelo_vehiculo VARCHAR(150),
    anio_vehiculo VARCHAR(4),
    tipo_poliza VARCHAR(50),
    -- Campos de otros seguros
    direccion_hogar VARCHAR(255),
    mensaje TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    INDEX idx_email (email),
    INDEX idx_fecha (fecha_creacion),
    INDEX idx_tipo_seguro (tipo_seguro)
);
```

---

### **6. `update_cotizaciones_table.sql` (NUEVO)**

Script SQL para actualizar la tabla existente sin perder datos.

**Uso:**
```sql
-- Ejecutar en phpMyAdmin o consola MySQL
ALTER TABLE cotizaciones 
ADD COLUMN IF NOT EXISTS marca_vehiculo VARCHAR(100) AFTER tipo_seguro,
ADD COLUMN IF NOT EXISTS modelo_vehiculo VARCHAR(150) AFTER marca_vehiculo,
ADD COLUMN IF NOT EXISTS anio_vehiculo VARCHAR(4) AFTER modelo_vehiculo,
ADD COLUMN IF NOT EXISTS tipo_poliza VARCHAR(50) AFTER anio_vehiculo;
```

---

## 🚀 Instrucciones de Implementación

### **Paso 1: Actualizar la Base de Datos**

**Opción A - Si la tabla ya existe:**
```sql
-- Ejecutar este script en phpMyAdmin
USE cfasegur_contactos;
SOURCE update_cotizaciones_table.sql;
```

**Opción B - Si MySQL no soporta `IF NOT EXISTS`:**
```sql
ALTER TABLE cotizaciones ADD COLUMN marca_vehiculo VARCHAR(100) AFTER tipo_seguro;
ALTER TABLE cotizaciones ADD COLUMN modelo_vehiculo VARCHAR(150) AFTER marca_vehiculo;
ALTER TABLE cotizaciones ADD COLUMN anio_vehiculo VARCHAR(4) AFTER modelo_vehiculo;
ALTER TABLE cotizaciones ADD COLUMN tipo_poliza VARCHAR(50) AFTER anio_vehiculo;
```

### **Paso 2: Subir Archivos Actualizados**

Subir al servidor los siguientes archivos:
- ✅ `index.html`
- ✅ `contacto.html`
- ✅ `script.js`
- ✅ `process_cotizacion.php`

### **Paso 3: Verificar Funcionamiento**

1. **Probar el formulario de cotización:**
   - Ir a la sección "Solicitá tu Cotización"
   - Completar datos personales (Paso 1)
   - Seleccionar "Seguro Automotor" (Paso 2)
   - Verificar que aparezcan los campos del vehículo
   - Completar todos los campos y avanzar
   - Enviar el formulario

2. **Verificar el email:**
   - Revisar que lleguen emails a `info@cfaseguros.com.ar` y `joaco.molinos.jm@gmail.com`
   - Confirmar que se muestren correctamente los datos del vehículo

3. **Verificar la base de datos:**
   - Comprobar que se guarden los datos en las nuevas columnas

---

## 📧 Ejemplo de Email Recibido

```
Nueva solicitud de cotización recibida:

Nombre: Juan Pérez
Email: juan@example.com
Teléfono: +54 9 11 1234-5678
Ciudad: Buenos Aires
Tipo de Seguro: Seguro Automotor

DATOS DEL VEHÍCULO:
Marca: Toyota
Modelo: Corolla XLI
Año: 2020
Tipo de Póliza: RC + Robo Total/Parcial + Incendio Total/Parcial + Daños Totales por Accidente

Mensaje:
Me gustaría recibir una cotización para mi vehículo.

Fecha: 15/10/2025 14:30:45
IP: 192.168.1.100
ID: 123
```

---

## ⚠️ Notas Importantes

1. **Backup:** Hacer backup de la base de datos antes de ejecutar el script de actualización
2. **Compatibilidad:** Los cambios son retrocompatibles - los formularios anteriores seguirán funcionando
3. **Validación:** Los campos solo son obligatorios cuando se selecciona "Seguro Automotor"
4. **Testing:** Probar con diferentes tipos de seguros para confirmar que la validación condicional funciona correctamente

---

## 🐛 Solución de Problemas

### Error: "Column already exists"
**Solución:** La columna ya fue agregada. Verificar con:
```sql
DESCRIBE cotizaciones;
```

### Error: "Unknown column in field list"
**Solución:** Ejecutar el script `update_cotizaciones_table.sql` para agregar las columnas faltantes.

### Los emails no muestran los datos del vehículo
**Solución:** Verificar que el formulario HTML tenga los atributos `name` correctos:
- `name="marca-vehiculo"`
- `name="modelo-vehiculo"`
- `name="anio-vehiculo"`
- `name="tipo-poliza"`

---

## ✅ Checklist de Implementación

- [ ] Backup de la base de datos
- [ ] Ejecutar script SQL de actualización
- [ ] Subir archivos al servidor
- [ ] Probar formulario con "Seguro Automotor"
- [ ] Probar formulario con otros tipos de seguro
- [ ] Verificar recepción de emails
- [ ] Verificar datos en la base de datos
- [ ] Comprobar validación de campos obligatorios
- [ ] Probar en móvil y desktop

---

**Fecha de actualización:** 15 de Octubre, 2025  
**Versión:** 2.0  
**Desarrollado para:** CFA & Asociados - Broker de Seguros

