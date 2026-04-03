# Sistema de Librerías Estáticas - Políticas y Versiones de Documentos

## ✅ IMPLEMENTACIÓN COMPLETADA

Se ha migrado exitosamente el sistema de carga CSV manual a un **sistema de librerías estáticas en PHP** que asigna automáticamente:
- **44 políticas estándar** (client_policies)
- **44 versiones de documentos estándar** (document_versions)

Todo de forma automática al crear un cliente nuevo, sin necesidad de cargar CSVs.

---

## 📋 ¿QUÉ SE IMPLEMENTÓ?

### 1. **Librerías Estáticas de Datos**

#### A. Políticas (client_policies)
📁 `app/Libraries/PolicyTypesLibrary.php`

- Contiene los 44 `policy_type_ids` estándar para Tienda a Tienda
- Define contenido predeterminado para algunos tipos (Misión, Visión, ARL, etc.)
- Genera automáticamente la estructura de datos para inserción masiva

#### B. Versiones de Documentos (document_versions)
📁 `app/Libraries/DocumentVersionsLibrary.php`

- Contiene las 44 versiones de documentos estándar (FT, MAN, PRC, PRG, PL, REG, MA)
- Define códigos SST (SST-001, SST-002, etc.) para cada documento
- Genera control de cambios automático con fecha
- Todos los documentos se crean en versión 1, ubicación DIGITAL, estado ACTIVO

### 2. **Helpers de Consumo**

#### A. Helper de Políticas
📁 `app/Helpers/client_policies_helper.php`

Funciones disponibles:
- `assign_standard_policies_to_client($clientId)` - Asigna automáticamente las 44 políticas
- `get_client_policy_content($clientId, $policyTypeId)` - Obtiene contenido de una política
- `sync_missing_policies_for_client($clientId)` - Sincroniza políticas faltantes
- `get_standard_policy_types_count()` - Devuelve el total (44)

#### B. Helper de Versiones de Documentos
📁 `app/Helpers/document_versions_helper.php`

Funciones disponibles:
- `assign_standard_document_versions_to_client($clientId)` - Asigna automáticamente las 44 versiones
- `get_client_document_version($clientId, $policyTypeId)` - Obtiene versión específica
- `sync_missing_document_versions_for_client($clientId)` - Sincroniza versiones faltantes
- `get_standard_document_versions_count()` - Devuelve el total (44)
- `get_document_versions_stats($clientId)` - Obtiene estadísticas completas

### 3. **Integración Automática**
📝 Modificado: `app/Controllers/ConsultantController.php` - Método `addClientPost()`

Ahora cuando creas un cliente:
1. ✅ Se crea el cliente
2. ✅ Se crea su carpeta en `public/uploads/`
3. ✅ **Se asignan automáticamente los 44 documentos estándar** (sin CSV)

### 4. **Eliminación de Proceso Manual CSV**
❌ Eliminado: `app/Controllers/CsvPoliticasParaDocumentosController.php`
❌ Eliminado: `app/Views/consultant/csvpoliticasparadocumentos.php`
🔒 Desactivado: Rutas en `app/Config/Routes.php` (comentadas)

---

## 🚀 CÓMO PROBAR

### Prueba 1: Crear Cliente Nuevo

1. Inicia sesión como consultor
2. Ve a **Agregar Cliente** (`/addClient`)
3. Completa el formulario con datos de prueba
4. Haz clic en **Guardar**

**Resultado esperado:**
```
✅ Cliente agregado exitosamente. Se asignaron automáticamente 44 documentos estándar.
```

### Prueba 2: Verificar en Base de Datos

```sql
-- Verificar que el cliente tiene 44 registros en client_policies
SELECT COUNT(*) as total_documentos
FROM client_policies
WHERE client_id = [ID_DEL_CLIENTE_CREADO];

-- Debería devolver: 44
```

### Prueba 3: Ver Documentos Asignados

