# 🪟 Guía de Instalación para Windows

Esta guía detallada te ayudará a instalar y configurar el Sistema de Gestión de Gimnasio en Windows.

## 📋 Requisitos Previos

- Windows 10 o superior
- Permisos de administrador (para algunas instalaciones)
- Conexión a Internet

## 🔧 Paso 1: Instalar PHP

### Opción A: Instalador de PHP (Recomendado)

1. **Descargar PHP:**
   - Visita: https://windows.php.net/download/
   - Descarga la versión "Thread Safe" más reciente (PHP 8.1 o superior)
   - Elige la versión "x64" si tienes Windows 64-bit

2. **Extraer PHP:**
   - Crea una carpeta: `C:\php`
   - Extrae todos los archivos del ZIP descargado a `C:\php`

3. **Configurar PHP:**
   - Copia `php.ini-development` y renómbralo a `php.ini`
   - Abre `php.ini` con un editor de texto (Notepad++ recomendado)

4. **Habilitar extensiones necesarias:**
   En `php.ini`, busca y descomenta (quita el `;` al inicio):
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   extension=mbstring
   ```

5. **Agregar PHP al PATH:**
   - Presiona `Win + R`, escribe `sysdm.cpl` y presiona Enter
   - Ve a la pestaña "Opciones avanzadas"
   - Click en "Variables de entorno"
   - En "Variables del sistema", busca "Path" y click en "Editar"
   - Click en "Nuevo" y agrega: `C:\php`
   - Click en "Aceptar" en todas las ventanas
   - **Reinicia PowerShell/CMD** para que los cambios surtan efecto

6. **Verificar instalación:**
   ```cmd
   php -v
   ```

### Opción B: XAMPP (Más fácil para principiantes)

1. **Descargar XAMPP:**
   - Visita: https://www.apachefriends.org/download.html
   - Descarga la versión para Windows (incluye PHP, Apache, MySQL)

2. **Instalar XAMPP:**
   - Ejecuta el instalador
   - Selecciona los componentes: Apache y PHP
   - Instala en `C:\xampp` (recomendado)

3. **Habilitar extensiones:**
   - Abre `C:\xampp\php\php.ini`
   - Busca y descomenta:
     ```ini
     extension=pdo_pgsql
     extension=pgsql
     ```

4. **Verificar:**
   ```cmd
   C:\xampp\php\php.exe -v
   ```

### Opción C: WAMP

1. **Descargar WAMP:**
   - Visita: https://www.wampserver.com/
   - Descarga e instala WAMP

2. **Habilitar extensiones:**
   - Click derecho en el icono de WAMP → PHP → php.ini
   - Busca y descomenta:
     ```ini
     extension=pdo_pgsql
     extension=pgsql
     ```

## 🗄️ Paso 2: Instalar PostgreSQL

1. **Descargar PostgreSQL:**
   - Visita: https://www.postgresql.org/download/windows/
   - Descarga el instalador para Windows

2. **Instalar PostgreSQL:**
   - Ejecuta el instalador
   - Durante la instalación:
     - **Puerto**: 5432 (por defecto)
     - **Usuario**: postgres (por defecto)
     - **Contraseña**: Anota la contraseña que configures (la necesitarás)
   - Completa la instalación

3. **Agregar PostgreSQL al PATH:**
   - Durante la instalación, marca la opción "Agregar al PATH"
   - O manualmente agrega: `C:\Program Files\PostgreSQL\15\bin` (ajusta la versión)

4. **Verificar instalación:**
   ```cmd
   psql --version
   ```

5. **Iniciar PostgreSQL:**
   - Busca "Services" en el menú de inicio
   - Busca "postgresql-x64-15" (o tu versión)
   - Asegúrate de que esté "Running"
   - Si no está corriendo, click derecho → Start

## 📁 Paso 3: Configurar el Proyecto

### 3.1. Descargar/Clonar el Proyecto

```cmd
# Si tienes Git instalado
git clone <url-del-repositorio>
cd ClienteServidor

# O descarga el ZIP y extráelo a una carpeta
```

### 3.2. Configurar Base de Datos

1. **Editar `config/database.php`:**
   ```php
   private const DB_HOST = 'localhost';
   private const DB_NAME = 'gimnasio_db';
   private const DB_USER = 'postgres';
   private const DB_PASS = 'tu_contraseña_aqui';  // La que configuraste
   private const DB_PORT = '5432';
   ```

2. **Crear la base de datos:**

   **Opción A: Usando psql (CMD/PowerShell)**
   ```cmd
   psql -U postgres
   ```
   Dentro de psql:
   ```sql
   CREATE DATABASE gimnasio_db;
   \q
   ```

   **Opción B: Usando pgAdmin (GUI)**
   - Abre pgAdmin 4 desde el menú de inicio
   - Conecta al servidor (usuario: postgres, contraseña: la que configuraste)
   - Click derecho en "Databases" → Create → Database
   - Nombre: `gimnasio_db`
   - Click en "Save"

3. **Ejecutar script SQL:**

   **Opción A: Desde línea de comandos**
   ```cmd
   psql -U postgres -d gimnasio_db -f database\schema.sql
   ```

   **Opción B: Desde pgAdmin**
   - Click derecho en `gimnasio_db` → Query Tool
   - Click en "Open File" (📁)
   - Selecciona `database/schema.sql`
   - Click en "Execute" (▶️) o presiona F5

## 🚀 Paso 4: Ejecutar el Proyecto

### Opción A: Servidor PHP Built-in (Recomendado para desarrollo)

1. **Abrir PowerShell o CMD:**
   - Navega a la carpeta del proyecto
   ```cmd
   cd C:\ruta\a\ClienteServidor
   ```

2. **Navegar a la carpeta public:**
   ```cmd
   cd public
   ```

3. **Iniciar servidor:**
   ```cmd
   php -S localhost:8000
   ```

4. **Abrir navegador:**
   - Visita: `http://localhost:8000/index.php`

