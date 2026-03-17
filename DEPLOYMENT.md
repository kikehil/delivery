# Guía de Despliegue en VPS

Este proyecto consta de un backend en **Laravel** y un frontend en **Next.js**. Sigue estos pasos para desplegar la aplicación en tu servidor.

---

## 1. Requisitos Previos
- Node.js (v18+)
- PHP (8.1+) con extensiones necesarias (pdo, sqlite, mbstring, etc.)
- Composer
- PM2 (para mantener el frontend activo)
- Nginx o Apache

---

## 2. Configuración del Backend (Laravel)

```bash
cd backend
composer install --optimize-autoloader --no-dev

# Configura las variables de entorno
cp .env.example .env
# Edita el .env con tus credenciales de base de datos y la URL de n8n
nano .env

# Genera la clave de la aplicación
php artisan key:generate

# Migraciones de base de datos
php artisan migrate --force

# Enlace simbólico para almacenamiento de imágenes
php artisan storage:link

# Optimización (Opcional pero recomendado)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Base de Datos (SQLite)
Si usas SQLite en el VPS, asegúrate de crear el archivo:
```bash
touch database/database.sqlite
```
Y actualizar en el `.env`:
```env
DB_CONNECTION=sqlite
# Deja los demás campos de DB vacíos o comentados
```

---

## 3. Configuración del Frontend (Next.js)

```bash
cd ../frontend
npm install
npm run build

# Iniciar con PM2 para que no se cierre
pm2 start npm --name "delivery-app" -- start
```

---

## 4. Configuración del Servidor Web (Nginx)

Ejemplo de configuración para Nginx:

```nginx
server {
    listen 80;
    server_name tu-dominio.com;

    # Frontend (Next.js)
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API (Laravel)
    location /api {
        alias /ruta/al/proyecto/backend/public;
        try_files $uri $uri/ /index.php?$query_string;

        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
        }
    }
}
```

---

## 5. Permisos de Archivos
Asegúrate de que el usuario del servidor web (ej. `www-data`) tenga permisos en el backend:
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```
