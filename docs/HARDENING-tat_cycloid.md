# HARDENING DE REPOSITORIO — tat_cycloid

**Fecha:** 2026-04-05
**Aplicativo:** tat_cycloid — Plataforma SST Tienda a Tienda (TAT)
**Empresa:** Cycloid Talent
**Preparado para:** Edwin Lopez (consultor de infraestructura)

---

## TABLA DE CONTENIDO

1. Descripcion del aplicativo
2. Mapa de base de datos
3. Inventario de API Keys y servicios externos
4. Documentacion del proyecto (README, CONTRIBUTING, .env.example)
5. Ramas de trabajo
6. Pipelines CI/CD
7. Organizacion del repositorio
8. Hallazgos criticos y acciones pendientes

---

## 1. DESCRIPCION DEL APLICATIVO

### Origen

`tat_cycloid` es un fork independiente de `enterprisesstph`. Mientras enterprisesstph atiende el segmento de Propiedad Horizontal, `tat_cycloid` atiende el segmento de Tienda a Tienda (TAT) — asesoria SST al tendero.

Comparten la misma base de codigo, pero cada uno tiene:
- Base de datos independiente
- Branding independiente (Cycloid TAT)
- Configuracion independiente (.env, dominio, deploy)

### Stack tecnologico

| Componente | Tecnologia |
|------------|-----------|
| Backend | PHP 8.2 + CodeIgniter 4 |
| Base de datos | MySQL 8 (DigitalOcean Managed, SSL required) |
| Servidor web | Nginx 1.24 (Ubuntu 24.04) |
| Email | SendGrid API v3 |
| PDF | TCPDF (contratos) + DOMPDF 3.0.0 (certificados, actas, inspecciones) |
| Excel | PhpSpreadsheet |
| IA | OpenAI GPT-4o-mini (Chat Otto, generacion de textos) |
| IA (tools) | Anthropic Claude Haiku (clasificacion de PDFs) |
| PWA | Modulo inspecciones (manifest + service worker) |
| Analytics | Looker Studio (embeds) |

### Modulos principales (16)

| Modulo | Descripcion |
|--------|-------------|
| Contratos | Ciclo completo: creacion, firma digital, PDF, renovacion, cancelacion |
| Plan de Trabajo Anual (PTA) | Actividades PHVA por cliente, edicion inline, exportacion Excel, auditoria |
| Evaluacion Estandares Minimos | Decreto 1072, evaluacion por ciclo, historial de puntajes |
| Actas de Visita | Registro con fotos, firma digital, PDF, notificaciones |
| Inspecciones (PWA) | Locativa, extintores, botiquin, gabinetes, senalizacion, comunicaciones, recursos |
| Capacitaciones | Cronograma, asistencia induccion, evaluacion, reportes |
| KPIs | 17 indicadores SST (frecuencia, severidad, mortalidad, ausentismo, etc.) |
| Pendientes | Compromisos con conteo de dias, recordatorios automaticos |
| Plan de Saneamiento | Limpieza, residuos, plagas, agua potable, contingencias, KPIs |
| Documentos SGSST | Documentos normativos (politicas, programas, formatos) |
| Matrices | Riesgos, vulnerabilidad, EPP (generacion automatica desde plantillas Excel) |
| Chat Otto (IA) | Asistente IA con function calling, consultas SQL readonly, 3 capas de seguridad |
| Presupuesto SST | Categorias, items, detalle de ejecucion |
| Informes de Avances | Reportes mensuales con metricas e imagenes |
| Firmas Digitales | Firma electronica via token por email (contratos + protocolo alturas) |
| Portal Cliente | Dashboard readonly, chat Otto, inspecciones, reportes, pendientes |

### Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultant | Gestion de clientes asignados + inspecciones + chat IA completo |
| client | Portal readonly + chat Otto (solo SELECT) |

### Estructura del proyecto

