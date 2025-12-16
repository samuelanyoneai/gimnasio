# 🖥️ Despliegue en Dos Nodos - Arquitectura Cliente-Servidor Distribuida

Esta guía explica cómo configurar la aplicación con el **CLIENTE** y el **SERVIDOR** en dos nodos separados, demostrando claramente la arquitectura Cliente-Servidor distribuida.

## 📋 Arquitectura Propuesta

```
┌─────────────────────────────────┐         ┌─────────────────────────────────┐
│         NODO 1                  │         │         NODO 2                  │
│         CLIENTE                 │         │         SERVIDOR                │
│                                 │         │                                 │
│  - Servidor Web (Nginx/Apache)  │◄───────►│  - PHP-FPM                     │
│  - HTML/CSS/JavaScript          │  HTTP   │  - PostgreSQL                  │
│  - Archivos estáticos           │         │  - API Endpoints                │
│                                 │         │                                 │
│  IP: 192.168.1.10               │         │  IP: 192.168.1.20               │
│  Puerto: 80/443                 │         │  Puerto: 80/443                │
└─────────────────────────────────┘         └─────────────────────────────────┘
```

## 🎯 Componentes por Nodo

### NODO 1: CLIENTE (Frontend)
- Servidor web (Nginx o Apache)
- Archivos HTML, CSS, JavaScript
- Archivos estáticos (assets)

### NODO 2: SERVIDOR (Backend)
- PHP-FPM
- PostgreSQL
- Aplicación PHP (Modelos, Controladores)
- API REST (opcional)

## 📦 Configuración del NODO 1: CLIENTE

### Requisitos
- **Linux/macOS**: Nginx o Apache
- **Windows**: IIS, Apache (XAMPP), o Nginx
- Acceso HTTP/HTTPS

### Paso 1: Ejecutar script de despliegue

**Linux/macOS:**
```bash
# El script detecta automáticamente el sistema operativo
./deploy-cliente.sh 192.168.1.20

# Donde 192.168.1.20 es la IP del NODO 2 (Servidor)
```

**Windows:**
El script `deploy-cliente.sh` no está disponible para Windows. Sigue los pasos manuales a continuación.

**Nota**: El script detecta automáticamente:
- **macOS**: Usa usuario `_www`
- **Linux Debian/Ubuntu**: Usa usuario `www-data`
- **Linux CentOS/RHEL**: Usa usuario `apache`

### Paso 2: Configurar Nginx (Recomendado)

**Archivo: `/etc/nginx/sites-available/gimnasio-cliente`**

```nginx
server {
    listen 80;
    server_name cliente.gimnasio.local;  # O tu dominio/IP
    
    root /var/www/gimnasio/cliente/public;
    index index.html;
    
    # Configuración CORS para permitir comunicación con el servidor
    add_header 'Access-Control-Allow-Origin' 'http://servidor.gimnasio.local' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization' always;
    
    # Servir archivos estáticos
    location / {
        try_files $uri $uri/ /index.html;
    }
    
    # Archivos CSS y JS
    location /assets/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
    
    # Proxy para API (opcional - si quieres que el cliente haga proxy)
    location /api/ {
        proxy_pass http://192.168.1.20/api/;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

### Paso 3: Configurar Apache (Alternativa)

**Linux/macOS - Archivo: `/etc/apache2/sites-available/gimnasio-cliente.conf`**

```apache
<VirtualHost *:80>
    ServerName cliente.gimnasio.local
    DocumentRoot /var/www/gimnasio/cliente/public
    
    <Directory /var/www/gimnasio/cliente/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # CORS Headers
    Header always set Access-Control-Allow-Origin "http://servidor.gimnasio.local"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
    
    # Proxy para API
    ProxyPass /api http://192.168.1.20/api
    ProxyPassReverse /api http://192.168.1.20/api
</VirtualHost>
```

**Windows - XAMPP/WAMP:**

1. **Copiar archivos del cliente:**
   - Copia `public/assets` a `C:\xampp\htdocs\gimnasio-cliente\assets`
   - Crea `C:\xampp\htdocs\gimnasio-cliente\index.html` (ver ejemplo en script deploy-cliente.sh)

2. **Configurar Apache (XAMPP):**
   Edita `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   <Directory "C:/xampp/htdocs/gimnasio-cliente">
       Options Indexes FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. **Habilitar módulos necesarios:**
   En `httpd.conf`, descomenta:
   ```apache
   LoadModule headers_module modules/mod_headers.so
   LoadModule proxy_module modules/mod_proxy.so
   LoadModule proxy_http_module modules/mod_proxy_http.so
   ```

4. **Configurar CORS:**
   Crea `.htaccess` en `C:\xampp\htdocs\gimnasio-cliente\`:
   ```apache
   Header set Access-Control-Allow-Origin "http://192.168.1.20"
   Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
   Header set Access-Control-Allow-Headers "Content-Type, Authorization"
   ```

## 📦 Configuración del NODO 2: SERVIDOR

### Requisitos
- **Linux/macOS**: PHP 7.4+ con PHP-FPM
- **Windows**: PHP 7.4+ (con XAMPP/WAMP o IIS)
- PostgreSQL
- **Linux/macOS**: Nginx o Apache
- **Windows**: IIS, Apache (XAMPP/WAMP), o Nginx

### Paso 1: Ejecutar script de despliegue

**Linux/macOS:**
```bash
# El script detecta automáticamente el sistema operativo
./deploy-servidor.sh 192.168.1.10

