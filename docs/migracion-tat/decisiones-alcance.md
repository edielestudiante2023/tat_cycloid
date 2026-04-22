# Decisiones de alcance — Migración TAT Cycloid

**Fecha:** 2026-04-19
**Documento relacionado:** [inventario-terminos.md](inventario-terminos.md)
**Autores de la decisión:** Usuario + Claude (sesión conjunta de alineación)

---

## 0. Contexto

TAT Cycloid es un fork del aplicativo **enterprisesstph** (orientado a **Propiedad Horizontal** — conjuntos residenciales, edificios, régimen Ley 675 de 2001). El fork se enfoca en un nicho nuevo: **tiendas y locales comerciales** (TAT = "Tienda a Tienda").

Este documento consolida las decisiones sobre **qué se conserva, qué se crea y qué se oculta**, además del glosario terminológico y la normativa legal que sustituye al régimen PH.

Referencias de validación del alcance:
- Formulario de **Bomberos Soacha** (permiso de visita técnica a establecimientos comerciales): <https://www.alcaldiasoacha.gov.co/AtencionalCiudadano/Paginas/Visita-Bomberos.aspx>
- **Acta de Inspección Sanitaria con Enfoque de Riesgo para Preparación de Alimentos** — SL-FR-019 (Secretaría de Salud Soacha, formato aplicado a panaderías, restaurantes, etc.)

---

## 1. Rebrand visual

| Aspecto | Decisión |
|---|---|
| Color primario | **Naranja** (reemplaza la paleta actual) |
| Nombre mostrado | Se conserva **"TAT Cycloid"** — no cambia |
| Razón social del prestador (pie de PDFs) | Se conserva **"Cycloid Talent SAS"** |

---

## 2. Glosario terminológico (reemplazos)

| Término PH original | Reemplazo TAT |
|---|---|
| copropiedad | **establecimiento comercial** |
| copropiedades (plural) | **establecimientos comerciales** |
| conjunto residencial | **`tbl_clientes.nombre_cliente`** (razón social del tendero) |
| residentes | **clientes y trabajadores del local comercial** |
| residente (sing.) | **cliente / trabajador del local** |
| consejo de administración | **eliminar** (el tendero no tiene este órgano) |
| administrador de la copropiedad | **`tbl_clientes.nombre_cliente`** (el tendero *es* el responsable) |
| asamblea | **eliminar** |
| áreas comunes / zonas comunes | **áreas del establecimiento** |
| unidades privadas | **zona del establecimiento** (si aplica) |

**Nota importante:** los **identificadores de código** (variables de sesión, campos POST, nombres de columnas) **no se renombran** — ver §7. El glosario aplica al texto visible.

---

## 3. Inventario de módulos

### 3.1. Conservados en UI (visibles al cliente)

Todos estos módulos deben ser **adaptados a contexto de local comercial** (tamaño, giro, normativa):

#### Inspecciones
- **Botiquines tipo A y tipo B** — ya adelgazadas previamente
- **Extintores** — patrón n-items ya existente; solo aterrizar al contexto del local comercial (típicamente 1–2 extintores)
- **Inspección Locativa**

#### Documentos / programas
- **SG-SST visible al cliente**
- **Plan de Saneamiento** (documento maestro)
- **Programa de manejo de plagas** + KPI
- **Programa de agua potable** + KPI
- **Programa de limpieza y desinfección** + KPI
- **Programa de manejo de residuos** + KPI

#### Operativos
- **Cronograma de capacitaciones** — los perfiles de asistentes ("residentes", "consejo de administración", "asamblea") deben reemplazarse por los aplicables al local (trabajadores, propietario, etc.)
- **Vencimientos de mantenimientos**
- **Pendientes**
- **Asignación de Vigía SST con firmas**
- **Acta de visita** (del consultor al local)
- **Reporte de capacitación / Asistencia inducción**

### 3.2. Nuevos (a crear)

#### Módulo Trabajadores
Con **4 slots de carga de soportes** (archivos PDF e imágenes). Los archivos se almacenan tanto en el registro del trabajador como en el **módulo reportlist**.

