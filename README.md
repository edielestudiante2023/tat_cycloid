# TAT Cycloid — Plataforma SST Tienda a Tienda

Sistema de gestion de Seguridad y Salud en el Trabajo (SST) para el segmento Tienda a Tienda (TAT), desarrollado por Cycloid Talent.

Fork independiente de [enterprisesstph](https://github.com/edielestudiante2023/enterprisesstph) (Propiedad Horizontal). Cada proyecto tiene base de datos, branding y configuracion independientes.

## Stack tecnologico

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

## Modulos principales (16)

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
| Matrices | Riesgos, vulnerabilidad, EPP (generacion desde plantillas Excel) |
| Chat Otto (IA) | Asistente IA con function calling, consultas SQL readonly, 3 capas de seguridad |
| Presupuesto SST | Categorias, items, detalle de ejecucion |
| Informes de Avances | Reportes mensuales con metricas e imagenes |
| Firmas Digitales | Firma electronica via token por email (contratos + protocolo alturas) |
| Portal Cliente | Dashboard readonly, chat Otto, inspecciones, reportes, pendientes |

## Roles de usuario

| Rol | Acceso |
|-----|--------|
| admin | Todo el sistema + gestion de usuarios + configuracion |
| consultant | Gestion de clientes asignados + inspecciones + chat IA completo |
| client | Portal readonly + chat Otto (solo SELECT) |

## Estructura del proyecto

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
├── docs/                  # Documentacion tecnica (~60 documentos)
├── public/                # Punto de entrada web (index.php)
├── tools/                 # Scripts utilitarios
├── tests/                 # Tests PHPUnit
├── writable/              # Logs, cache, sesiones
├── .env                   # Variables de entorno (NO commitear)
├── .env.example           # Template de variables (SI commitear)
├── deploy.sh              # Script de deploy seguro
├── CONTRIBUTING.md        # Guia de contribucion
└── spark                  # CLI de CodeIgniter
```

## Requisitos previos

- PHP 8.2+ con extensiones: intl, mbstring, mysqlnd, curl, gd, openssl
- MySQL 8.0+ o MariaDB 10.11+
- Composer 2.x
- Nginx o Apache
- Git

## Instalacion local

```bash
# 1. Clonar el repositorio
git clone https://github.com/edielestudiante2023/tat_cycloid.git
cd tat_cycloid

# 2. Instalar dependencias
composer install

# 3. Configurar variables de entorno
cp .env.example .env
# Editar .env con tus valores locales

# 4. Crear base de datos
mysql -u root -e "CREATE DATABASE tat_cycloid CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"

# 5. Configurar servidor web
# Apuntar el document root a la carpeta public/
# Ejemplo XAMPP: http://localhost/tat_cycloid/public/

# 6. Verificar instalacion
php spark serve
```

## Variables de entorno

Ver `.env.example` para la lista completa. Las principales:

| Variable | Descripcion |
|----------|-------------|
| `CI_ENVIRONMENT` | development / production |
| `app.baseURL` | URL base de la aplicacion |
| `database.default.*` | Conexion BD principal |
| `readonly.*` | Conexion BD readonly (portal cliente) |
| `SENDGRID_API_KEY` | API Key de SendGrid (email transaccional) |
| `OPENAI_API_KEY` | API Key de OpenAI (Chat Otto, generacion de textos) |
| `OPENAI_MODEL` | Modelo OpenAI (default: gpt-4o-mini) |
| `APP_API_KEY` | Token para endpoints API internos |
| `CRON_TOKEN` | Token para endpoints cron via HTTP |

## Cron jobs (10 tareas programadas)

| Comando | Frecuencia | Descripcion |
|---------|-----------|-------------|
| `php spark auditoria:revisar-visitas-diario` | Diario 7 AM | Verificar visitas del dia anterior |
| `php spark recordatorio:visitas` | Diario 3 PM | Recordatorio de visitas |
| `php spark seguimiento:agenda-diario` | Diario 3 PM | Seguimiento agenda |
| `php spark inspecciones:resumen-pendientes` | Diario 5 PM | Inspecciones pendientes |
| `php spark firmas:protocolo-alturas --reporte` | Diario 7 AM | Reporte firmas alturas |
| `php spark contratos:resumen-semanal` | Lunes 7 AM | Resumen contratos |
| `php spark auditoria:recordatorio-sin-agendar` | L-V 7 AM | Clientes sin agendar |
| `php spark pendientes:recordatorio` | Dia 1 y 16 | Recordatorio pendientes |
| `php spark reportes:limpiar-404` | Semanal | Limpiar reportes con archivos faltantes |
| `php spark pdfs:regenerar` | Manual | Regenerar PDFs masivamente |

## Deploy a produccion

```bash
ssh root@66.29.154.174 "cd /www/wwwroot/tat_cycloid && bash deploy.sh"
```

El script `deploy.sh` es seguro: hace stash, pull, pop. Nunca borra archivos de uploads.

## Documentacion adicional

- [HARDENING-tat_cycloid.md](docs/HARDENING-tat_cycloid.md) — Auditoria de seguridad y hardening
- [CONTRIBUTING.md](CONTRIBUTING.md) — Guia de contribucion
- [docs/](docs/) — 60+ documentos tecnicos por modulo