```sql
-- Ver todos los documentos del cliente con nombres
SELECT
    cp.id,
    cp.client_id,
    cp.policy_type_id,
    pt.type_name,
    CASE
        WHEN cp.policy_content != '' THEN 'Con contenido'
        ELSE 'Vacío'
    END as estado_contenido
FROM client_policies cp
INNER JOIN policy_types pt ON cp.policy_type_id = pt.id
WHERE cp.client_id = [ID_DEL_CLIENTE_CREADO]
ORDER BY cp.policy_type_id;
```

---

## 🔄 SINCRONIZAR CLIENTES EXISTENTES (OPCIONAL)

Si quieres que **clientes antiguos** también tengan los 44 documentos asignados automáticamente, puedes usar el helper de sincronización.

### Opción A: Desde PHP (Crear un script temporal)

Crea: `c:\xampp\htdocs\enterprisesstph\sync_old_clients.php`

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = \Config\Services::codeigniter();
$app->initialize();

helper('client_policies');

use App\Models\ClientModel;

$clientModel = new ClientModel();
$clients = $clientModel->findAll();

foreach ($clients as $client) {
    $added = sync_missing_policies_for_client($client['id']);
    echo "Cliente {$client['nombre_cliente']} (ID: {$client['id']}): {$added} documentos sincronizados\n";
}

echo "\n✅ Sincronización completada\n";
```

Ejecutar desde terminal:
```bash
cd c:\xampp\htdocs\enterprisesstph
php sync_old_clients.php
```

### Opción B: Desde SQL Directo (Más rápido)

```sql
-- Script SQL para sincronizar clientes existentes
-- IMPORTANTE: Ejecuta esto solo UNA VEZ

USE enterprise_sst2024;

-- Insertar documentos faltantes para todos los clientes
INSERT INTO client_policies (client_id, policy_type_id, policy_content, created_at, updated_at)
SELECT
    c.id as client_id,
    pt.policy_type_id,
    CASE
        -- Contenidos predeterminados específicos
        WHEN pt.policy_type_id = 11 THEN 'Administración e implementación del Sistema de Gestión de Seguridad y Salud en el Trabajo...'
        WHEN pt.policy_type_id = 15 THEN 'Misión: Liderar la transformación positiva en el entorno laboral...'
        WHEN pt.policy_type_id = 16 THEN 'Visión: Posicionar a Cycloid Talent como el principal proveedor...'
        WHEN pt.policy_type_id = 26 THEN 'No Existen Sucursales'
        WHEN pt.policy_type_id = 34 THEN 'ARL SURA'
        ELSE ''
    END as policy_content,
    NOW() as created_at,
    NOW() as updated_at
FROM
    tbl_clientes c
CROSS JOIN (
    -- Los 44 policy_type_ids estándar
    SELECT 1 as policy_type_id UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL
    SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL
    SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12 UNION ALL SELECT 13 UNION ALL
    SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16 UNION ALL SELECT 17 UNION ALL
    SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20 UNION ALL SELECT 21 UNION ALL
    SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24 UNION ALL SELECT 25 UNION ALL
    SELECT 26 UNION ALL SELECT 27 UNION ALL SELECT 28 UNION ALL SELECT 29 UNION ALL
    SELECT 30 UNION ALL SELECT 31 UNION ALL SELECT 32 UNION ALL SELECT 33 UNION ALL
    SELECT 34 UNION ALL SELECT 35 UNION ALL SELECT 36 UNION ALL SELECT 37 UNION ALL
    SELECT 38 UNION ALL SELECT 39 UNION ALL SELECT 40 UNION ALL SELECT 41 UNION ALL
    SELECT 42 UNION ALL SELECT 43 UNION ALL SELECT 44 UNION ALL SELECT 45 UNION ALL
    SELECT 46
) pt
WHERE NOT EXISTS (
    -- Solo insertar si no existe ya esa combinación cliente-policy_type
    SELECT 1
    FROM client_policies cp
    WHERE cp.client_id = c.id
      AND cp.policy_type_id = pt.policy_type_id
);

-- Verificar resultados
SELECT
    'Total clientes' as descripcion,
    COUNT(DISTINCT client_id) as cantidad
FROM client_policies
UNION ALL
SELECT
    'Total registros client_policies',
    COUNT(*)