### Opción B: XAMPP

1. **Copiar proyecto:**
   - Copia la carpeta `ClienteServidor` a `C:\xampp\htdocs\`

2. **Iniciar Apache:**
   - Abre el Panel de Control de XAMPP
   - Click en "Start" junto a Apache

3. **Acceder:**
   - Visita: `http://localhost/ClienteServidor/public/index.php`

### Opción C: WAMP

1. **Copiar proyecto:**
   - Copia la carpeta `ClienteServidor` a `C:\wamp64\www\`

2. **Iniciar WAMP:**
   - Asegúrate de que el icono de WAMP esté verde

3. **Acceder:**
   - Visita: `http://localhost/ClienteServidor/public/index.php`

## 🔍 Verificación

### Verificar PHP
```cmd
php -v
php -m | findstr pdo_pgsql
```

### Verificar PostgreSQL
```cmd
psql --version
psql -U postgres -c "SELECT version();"
```

### Verificar Base de Datos
```cmd
psql -U postgres -d gimnasio_db -c "\dt"
```

Deberías ver: `members`, `classes`, `membership_types`, `payments`

## 🐛 Solución de Problemas Comunes

### Error: "php no se reconoce como comando"

**Solución:**
- Verifica que PHP esté en el PATH
- Reinicia PowerShell/CMD después de agregar al PATH
- Usa la ruta completa: `C:\php\php.exe -v`

### Error: "psql no se reconoce como comando"

**Solución:**
- Agrega PostgreSQL al PATH: `C:\Program Files\PostgreSQL\15\bin`
- O usa la ruta completa: `"C:\Program Files\PostgreSQL\15\bin\psql.exe" -U postgres`

### Error: "extension pdo_pgsql not found"

**Solución:**
1. Verifica que `php_pgsql.dll` y `php_pdo_pgsql.dll` existan en `C:\php\ext\`
2. Si no existen, descárgalos de: https://pecl.php.net/package/pgsql
3. Descomenta en `php.ini`:
   ```ini
   extension=pdo_pgsql
   extension=pgsql
   ```
4. Reinicia el servidor web o PowerShell

### Error: "Connection refused" o "No se puede conectar"

**Solución:**
1. Verifica que PostgreSQL esté ejecutándose:
   - Abre "Services" (Win + R → `services.msc`)
   - Busca "postgresql" y verifica que esté "Running"
2. Verifica el puerto en `config/database.php`
3. Verifica la contraseña de PostgreSQL

### Error: "Database does not exist"

**Solución:**
```cmd
psql -U postgres
CREATE DATABASE gimnasio_db;
\q
psql -U postgres -d gimnasio_db -f database\schema.sql
```

### Puerto 8000 ocupado

**Solución:**
Usa otro puerto:
```cmd
php -S localhost:8080
```

## 📝 Notas Importantes

1. **Rutas en Windows:**
   - Usa barras invertidas `\` en CMD
   - Usa barras normales `/` en PowerShell
   - O usa comillas dobles para rutas con espacios

2. **Permisos:**
   - Algunas operaciones requieren ejecutar como Administrador
   - Click derecho en PowerShell/CMD → "Ejecutar como administrador"

3. **Firewall:**
   - Windows Defender puede bloquear PostgreSQL
   - Permite PostgreSQL a través del firewall si es necesario

4. **Servicios:**
   - PostgreSQL se ejecuta como servicio de Windows
   - Puedes iniciarlo/detenerlo desde "Services" (`services.msc`)

## 🎓 Próximos Pasos

Una vez que el proyecto esté ejecutándose:

1. Abre `http://localhost:8000/index.php` en tu navegador
2. Prueba crear, editar y eliminar miembros
3. Prueba crear clases y registrar pagos
4. Revisa `GUIA_ESTUDIANTE.md` para aprender a agregar funcionalidades

## 📚 Recursos Adicionales

- **PHP para Windows**: https://windows.php.net/
- **PostgreSQL para Windows**: https://www.postgresql.org/download/windows/
- **XAMPP**: https://www.apachefriends.org/
- **WAMP**: https://www.wampserver.com/
- **pgAdmin**: Incluido con PostgreSQL

---

**¡Listo para desarrollar en Windows!** 🚀

