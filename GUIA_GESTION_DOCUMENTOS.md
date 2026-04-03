# Guía de Gestión de Documentos SST

Esta guía explica cómo agregar o eliminar documentos del sistema de gestión SST de Tienda a Tienda.

---

## 📋 Tabla de Contenidos

1. [Agregar un Nuevo Documento](#agregar-un-nuevo-documento)
2. [Eliminar un Documento Existente](#eliminar-un-documento-existente)
3. [Estructura de Archivos](#estructura-de-archivos)
4. [Convenciones de Nombres](#convenciones-de-nombres)

---

## ➕ Agregar un Nuevo Documento

### Ejemplo: Política de Prevención del Acoso Sexual

Sigue estos 6 pasos para agregar un nuevo documento al sistema:

---

### **Paso 1: Agregar el Documento a DocumentLibrary.php**

📁 **Archivo:** `app/Libraries/DocumentLibrary.php`

Edita el método `getAllDocuments()` y agrega una nueva entrada en el array:

```php
// Dentro del array en DocumentLibrary::getAllDocuments()
45 => [
    'id' => 45,
    'type_name' => 'Política de Prevención del Acoso Sexual',
    'acronym' => 'PAS',
    'version_number' => '1.0',
    'is_active' => 1,
    'created_at' => '2026-01-09 00:00:00',
    'document_type' => 'politica', // Opciones: politica, procedimiento, programa, formato
    'default_content' => '
        <div class="policy-content">
            <h1>POLÍTICA DE PREVENCIÓN DEL ACOSO SEXUAL</h1>

            <h2>1. OBJETIVO</h2>
            <p>Establecer lineamientos para prevenir y sancionar el acoso sexual...</p>

            <h2>2. ALCANCE</h2>
            <p>Aplica a todos los trabajadores, contratistas y visitantes...</p>

            <h2>3. DEFINICIONES</h2>
            <p><strong>Acoso Sexual:</strong> Conducta de naturaleza sexual...</p>

            <h2>4. RESPONSABILIDADES</h2>
            <ul>
                <li><strong>Alta Dirección:</strong> ...</li>
                <li><strong>Área de Recursos Humanos:</strong> ...</li>
                <li><strong>Trabajadores:</strong> ...</li>
            </ul>

            <h2>5. DESARROLLO</h2>
            <p>Descripción detallada del procedimiento...</p>

            <h2>6. SANCIONES</h2>
            <p>Las sanciones aplicables incluyen...</p>

            <h2>7. REFERENCIAS NORMATIVAS</h2>
            <ul>
                <li>Ley 1010 de 2006</li>
                <li>Resolución 652 de 2012</li>
            </ul>
        </div>
    '
],
```

**📝 Notas importantes:**
- El ID debe ser único y secuencial (siguiente número disponible)
- `document_type` puede ser: `politica`, `procedimiento`, `programa`, `formato`, `instructivo`
- `default_content` debe contener HTML válido
- La fecha en `created_at` usa formato MySQL: `YYYY-MM-DD HH:MM:SS`

---

### **Paso 2: Agregar el Acceso al Dashboard (AccessLibrary.php)**

📁 **Archivo:** `app/Libraries/AccessLibrary.php`

Edita el método `getAllAccesses()` y agrega una nueva entrada:

```php
// Dentro del array en AccessLibrary::getAllAccesses()
60 => [
    'id_acceso' => 60,
    'nombre' => '2.1.8 Política de Prevención del Acoso Sexual',
    'url' => '/politicaAcosoSexual/1',
    'dimension' => 'Planear' // Opciones: Planear, Hacer, Verificar, Actuar, Indicadores
],
```

**📝 Notas importantes:**
- `id_acceso` debe ser único y secuencial
- `nombre` debe incluir el código de numeración (ej: 2.1.8)
- `url` debe coincidir con la ruta que crearás en Routes.php
- `dimension` determina en qué sección del ciclo PHVA aparece

---

### **Paso 3: Agregar el Acceso a los Estándares**

📁 **Archivo:** `app/Libraries/AccessLibrary.php`

En el mismo archivo, edita el método `getAccessesByStandard()` y agrega el ID del nuevo acceso a los estándares que apliquen:

```php
public static function getAccessesByStandard($standardName)
{
    $standardMappings = [
        // Agrega el ID 60 a los estándares donde debe aparecer este documento
        'Mensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41, 60],
        'Bimensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41, 60],
        'Trimestral' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41, 60],
        'Proyecto' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41, 60]
    ];
    // ... resto del método
}
```

**📝 Notas importantes:**
- Si el documento aplica a todos los estándares, agrégalo a los 4 arrays
- Si solo aplica a algunos, agrégalo solo a esos (ej: solo Mensual y Bimensual)

---

### **Paso 4: Crear el Controlador**

📁 **Archivo:** `app/Controllers/PzpoliticaacosexualController.php`

Crea un nuevo archivo de controlador:

```php
<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
// Ya no usamos ClientPoliciesModel, DocumentVersionModel, PolicyTypeModel (migrado a DocumentLibrary.php)
use CodeIgniter\I18n\Time;
use Dompdf\Dompdf;
use CodeIgniter\Controller;

class PzpoliticaacosexualController extends Controller
{
    public function politicaAcosoSexual()
    {
        // Cargar helper para acceso a DocumentLibrary
        helper('document_library');

        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Obtener los datos del cliente
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }

        // Obtener los datos del consultor
        $consultant = $consultantModel->find($client['id_consultor']);
        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }

        // ID del documento (Prevención del Acoso Sexual)
        $policyTypeId = 45;

        // Obtener documento desde la librería estática
        $policyType = get_document($policyTypeId);
        if (!$policyType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento.');
        }

        // Para Tienda a Tienda, latestVersion y policyType son lo mismo
        $latestVersion = $policyType;
        $allVersions = get_all_document_versions($policyTypeId);

        // Para compatibilidad con vistas que usan $clientPolicy
        $clientPolicy = [
            'policy_content' => $policyType['default_content'] ?? ''
        ];

        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,
        ];

        return view('client/sgsst/1planear/p2_1_8politica_acoso_sexual', $data);
    }

    public function generatePdf_politicaAcosoSexual()
    {
        helper('document_library');

        $dompdf = new Dompdf();
        $dompdf->setOption('isRemoteEnabled', true);

        $session = session();
        $clientId = $session->get('user_id');

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $client = $clientModel->find($clientId);
        $consultant = $consultantModel->find($client['id_consultor']);

        $policyTypeId = 45;
        $policyType = get_document($policyTypeId);
        $latestVersion = $policyType;
        $allVersions = get_all_document_versions($policyTypeId);

        if ($latestVersion) {
            $latestVersion['created_at'] = Time::parse($latestVersion['created_at'], 'America/Bogota')
                                               ->toLocalizedString('d MMMM yyyy');
        }

        $clientPolicy = [
            'policy_content' => $policyType['default_content'] ?? ''
        ];

        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,
        ];

        $html = view('client/sgsst/1planear/p2_1_8politica_acoso_sexual', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('politica_acoso_sexual.pdf', ['Attachment' => false]);
    }
}
```

**📝 Notas importantes:**
- Nombre del controlador debe seguir el patrón: `Pz` (Planear) o `Hz` (Hacer) + descripción + `Controller`
- Los nombres de métodos deben ser camelCase y descriptivos
- Siempre incluye tanto el método de visualización como el de generación de PDF

---

### **Paso 5: Agregar las Rutas**

📁 **Archivo:** `app/Config/Routes.php`

Agrega las rutas al final del archivo (antes del cierre):

```php
// Política de Prevención del Acoso Sexual
$routes->get('politicaAcosoSexual/1', 'PzpoliticaacosexualController::politicaAcosoSexual');
$routes->get('generatePdf_politicaAcosoSexual', 'PzpoliticaacosexualController::generatePdf_politicaAcosoSexual');
```

**📝 Notas importantes:**
- La primera ruta es para visualizar el documento
- La segunda ruta es para generar el PDF
- El `/1` al final de la primera ruta es una convención del sistema (no lo cambies)

---

### **Paso 6: Crear la Vista**

📁 **Archivo:** `app/Views/client/sgsst/1planear/p2_1_8politica_acoso_sexual.php`

Crea el archivo de vista (puedes copiar y adaptar una vista existente):

```php
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($policyType['type_name']) ?></title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 200px;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin: 0;
        }
        .content {
            margin: 20px 0;
        }
        .content h2 {
            color: #34495e;
            font-size: 18px;
            margin-top: 20px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        .content p {
            text-align: justify;
            margin: 10px 0;
        }
        .content ul {
            margin: 10px 0 10px 20px;
        }
        .content li {
            margin: 5px 0;
        }
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Encabezado con logo del cliente -->
    <div class="header">
        <?php if (!empty($client['logo'])): ?>
            <img src="<?= base_url('uploads/' . esc($client['logo'])) ?>" alt="Logo Cliente">
        <?php endif; ?>
        <h1><?= esc($policyType['type_name']) ?></h1>
        <p><strong>Versión:</strong> <?= esc($latestVersion['version_number']) ?> |
           <strong>Fecha:</strong> <?= esc($latestVersion['created_at']) ?></p>
    </div>

    <!-- Contenido del documento -->
    <div class="content">
        <?= $clientPolicy['policy_content'] ?>
    </div>

    <!-- Pie de página con control de versiones -->
    <?= $this->include('client/sgsst/footer') ?>
</body>
</html>
```

**📝 Notas importantes:**
- Ruta de la vista debe seguir la estructura: `client/sgsst/[dimension]/[codigo_numeracion][nombre]`
- Usa `esc()` para escapar variables y prevenir XSS
- El footer incluye automáticamente el control de versiones

---

### **Paso 7: Probar el Documento**

1. **Refrescar el dashboard:**
   ```
   http://localhost/enterprisesstph/public/dashboardclient
   ```

2. **Verificar que aparezca en el menú:**
   - Busca "2.1.8 Política de Prevención del Acoso Sexual" en la sección correspondiente

3. **Hacer clic para ver el documento:**
   ```
   http://localhost/enterprisesstph/public/politicaAcosoSexual/1
   ```

4. **Probar generación de PDF:**
   - Busca el botón "Generar PDF" en la vista del documento
   - Verifica que se descargue correctamente

---

## ➖ Eliminar un Documento Existente

### Ejemplo: Eliminar Política de Prevención del Acoso Sexual (ID: 45)

Sigue estos 5 pasos para eliminar un documento del sistema:

---

### **Paso 1: Eliminar de DocumentLibrary.php**

📁 **Archivo:** `app/Libraries/DocumentLibrary.php`

Localiza y **elimina completamente** la entrada del documento:

```php
// ELIMINAR ESTE BLOQUE COMPLETO:
45 => [
    'id' => 45,
    'type_name' => 'Política de Prevención del Acoso Sexual',
    'acronym' => 'PAS',
    'version_number' => '1.0',
    'is_active' => 1,
    'created_at' => '2026-01-09 00:00:00',
    'document_type' => 'politica',
    'default_content' => '...'
],
```

**⚠️ Importante:** No dejes el ID vacío, simplemente elimina todo el bloque.

---

### **Paso 2: Eliminar de AccessLibrary.php (Array de Accesos)**

📁 **Archivo:** `app/Libraries/AccessLibrary.php`

Localiza y **elimina completamente** la entrada del acceso en `getAllAccesses()`:

```php
// ELIMINAR ESTE BLOQUE COMPLETO:
60 => [
    'id_acceso' => 60,
    'nombre' => '2.1.8 Política de Prevención del Acoso Sexual',
    'url' => '/politicaAcosoSexual/1',
    'dimension' => 'Planear'
],
```

---

### **Paso 3: Eliminar de los Estándares**

📁 **Archivo:** `app/Libraries/AccessLibrary.php`

En el método `getAccessesByStandard()`, elimina el ID del acceso de todos los arrays de estándares:

```php
public static function getAccessesByStandard($standardName)
{
    $standardMappings = [
        // ELIMINAR el 60 de todos los arrays donde aparezca:
        'Mensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41], // 60 eliminado
        'Bimensual' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41], // 60 eliminado
        'Trimestral' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41], // 60 eliminado
        'Proyecto' => [1, 2, 3, 4, 5, 15, 16, 17, 18, 19, 20, 21, 23, 24, 25, 26, 28, 31, 36, 41] // 60 eliminado
    ];
    // ... resto del método
}
```

---

### **Paso 4: Eliminar las Rutas**

📁 **Archivo:** `app/Config/Routes.php`

Localiza y **elimina** las dos rutas relacionadas:

```php
// ELIMINAR ESTAS LÍNEAS:
$routes->get('politicaAcosoSexual/1', 'PzpoliticaacosexualController::politicaAcosoSexual');
$routes->get('generatePdf_politicaAcosoSexual', 'PzpoliticaacosexualController::generatePdf_politicaAcosoSexual');
```

---

### **Paso 5: Eliminar el Controlador**

📁 **Archivo:** `app/Controllers/PzpoliticaacosexualController.php`

**Elimina completamente el archivo** del sistema de archivos:

```bash
rm app/Controllers/PzpoliticaacosexualController.php
```

O desde Windows:
```cmd
del app\Controllers\PzpoliticaacosexualController.php
```

---

### **Paso 6: Eliminar la Vista**

📁 **Archivo:** `app/Views/client/sgsst/1planear/p2_1_8politica_acoso_sexual.php`

**Elimina completamente el archivo** del sistema de archivos:

```bash
rm app/Views/client/sgsst/1planear/p2_1_8politica_acoso_sexual.php
```

O desde Windows:
```cmd
del app\Views\client\sgsst\1planear\p2_1_8politica_acoso_sexual.php
```

---

### **Paso 7: Verificar la Eliminación**

1. **Refrescar el dashboard:**
   ```
   http://localhost/enterprisesstph/public/dashboardclient
   ```

2. **Verificar que YA NO aparezca en el menú:**
   - El documento no debe aparecer en ninguna sección del dashboard

3. **Verificar que la URL devuelva 404:**
   ```
   http://localhost/enterprisesstph/public/politicaAcosoSexual/1
   ```
   - Debe mostrar error 404 (página no encontrada)

---

## 📁 Estructura de Archivos

```
enterprisesstph/
├── app/
│   ├── Controllers/
│   │   ├── PzasignacionresponsableController.php       (Ejemplo: Planear)
│   │   ├── HzaccioncorrectivaController.php            (Ejemplo: Hacer)
│   │   └── [Nuevo]Controller.php                       (Tu nuevo controlador)
│   │
│   ├── Libraries/
│   │   ├── DocumentLibrary.php                         (Catálogo de documentos)
│   │   └── AccessLibrary.php                           (Catálogo de accesos/menú)
│   │
│   ├── Helpers/
│   │   ├── document_library_helper.php                 (Funciones helper para docs)
│   │   └── access_library_helper.php                   (Funciones helper para accesos)
│   │
│   ├── Views/
│   │   └── client/
│   │       └── sgsst/
│   │           ├── 1planear/                           (Documentos de Planear)
│   │           ├── 2hacer/                             (Documentos de Hacer)
│   │           ├── 3verificar/                         (Documentos de Verificar)
│   │           ├── 4actuar/                            (Documentos de Actuar)
│   │           └── footer.php                          (Footer con control de versiones)
│   │
│   └── Config/
│       └── Routes.php                                   (Rutas de la aplicación)
│
└── public/
    └── uploads/                                         (Logos de clientes)
```

---

## 🏷️ Convenciones de Nombres

### Controladores

```
Prefijo + Descripción + "Controller"
```

**Prefijos por dimensión PHVA:**
- `Pz` = Planear (Ej: `PzpoliticasstController`)
- `Hz` = Hacer (Ej: `HzaccioncorrectivaController`)
- `Vz` = Verificar (Ej: `VzauditoriainnaController`)
- `Az` = Actuar (Ej: `AzmejoracontinuaController`)
- `kpi` = Indicadores (Ej: `kpiausentismoController`)

**Ejemplos:**
- `PzpoliticasstController.php` → Política SST (Planear)
- `HzrevaltagerenciaController.php` → Revisión Alta Gerencia (Hacer)
- `kpiausentismoController.php` → Indicador de Ausentismo

### Métodos en Controladores

```php
// Método principal (visualización)
public function nombreDelDocumento()

// Método de generación de PDF
public function generatePdf_nombreDelDocumento()
```

**Ejemplos:**
- `politicaSst()` + `generatePdf_politicaSst()`
- `asignacionResponsable()` + `generatePdf_asignacionResponsable()`

### Rutas (URLs)

```
/nombreDocumento/1
/generatePdf_nombreDocumento
```

**Ejemplos:**
- `/politicaSst/1` → Visualización
- `/generatePdf_politicaSst` → Generación de PDF

### Vistas

```
client/sgsst/[dimension]/[codigo][nombre].php
```

**Ejemplos:**
- `client/sgsst/1planear/p2_1_1politicasst.php`
- `client/sgsst/2hacer/h4_1_2accion_correctiva.php`
- `client/sgsst/5indicadores/kpi_ausentismo.php`

---

## 📝 Tipos de Documentos

En `document_type` de DocumentLibrary.php puedes usar:

- `politica` - Políticas del sistema
- `procedimiento` - Procedimientos operativos
- `programa` - Programas de gestión
- `formato` - Formatos y plantillas
- `instructivo` - Instructivos de trabajo
- `manual` - Manuales de operación
- `reglamento` - Reglamentos internos

---

## ✅ Checklist de Verificación

### Al Agregar un Documento:

- [ ] ✅ Agregado a `DocumentLibrary.php` con ID único
- [ ] ✅ Agregado a `AccessLibrary.php` (array de accesos)
- [ ] ✅ Agregado a los estándares en `AccessLibrary.php`
- [ ] ✅ Creado el controlador con ambos métodos (view + pdf)
- [ ] ✅ Agregadas las 2 rutas en `Routes.php`
- [ ] ✅ Creada la vista con estilos y estructura
- [ ] ✅ Probado en el dashboard
- [ ] ✅ Probada la generación de PDF

### Al Eliminar un Documento:

- [ ] ✅ Eliminado de `DocumentLibrary.php`
- [ ] ✅ Eliminado de `AccessLibrary.php` (array de accesos)
- [ ] ✅ Eliminado de los estándares en `AccessLibrary.php`
- [ ] ✅ Eliminadas las rutas en `Routes.php`
- [ ] ✅ Eliminado el archivo del controlador
- [ ] ✅ Eliminado el archivo de la vista
- [ ] ✅ Verificado que no aparece en el dashboard
- [ ] ✅ Verificado que la URL devuelve 404

---

## 🚀 Ventajas de este Sistema

1. **Sin Base de Datos**: Todo es código PHP estático
2. **Versionamiento Git**: Todos los cambios quedan registrados
3. **Deployment Simple**: Solo copiar archivos
4. **Rendimiento**: No hay queries a BD para documentos
5. **Mantenibilidad**: Todo centralizado en 2 librerías

---

## 📞 Soporte

Si encuentras algún problema o tienes dudas:

1. Revisa el archivo `MIGRACION_COMPLETADA.md` para entender la arquitectura
2. Consulta controladores existentes como ejemplos de referencia
3. Verifica que seguiste todos los pasos en orden

---

**Última actualización:** 2026-01-09
**Versión del sistema:** Tienda a Tienda SST v2.0