# Donde 192.168.1.10 es la IP del NODO 1 (Cliente)
```

**Windows:**
El script `deploy-servidor.sh` no está disponible para Windows. Sigue los pasos manuales a continuación o consulta `INSTALACION_WINDOWS.md`.

### Paso 2: Instalar PHP-FPM

**Linux:**
```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install php-fpm php-pgsql php-mbstring

# CentOS/RHEL
sudo yum install php-fpm php-pgsql php-mbstring
```

**macOS:**
```bash
brew install php
```

**Windows:**
- PHP viene incluido con XAMPP/WAMP
- O instala PHP manualmente desde https://windows.php.net/
- Asegúrate de habilitar `pdo_pgsql` y `pgsql` en `php.ini`

### Paso 3: Configurar Nginx para PHP-FPM

**Archivo: `/etc/nginx/sites-available/gimnasio-servidor`**

```nginx
server {
    listen 80;
    server_name servidor.gimnasio.local;  # O tu dominio/IP
    
    root /var/www/gimnasio/servidor/public;
    index index.php;
    
    # CORS: Permitir peticiones desde el CLIENTE
    add_header 'Access-Control-Allow-Origin' 'http://192.168.1.10' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization' always;
    
    # Manejar preflight requests
    if ($request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' 'http://192.168.1.10';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization';
        add_header 'Content-Length' 0;
        add_header 'Content-Type' 'text/plain';
        return 204;
    }
    
    # PHP-FPM (ajustar según tu versión de PHP)
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;  # Linux
        # fastcgi_pass 127.0.0.1:9000;  # macOS
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Denegar acceso a archivos sensibles
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(config|models|controllers)/ {
        deny all;
    }
}
```

### Paso 4: Configurar Apache para PHP

**Linux/macOS - Archivo: `/etc/apache2/sites-available/gimnasio-servidor.conf`**

```apache
<VirtualHost *:80>
    ServerName servidor.gimnasio.local
    DocumentRoot /var/www/gimnasio/servidor/public
    
    <Directory /var/www/gimnasio/servidor/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    # CORS Headers
    Header always set Access-Control-Allow-Origin "http://192.168.1.10"
    Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
    
    # PHP
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/var/run/php/php8.1-fpm.sock|fcgi://localhost"
    </FilesMatch>
    
    # Denegar acceso a archivos sensibles
    <DirectoryMatch "^/.*/(config|models|controllers)/">
        Require all denied
    </DirectoryMatch>
</VirtualHost>
```

**Windows - XAMPP/WAMP:**

1. **Copiar proyecto:**
   - Copia todo el proyecto a `C:\xampp\htdocs\gimnasio-servidor\`

2. **Configurar Apache:**
   Edita `C:\xampp\apache\conf\httpd.conf`:
   ```apache
   <Directory "C:/xampp/htdocs/gimnasio-servidor/public">
       Options -Indexes +FollowSymLinks
       AllowOverride All
       Require all granted
   </Directory>
   ```

3. **Habilitar módulos:**
   ```apache
   LoadModule headers_module modules/mod_headers.so
   ```

4. **Configurar CORS:**
   Crea `.htaccess` en `C:\xampp\htdocs\gimnasio-servidor\public\`:
   ```apache
   Header set Access-Control-Allow-Origin "http://192.168.1.10"
   Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
   Header set Access-Control-Allow-Headers "Content-Type, Authorization"
   
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

5. **Acceder:**
   - `http://localhost/gimnasio-servidor/public/index.php`

### Paso 5: Configurar PostgreSQL

**Archivo: `/etc/postgresql/14/main/postgresql.conf`**

```conf
# Permitir conexiones remotas
listen_addresses = '*'
```

**Archivo: `/etc/postgresql/14/main/pg_hba.conf`**

```conf
# Permitir conexiones desde el servidor local
host    all             all             127.0.0.1/32            md5
# Si necesitas conexiones remotas (solo desde el servidor)
host    all             all             192.168.1.20/32         md5
```

### Paso 6: Actualizar configuración de base de datos

**Archivo: `config/database.php`**

```php
<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración para NODO 2 (SERVIDOR)
    private const DB_HOST = 'localhost';  // PostgreSQL en el mismo nodo
    private const DB_NAME = 'gimnasio_db';
    private const DB_USER = 'gimnasio_user';
    private const DB_PASS = 'contraseña_segura';
    private const DB_PORT = '5432';
    
    // ... resto del código igual ...
}
```

## 🚀 Pasos de Despliegue Rápido

### En NODO 1 (Cliente):