| Slot | Descripción |
|---|---|
| Datos del trabajador | Identificación + información básica |
| Afiliación a salud | EPS / ARL / pensión |
| Manipulación de alimentos | Certificado/curso (aplica si el local maneja alimentos) |
| Dotación / EPP del manipulador | Foto del uniforme (color claro, gorro, tapabocas) — requisito ítem 3.3 del acta sanitaria |

#### Módulo Permisos de Bomberos
**Repositorio con medidor de completitud**, sin PDF generado.
- Captura datos del representante legal + establecimiento
- Carga de los **5 documentos obligatorios** (Soacha):
  1. Cédula del representante legal
  2. Recibo del impuesto predial
  3. Certificado de existencia y representación legal (Cámara de Comercio)
  4. RUT actualizado
  5. Concepto de uso de suelo
- Documentos **condicionales** según tipo de establecimiento (estaciones de servicio, centros comerciales, industrias, microempresas, instituciones educativas, hospitales/IPS, restaurantes, hoteles/moteles/discotecas): respuesta de la Oficina de Gestión del Riesgo.
- **Medidor "% completitud"** para que el consultor sepa cuándo radicar
- Flujo: una vez completo, el consultor radica **manualmente** en el portal de la alcaldía. Los PDFs de respuesta/concepto que emita bomberos se guardan en **reportlist**.

#### Inspecciones nuevas (patrón plana — ver [docs/12_PATRON_INSPECCION_PLANA.md](../12_PATRON_INSPECCION_PLANA.md))

| Inspección | Objetivo | Mapeo al acta sanitaria SL-FR-019 |
|---|---|---|
| **Control de neveras** | Registro de temperatura + humedad + foto evidencia | Ítem 4.3 Manejo de temperaturas |
| **Limpieza del Local** (transforma auditoría-zona-residuos) | Techos, paredes, pisos | Bloque 1 (edificaciones) + Bloque 5.5 (limpieza) |
| **Condiciones de Equipos y Utensilios** | Equipos de preparación + superficies de contacto con alimento | Bloque 2 completo (12%) |
| **POES Control de materias primas e insumos** | Recepción, verificación, rotación | Ítem 4.1 |
| **POES Prevención de contaminación cruzada** | Separación física, tiempos, temperaturas | Ítem 4.2 |
| **POES Condiciones de almacenamiento** | Estanterías, temperaturas, PEPS/FIFO | Ítem 4.4 |

### 3.3. Ocultos (comentados, NO eliminados)

**Estrategia:** comentar rutas, enlaces de menú y botones para que **no se vean en UI**, pero el código permanece, para evitar romper dependencias latentes.

Módulos/archivos a ocultar:
- Plan de emergencia
- Matriz de vulnerabilidad
- Simulacro
- Preparación-simulacro
- HV Brigadista (público y privado)
- Firma alturas
- Dotaciones: vigilante · todero · aseadora
- Auditoría zona residuos *(reemplazada por Inspección Limpieza del Local)*
- Señalización
- Gabinetes
- Recursos de seguridad
- Probabilidad de peligros
- Comunicaciones
- Contingencias: agua · basura · plagas
- Carta vigía
- Planilla seguridad social
- Módulo de firma general *(a menos que sea dependencia de la asignación del vigía)*

---

## 4. Normativa legal a citar

**Reemplaza la Ley 675 de 2001 (régimen de PH) por el marco aplicable a establecimientos de comercio y preparación de alimentos:**

| Norma | Alcance |
|---|---|
| **Resolución 2674 de 2013** | BPM — núcleo del acta sanitaria. Se cita transversalmente. |
| **Ley 9 de 1979** (Código Sanitario Nacional) — Arts. 293, 425 | Marco general, control de temperaturas |
| **Resolución 2115 de 2007** | Calidad de agua potable |
| **Decreto 1575 de 2007** | Soportes documentales de saneamiento |
| **Decreto 561 de 1984** | Manipulación de alimentos |
| **Resoluciones 5109/2005, 1506/2011** | Etiquetado y rotulado |
| **Resoluciones 683, 4142, 4143 de 2012; 834, 835 de 2013** | Aditivos, materiales contacto alimentos, rotulado |
| **Ley 1801 de 2016 Art. 87** (Código Nacional de Policía) | Condiciones de funcionamiento |
| **Ley 232 de 1995** | Condiciones para el funcionamiento de establecimientos de comercio |
| **Ley 1474 de 2011** (Anticorrupción) | Nota informativa: "el concepto sanitario no tiene costo" |

