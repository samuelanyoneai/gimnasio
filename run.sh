#!/bin/bash

# Script para ejecutar el servidor PHP del Sistema de Gestión de Gimnasio
# Arquitectura Cliente-Servidor con MVC
#
# Uso:
#   ./run.sh [puerto] [host]
#   ./run.sh 8000 localhost
#   ./run.sh 8080 0.0.0.0  # Para acceso desde red local

# Colores para output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Función para mostrar ayuda
show_help() {
    echo -e "${BLUE}Uso:${NC} $0 [opciones]"
    echo ""
    echo -e "${CYAN}Opciones:${NC}"
    echo -e "  [puerto]     Puerto donde escuchar (default: 8000)"
    echo -e "  [host]       Host donde escuchar (default: localhost)"
    echo -e "  -h, --help   Mostrar esta ayuda"
    echo ""
    echo -e "${CYAN}Ejemplos:${NC}"
    echo -e "  $0                    # Ejecuta en localhost:8000"
    echo -e "  $0 8080               # Ejecuta en localhost:8080"
    echo -e "  $0 8000 0.0.0.0       # Ejecuta en 0.0.0.0:8000 (accesible desde red)"
    echo ""
}

# Verificar argumentos de ayuda
if [[ "$1" == "-h" ]] || [[ "$1" == "--help" ]]; then
    show_help
    exit 0
fi

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Sistema de Gestión de Gimnasio${NC}"
echo -e "${BLUE}  Arquitectura Cliente-Servidor${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Función para encontrar PHP
find_php() {
    # Detectar versión de PHP instalada primero
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;" 2>/dev/null || echo "")
    fi
    
    # Rutas comunes donde puede estar PHP
    PHP_PATHS=()
    
    # macOS con Homebrew
    if [[ "$OSTYPE" == "darwin"* ]]; then
        if [ -n "$PHP_VERSION" ]; then
            PHP_PATHS+=("/opt/homebrew/Cellar/php/$PHP_VERSION/bin/php")
        fi
        PHP_PATHS+=("/opt/homebrew/bin/php")
        PHP_PATHS+=("/usr/local/bin/php")
    fi
    
    # Rutas comunes
    PHP_PATHS+=("/usr/bin/php")
    PHP_PATHS+=("$(which php 2>/dev/null)")
    
    for php_path in "${PHP_PATHS[@]}"; do
        if [ -f "$php_path" ] && [ -x "$php_path" ]; then
            if "$php_path" -v > /dev/null 2>&1; then
                echo "$php_path"
                return 0
            fi
        fi
    done
    
    return 1
}

# Verificar si PHP está instalado
echo -e "${YELLOW}[1/4] Verificando PHP...${NC}"
PHP_CMD=$(find_php)

if [ -z "$PHP_CMD" ]; then
    echo -e "${RED}❌ PHP no encontrado${NC}"
    echo -e "${YELLOW}Por favor instala PHP:${NC}"
    echo -e "  macOS: brew install php"
    echo -e "  Linux: sudo apt-get install php"
    exit 1
fi

PHP_VERSION=$($PHP_CMD -v | head -n 1)
echo -e "${GREEN}✅ PHP encontrado: $PHP_VERSION${NC}"

# Verificar extensión PDO PostgreSQL
echo -e "${YELLOW}[2/4] Verificando extensión PDO PostgreSQL...${NC}"
if ! $PHP_CMD -m | grep -q "pdo_pgsql"; then
    echo -e "${RED}⚠️  Advertencia: Extensión pdo_pgsql no encontrada${NC}"
    echo -e "${YELLOW}Si tienes errores de conexión, instala la extensión:${NC}"
    echo -e "  macOS: brew install php-pgsql"
    echo -e "  Linux: sudo apt-get install php-pgsql"
else
    echo -e "${GREEN}✅ Extensión PDO PostgreSQL encontrada${NC}"
fi