```
tat_cycloid/
├── app/
│   ├── Commands/          # 18 comandos spark (cron jobs + utilidades)
│   ├── Config/            # Routes.php, Database.php, Filters.php
│   ├── Controllers/       # ~171 controladores
│   │   └── Inspecciones/  # ~44 controladores de inspecciones (PWA)
│   ├── Filters/           # AuthFilter, ApiKeyFilter, AuthOrApiKeyFilter
│   ├── Libraries/         # 17 librerias de logica de negocio
│   ├── Models/            # ~111 modelos
│   ├── Services/          # IADocumentacionService, PtaAuditService, PtaTransicionesService
│   ├── SQL/               # Scripts de migracion
│   ├── Templates/         # Plantillas Excel para matrices
│   └── Views/             # Vistas PHP
├── docs/                  # ~60 documentos tecnicos
├── public/                # Punto de entrada web (index.php)
├── tools/                 # Scripts utilitarios
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones
├── .env                   # Variables de entorno (NO commitear)
├── deploy.sh              # Script de deploy
├── README.md              # Documentacion principal
└── spark                  # CLI de CodeIgniter
```

### Servidor de produccion

| Aspecto | Valor |
|---------|-------|
| IP | 66.29.154.174 |
| Hostname | server1.cycloidtalent.com |
| OS | Ubuntu 24.04.3 LTS |
| PHP | 8.2.28 |
| MySQL | MariaDB 10.11.13 (local) / MySQL 8 (DigitalOcean Managed) |
| Nginx | 1.24.0 |
| RAM | 1.9 GB total, 1.0 GB usada |
| Disco | 40 GB, 62% usado (15 GB disponibles) |
| Uptime | 179 dias |

**Nota:** El servidor es compartido con `enterprisesstph`. La auditoria detallada del servidor esta en `HARDENING-enterprisesstph.md`.

---

## 2. MAPA DE BASE DE DATOS

**Motor:** MySQL 8 (DigitalOcean Managed)
**Base de datos:** tat_cycloid
**Tamano total:** 5.34 MB
**SSL:** Required

### Usuarios de base de datos

| Usuario | Permisos | Uso |
|---------|----------|-----|
| cycloid_userdb | Full access | Aplicacion principal (CRUD) |
| cycloid_readonly | SELECT only (vistas v_* + tablas maestras) | Portal cliente (Chat Otto) |
| doadmin | Super admin | Administracion DigitalOcean |

### Resumen

- **107 tablas** (BASE TABLE)
- **75 vistas** (VIEW) — 66 con prefijo `v_` para portal cliente + 9 de negocio
- **71 foreign keys** definidas
- **~85 tablas vacias** (79%) — el sistema esta recien desplegado, con solo 2 clientes de prueba

### Tablas con datos (registros > 0)

| Tabla | Registros | Descripcion |
|-------|-----------|-------------|
| estandares_accesos | 80 | Accesos a estandares |
| dashboard_items | 67 | Items del dashboard |
| accesos | 59 | Permisos de acceso |
| client_policies | 42 | Politicas de cliente |
| policy_types | 44 | Tipos de politica |
| document_versions | 42 | Versiones de documentos |
| capacitaciones_sst | 31 | Catalogos de capacitaciones |
| tbl_presupuesto_items | 25 | Items de presupuesto |
| report_type_table | 20 | Tipos de reporte |
| tbl_kpi_definition | 17 | Definiciones de KPI |
| tbl_presupuesto_categorias | 8 | Categorias presupuesto |
| tbl_sesiones_usuario | 8 | Sesiones activas |
| tbl_usuarios | 6 | Usuarios del sistema |
| tbl_usuario_roles | 6 | Roles asignados |
| tbl_consultor | 4 | Consultores |
| tbl_kpi_type | 4 | Tipos de KPI |
| estandares | 4 | Estandares |
| tbl_roles | 3 | Roles (admin, consultant, client) |
| tbl_clientes | 2 | Clientes de prueba |
| tbl_measurement_period | 2 | Periodos de medicion |

### Tabla central: tbl_clientes

40+ tablas dependen de `tbl_clientes.id_cliente` via foreign key. Es la entidad central del sistema.

### Tablas principales por modulo

**Nucleo (7 tablas):** tbl_clientes (2 reg), tbl_usuarios (6), tbl_usuario_roles (6), tbl_roles (3), tbl_consultor (4), tbl_sesiones_usuario (8), tbl_chat_log (0)

**Plan de trabajo (5 tablas):** tbl_pta_cliente, tbl_pta_cliente_audit, tbl_pta_cliente_old, tbl_pta_transiciones, tbl_inventario_actividades_plandetrabajo — todas vacias

**Evaluacion estandares (5 tablas):** evaluacion_inicial_sst, estandares (4), estandares_accesos (80), historial_resumen_estandares, historial_resumen_plan_trabajo

