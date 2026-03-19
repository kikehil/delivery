# Guía de Despliegue — Menuvi

Backend en **Laravel 12** + Frontend en **Next.js 16**. Desplegado en VPS con Nginx + PHP-FPM.

---

## Requisitos Previos

- PHP 8.2+ con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`
- Composer 2+
- Node.js 18+
- MySQL 8+
- PM2 (`npm install -g pm2`)
- Nginx

---

## 1. Clonar el repositorio

```bash
git clone <repo-url> /var/www/menuvi
cd /var/www/menuvi
```

---

## 2. Backend (Laravel)

```bash
cd backend

# Instalar dependencias
composer install --optimize-autoloader --no-dev

# Configurar entorno
cp .env.example .env
nano .env
# Completa: APP_URL, DB_*, JWT_SECRET, N8N_WEBHOOK_URL

# Generar claves
php artisan key:generate
php artisan jwt:secret

# Base de datos
php artisan migrate --force

# Enlace de almacenamiento
php artisan storage:link

# Permisos
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Cache de producción (opcional)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. Frontend (Next.js)

```bash
cd ../frontend

# Instalar dependencias
npm install

# Configurar entorno
cp .env.example .env.local
# Edita NEXT_PUBLIC_API_URL con la URL de tu backend
nano .env.local

# Build de producción
npm run build

# Iniciar con PM2
pm2 start npm --name "menuvi-frontend" -- start
pm2 save
pm2 startup
```

---

## 4. Nginx

```nginx
server {
    listen 80;
    server_name tu-dominio.com;

    # Frontend (Next.js en puerto 3000)
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API (Laravel via PHP-FPM)
    location /api {
        alias /var/www/menuvi/backend/public;
        try_files $uri $uri/ @laravel;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }

    location @laravel {
        rewrite ^/api/(.*)$ /index.php?/$1 last;
    }

    # Storage de imágenes
    location /storage {
        alias /var/www/menuvi/backend/public/storage;
    }
}
```

> Activa HTTPS con Certbot: `sudo certbot --nginx -d tu-dominio.com`

---

## 5. Variables de entorno requeridas

### Backend (`backend/.env`)
| Variable | Descripción |
|----------|-------------|
| `APP_URL` | URL completa del backend (ej. `https://api.tu-dominio.com`) |
| `DB_DATABASE` | Nombre de la base de datos MySQL |
| `DB_USERNAME` | Usuario MySQL |
| `DB_PASSWORD` | Contraseña MySQL |
| `JWT_SECRET` | Generado con `php artisan jwt:secret` |
| `N8N_WEBHOOK_URL` | URL del webhook n8n para notificaciones WhatsApp |

### Frontend (`frontend/.env.local`)
| Variable | Descripción |
|----------|-------------|
| `NEXT_PUBLIC_API_URL` | URL del API backend (ej. `https://tu-dominio.com/api`) |

---

## 6. Actualizar en producción

```bash
# Backend
cd /var/www/menuvi/backend
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache

# Frontend
cd /var/www/menuvi/frontend
git pull
npm install
npm run build
pm2 restart menuvi-frontend
```
