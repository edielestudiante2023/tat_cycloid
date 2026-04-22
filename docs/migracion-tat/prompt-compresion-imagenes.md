# Prompt aislado — Compresión y optimización de imágenes subidas

> **Estado**: ✅ Ejecutado 2026-04-22. Ver sección "Estado de ejecución" al final.

> **Úsame en una sesión nueva** (no interrumpas la sesión de Fase 5 TAT).
> Copia desde la siguiente línea horizontal.

---

## Contexto

Proyecto: **TAT Cycloid** — aplicativo CI4 (CodeIgniter 4.7.2, PHP 8.4) en `c:\xampp\htdocs\tat_cycloid`. Fork de propiedad horizontal enfocado ahora a locales comerciales. Los usuarios (tenderos y consultores) suben fotos desde teléfonos móviles constantemente (inspecciones diarias de limpieza, temperaturas de neveras, soportes de trabajadores, documentos de bomberos). **El servidor está a punto de llenarse** si no se optimiza el peso.

**Ambientes**:
- LOCAL: XAMPP, MySQL en `127.0.0.1`, db `tat_cycloid`
- PROD: DigitalOcean managed MySQL (SSL required), db `tat_cycloid`
- Credenciales en `.env` (`database.production.password`)

**Protocolo BD inviolable**:
- Cambios SQL SIEMPRE via script PHP CLI en `app/SQL/`
- Ejecutar LOCAL primero; solo si pasa, ejecutar PROD
- Prohibido phpMyAdmin/GUI

## Lo que ya está hecho

1. **Compresor client-side JS** en [`public/js/image-compress.js`](../../public/js/image-compress.js):
   - Se auto-engancha a `input[type=file][accept*=image]`
   - Redimensiona a 1200px máx, JPEG 0.75, skip si <300 KB
   - Ignora PDFs (check `file.type.startsWith('image/')`)
   - Muestra banner "Optimizada ✓ (XKB → YKB)" junto al input
   - Expone `window.TAT_imageCompressAttach()` para inputs dinámicos
2. **Incluido en** [`app/Views/client/inspecciones/layout.php`](../../app/Views/client/inspecciones/layout.php) — aplica a todos los módulos del cliente que usan ese layout (Neveras, Limpieza del Local, Trabajadores, Permisos Bomberos).

## Lo que falta

### 1. Cobertura — formularios que NO usan el layout
Incluir `<script src="<?= base_url('js/image-compress.js?v=1') ?>" defer></script>` en:
- [`app/Views/consultant/add_client.php`](../../app/Views/consultant/add_client.php) — campos `logo`, `firma_representante_legal`, `rut`, `camara_comercio`, `cedula_rep_legal_doc`, `oferta_comercial`
- [`app/Views/consultant/edit_client.php`](../../app/Views/consultant/edit_client.php) — mismos campos
- [`app/Views/clients/onboarding.php`](../../app/Views/clients/onboarding.php) — revisar qué uploads tiene
- Cualquier otra vista con `<input type="file" accept="image/*">` o `accept=".pdf,image/*"`

**Comando para encontrarlos rápido**:
```bash
grep -rn 'accept=.*image' c:/xampp/htdocs/tat_cycloid/app/Views | grep -v "layout.php"
```

### 2. Safety net server-side
El JS puede fallar (Safari iOS viejo, usuarios curiosos que lo bypasseen con curl). Agregar **compresión server-side** como respaldo:

- Crear helper `app/Helpers/image_helper.php` con función `compress_uploaded_image(string $path, int $maxDim = 1600, int $jpegQuality = 78): bool`
- Usa GD (ya disponible en XAMPP PHP 8.4) o intenta Imagick si está disponible
- Lógica:
  - Leer con `getimagesize()`
  - Si ancho y alto ≤ maxDim y el archivo < 400 KB, no tocar
  - Si es PNG con alpha y <500 KB, no tocar (preservar transparencia)
  - Respetar EXIF orientation (rotar con `imagerotate` según `exif_read_data`)
  - Guardar como JPEG quality 78 si es foto; PNG si era screenshot con alpha