**Lo que deben decir los documentos TAT:** se eliminan todas las citas a Ley 675/2001 y se sustituyen por las de arriba, según el módulo:
- Programas de saneamiento → Res. 2674/2013 + norma específica del programa
- Agua potable → Res. 2115/2007 + Res. 2674/2013 Art. 6 y 26
- Residuos → Res. 2674/2013 Art. 6, 33; Ley 9/1979
- Plagas → Res. 2674/2013 Art. 26
- Limpieza y desinfección → Res. 2674/2013 Art. 6.5, 26
- Inspecciones locativas / equipos → Res. 2674/2013 Art. 6, 7, 8, 9, 10, 32, 33, 34

---

## 5. Extensión de `tbl_clientes`

Campos a añadir al formulario de alta/edición del cliente. Unifica exigencias de Secretaría de Salud + Bomberos para evitar formularios duplicados.

| Campo | Tipo | Fuente del requisito | Notas |
|---|---|---|---|
| Razón social | string | Ambos | Puede coincidir con el propietario si es persona natural |
| Nombre comercial | string | Salud (acta SL-FR-019) | Panadería/local/tienda |
| Cédula / NIT | string | Ambos | |
| Número de inscripción sanitaria | string | Salud | Asignado por la Sec. de Salud |
| Matrícula mercantil | string | Ambos | Cámara de Comercio |
| CIIU | string | Salud | Actividad económica |
| Departamento · Municipio · Comuna · Barrio · Dirección | strings | Ambos | |
| Propietario: nombre + tipo ID + número ID | strings | Ambos | |
| Representante legal: nombre + tipo ID + número ID | strings | Ambos | Puede coincidir con propietario |
| Correo electrónico · Teléfono 1 · Teléfono 2 | strings | Ambos | |
| Autoriza notificación electrónica | bool | Salud | Si/No |
| Horario de funcionamiento | string | Salud | |
| Número de trabajadores | integer | Ambos | |
| Tipo de establecimiento | enum | Bomberos | Catálogo Bomberos: panadería, droguería, ferretería, restaurante, estación de servicio, centro comercial, hotel, discoteca, etc. |
| Aforo | integer | Bomberos | |
| Área en m² | decimal | Bomberos | |

**Observación:** verificar qué campos ya existen en `tbl_clientes` antes de migrar — probablemente varios (dirección, correo, teléfono) ya estén y solo haga falta añadir los específicos.

---

## 6. Matriz de cobertura: alcance TAT vs. acta sanitaria SL-FR-019

Verificación de que los módulos del MVP cubren el 100% del acta que aplica la Secretaría de Salud:

| Bloque del acta | % acta | Módulo TAT que lo cubre |
|---|---|---|
| 1. Edificaciones e instalaciones | 10% | Inspección Locativa + Inspección Limpieza del Local |
| 2. Equipos y utensilios | 12% | Inspección Condiciones de Equipos y Utensilios (nueva) |
| 3.1 + 3.2 Estado salud + reconocimiento médico | ~9% | Módulo Trabajadores — slot "Afiliación a salud" |
| 3.3 Prácticas higiénicas / dotación | ~7% | Módulo Trabajadores — slot "Dotación / EPP" (nuevo) |
| 3.4 Educación y capacitación | ~4% | Reporte de capacitación + Cronograma de capacitaciones + Módulo Trabajadores — slot "Manipulación de alimentos" |
| 4.1 Control de materias primas | ~5% | Inspección POES MP (nueva) |
| 4.2 Prevención contaminación cruzada | ~9% | Inspección POES Contaminación cruzada (nueva) |
| 4.3 Manejo de temperaturas | ~7% | Inspección Control de neveras (nueva) |
| 4.4 Condiciones de almacenamiento | ~4% | Inspección POES Almacenamiento (nueva) |
| 5.1 Agua potable | 7% | Programa agua potable + KPI |
| 5.2 Residuos líquidos | 4% | Programa manejo de residuos |
| 5.3 Residuos sólidos | 4% | Programa manejo de residuos |
| 5.4 Control plagas | 9% | Programa manejo de plagas + KPI |
| 5.5 Limpieza y desinfección | 7% | Programa limpieza y desinfección + KPI |
| 5.6 Soportes documentales | 2% | Generación automática por la plataforma |