**Reportes (5 tablas):** tbl_reporte, report_type_table (20), detail_report, document_versions (42), tbl_listado_maestro_documentos

**Actas de visita (5 tablas):** tbl_acta_visita, tbl_acta_visita_fotos, tbl_acta_visita_integrantes, tbl_acta_visita_pta, tbl_acta_visita_temas

**Inspecciones (12 tablas):** 6 master + 5 detalle + 1 recursos seguridad — todas vacias

**Capacitaciones (6 tablas):** capacitaciones_sst (31), tbl_cronog_capacitacion, tbl_reporte_capacitacion, tbl_asistencia_induccion + asistentes

**KPIs (10 tablas):** tbl_kpis, tbl_kpi_definition (17), tbl_client_kpi, + variables, periodos, objetivos

**Plan saneamiento (13 tablas):** programas (agua, limpieza, plagas, residuos) + KPIs + contingencias

**Otros:** contratos, pendientes, informe_avances, presupuesto (4 tablas), matrices, mantenimientos, vigias, dotaciones (3 tablas)

### Vistas de negocio (9)

cronograma_capacitaciones_cliente, evaluacion_inicial_cliente, evaluacion_inicial_cliente_consultor, mantenimientos_por_vencer, pendientes_abiertos_vencidos, pendientes_del_cliente, plan_de_trabajo_del_cliente, resumen_estandares_cliente, resumen_mensual_plan_trabajo, view_clientes_consultores, vista_cronograma_capacitaciones, vw_consumo_usuarios, vw_reporte_completo

### Vistas del portal cliente (66 con prefijo v_)

Todas las tablas principales tienen su vista `v_` correspondiente para acceso readonly del portal cliente.

---

## 3. INVENTARIO DE API KEYS Y SERVICIOS EXTERNOS

### Resumen

| Servicio | Variable | Archivos | Estado |
|----------|----------|----------|--------|
| SendGrid | `SENDGRID_API_KEY` | 20+ | Activa |
| OpenAI | `OPENAI_API_KEY` | 7 | Activa |
| Anthropic | `ANTHROPIC_API_KEY` | 1 | Activa (solo tools/) |
| Token API | `APP_API_KEY` | 2 | Activa — token interno |
| Token Cron | `CRON_TOKEN` | 1 | Activa |

### SendGrid

Usado en 20+ archivos para todo el email transaccional: recuperacion de password, notificaciones de firma, recordatorios de pendientes, auditorias, inspecciones, contratos, onboarding de clientes.

**Patron:** `new \SendGrid(getenv('SENDGRID_API_KEY'))`

**Archivos principales:**
- 10+ controladores (Auth, Chat, AdminDashboard, Email, FirmaElectronica, FirmaAlturas, Contract, Consultant, ClientOnboarding, InformeAvances, VencimientosMantenimiento, PlanillaSegSocial, SocializacionEmail)
- 3 libraries (ResumenPendientesNotificador, NotificadorVisita, InspeccionEmailNotifier)
- 4+ commands/cron (Seguimiento, Contratos, Pendientes, SinAgendar)
- Controladores de inspecciones (AgendamientoController, ActaVisitaController, CartaVigiaPwaController)

### OpenAI

Usado en 7 archivos para IA generativa: Chat Otto, generacion de clausulas de contratos, sugerencias PTA, analisis de pendientes, evaluaciones de capacitacion.

**Patron:** cURL directo a `https://api.openai.com/v1/chat/completions`
**Modelo:** gpt-4o-mini (configurable via `OPENAI_MODEL`)

**Archivos:**
- `app/Controllers/ChatController.php` — Chat Otto con function calling
- `app/Controllers/ContractController.php` — Generacion de clausulas
- `app/Controllers/PtaClienteNuevaController.php` — Sugerencias PTA
- `app/Controllers/PendientesController.php` — Analisis de pendientes
- `app/Controllers/Inspecciones/ReporteCapacitacionController.php` — Evaluaciones
- `app/Controllers/Inspecciones/AsistenciaInduccionController.php` — Evaluaciones
- `app/Services/IADocumentacionService.php` — Servicio centralizado

### Anthropic (Claude)

Solo 1 archivo: `tools/clasificar_pdfs.php` — clasificacion automatica de PDFs con Claude Haiku.