FROM client_policies;
```

---

## 📊 BENEFICIOS OBTENIDOS

### Antes (Sistema CSV)
- ❌ Proceso manual: subir CSV por cada cliente
- ❌ Tiempo: ~2-5 minutos por cliente
- ❌ Propenso a errores: olvidar cargar CSV
- ❌ Mantenimiento: si cambias contenido, debes actualizar CSV y recargar para todos

### Ahora (Sistema de Librería)
- ✅ **Automático**: cero intervención manual
- ✅ **Tiempo**: 0 segundos (instantáneo)
- ✅ **Sin errores**: siempre se asignan los 44 documentos
- ✅ **Fácil mantenimiento**: editas `PolicyTypesLibrary.php` y afecta a todos

---

## 🔧 MANTENIMIENTO Y ACTUALIZACIONES

### Agregar nuevo policy_type (documento)

1. Edita [`app/Libraries/PolicyTypesLibrary.php`](app/Libraries/PolicyTypesLibrary.php)

2. Agrega el nuevo ID en `getStandardPolicyTypeIds()`:
```php
return [
    1, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
    21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36,
    37, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47 // <-- NUEVO
];
```

3. (Opcional) Si tiene contenido predeterminado, agrégalo en `getDefaultContents()`:
```php
47 => 'Contenido del nuevo documento...',
```

4. Para clientes existentes, ejecuta:
```php
helper('client_policies');
sync_missing_policies_for_client($clientId); // Para un cliente específico

// O sincronizar todos:
$clientModel = new ClientModel();
$clients = $clientModel->findAll();
foreach ($clients as $client) {
    sync_missing_policies_for_client($client['id']);
}
```

### Cambiar contenido predeterminado

Simplemente edita [`app/Libraries/PolicyTypesLibrary.php`](app/Libraries/PolicyTypesLibrary.php) en el método `getDefaultContents()`.

**Importante:** Esto solo afecta a **nuevos clientes**. Los existentes mantienen su contenido actual.

---

## 🐛 TROUBLESHOOTING

### Error: "No se asignaron documentos"

1. Verifica logs: `c:\xampp\htdocs\enterprisesstph\writable\logs\log-[fecha].php`
2. Busca mensajes como: `"Error al asignar documentos estándar al cliente"`
3. Verifica permisos de escritura en base de datos

### Cliente no tiene los 44 documentos

```sql
-- Ver cuántos tiene
SELECT COUNT(*) FROM client_policies WHERE client_id = [ID];

-- Si tiene menos de 44, sincronizar:
```
```php
helper('client_policies');
sync_missing_policies_for_client([ID]);
```

### ¿Cómo verifico que el helper se cargó?

```php
// En cualquier controlador:
helper('client_policies');
$count = get_standard_policy_types_count();
echo "Total documentos estándar: " . $count; // Debería mostrar: 44
```

---

## 📝 NOTAS IMPORTANTES

1. **No afecta clientes existentes**: Los clientes antiguos conservan sus registros actuales en `client_policies`. Puedes sincronizarlos opcionalmente con el script de sincronización.

2. **CSV eliminado**: Ya no necesitas cargar CSV para políticas. El proceso es 100% automático.

3. **Librería centralizada**: Toda la lógica está en un solo archivo: [`PolicyTypesLibrary.php`](app/Libraries/PolicyTypesLibrary.php)

4. **Logs habilitados**: Todas las asignaciones se registran en logs de CodeIgniter para auditoría.

---

## 🎉 RESULTADO FINAL

Al crear un cliente ahora:
1. Se crea el registro del cliente
2. Se crea carpeta `public/uploads/[NIT]/`
3. **Se insertan automáticamente 44 registros en `client_policies`**
4. Todo en menos de 1 segundo

**¡Ya no necesitas cargar CSVs nunca más!** 🚀

---

## 👨‍💻 SOPORTE

Si tienes dudas o problemas:
- Revisa los logs en `writable/logs/`
- Verifica la tabla `client_policies` en la base de datos
- Contacta al desarrollador del sistema

---

**Fecha de implementación:** 2025-01-09
**Versión:** 1.0
**Sistema:** Enterprise SST - Tienda a Tienda Colombia
