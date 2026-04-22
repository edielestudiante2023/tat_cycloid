# Inventario de términos de Propiedad Horizontal (PH) en TAT Cycloid

**Fecha del inventario:** 2026-04-19
**Alcance:** Código PHP — vistas (`app/Views/**`) y controladores (`app/Controllers/**`), con foco en texto visible al cliente y en la generación de PDFs.
**Fuera de alcance en esta iteración:** migraciones SQL, nombres de tablas/columnas en BD, archivos en `app/SQL/`, assets estáticos (CSS/JS/imágenes).

---

## 1. Resumen ejecutivo

El aplicativo está fuertemente anclado al dominio de **propiedad horizontal** (conjuntos residenciales, edificios). Los términos PH aparecen en **tres capas**:

1. **Texto visible al cliente** — labels de formularios, títulos de PDF, párrafos narrativos en documentos SG-SST y planes de emergencia/contingencia. Esta es la capa **más extensa y más crítica** para el cambio de nicho.
2. **Texto generado por IA / prompts de sistema** — el chat Otto y la redacción automática de reportes explícitamente describen al cliente como "copropiedad" / "conjunto residencial".
3. **Identificadores en código** — variables de sesión, campos de formulario, nombres de parámetros POST (`nombre_copropiedad`, `flujo_residente`, `parqueaderos_carros_residentes`, etc.). Requieren refactor técnico, no solo cambio de copy.

También se detectó una **migración parcial ya iniciada**: varios archivos mezclan "copropiedad / conjunto residencial" con "tienda a tienda" en el mismo párrafo (ver [h1_1_1saneamiento.php](../../app/Views/client/sgsst/1planear/h1_1_1saneamiento.php), [gabinetes/pdf.php](../../app/Views/inspecciones/gabinetes/pdf.php), [plan-saneamiento/pdf.php](../../app/Views/inspecciones/plan-saneamiento/pdf.php)). Conviene consolidar el glosario TAT antes de continuar reemplazos aislados.

---

## 2. Glosario: términos PH detectados y su rol

| Término PH | Rol en el dominio original | Uso en el código |
|---|---|---|
| **copropiedad / copropiedades** | Nombre genérico del cliente (el edificio/conjunto como persona jurídica) | Label de campos, título de sección en PDFs, narrativa de políticas, nombre de variable de sesión |
| **propiedad horizontal / propiedades horizontales** | Régimen jurídico (Ley 675 de 2001 — Col.) | Párrafos introductorios en PDFs de inspección |
| **copropietario / copropietarios** | Dueño de unidad privada | Uso puntual (recursos-seguridad) |
| **residente / residentes** | Habitante de una unidad | Texto narrativo masivo, label en `plan_emergencia`, parámetros `parqueaderos_carros_residentes`, `flujo_residente`, dropdowns de cronogramas |
| **conjunto / conjunto residencial** | Sinónimo coloquial de copropiedad | Default value (`CONJUNTO RESIDENCIAL`), placeholder (`[NOMBRE DEL CONJUNTO]`), títulos |
| **unidad residencial / unidades privadas** | Apartamento/casa | Textos narrativos |
| **áreas comunes / zonas comunes** | Espacios de uso colectivo (Ley 675) | Estructuralmente embebido en programas de saneamiento, limpieza, plagas |
| **consejo de administración** | Órgano de gobierno de la copropiedad | Labels de onboarding, narrativas, dropdowns, prompts IA |
| **administrador (de la copropiedad)** | Representante legal | Narrativas, políticas, prompts IA |
| **asamblea** | Reunión de propietarios | Dropdown de perfiles de asistentes a cronogramas |
| **torres, parqueaderos** | Elementos arquitectónicos típicos | Campos de inspección plan de emergencia |

---

## 3. Inventario por módulo

### 3.1. Portal del cliente (Chat / Dashboard)

- [app/Views/client/chat.php](../../app/Views/client/chat.php)
  - `L438` — header muestra `nombre_copropiedad` del usuario
  - `L463` — mensaje de bienvenida: *"Puedo consultarte el estado de seguridad y salud en el trabajo de **tu copropiedad**"*