### HALLAZGOS CRITICOS DE SEGURIDAD

**CRITICO — Credenciales hardcodeadas en codigo:**

| Archivo | Problema |
|---------|----------|
| `app/Commands/ReplicateToTat.php:19` | Password BD produccion `AVNS_***REDACTED***` hardcodeado |
| `app/Commands/ReplicateDbStructure.php:19` | Password BD produccion hardcodeado |
| `app/Commands/CreateTestClient.php:18` | Password BD produccion hardcodeado |
| `app/Commands/FixTestClient.php:18` | Password BD produccion hardcodeado |
| `app/Commands/CreateUsuariosConsultores.php:18` | Password BD produccion hardcodeado |
| `app/Commands/CopyUsuarios.php:18` | Password BD produccion hardcodeado |
| `app/Commands/CopyConsultores.php:19` | Password BD produccion hardcodeado |
| `app/Config/Database.php:62` | Password readonly `CycloidPortal2026!` hardcodeado |
| `app/Controllers/UserController.php:288` | Fallback `SG.xxxxxx` hardcodeado |
| `app/SQL/query_pta_indicadores.php:5` | Password BD produccion `AVNS_***REDACTED_OLD***` (old) hardcodeado |
| `tools/cargar_pdfs_produccion.php:29,126` | Password BD produccion hardcodeado |
| `tools/clasificar_pdfs.php:418` | Password BD produccion hardcodeado |
| `tools/reparar_nombres_v2.php:22` | Password BD produccion hardcodeado |
| `tools/reparar_nombres_archivos.php:29` | Password BD produccion hardcodeado |

**Total: 14 archivos con credenciales hardcodeadas.**

**CRITICO — Repositorio PUBLICO:**

El repositorio `github.com/edielestudiante2023/tat_cycloid` es **PUBLICO**. El archivo `.env` esta en `.gitignore` pero las credenciales estan hardcodeadas en multiples archivos del codigo fuente. Ademas, algunos de estos archivos con credenciales estan en `.gitignore` (Commands de replicacion, tools/) pero otros NO:

**Archivos con credenciales que SI estan trackeados en git:**
- `app/Config/Database.php` — en `.gitignore` (OK)
- `app/Controllers/UserController.php:288` — **NO en .gitignore** (EXPUESTO)
- `app/SQL/query_pta_indicadores.php` — en `.gitignore` (OK)

**CRITICO — .env con credenciales reales:**

El `.env` local contiene:
- `SENDGRID_API_KEY=SG.6jqYPC-TRGeBhbVpjoazeQ...` (API Key real)
- `OPENAI_API_KEY=sk-proj-Efjaw4TAiR...` (API Key real)
- `APP_API_KEY=sst_4f480e90e9c67de074c6...` (Token real)
- `database.production.password=AVNS_***REDACTED_OLD***` (password BD produccion)

Aunque `.env` esta en `.gitignore`, si alguna vez se commiteo por error, las credenciales quedarian en el historial de git.

### Claves que DEBEN rotarse

| Variable | Accion |
|----------|--------|
| `SENDGRID_API_KEY` | Rotar |
| `OPENAI_API_KEY` | ROTAR INMEDIATAMENTE |
| `database.production.password` | Rotar en DigitalOcean (ya se uso `AVNS_***REDACTED***` nuevo) |
| `APP_API_KEY` | Regenerar |
| Password readonly `CycloidPortal2026!` | Cambiar y mover a .env |

---

## 4. DOCUMENTACION DEL PROYECTO

### Estado actual

| Archivo | Estado | Accion |
|---------|--------|--------|
| `README.md` | Existe pero es generico (CodeIgniter default) | **Reescribir** |
| `CONTRIBUTING.md` | No existe | **Crear** |
| `.env.example` | No existe | **Crear** |
| `docs/` | 60+ documentos tecnicos | OK |

### Archivos pendientes de crear

**README.md** — Debe incluir:
- Stack tecnologico completo
- 16 modulos con descripcion
- 3 roles de usuario con accesos
- Estructura de carpetas
- Requisitos previos e instrucciones de instalacion
- Variables de entorno documentadas
- Cron jobs con frecuencia y descripcion
- Instrucciones de deploy