**Cobertura total:** 100%.

---

## 7. Política de refactor de código

**Regla:** los identificadores legacy **no se renombran**.

Concretamente, **se mantienen tal como están** (no tocar):
- Variables de sesión: `nombre_copropiedad`
- Campos de formulario / parámetros POST: `flujo_residente`, `parqueaderos_carros_residentes`, `parqueaderos_motos_residentes`, etc.
- Nombres de columnas en BD
- Nombres de clases / archivos / controladores

**Razón:** el costo de refactor técnico es alto, el beneficio es marginal, y el foco está en lo que ve el **cliente final** y la **Secretaría de Salud**, no en la legibilidad del código.

**Qué sí se cambia:**
- Texto visible (labels, títulos, mensajes, párrafos narrativos en PDFs)
- Prompts del sistema de IA (chat Otto, `AsistenciaInduccionController`)
- Dropdowns hardcoded de perfiles (cronogramas)
- Defaults y placeholders (`CONJUNTO RESIDENCIAL`, `[NOMBRE DEL CONJUNTO]`)

---

## 8. Roadmap sugerido (borrador — puede reordenarse)

Fases propuestas, cada una entregable por separado:

1. **Fase 1 — Rebrand + extensión cliente** *(infra)*: color naranja, extender `tbl_clientes`, adaptar formularios de alta/edición. Refactor del layout.
2. **Fase 2 — Ocultar módulos no aplicables**: comentar rutas/menús de los módulos listados en §3.3.
3. **Fase 3 — Glosario y textos existentes**: reemplazos de "copropiedad/conjunto residencial/residentes" en todos los módulos conservados (vistas + PDFs + prompts IA).
4. **Fase 4 — Nuevos módulos pequeños**: Trabajadores (4 slots), Permisos de Bomberos.
5. **Fase 5 — Inspecciones nuevas**: Control de neveras, Limpieza del Local, Equipos y Utensilios, POES 4.1 / 4.2 / 4.4.
6. **Fase 6 — Normativa legal**: reemplazo de citas a Ley 675/2001 por la normativa del §4, en todos los documentos SG-SST y programas.

   **Puntos PRIORITARIOS ya detectados para esta fase (2026-04-19):**
   - [inspecciones/plan-saneamiento/pdf.php:194](../../app/Views/inspecciones/plan-saneamiento/pdf.php#L194) — *"Ley 675 de 2001, que regula el régimen de Tienda a Tienda en Colombia"* — disparate legal. La Ley 675 regula Propiedad Horizontal, no comercio minorista. Reescribir el párrafo completo citando Ley 232/1995 + Res. 2674/2013.
   - [inspecciones/plan-emergencia/pdf.php:137](../../app/Views/inspecciones/plan-emergencia/pdf.php#L137) — *"Ley 675 de 2001 por medio de la cual se expide el Régimen de Tienda a Tienda"* — mismo problema. Reescribir cita.
   - Buscar patrón en vistas: `rg "Ley 675" app/Views` y `rg "régimen de" app/Views` para encontrar citas residuales.
   - Buscar "Decreto 1295 de 1994" y "Resolución 2013 de 1986" en `app/Views/client/sgsst/` — son normativa de empresas con trabajadores directos, aplicabilidad a tenderos depende del número de empleados (si >10).

Cada fase debe acordarse individualmente antes de iniciar, según la REGLA DE MÁXIMA PRIORIDAD de este proyecto.