**Linux/macOS:**
```bash
# 1. Ejecutar script de despliegue
./deploy-cliente.sh 192.168.1.20

# 2. Configurar Nginx (si no está configurado automáticamente)
sudo ln -sf /etc/nginx/sites-available/gimnasio-cliente /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 3. Verificar
curl http://localhost
```

**Windows:**
```cmd
# 1. Copiar archivos del cliente a C:\xampp\htdocs\gimnasio-cliente\
# 2. Configurar Apache según instrucciones anteriores
# 3. Reiniciar Apache desde XAMPP Control Panel
# 4. Verificar: http://localhost/gimnasio-cliente/
```

### En NODO 2 (Servidor):

**Linux/macOS:**
```bash
# 1. Ejecutar script de despliegue
./deploy-servidor.sh 192.168.1.10

# 2. Habilitar sitio Nginx
sudo ln -sf /etc/nginx/sites-available/gimnasio-servidor /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx

# 3. Reiniciar PHP-FPM
sudo systemctl restart php8.1-fpm

# 4. Verificar
curl http://localhost/index.php?controller=member&action=index
```

**Windows:**
```cmd
# 1. Copiar proyecto a C:\xampp\htdocs\gimnasio-servidor\
# 2. Configurar base de datos (ver INSTALACION_WINDOWS.md)
# 3. Configurar Apache según instrucciones anteriores
# 4. Reiniciar Apache desde XAMPP Control Panel
# 5. Verificar: http://localhost/gimnasio-servidor/public/index.php
```

## 🔧 Configuración de Red

### Opción 1: Red Local (Recomendado para pruebas)

```bash
# NODO 1 (Cliente)
sudo ip addr add 192.168.1.10/24 dev eth0

# NODO 2 (Servidor)
sudo ip addr add 192.168.1.20/24 dev eth0
```

### Opción 2: DNS Local

**Archivo: `/etc/hosts` en ambos nodos**

```
192.168.1.10  cliente.gimnasio.local
192.168.1.20  servidor.gimnasio.local
```

## 🔒 Seguridad

### Firewall

```bash
# NODO 1 (Cliente) - Solo HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# NODO 2 (Servidor) - HTTP/HTTPS y PostgreSQL (solo local)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow from 127.0.0.1 to any port 5432
```

### SSL/TLS (Opcional pero recomendado)

```bash
# Instalar Certbot
sudo apt-get install certbot python3-certbot-nginx

# Generar certificados
sudo certbot --nginx -d cliente.gimnasio.local
sudo certbot --nginx -d servidor.gimnasio.local
```

## ✅ Verificación

### Probar conexión desde NODO 1 (Cliente)

```bash
# Verificar que puede comunicarse con el servidor
curl http://192.168.1.20/index.php?controller=member&action=index
```

### Probar desde navegador

1. Abre navegador en cualquier máquina
2. Visita: `http://192.168.1.10` (Cliente)
3. El cliente debería cargar datos desde `http://192.168.1.20` (Servidor)

## 📊 Diagrama de Flujo

```
Usuario → NODO 1 (Cliente) → HTTP Request → NODO 2 (Servidor)
                                    ↓
                            Procesa en PHP
                                    ↓
                            Consulta PostgreSQL
                                    ↓
                            Genera HTML/JSON
                                    ↓
Usuario ← NODO 1 (Cliente) ← HTTP Response ← NODO 2 (Servidor)
```

## 🐛 Solución de Problemas

### Error: CORS bloqueado

**Solución**: Verificar headers CORS en ambos nodos

### Error: No se puede conectar al servidor

**Solución**: 
- Verificar firewall
- Verificar que el servidor esté escuchando
- Verificar conectividad de red: `ping 192.168.1.20`

### Error: Base de datos no accesible

**Solución**: 
- Verificar que PostgreSQL esté ejecutándose
- Verificar configuración de `pg_hba.conf`
- Verificar credenciales en `config/database.php`

### Error: "www-data: illegal group name" (macOS)

**Solución**: Los scripts detectan automáticamente macOS y usan `_www` en lugar de `www-data`. Si aún hay problemas, ejecuta sin sudo o ajusta manualmente los permisos.

## 📝 Notas Importantes

1. **IPs**: Reemplaza las IPs de ejemplo (192.168.1.10/20) con las IPs reales de tus nodos
2. **Dominios**: Puedes usar IPs directamente o configurar DNS
3. **CORS**: Es necesario para permitir comunicación entre dominios diferentes
4. **Seguridad**: En producción, usa HTTPS y configura firewall apropiadamente
5. **Sistema Operativo**: Los scripts detectan automáticamente macOS/Linux y ajustan usuarios y permisos

## 🎓 Detección Automática de Sistema Operativo

Los scripts `deploy-cliente.sh` y `deploy-servidor.sh` detectan automáticamente:

- **macOS**: Usa usuario `_www` y grupo `_www`
- **Linux Debian/Ubuntu**: Usa usuario `www-data` y grupo `www-data`
- **Linux CentOS/RHEL**: Usa usuario `apache` y grupo `apache`
- **Otros**: Usa el usuario actual

Esto elimina el error "illegal group name" en macOS.

---

**Configuración lista para despliegue en dos nodos** 🚀