# Verificar que PostgreSQL esté ejecutándose
echo -e "${YELLOW}[3/4] Verificando PostgreSQL...${NC}"
if command -v psql &> /dev/null; then
    # Detectar host y puerto de PostgreSQL
    PG_HOST="localhost"
    PG_PORT="5432"
    
    # Verificar si está ejecutándose
    if pg_isready -h "$PG_HOST" -p "$PG_PORT" > /dev/null 2>&1; then
        echo -e "${GREEN}✅ PostgreSQL está ejecutándose en $PG_HOST:$PG_PORT${NC}"
        
        # Verificar conexión a la base de datos
        if psql -h "$PG_HOST" -p "$PG_PORT" -d gimnasio_db -c "\dt" > /dev/null 2>&1; then
            TABLES=$(psql -h "$PG_HOST" -p "$PG_PORT" -d gimnasio_db -t -c "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'public';" 2>/dev/null | tr -d ' ')
            if [ ! -z "$TABLES" ] && [ "$TABLES" -ge 4 ]; then
                echo -e "${GREEN}✅ Base de datos 'gimnasio_db' configurada ($TABLES tablas)${NC}"
            else
                echo -e "${YELLOW}⚠️  Base de datos existe pero tiene menos tablas de las esperadas${NC}"
            fi
        else
            echo -e "${YELLOW}⚠️  Base de datos 'gimnasio_db' no accesible o no existe${NC}"
            echo -e "${YELLOW}   Ejecuta: createdb gimnasio_db${NC}"
            echo -e "${YELLOW}   Luego: psql -d gimnasio_db -f database/schema.sql${NC}"
        fi
    else
        echo -e "${RED}⚠️  PostgreSQL no está ejecutándose${NC}"
        echo -e "${YELLOW}Inicia PostgreSQL con:${NC}"
        if [[ "$OSTYPE" == "darwin"* ]]; then
            echo -e "  brew services start postgresql@14"
            echo -e "  o"
            echo -e "  pg_ctl -D /opt/homebrew/var/postgresql@14 start"
        else
            echo -e "  sudo service postgresql start"
            echo -e "  o"
            echo -e "  sudo systemctl start postgresql"
        fi
    fi
else
    echo -e "${YELLOW}⚠️  psql no encontrado en PATH${NC}"
    echo -e "${YELLOW}Asegúrate de que PostgreSQL esté instalado${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        echo -e "${YELLOW}  Instala con: brew install postgresql@14${NC}"
    else
        echo -e "${YELLOW}  Instala con: sudo apt-get install postgresql${NC}"
    fi
fi

# Navegar a la carpeta public
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PUBLIC_DIR="$SCRIPT_DIR/public"

if [ ! -d "$PUBLIC_DIR" ]; then
    echo -e "${RED}❌ Error: Carpeta public/ no encontrada${NC}"
    exit 1
fi

# Verificar que index.php existe
if [ ! -f "$PUBLIC_DIR/index.php" ]; then
    echo -e "${RED}❌ Error: index.php no encontrado en public/${NC}"
    exit 1
fi

# Verificar configuración de base de datos
echo -e "${YELLOW}[4/5] Verificando configuración...${NC}"
CONFIG_FILE="$SCRIPT_DIR/config/database.php"
if [ -f "$CONFIG_FILE" ]; then
    echo -e "${GREEN}✅ Archivo de configuración encontrado${NC}"
else
    echo -e "${RED}❌ Archivo de configuración no encontrado: $CONFIG_FILE${NC}"
    echo -e "${YELLOW}   Crea el archivo config/database.php con tus credenciales${NC}"
    exit 1
fi

# Configurar puerto y host
PORT=${1:-8000}
HOST=${2:-localhost}

# Verificar que el puerto no esté en uso
if command -v lsof &> /dev/null; then
    if lsof -Pi :$PORT -sTCP:LISTEN -t >/dev/null 2>&1; then
        echo -e "${RED}❌ Puerto $PORT ya está en uso${NC}"
        echo -e "${YELLOW}   Usa otro puerto: ./run.sh 8080${NC}"
        exit 1
    fi
fi

echo -e "${YELLOW}[5/5] Iniciando servidor...${NC}"
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}  Servidor iniciado exitosamente${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "${BLUE}📍 URL Principal:${NC}"
echo -e "   http://$HOST:$PORT/index.php"
echo ""
echo -e "${BLUE}📋 Rutas disponibles:${NC}"
echo -e "   • Miembros:    http://$HOST:$PORT/index.php?controller=member&action=index"
echo -e "   • Clases:      http://$HOST:$PORT/index.php?controller=class&action=index"
echo -e "   • Pagos:       http://$HOST:$PORT/index.php?controller=payment&action=index"
echo ""
if [[ "$HOST" == "0.0.0.0" ]]; then
    LOCAL_IP=$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "tu-ip-local")
    echo -e "${CYAN}🌐 Acceso desde red local:${NC}"
    echo -e "   http://$LOCAL_IP:$PORT/index.php"
    echo ""
fi
echo -e "${YELLOW}Presiona Ctrl+C para detener el servidor${NC}"
echo ""

# Cambiar al directorio public y ejecutar el servidor
cd "$PUBLIC_DIR"

# Función para limpiar al salir
cleanup() {
    echo ""
    echo -e "${YELLOW}Deteniendo servidor...${NC}"
    exit 0
}

trap cleanup INT TERM

# Ejecutar servidor PHP
$PHP_CMD -S $HOST:$PORT