- Llamar `compress_uploaded_image()` en cada punto del código que hace `$file->move(...)` en controladores con uploads de imagen:
  - `ClientNeverasController::guardarMedicion` + `guardarNevera` + `actualizarNevera`
  - `ClientLimpiezaLocalController::guardar`
  - `ClientTrabajadoresController::uploadSoporte` (solo si es imagen, no PDF)
  - `ClientBomberosController::uploadDocumento` (igual)
  - `ConsultantController::addClientPost` + `updateClient`

### 3. Fix EXIF orientation
Fotos de móvil vertical a menudo se suben rotadas. El compresor JS actual **no corrige orientación** (Canvas ignora EXIF). Opciones:
- **a) Client-side**: leer EXIF en JS (librería `exif-js` o manual) y rotar el canvas antes de toBlob
- **b) Server-side**: rotar con GD basado en `exif_read_data` (más confiable)

Recomendación: (b) — en el helper `compress_uploaded_image` del punto 2.

### 4. Limpieza retroactiva (opcional pero útil)
Script CLI `app/SQL/cleanup_uploads_huge.php` que:
- Recorra `public/uploads/`
- Identifique archivos .jpg/.jpeg/.png > 1 MB
- Los comprima con el mismo helper
- Imprima el espacio ahorrado
- Sea dry-run por default (flag `--apply` para ejecutar)

### 5. Métricas / observabilidad
- En el helper server-side, loguear con `log_message('info', ...)`: tamaño original, tamaño final, archivo.
- Crear una vista admin simple `/admin/uploads-stats` que muestre:
  - Total de archivos y bytes en `public/uploads/`
  - Top 20 más pesados
  - Crecimiento mensual (si hay metadata de fecha)

### 6. Verificación
Después de todo:
1. Test manual: subir foto 5MP desde móvil a `/client/neveras/{id}/medir`, verificar que el archivo en `public/uploads/` sea < 400 KB.
2. Test manual: desactivar JS en el navegador, subir misma foto, verificar que el server la comprime también (log).
3. Test manual: subir PDF a `/client/trabajadores/.../soportes` — debe quedar intacto.
4. Test manual: imagen con canal alpha (PNG screenshot) — debe preservarse transparencia.

## Restricciones

1. No tocar los módulos de Fase 5.3 que están en desarrollo (Equipos y Utensilios, POES 4.1/4.2/4.4).
2. No romper flujos existentes — los controladores tienen muchos puntos de `$file->move(...)`, revisarlos con `grep -n "->move(" app/Controllers/**/*.php`.
3. Mantener el patrón del proyecto: PHP CLI para cambios BD, idempotente, LOCAL antes que PROD.
4. No exceder 1200 líneas de código nuevo. Si pasa, parar y pedir validación.

## Documentación y commit

Al terminar:
- Actualizar este archivo marcando qué se completó
- Commit con mensaje: `feat: compresion imagenes (client + server) + limpieza retroactiva`
- No hacer `git push` sin que el usuario apruebe

## Referencias útiles

- `docs/migracion-tat/decisiones-alcance.md` — scope general del proyecto
- `docs/migracion-tat/inventario-terminos.md` — términos del dominio
- `public/js/image-compress.js` — compresor client-side actual
- `app/Views/client/inspecciones/layout.php` — layout que ya lo incluye

---

## Estado de ejecución (2026-04-22)

### ✅ Completado

1. **Helper server-side** — `app/Helpers/image_helper.php`
   - Función `compress_uploaded_image(path, maxDim=1600, jpegQuality=78)`
   - GD, respeta EXIF orientation (rotación automática), preserva PNG con alpha <500KB
   - Skip si archivo ya es chico/liviano (<400KB y ≤maxDim sin rotación)
   - Loguea a `log_message('info', ...)` cada compresión
   - Registrado en `app/Config/Autoload.php` como helper 'image' (autocarga global)