- [app/Controllers/ClientChatController.php](../../app/Controllers/ClientChatController.php)
  - `L64, L87, L111, L236, L298` — variable de sesión `nombre_copropiedad`
  - `L139, L222, L235, L275, L298–L377` — prompt del sistema para el LLM explícitamente orientado a "residente y administrador" de copropiedad, con REGLA ABSOLUTA de filtro por nombre de copropiedad
- [app/Controllers/AuthController.php](../../app/Controllers/AuthController.php#L66) — al login, guarda `nombre_copropiedad` en sesión desde `nombre_cliente`
- [app/Controllers/ChatController.php](../../app/Controllers/ChatController.php)
  - `L826` — *"Cliente / copropiedad: ¿para qué cliente o conjunto residencial?"*
  - `L889` — *"Clientes = conjuntos residenciales / edificios / copropiedades"*

### 3.2. Onboarding / Gestión de clientes (consultor)

- [app/Views/clients/onboarding.php](../../app/Views/clients/onboarding.php#L306) — label *"Correo Consejo de Administración"*
- [app/Views/consultant/add_client.php](../../app/Views/consultant/add_client.php#L340) — idem
- [app/Views/consultant/edit_client.php](../../app/Views/consultant/edit_client.php#L463) — idem

### 3.3. Cronogramas (perfiles de asistentes)

- [app/Views/consultant/list_cronogramas.php](../../app/Views/consultant/list_cronogramas.php#L1568) — array hardcodeado: `['CONTRATISTAS', 'RESIDENTES', 'TODOS', 'ASAMBLEA', 'CONSEJO DE ADMINISTRACIÓN', 'ADMINISTRADOR']`
- [app/Views/consultant/add_cronograma.php](../../app/Views/consultant/add_cronograma.php#L196) — placeholder *"Ej: TODOS, CONTRATISTAS, RESIDENTES"*
- [app/Views/consultant/edit_cronograma.php](../../app/Views/consultant/edit_cronograma.php#L204) — idem

### 3.4. Formularios públicos (captura de datos por el asistente)

- [app/Views/simulacro/form_publico.php](../../app/Views/simulacro/form_publico.php)
  - `L99, L102, L104, L106, L415, L489` — label, header, placeholder y `<option>` con *"Copropiedad"* / *"Busque su copropiedad"*
- [app/Views/hv-brigadista/form_publico.php](../../app/Views/hv-brigadista/form_publico.php)
  - `L86, L88, L90, L335, L561` — idem, incluida validación de campo requerido `'Copropiedad'`
- [app/Controllers/HvBrigadistaPublicoController.php](../../app/Controllers/HvBrigadistaPublicoController.php#L56) — mensaje de error *"Copropiedad"*

### 3.5. Inspecciones — labels en vistas "info card" (tabla de encabezado de inspección)

Todos estos archivos muestran `"Copropiedad: <nombre_cliente>"` como primer dato de la inspección, tanto en la vista interna (consultor) como en la vista cliente:

- [inspecciones/simulacro/view.php#L37](../../app/Views/inspecciones/simulacro/view.php#L37)
- [inspecciones/simulacro/pdf.php#L104](../../app/Views/inspecciones/simulacro/pdf.php#L104)
- [inspecciones/hv-brigadista/view.php#L42](../../app/Views/inspecciones/hv-brigadista/view.php#L42)
- [inspecciones/hv-brigadista/pdf.php#L104](../../app/Views/inspecciones/hv-brigadista/pdf.php#L104)
- [firma_alturas/pdf.php#L78](../../app/Views/firma_alturas/pdf.php#L78)
- Vistas cliente: `simulacro_view.php`, `reporte_capacitacion_view.php`, `preparacion_simulacro_view.php`, `hv_brigadista_view.php`, `dotacion_vigilante_view.php`, `dotacion_todero_view.php`, `dotacion_aseadora_view.php`, `auditoria_zona_residuos_view.php`, `asistencia_induccion_view.php` (todas en `app/Views/client/inspecciones/`)

### 3.6. Inspecciones — PDFs con narrativa PH en cuerpo del documento

PDFs cuyo cuerpo/introducción menciona "copropiedad", "conjunto residencial", "residentes" o "propiedades horizontales":

| PDF | Ubicación | Observación |
|---|---|---|
| firma-alturas | [firma_alturas/pdf.php](../../app/Views/firma_alturas/pdf.php) L78, 104, 108, 115 | Protocolo y responsabilidad de copropiedad |
| simulacro | [inspecciones/simulacro/pdf.php](../../app/Views/inspecciones/simulacro/pdf.php) L68, 71, 104 | "copropiedades atendidas por CYCLOID" |
| hv-brigadista | [inspecciones/hv-brigadista/pdf.php](../../app/Views/inspecciones/hv-brigadista/pdf.php) L67, 70, 74, 79, 104 | Brigada de emergencias "dentro de la copropiedad" |
| botiquín | [inspecciones/botiquin/pdf.php](../../app/Views/inspecciones/botiquin/pdf.php) L82, 94, 98, 99, 100, 105 | Obligatoriedad de botiquín tipo B en PH |
| plan-emergencia | [inspecciones/plan-emergencia/pdf.php](../../app/Views/inspecciones/plan-emergencia/pdf.php) L133, 143, 159, 330, 354, 425, 478, 483, 510, 592, 609, 633 | Mayor concentración de texto PH |
| plan-saneamiento | [inspecciones/plan-saneamiento/pdf.php](../../app/Views/inspecciones/plan-saneamiento/pdf.php) L186, 188, 192, 196, 200, 211, 220 | Ley 675 de 2001 citada explícitamente |
| plan-saneamiento presentación | [inspecciones/plan-saneamiento/presentacion.php](../../app/Views/inspecciones/plan-saneamiento/presentacion.php) L115 | `[NOMBRE DEL CONJUNTO]` como placeholder |
| residuos-sólidos | [inspecciones/residuos-solidos/pdf.php](../../app/Views/inspecciones/residuos-solidos/pdf.php) L135 (default `CONJUNTO RESIDENCIAL`), 179, 182, 196, 205, 245, 264, 267, 324, 328, 341, 350, 396–409, 468, 477, 490, 522 | Campo `flujo_residente` + default |
| residuos-sólidos form | [inspecciones/residuos-solidos/form.php](../../app/Views/inspecciones/residuos-solidos/form.php) L18, 55, 58, 62, 63, 153 | Label y placeholder "flujo del residente" |
| residuos-sólidos presentación | [inspecciones/residuos-solidos/presentacion.php](../../app/Views/inspecciones/residuos-solidos/presentacion.php) L92, 103, 171, 206, 229, 233, 244 | `[NOMBRE DEL CONJUNTO]` |
| limpieza-desinfección | [inspecciones/limpieza-desinfeccion/pdf.php](../../app/Views/inspecciones/limpieza-desinfeccion/pdf.php) L178, 179, 183, 186, 214, 238, 265, 274, 374, 532, 565 | "Administrador(a) de la Copropiedad" |
| limpieza-desinfección presentación | [inspecciones/limpieza-desinfeccion/presentacion.php](../../app/Views/inspecciones/limpieza-desinfeccion/presentacion.php) L206, 209, 224, 225, 268, 271, 399 | Mezcla ya "Tienda a Tienda" |
| control-plagas | [inspecciones/control-plagas/pdf.php](../../app/Views/inspecciones/control-plagas/pdf.php) L171, 175, 176, 205, 289, 307, 323, 440, 484 | "plagas más comunes en copropiedades" |
| control-plagas presentación | [inspecciones/control-plagas/presentacion.php](../../app/Views/inspecciones/control-plagas/presentacion.php) L90, 101, 203 | |
| agua-potable | [inspecciones/agua-potable/pdf.php](../../app/Views/inspecciones/agua-potable/pdf.php) L146 (default), 235 | Default `CONJUNTO RESIDENCIAL` |
| agua-potable presentación | [inspecciones/agua-potable/presentacion.php](../../app/Views/inspecciones/agua-potable/presentacion.php) L139, 140, 196 | |
| contingencia-agua | [inspecciones/contingencia-agua/pdf.php](../../app/Views/inspecciones/contingencia-agua/pdf.php) L61, 65, 102, 128, 130, 136, 142, 144, 157, 160, 168, 170 | Default fallback `'la copropiedad'` |
| contingencia-basura | [inspecciones/contingencia-basura/pdf.php](../../app/Views/inspecciones/contingencia-basura/pdf.php) L62, 66, 111, 129, 136, 137, 142, 159, 166, 169, 170, 179, 181 | "INSTRUCCIONES PARA RESIDENTES" |
| contingencia-plagas | [inspecciones/contingencia-plagas/pdf.php](../../app/Views/inspecciones/contingencia-plagas/pdf.php) L65, 162, 173, 177, 188, 199 | |
| comunicaciones | [inspecciones/comunicaciones/pdf.php](../../app/Views/inspecciones/comunicaciones/pdf.php) L75, 80–109 | Bloque extenso sobre "propiedades horizontales" |
| recursos-seguridad | [inspecciones/recursos-seguridad/pdf.php](../../app/Views/inspecciones/recursos-seguridad/pdf.php) L75, 80, 98, 102 | "condominios y conjuntos residenciales" |
| señalización | [inspecciones/senalizacion/pdf.php](../../app/Views/inspecciones/senalizacion/pdf.php) L110, 113, 119 | Líneas largas |
| gabinetes | [inspecciones/gabinetes/pdf.php](../../app/Views/inspecciones/gabinetes/pdf.php) L102, 104, 113 | Mezcla "tienda a tienda" + "unidad residencial" |
| dotación-vigilante | [inspecciones/dotacion-vigilante/pdf.php](../../app/Views/inspecciones/dotacion-vigilante/pdf.php) L131 | |
| auditoría-zona-residuos | [inspecciones/auditoria-zona-residuos/pdf.php](../../app/Views/inspecciones/auditoria-zona-residuos/pdf.php) L94 | |
| inspección-locativa | [inspecciones/inspeccion_locativa/pdf.php](../../app/Views/inspecciones/inspeccion_locativa/pdf.php) L96, 99, 104, 112, 115 | |
| probabilidad-peligros | [inspecciones/probabilidad-peligros/pdf.php](../../app/Views/inspecciones/probabilidad-peligros/pdf.php) L166, 169, 172, 178 | Líneas largas |
| carta-vigia | [inspecciones/carta_vigia/pdf.php](../../app/Views/inspecciones/carta_vigia/pdf.php) L94, 112 | |

### 3.7. Plan de emergencia — formulario (campos estructurales)

- [inspecciones/plan-emergencia/form.php](../../app/Views/inspecciones/plan-emergencia/form.php)
  - `L162, L165` — sección `AREAS COMUNES`
  - `L218` — label *"Cuantas salidas de emergencia tiene la copropiedad"*
- [inspecciones/plan-emergencia/view.php](../../app/Views/inspecciones/plan-emergencia/view.php) L108, L111
- [client/inspecciones/plan_emergencia_view.php](../../app/Views/client/inspecciones/plan_emergencia_view.php) L84, 86, 105, 108 — *"Carros residentes"*, *"Motos residentes"*, *"ÁREAS COMUNES"*

Campos en BD / POST (sólo se listan porque afectan el HTML visible):
- `parqueaderos_carros_residentes`, `parqueaderos_motos_residentes`
- `parqueaderos_carros_visitantes`, `parqueaderos_motos_visitantes`

### 3.8. Documentos SG-SST (planear) — el bloque más denso de narrativa PH

Todas estas vistas son documentos generados para el cliente (políticas, programas, responsabilidades). Contienen párrafos extensos de texto orientado a copropiedad / conjunto residencial / residentes.

Archivos con aparición múltiple y sustantiva (ver grep completo en §5):
- [client/sgsst/1planear/p2_1_1politicasst.php](../../app/Views/client/sgsst/1planear/p2_1_1politicasst.php) — Política SST
- [client/sgsst/1planear/p2_1_2politicaalcohol.php](../../app/Views/client/sgsst/1planear/p2_1_2politicaalcohol.php) — Política alcohol/drogas
- [client/sgsst/1planear/p2_1_3politicaemergencias.php](../../app/Views/client/sgsst/1planear/p2_1_3politicaemergencias.php) — Política emergencias
- [client/sgsst/1planear/p2_1_4politicaepps.php](../../app/Views/client/sgsst/1planear/p2_1_4politicaepps.php) — Política EPPs
- [client/sgsst/1planear/p2_2_1objetivos.php](../../app/Views/client/sgsst/1planear/p2_2_1objetivos.php) — Objetivos SG-SST
- [client/sgsst/1planear/p2_5_4manproveedores.php](../../app/Views/client/sgsst/1planear/p2_5_4manproveedores.php) — Manual de proveedores
- [client/sgsst/1planear/p1_2_1prgcapacitacion.php](../../app/Views/client/sgsst/1planear/p1_2_1prgcapacitacion.php) — Programa capacitación
- [client/sgsst/1planear/p1_2_2prginduccion.php](../../app/Views/client/sgsst/1planear/p1_2_2prginduccion.php) — Programa inducción
- [client/sgsst/1planear/p1_2_3ftevaluacioninduccion.php](../../app/Views/client/sgsst/1planear/p1_2_3ftevaluacioninduccion.php) — Evaluación inducción (preguntas con opciones que mencionan "residentes")
- [client/sgsst/1planear/p1_1_3vigia.php](../../app/Views/client/sgsst/1planear/p1_1_3vigia.php) — Responsabilidades vigía SST
- [client/sgsst/1planear/p1_1_4exoneracion_cocolab.php](../../app/Views/client/sgsst/1planear/p1_1_4exoneracion_cocolab.php) — Exoneración COCOLAB
- [client/sgsst/1planear/h1_1_1saneamiento.php](../../app/Views/client/sgsst/1planear/h1_1_1saneamiento.php) — Plan saneamiento (ya con mezcla "tienda a tienda")
- [client/sgsst/1planear/h1_1_3repoaccidente.php](../../app/Views/client/sgsst/1planear/h1_1_3repoaccidente.php) — Reporte accidente
- [client/sgsst/1planear/h1_1_5funcionesyresp.php](../../app/Views/client/sgsst/1planear/h1_1_5funcionesyresp.php) — **30+ apariciones**, tabla de responsabilidades anclada a roles PH
- [client/sgsst/1planear/h1_1_7identfpeligriesg.php](../../app/Views/client/sgsst/1planear/h1_1_7identfpeligriesg.php) — Identificación peligros

### 3.9. Controladores con texto PH embebido o prompts IA

| Controlador | Líneas | Tipo |
|---|---|---|
| [FirmaAlturasController.php](../../app/Controllers/FirmaAlturasController.php) | 312, 321, 325, 335 | HTML embebido en email de firma |
| [InformeAvancesController.php](../../app/Controllers/InformeAvancesController.php) | 744 | HTML embebido en informe |
| [Inspecciones/AsistenciaInduccionController.php](../../app/Controllers/Inspecciones/AsistenciaInduccionController.php) | 757, 765 | Prompt de IA: *"propiedades horizontales colombianas (conjuntos residenciales y edificios)"* |
| [Inspecciones/MatrizVulnerabilidadController.php](../../app/Controllers/Inspecciones/MatrizVulnerabilidadController.php) | 24, 32–34, 68, 86, 122, 165, 203, 204, 210, 219, 230, 237, 248, 271, 273, 275 | Preguntas y descripciones de la matriz; comentario L24: *"Terminologia adaptada a copropiedad/tienda a tienda"* (ya se intentó, inconsistente) |
| [Inspecciones/PlanContingenciaAguaController.php](../../app/Controllers/Inspecciones/PlanContingenciaAguaController.php) | 267 | Texto de sugerencia (hint) |
| [Inspecciones/PlanEmergenciaController.php](../../app/Controllers/Inspecciones/PlanEmergenciaController.php) | 376–377 | Nombres de campos `parqueaderos_*_residentes` |
| [Inspecciones/ProgramaResiduosController.php](../../app/Controllers/Inspecciones/ProgramaResiduosController.php) | 82, 129, 245 | Campo `flujo_residente` |
| [Inspecciones/InspeccionRecursosSeguridadController.php](../../app/Controllers/Inspecciones/InspeccionRecursosSeguridadController.php) | 60 | Hint: *"Seguridad de Residentes"* |

---

## 4. Clasificación por tipo de aparición (para planear estrategia de cambio)

| Categoría | Acción sugerida | Esfuerzo |
|---|---|---|
| **A. Labels UI simples** (ej. `"Copropiedad:"` → `"Tienda / Local:"`) | Reemplazo directo de string | Bajo |
| **B. Narrativa de PDFs / documentos SG-SST** | Reescritura de párrafos; muchos citan normativa PH específica (Ley 675/2001, etc.) que no aplica a comercio minorista | **Alto** |
| **C. Defaults y placeholders** (`CONJUNTO RESIDENCIAL`, `[NOMBRE DEL CONJUNTO]`) | Reemplazo con valor genérico o del cliente | Bajo |
| **D. Variables de sesión, campos POST, parámetros** (`nombre_copropiedad`, `flujo_residente`, `parqueaderos_*_residentes`) | Refactor técnico + posibles migraciones BD | Medio–Alto |
| **E. Enums hardcoded** (dropdown cronogramas con `'RESIDENTES', 'ASAMBLEA', 'CONSEJO DE ADMINISTRACIÓN'`) | Definir nueva lista de perfiles TAT (ej. `CLIENTES`, `EMPLEADOS DE TIENDA`, `PROPIETARIO`, `PROVEEDORES`) | Bajo |
| **F. Prompts de IA (Otto, AsistenciaInduccion)** | Reescribir system prompt; validar resultados | Medio |
| **G. Referencias legales específicas de PH** (Ley 675 de 2001, régimen de PH) | Decidir si se reemplazan con normativa comercial (Ley 232 de 1995, Decreto 1879 de 2008) o se eliminan | **Alto — requiere criterio legal** |

---

## 5. Archivos completos afectados (listado consolidado)

**Totales aproximados:**
- Vistas con "copropiedad/copropietario": ~50 archivos
- Vistas con "propiedad horizontal": 8 archivos
- Vistas con "residente/conjunto/áreas comunes": ~50 archivos adicionales
- Controladores con texto PH o prompts IA: 9 archivos

*(Para consultar el detalle de líneas, ejecutar los greps del §6; evito duplicar aquí 300+ líneas de output.)*

---

## 6. Comandos de reproducción

Para regenerar este inventario desde cero (útil cuando se hagan reemplazos y se quiera medir avance):

```bash
# Términos PH-puros en vistas
rg -in "copropiedad|copropietario|propiedad horizontal|propiedades horizontales" app/Views

# Términos semánticos de PH en vistas
rg -in "residente|conjunto residencial|unidad residencial|áreas? comunes|zonas? comunes|consejo de administración|asamblea" app/Views

# En controladores (prompts IA, HTML embebido, nombres de campos)
rg -in "copropiedad|residente|propiedad horizontal|conjunto residencial" app/Controllers
```

---

## 7. Decisiones de alcance

Los cuatro puntos que originalmente quedaron abiertos al final de este inventario fueron resueltos en sesión conjunta con el usuario el 2026-04-19 y consolidados (con alcance ampliado) en:

→ **[decisiones-alcance.md](decisiones-alcance.md)** — glosario TAT definitivo, módulos que se conservan / crean / ocultan, normativa legal de reemplazo, extensión de `tbl_clientes`, política de refactor de código y roadmap propuesto.
