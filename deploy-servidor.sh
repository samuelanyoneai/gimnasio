#!/bin/bash

# Script para desplegar el SERVIDOR en NODO 2
# Este script prepara la aplicación PHP y PostgreSQL

set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Despliegue SERVIDOR - NODO 2${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Variables configurables
SERVIDOR_DIR="/var/www/gimnasio/servidor"
CLIENTE_IP="${1:-192.168.1.10}"  # IP del cliente por defecto

echo -e "${YELLOW}Configurando SERVIDOR para aceptar peticiones desde CLIENTE: $CLIENTE_IP${NC}"
echo ""

# Crear estructura de directorios
echo -e "${YELLOW}[1/6] Creando estructura de directorios...${NC}"
sudo mkdir -p "$SERVIDOR_DIR"/{config,models,controllers,views/{members,classes,payments,layouts},public/assets/{css,js},database}
echo -e "${GREEN}✅ Directorios creados${NC}"

# Copiar archivos de la aplicación
echo -e "${YELLOW}[2/6] Copiando archivos de la aplicación...${NC}"
if [ -d "config" ]; then
    sudo cp -r config/* "$SERVIDOR_DIR/config/"
    echo -e "${GREEN}✅ Config copiado${NC}"
else
    echo -e "${RED}❌ No se encontró config${NC}"
    exit 1
fi

if [ -d "models" ]; then
    sudo cp -r models/* "$SERVIDOR_DIR/models/"
    echo -e "${GREEN}✅ Models copiados${NC}"
fi

if [ -d "controllers" ]; then
    sudo cp -r controllers/* "$SERVIDOR_DIR/controllers/"
    echo -e "${GREEN}✅ Controllers copiados${NC}"
fi

if [ -d "views" ]; then
    sudo cp -r views/* "$SERVIDOR_DIR/views/"
    echo -e "${GREEN}✅ Views copiadas${NC}"
fi

if [ -d "public" ]; then
    sudo cp -r public/* "$SERVIDOR_DIR/public/"
    echo -e "${GREEN}✅ Public copiado${NC}"
fi

if [ -d "database" ]; then
    sudo cp -r database/* "$SERVIDOR_DIR/database/"
    echo -e "${GREEN}✅ Database copiado${NC}"
fi

# Crear archivo .htaccess con CORS
echo -e "${YELLOW}[3/6] Configurando CORS...${NC}"
cat > /tmp/.htaccess << EOF
# CORS Headers para permitir peticiones desde el CLIENTE
<IfModule mod_headers.c>
    Header set Access-Control-Allow-Origin "http://$CLIENTE_IP"
    Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
    Header set Access-Control-Allow-Headers "Content-Type, Authorization"
    
    # Manejar preflight requests
    RewriteEngine On
    RewriteCond %{REQUEST_METHOD} OPTIONS
    RewriteRule ^(.*)$ $1 [R=200,L]
</IfModule>

# Rewrite rules
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Proteger archivos sensibles
<FilesMatch "^(config|models|controllers|views)">
    Order allow,deny
    Deny from all
</FilesMatch>
EOF

sudo cp /tmp/.htaccess "$SERVIDOR_DIR/public/.htaccess"
echo -e "${GREEN}✅ CORS configurado${NC}"

# Configurar permisos
echo -e "${YELLOW}[4/6] Configurando permisos...${NC}"

# Detectar usuario del servidor web según el sistema operativo
if [[ "$OSTYPE" == "darwin"* ]]; then
    # macOS
    WEB_USER="_www"
    WEB_GROUP="_www"
elif [ -f /etc/debian_version ]; then
    # Debian/Ubuntu
    WEB_USER="www-data"
    WEB_GROUP="www-data"
elif [ -f /etc/redhat-release ]; then
    # CentOS/RHEL
    WEB_USER="apache"
    WEB_GROUP="apache"
else
    # Por defecto, usar usuario actual
    WEB_USER=$(whoami)
    WEB_GROUP=$(whoami)
fi

# Verificar si el usuario existe antes de cambiar ownership
if id "$WEB_USER" &>/dev/null; then
    sudo chown -R "$WEB_USER:$WEB_GROUP" "$SERVIDOR_DIR"
    echo -e "${GREEN}✅ Permisos configurados (usuario: $WEB_USER)${NC}"
else
    # Si no existe, usar usuario actual
    sudo chown -R "$(whoami):$(whoami)" "$SERVIDOR_DIR"
    echo -e "${YELLOW}⚠️  Usuario $WEB_USER no encontrado, usando usuario actual${NC}"
fi

sudo chmod -R 755 "$SERVIDOR_DIR"
sudo chmod 600 "$SERVIDOR_DIR/config/database.php" 2>/dev/null || true
echo -e "${GREEN}✅ Permisos configurados${NC}"

# Configurar base de datos
echo -e "${YELLOW}[5/6] Configurando base de datos...${NC}"
if command -v psql &> /dev/null; then
    # Detectar usuario de PostgreSQL según el sistema
    if [[ "$OSTYPE" == "darwin"* ]]; then
        # macOS - PostgreSQL instalado con Homebrew usa el usuario actual
        PG_USER=$(whoami)
        PG_CMD="psql"
    elif id "postgres" &>/dev/null 2>&1; then
        # Linux - Usuario postgres existe
        PG_USER="postgres"
        PG_CMD="sudo -u postgres psql"
    else
        # Usuario actual
        PG_USER=$(whoami)
        PG_CMD="psql"
    fi
    
    # Verificar si la base de datos existe
    if $PG_CMD -lqt 2>/dev/null | cut -d \| -f 1 | grep -qw gimnasio_db; then
        echo -e "${YELLOW}⚠️  Base de datos ya existe${NC}"
    else
        # Crear base de datos
        if $PG_CMD -c "CREATE DATABASE gimnasio_db;" 2>/dev/null; then
            echo -e "${GREEN}✅ Base de datos creada${NC}"
        else
            # Intentar sin sudo
            if psql -c "CREATE DATABASE gimnasio_db;" 2>/dev/null; then
                echo -e "${GREEN}✅ Base de datos creada${NC}"
            else
                echo -e "${YELLOW}⚠️  No se pudo crear la base de datos automáticamente${NC}"
                echo -e "${YELLOW}   Ejecuta manualmente: createdb gimnasio_db${NC}"
            fi
        fi
    fi
    
    # Ejecutar script SQL
    if [ -f "$SERVIDOR_DIR/database/schema.sql" ]; then
        if $PG_CMD -d gimnasio_db -f "$SERVIDOR_DIR/database/schema.sql" > /dev/null 2>&1; then
            echo -e "${GREEN}✅ Script SQL ejecutado${NC}"
        elif psql -d gimnasio_db -f "$SERVIDOR_DIR/database/schema.sql" > /dev/null 2>&1; then
            echo -e "${GREEN}✅ Script SQL ejecutado${NC}"
        else
            echo -e "${YELLOW}⚠️  No se pudo ejecutar el script SQL automáticamente${NC}"
            echo -e "${YELLOW}   Ejecuta manualmente: psql -d gimnasio_db -f $SERVIDOR_DIR/database/schema.sql${NC}"
        fi
    fi
    echo -e "${GREEN}✅ Base de datos configurada${NC}"
else
    echo -e "${YELLOW}⚠️  PostgreSQL no encontrado, saltando configuración de BD${NC}"
    echo -e "${YELLOW}   Instala PostgreSQL y ejecuta manualmente:${NC}"
    echo -e "${YELLOW}   createdb gimnasio_db${NC}"
    echo -e "${YELLOW}   psql -d gimnasio_db -f $SERVIDOR_DIR/database/schema.sql${NC}"
fi

# Crear configuración de Nginx (si está instalado)
echo -e "${YELLOW}[6/6] Creando configuración de servidor web...${NC}"

# Detectar si Nginx está instalado
NGINX_AVAILABLE=""
NGINX_ENABLED=""

if command -v nginx &> /dev/null; then
    # Detectar estructura de directorios de Nginx
    if [ -d "/etc/nginx/sites-available" ]; then
        # Linux (Debian/Ubuntu)
        NGINX_AVAILABLE="/etc/nginx/sites-available"
        NGINX_ENABLED="/etc/nginx/sites-enabled"
    elif [ -d "/opt/homebrew/etc/nginx/servers" ]; then
        # macOS con Homebrew
        NGINX_AVAILABLE="/opt/homebrew/etc/nginx/servers"
        NGINX_ENABLED="/opt/homebrew/etc/nginx/servers"
    elif [ -d "/usr/local/etc/nginx/servers" ]; then
        # macOS con Homebrew (ruta alternativa)
        NGINX_AVAILABLE="/usr/local/etc/nginx/servers"
        NGINX_ENABLED="/usr/local/etc/nginx/servers"
    fi
    
    if [ -n "$NGINX_AVAILABLE" ]; then
        cat > /tmp/gimnasio-servidor << NGINX_EOF
server {
    listen 80;
    server_name servidor.gimnasio.local;
    
    root $SERVIDOR_DIR/public;
    index index.php;
    
    # CORS: Permitir peticiones desde el CLIENTE
    add_header 'Access-Control-Allow-Origin' 'http://$CLIENTE_IP' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization' always;
    
    # Manejar preflight requests
    if (\$request_method = 'OPTIONS') {
        add_header 'Access-Control-Allow-Origin' 'http://$CLIENTE_IP';
        add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS';
        add_header 'Access-Control-Allow-Headers' 'Content-Type, Authorization';
        add_header 'Content-Length' 0;
        add_header 'Content-Type' 'text/plain';
        return 204;
    }
    
    # PHP-FPM
    location ~ \.php\$ {
NGINX_EOF
        
        # Detectar socket de PHP-FPM según el sistema
        if [[ "$OSTYPE" == "darwin"* ]]; then
            # macOS - buscar socket de PHP-FPM
            PHP_SOCKET=$(find /opt/homebrew /usr/local -name "php*-fpm.sock" 2>/dev/null | head -1)
            if [ -z "$PHP_SOCKET" ]; then
                PHP_SOCKET="127.0.0.1:9000"
            fi
            echo "        fastcgi_pass $PHP_SOCKET;" >> /tmp/gimnasio-servidor
        else
            # Linux
            PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "8.1")
            echo "        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;" >> /tmp/gimnasio-servidor
        fi
        
        cat >> /tmp/gimnasio-servidor << 'NGINX_EOF'
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
NGINX_EOF
        
        if [ -n "$USE_SUDO" ]; then
            sudo mkdir -p "$NGINX_AVAILABLE"
            sudo cp /tmp/gimnasio-servidor "$NGINX_AVAILABLE/gimnasio-servidor"
            echo -e "${GREEN}✅ Configuración de Nginx creada en $NGINX_AVAILABLE/gimnasio-servidor${NC}"
        else
            mkdir -p "$NGINX_AVAILABLE"
            cp /tmp/gimnasio-servidor "$NGINX_AVAILABLE/gimnasio-servidor"
            echo -e "${GREEN}✅ Configuración de Nginx creada en $NGINX_AVAILABLE/gimnasio-servidor${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  Nginx instalado pero estructura de directorios no reconocida${NC}"
        echo -e "${YELLOW}   Configura Nginx manualmente apuntando a: $SERVIDOR_DIR/public${NC}"
    fi
else
    echo -e "${YELLOW}⚠️  Nginx no encontrado${NC}"
    echo -e "${YELLOW}   Instala Nginx o configura Apache manualmente${NC}"
    echo -e "${YELLOW}   DocumentRoot debe apuntar a: $SERVIDOR_DIR/public${NC}"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Servidor desplegado exitosamente${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}Próximos pasos:${NC}"

if command -v nginx &> /dev/null && [ -n "$NGINX_AVAILABLE" ]; then
    if [[ "$OSTYPE" == "darwin"* ]]; then
        echo -e "1. Habilitar sitio Nginx (macOS):"
        echo -e "   brew services restart nginx"
        echo -e "   O edita: /opt/homebrew/etc/nginx/nginx.conf"
    else
        echo -e "1. Habilitar sitio Nginx (Linux):"
        if [ "$NGINX_AVAILABLE" != "$NGINX_ENABLED" ]; then
            echo -e "   sudo ln -sf $NGINX_AVAILABLE/gimnasio-servidor $NGINX_ENABLED/"
        fi
        echo -e "   sudo nginx -t && sudo systemctl reload nginx"
    fi
else
    echo -e "1. Configurar servidor web (Nginx/Apache) apuntando a:"
    echo -e "   $SERVIDOR_DIR/public"
fi

if [[ "$OSTYPE" == "darwin"* ]]; then
    echo -e ""
    echo -e "2. Reiniciar PHP-FPM (macOS):"
    echo -e "   brew services restart php"
    echo -e "   O: sudo killall php-fpm && php-fpm -D"
else
    PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "8.1")
    echo -e ""
    echo -e "2. Reiniciar PHP-FPM (Linux):"
    echo -e "   sudo systemctl restart php${PHP_VERSION}-fpm"
fi

echo -e ""
echo -e "3. Verificar que el servidor responde:"
echo -e "   curl http://localhost/index.php?controller=member&action=index"
echo -e ""
echo -e "4. Configurar CORS para permitir peticiones desde: $CLIENTE_IP"
echo ""