2. **Cobertura client-side** — `image-compress.js` agregado a:
   - `app/Views/inspecciones/layout_pwa.php` (cubre todas las vistas de inspecciones/*)
   - 13 vistas standalone: `consultant/{add,edit}_{client,consultant,vigia}.php`, `clients/onboarding.php`, `contracts/contrato_firma.php`, `firma/firmar.php`, `firma_alturas/form.php`, `informe_avances/form.php`, `hv-brigadista/form_publico.php`, `simulacro/form_publico.php`
   - El layout `client/inspecciones/layout.php` ya lo tenía; cubre las vistas POES (almacenamiento, contaminación, equipos, recepcion-mp) que se renderizan vía ese layout

3. **Integración en controladores**:
   - **Client**: `ClientNeverasController` (uploadFoto), `ClientLimpiezaLocalController` (uploadFoto), `ClientTrabajadoresController::uploadSoporte`, `ClientBomberosController::uploadDocumento`, `ClientOnboardingController::moveFile`
   - **Consultant/Admin**: `ConsultantController` (addClientPost, updateClient, photos/firmas, fileFields), `AdminDashboardController` (logo+firma del cliente), `ConsultantDashboardController` (logo+firma), `VigiaController` (firma vigía)
   - **Inspecciones sin trait**: `SimulacroPublicoController`, `HvBrigadistaPublicoController`, `InformeAvancesController::uploadFoto`, `CertificadoServicioController`, `PlanillaSSController`
   - **Inspecciones con `ImagenCompresionTrait`** (18 controladores: KpiResiduos/Plagas/Limpieza/AguaPotable, DotacionAseadora/Todero/Vigilante, InspeccionGabinete/Botiquin/BotiquinTipoA/Comunicacion/RecursosSeguridad, HvBrigadista, EvaluacionSimulacro, PreparacionSimulacro, PlanEmergencia, ReporteCapacitacion, AuditoriaZonaResiduos) → **NO se duplicó**: el trait existente (`app/Traits/ImagenCompresionTrait.php`, quality 70, max 1200, EXIF) ya hace el trabajo. Evita doble compresión.

4. **Script CLI retroactivo** — `app/SQL/cleanup_uploads_huge.php`
   - Dry-run por default, flag `--apply`, `--min=KB`, `--dir=subdir`
   - Recorre `public/uploads/`, encuentra imágenes >1MB, muestra dry-run o comprime
   - Imprime ahorro total en MB al final

5. **Vista admin** — `/admin/uploads-stats`
   - Controller: `app/Controllers/AdminUploadsStatsController.php`
   - Vista: `app/Views/admin/uploads/stats.php`
   - Ruta registrada en `Routes.php` (solo rol admin)
   - Muestra: total archivos/bytes, top 20 pesados, crecimiento mensual (12m), desglose por extensión

### 🟡 Excluido intencionalmente (restricción del prompt)

- **Módulos Fase 5.3 / POES en desarrollo**: `ClientAlmacenamientoController` (POES 4.4), `ClientContaminacionController` (POES 4.2), `ClientEquiposController` (Fase 5.3a — Equipos y Utensilios), `ClientRecepcionMpController` (Fase 5.3b — POES 4.1)
  - Las vistas de esos módulos igual quedan cubiertas client-side vía `client/inspecciones/layout.php`
  - Para agregar server-side en el futuro: insertar `compress_uploaded_image()` después de cada `$file->move()` en esos controladores

### 🟢 Pendiente de verificación manual (usuario)

1. Subir foto 5MP desde móvil a `/client/neveras/{id}/medir` y verificar que el archivo en `public/uploads/` queda <400KB
2. Desactivar JS del navegador y subir la misma foto → confirmar que el helper server-side comprime (ver `writable/logs/log-*.log`)
3. Subir PDF a `/client/trabajadores/.../soportes` → debe quedar intacto (el helper detecta que no es imagen)
4. Subir PNG con canal alpha (<500KB) → debe preservarse la transparencia

### 📊 Métricas

- Líneas nuevas: ~600 (helper + CLI + controller + vista + script tags)
- Archivos nuevos: 4 (`image_helper.php`, `cleanup_uploads_huge.php`, `AdminUploadsStatsController.php`, `admin/uploads/stats.php`)
- Archivos modificados: ~35 (vistas con script + controladores con helper + Routes + Autoload)
- No se superó el límite de 1200 LOC establecido en restricciones.
