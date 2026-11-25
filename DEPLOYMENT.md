# Manual de Despliegue — Auditor-IA

Este manual detalla los requisitos, la configuración de entorno y los procedimientos paso a paso para desplegar **Auditor-IA** en producción (VPS/Servidor Linux con Nginx + PHP-FPM + PostgreSQL + Redis), así como la generación del bundle estático de demostración para **Vercel**.

---

## Tabla de Contenidos

1. [Requisitos de Infraestructura](#1-requisitos-de-infraestructura)
2. [Variables de Entorno en Producción](#2-variables-de-entorno-en-producción)
3. [Despliegue en Servidor Linux / VPS (Producción)](#3-despliegue-en-servidor-linux--vps-producción)
   - [3.1 Preparación del código y dependencias](#31-preparación-del-código-y-dependencias)
   - [3.2 Base de datos PostgreSQL y pgvector](#32-base-de-datos-postgresql-y-pgvector)
   - [3.3 Configuración de Nginx](#33-configuración-de-nginx)
   - [3.4 Workers de Colas con Supervisor](#34-workers-de-colas-con-supervisor)
   - [3.5 Tareas Programadas (Cron / Scheduler)](#35-tareas-programadas-cron--scheduler)
4. [Despliegue de Unstructured API (Parsing de Documentos)](#4-despliegue-de-unstructured-api-parsing-de-documentos)
5. [Estrategia Cloud Híbrida (Supabase + S3)](#5-estrategia-cloud-híbrida-supabase--s3)
6. [Despliegue de la Demo Estática en Vercel](#6-despliegue-de-la-demo-estática-en-vercel)
7. [Verificación Post-Despliegue y Mantenimiento](#7-verificación-post-despliegue-y-mantenimiento)

---

## 1. Requisitos de Infraestructura

| Componente | Versión / Requisito | Propósito |
|---|---|---|
| **PHP** | `^8.2` (recomendado `8.3+`) | Runtime de Laravel 12 |
| **Extensiones PHP** | `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`, `zip`, `redis` (`phpredis`) | Base de datos, cifrado, manejo de archivos y colas |
| **Composer** | `2.x` | Gestión de dependencias PHP |
| **Node.js & npm** | Node `20 LTS` o superior | Compilación de assets frontend (Vite + Tailwind CSS v4) |
| **PostgreSQL** | `15+` con extensión **`pgvector`** | Base de datos relacional multi-tenant y vectores RAG (`law_embeddings`) |
| **Redis** | `7.x` | Driver de colas (`QUEUE_CONNECTION=redis`) y caché (`CACHE_STORE=redis`) |
| **Servidor Web** | Nginx + PHP-FPM (o Apache) | Servicio HTTP/HTTPS |
| **Supervisor** | `4.x` | Ejecución persistente de workers de colas |
| **Unstructured API** | Cloud API o contenedor Docker local | Extracción estructurada de PDF y DOCX |

---

## 2. Variables de Entorno en Producción

Copia `.env.example` a `.env` y ajusta las variables críticas según tu entorno. **Nunca** subas el archivo `.env` al repositorio.

### Aplicación y Seguridad
```env
APP_NAME="Auditor-IA"
APP_ENV=production
APP_KEY=                # Generar con: php artisan key:generate
APP_DEBUG=false         # SIEMPRE false en producción
APP_URL=https://tu-dominio.com

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
APP_FAKER_LOCALE=es_CL

BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### Base de Datos (PostgreSQL)
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=auditor_ia
DB_USERNAME=auditor_user
DB_PASSWORD=tu_password_seguro
```

### Sesiones, Caché y Colas (Redis)
```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true

QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Almacenamiento Privado y Documentos
Para almacenamiento local privado usa `local`; para almacenamiento distribuido compatible con S3 (AWS S3, Supabase Storage, MinIO) usa `s3`:
```env
FILESYSTEM_DISK=local
DOCUMENT_STORAGE_DISK=local   # Cambiar a "s3" en entornos multi-instancia
DOCUMENT_MAX_SIZE_KB=51200    # 50 MB máximo por defecto
DOCUMENT_PROCESSING_QUEUE=documents

# Si DOCUMENT_STORAGE_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

### Proveedores LLM
`config/llm.php` soporta múltiples proveedores. La plataforma enruta automáticamente según el tier de la organización (`cerebras` para Free; `qwen_cloud` para Pro/Lifetime) y permite configurar proveedores adicionales:
```env
LLM_PROVIDER=cerebras

# Cerebras (Tier Free)
CEREBRAS_API_KEY=
CEREBRAS_MODEL=gpt-oss-20b

# Qwen Cloud / DashScope (Tiers Pro & Lifetime)
QWEN_API_KEY=
QWEN_MODEL=qwen3-max

# Proveedores alternativos / Fallback
GROQ_API_KEY=
GROQ_MODEL=llama-3.3-70b-versatile

NVIDIA_NIM_API_KEY=
NVIDIA_NIM_MODEL=meta/llama-3.3-70b-instruct

DEEPSEEK_API_KEY=
AZURE_OPENAI_ENDPOINT=
AZURE_OPENAI_KEY=
AZURE_OPENAI_DEPLOYMENT=
```

### Unstructured API (Parsing de PDF/DOCX)
```env
# Cloud API o contenedor Docker local (ej. http://127.0.0.1:8000/general/v0/general)
UNSTRUCTURED_API_URL=https://api.unstructuredapp.io/general/v0/general
UNSTRUCTURED_API_KEY=
UNSTRUCTURED_TIMEOUT=120
```

---

## 3. Despliegue en Servidor Linux / VPS (Producción)

### 3.1 Preparación del código y dependencias

```bash
# 1. Clonar el repositorio en el servidor
cd /var/www
git clone <URL_DEL_REPOSITORIO> auditor-ia
cd auditor-ia

# 2. Instalar dependencias PHP optimizadas para producción
composer install --no-dev --optimize-autoloader

# 3. Instalar dependencias JS y compilar assets
npm ci
npm run build

# 4. Configurar variables de entorno
cp .env.example .env
php artisan key:generate
# Editar .env con credenciales reales de DB, Redis, LLM y Unstructured

# 5. Permisos de escritura para storage y cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### 3.2 Base de datos PostgreSQL y `pgvector`

La migración `2026_05_14_100000_enable_pgvector_extension.php` habilita la extensión `vector` en PostgreSQL. Asegúrate de tener instalado el paquete `pgvector` en el servidor de base de datos (por ejemplo, `postgresql-16-pgvector` en Ubuntu/Debian) y de que el usuario de la base de datos tenga permisos para crear extensiones, o créala previamente como superusuario:

```sql
CREATE DATABASE auditor_ia;
\c auditor_ia
CREATE EXTENSION IF NOT EXISTS vector;
```

Luego ejecuta las migraciones y los seeders iniciales necesarios (planes y modelos de IA):

```bash
php artisan migrate --force
php artisan db:seed --class=PlanSeeder --force
php artisan db:seed --class=AiModelSeeder --force
```

> **Nota:** Si también deseas crear la cuenta de administrador por defecto (`admin@auditor-ia.test`), ejecuta `php artisan db:seed --class=AdminUserSeeder --force` y cambia la contraseña inmediatamente tras el primer inicio de sesión.

### 3.3 Configuración de Nginx

Configura el `root` apuntando a `/var/www/auditor-ia/public` y permite cargas de hasta `50M` (acorde a `DOCUMENT_MAX_SIZE_KB=51200`):

```nginx
server {
    listen 80;
    server_name tu-dominio.com;
    root /var/www/auditor-ia/public;

    index index.php;
    charset utf-8;
    client_max_body_size 55M;

    add_header X-Frame-Options "DENY";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 180;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Se recomienda habilitar HTTPS con Certbot (`certbot --nginx -d tu-dominio.com`).

### 3.4 Workers de Colas con Supervisor

El procesamiento de documentos (`ProcessDocumentJob`) y de análisis contextuales (`ProcessAncillaryAnalysisJob`) se despacha de forma asíncrona a través de Redis en la cola `documents` (configurable vía `DOCUMENT_PROCESSING_QUEUE`).

Crea el archivo `/etc/supervisor/conf.d/auditor-ia-worker.conf`:

```ini
[program:auditor-ia-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/auditor-ia/artisan queue:work redis --queue=documents,default --sleep=3 --tries=3 --max-time=3600 --timeout=300
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/auditor-ia/storage/logs/worker.log
stopwaitsecs=300
```

Aplica los cambios:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start auditor-ia-worker:*
```

### 3.5 Tareas Programadas (Cron / Scheduler)

En `routes/console.php` está programada la limpieza diaria de documentos de usuarios del plan Free con más de 7 días de antigüedad (`documents:prune-free --days=7`).

Agrega la entrada estándar del scheduler de Laravel en el crontab del usuario `www-data` (`crontab -u www-data -e`):

```cron
* * * * * cd /var/www/auditor-ia && php artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Despliegue de Unstructured API (Parsing de Documentos)

Puedes utilizar el servicio gestionado de **Unstructured Cloud** o desplegar el contenedor oficial de **Unstructured API** en tu infraestructura privada para mayor privacidad documental:

```bash
docker run -d \
  --name unstructured-api \
  --restart unless-stopped \
  -p 8000:8000 \
  quay.io/unstructured-io/unstructured-api:latest
```

Luego configura en `.env`:

```env
UNSTRUCTURED_API_URL=http://127.0.0.1:8000/general/v0/general
UNSTRUCTURED_API_KEY=
UNSTRUCTURED_TIMEOUT=120
```

---

## 5. Estrategia Cloud Híbrida (Supabase + S3)

Si utilizas **Supabase** como base de datos gestionada y almacenamiento:

1. **PostgreSQL + pgvector:** Habilita la extensión `vector` desde el panel de Supabase (*Database → Extensions*) y configura `DB_HOST`, `DB_PORT` (generalmente `5432` o pooler `6543`), `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`.
2. **Almacenamiento Privado S3:** Configura un bucket privado en Supabase Storage (o AWS S3), asigna `DOCUMENT_STORAGE_DISK=s3` y completa `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET` y `AWS_USE_PATH_STYLE_ENDPOINT=true` si usas endpoints path-style.

---

## 6. Despliegue de la Demo Estática en Vercel

El proyecto incluye el comando `demo:build-static` (`App\Console\Commands\BuildStaticDemoCommand`) y `vercel.json` para exportar una versión puramente estática (HTML/CSS/JS) del entorno de demostración sin dependencias de base de datos ni llamadas a APIs externas.

1. Compila los assets y genera la carpeta `vercel-dist`:
   ```bash
   composer demo:build
   ```
   *(Equivale a ejecutar `npm run build` seguido de `php artisan demo:build-static`)*
2. Despliega con Vercel CLI desde la raíz del proyecto:
   ```bash
   npx vercel --prod
   ```
   `vercel.json` ya está preconfigurado con `outputDirectory: "vercel-dist"`, cabeceras de seguridad (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`) y redirecciones de `/login`, `/register` y `/dashboard` hacia `/demo`.

---

## 7. Verificación Post-Despliegue y Mantenimiento

Después de cada despliegue o cambio en `.env`, ejecuta:

```bash
# Cachear configuración, rutas y vistas en producción
php artisan optimize

# Reiniciar workers para que tomen la nueva configuración y código
sudo supervisorctl restart auditor-ia-worker:*

# Verificar conectividad con proveedores LLM y Unstructured API
php artisan services:check
```

Si necesitas limpiar cachés durante mantenimiento:

```bash
php artisan optimize:clear
```