**CONTRIBUTING.md** — Debe incluir:
- Flujo de ramas (main → develop → feature/ → hotfix/)
- Convencion de commits (feat:, fix:, docs:, refactor:, chore:)
- Convencion de nombres de ramas
- Reglas (no push directo, no credenciales, no temporales, no destructivos)
- Proceso de revision con pipeline CI/CD

**.env.example** — Debe incluir:
- Variables de entorno para BD principal y readonly
- API Keys de email (SendGrid), OpenAI, Anthropic
- Tokens internos (APP_API_KEY, CRON_TOKEN)
- Configuracion de BD de produccion (sin valores reales)

---

## 5. RAMAS DE TRABAJO

### Estado actual

| Rama | Estado | Commit actual |
|------|--------|---------------|
| main | En remoto (origin/main) | Commit inicial |
| cycloid | Local, rama activa de desarrollo | Rama de trabajo actual |

**No existe rama `develop`.** Todo el desarrollo se hace en `cycloid` y se pushea a `main` directamente.

### Estructura propuesta

```
main          ← Produccion. Solo codigo validado y estable.
develop       ← Integracion. Aqui se unen los cambios antes de ir a main.
feature/xxx   ← Nuevas funcionalidades. Se crean desde develop.
hotfix/xxx    ← Correcciones urgentes. Se crean desde main.
```

### Acciones pendientes

1. Crear rama `develop` a partir de `main`
2. Mergear `cycloid` a `develop` (o renombrar `cycloid` → `develop`)
3. Proteger `main`: requiere PR, no push directo
4. Proteger `develop`: requiere PR desde feature/
5. Documentar flujo en CONTRIBUTING.md

### Flujo de trabajo propuesto

- Nueva funcionalidad: `develop` → `feature/nombre` → PR a `develop` → PR a `main`
- Hotfix urgente: `main` → `hotfix/nombre` → PR a `main` + PR a `develop`

---

## 6. PIPELINES CI/CD

### Estado actual

No hay pipelines configurados. No existe carpeta `.github/workflows/` ni `.gitea/workflows/`.

### Propuesta (GitHub Actions, ya que el repo esta en GitHub)

**Pipeline 1: Validacion (en cada push/PR)**

Archivo: `.github/workflows/validate.yml`

| Job | Que hace | Bloquea si falla |
|-----|----------|------------------|
| syntax | `php -l` en todos los .php de app/ | Si |
| secrets-scan | Busca API keys hardcodeadas (SendGrid, OpenAI, Anthropic, DB passwords) | Si |

**Pipeline 2: Deploy a produccion (en merge a main)**

Archivo: `.github/workflows/deploy.yml`

| Job | Que hace |
|-----|----------|
| validate | Sintaxis PHP + busqueda de credenciales |
| deploy | SSH al servidor + deploy.sh |
| verify | Verificacion HTTP post-deploy |

### Secrets necesarios en GitHub

- `PROD_HOST`: 66.29.154.174
- `PROD_USER`: root
- `PROD_SSH_KEY`: Llave privada ed25519
- `PROD_PATH`: Ruta del proyecto en el servidor (por confirmar)

### deploy.sh

Ya existe un `deploy.sh` funcional, pero referencia a `enterprisesstph`. Debe actualizarse:
- Cambiar nombre de "DEPLOY SEGURO — enterprisesstph" a "DEPLOY SEGURO — tat_cycloid"
- Actualizar ruta de uploads si es diferente

---

## 7. ORGANIZACION DEL REPOSITORIO

### Estado del repositorio

| Aspecto | Estado actual | Accion |
|---------|--------------|--------|
| Visibilidad | **PUBLICO** en GitHub | **HACER PRIVADO INMEDIATAMENTE** |
| .gitignore | Parcialmente configurado | Falta agregar mas archivos basura |
| .env.example | No existe | **Crear** |
| Archivos basura | 20+ archivos .txt, tmp_*.php trackeados | **Limpiar** |
| deploy.sh | Referencia a enterprisesstph | **Actualizar** |

### Archivos basura trackeados en git (pendiente limpieza)

**Archivos de notas/bocetos (no son parte del sistema):**
- z_botiquin.txt, z_gabinetes.txt, z_comunicaciones.txt, z_limpieza.txt, z_ocurrencia.txt, z_plagas.txt, z_plandeemergencia.txt, z_recursosseguridad.txt, z_residuos.txt, z_responsabilidadessst.txt, z_aguapotable.txt, z_asistentes.txt, z_dotacion_vigilante.txt, z_hvbrigadista.txt, z_kpis.txt, y_appscriptbrigadista.txt, Z_PLANSANEAMIENTO.TXT, ZZ_PASCRIPTEVSIMULACTRO.txt, zz_todaslasrutas.txt, emergencias.txt, senalizacion.txt

**Scripts temporales:**
- tmp_actas.php, tmp_describe.php, tmp_insert_compromisos.php, tmp_query.php

**Otros:**
- bash.exe.stackdump
- composer-setup.php
- generate_icons.php
- tbl_clientes.sql
- EJECUTAR_AHORA_rename.sql
- migration_add_capacitacion_fields.sql, migration_safe_rename.sql
- csv_plandetrabajocliente - copia.csv, csvcapacitaciones2025.csv
- lpta2026.xlsx

**15+ archivos .md sueltos en raiz** que deberian moverse a `docs/`:
- ACTUALIZACION_FECHAS_CONTRATOS.md, FECHAS_DOCUMENTOS.md, GUIA_GESTION_DOCUMENTOS.md, GUIA_MIGRACION_CONTROLADORES.md, GUIA_MIGRACION_SEGURA_CAPACITACIONES.md, GUIA_SISTEMA_PLAN_TRABAJO.md, IMPLEMENTACION_GENERACION_CONTRATOS.md, INSTRUCCIONES_FECHAS_PRIMER_CONTRATO.md, INSTRUCCIONES_LIBRERIA_POLICIES.md, MIGRACION_CAPACITACIONES.md, MIGRACION_COMPLETADA.md, QUICK_ACCESS_DASHBOARD_DOCUMENTATION.md, RESUMEN_IMPLEMENTACION_LIBRERIAS.md, SISTEMA_ESTANDARES_MINIMOS.md, SISTEMA_FECHAS_PRIMER_CONTRATO.md, SISTEMA_HISTORIAL_CONTRATOS.md

### Archivos CSV que SI deben quedarse (usados por Libraries)

- PTA2026.csv (WorkPlanLibrary)
- capacitaciones ph.csv (TrainingLibrary)
- csvevaluacionestandaresminimosph.csv (StandardsLibrary)

---

## 8. HALLAZGOS CRITICOS Y ACCIONES PENDIENTES

### Prioridad CRITICA

| # | Accion | Responsable |
|---|--------|-------------|
| 1 | **Hacer repo PRIVADO en GitHub** — Credenciales expuestas en codigo fuente | INMEDIATO |
| 2 | **Rotar TODAS las API Keys y passwords de BD** — SendGrid, OpenAI, BD produccion, readonly | INMEDIATO |
| 3 | **Eliminar credenciales hardcodeadas** de 14 archivos — migrar a `getenv('DB_PROD_PASS')` | Cliente |
| 4 | **Mover password readonly de Database.php a .env** — actualmente `CycloidPortal2026!` en codigo | Cliente |

### Prioridad ALTA

| # | Accion | Responsable |
|---|--------|-------------|
| 5 | Crear `.env.example` con todas las variables (sin valores reales) | Cliente |
| 6 | Reescribir `README.md` (actualmente generico de CodeIgniter) | Cliente |
| 7 | Crear `CONTRIBUTING.md` con flujo de ramas y convencion de commits | Cliente |
| 8 | Crear rama `develop` y establecer flujo de ramas | Cliente |
| 9 | Actualizar `deploy.sh` (referencia a enterprisesstph) | Cliente |
| 10 | Configurar GitHub Actions (validacion + deploy) | Consultor |

### Prioridad MEDIA

| # | Accion | Responsable |
|---|--------|-------------|
| 11 | Limpiar 20+ archivos basura del repo (z_*.txt, tmp_*.php, etc.) | Cliente |
| 12 | Mover 15+ .md sueltos de raiz a docs/ | Cliente |
| 13 | Proteger ramas main y develop en GitHub | Consultor |
| 14 | Centralizar email en clase EmailService (20+ archivos con SendGrid directo) | Cliente |
| 15 | Centralizar OpenAI en servicio unico (7 archivos con cURL directo) | Cliente |

---

*Documento generado el 2026-04-05. Preparado como entregable del proceso de hardening del repositorio tat_cycloid.*
