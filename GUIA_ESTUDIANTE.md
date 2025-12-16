# Guía del Estudiante: Arquitectura Cliente-Servidor con MVC

## 📚 Introducción

Esta guía te ayudará a entender la arquitectura **Cliente-Servidor** y el patrón **MVC** (Modelo-Vista-Controlador) mediante una aplicación práctica de gestión de gimnasio.

## 🏗️ Arquitectura Cliente-Servidor

### ¿Qué es la Arquitectura Cliente-Servidor?

La arquitectura Cliente-Servidor es un modelo de comunicación donde:

- **CLIENTE**: Es el navegador web donde el usuario interactúa. Su responsabilidad es:
  - Mostrar la interfaz de usuario (HTML/CSS)
  - Capturar datos del usuario (formularios)
  - Enviar peticiones HTTP al servidor
  - Recibir y mostrar respuestas del servidor

- **SERVIDOR**: Es el servidor web (PHP) que procesa las peticiones. Su responsabilidad es:
  - Recibir peticiones HTTP del cliente
  - Procesar la lógica de negocio
  - Acceder y modificar la base de datos
  - Generar respuestas HTML/JSON
  - Validar seguridad y datos

### Flujo de Comunicación

```
┌─────────────┐                    ┌─────────────┐                    ┌─────────────┐
│   CLIENTE   │                    │   SERVIDOR  │                    │   BASE DE  │
│ (Navegador) │                    │    (PHP)    │                    │   DATOS    │
└─────────────┘                    └─────────────┘                    └─────────────┘
      │                                   │                                   │
      │  1. Petición HTTP (GET/POST)     │                                   │
      │──────────────────────────────────>│                                   │
      │                                   │  2. Consulta SQL                 │
      │                                   │──────────────────────────────────>│
      │                                   │  3. Resultados                   │
      │                                   │<──────────────────────────────────│
      │  4. Respuesta HTML                │                                   │
      │<──────────────────────────────────│                                   │
      │                                   │                                   │
```

## 🎯 Patrón MVC

El patrón MVC separa la aplicación en tres componentes:

### 1. **Modelo (Model)**
- **Ubicación**: `models/`
- **Responsabilidad**: Acceso a datos (base de datos)
- **Ejemplo**: `Member.php` - Maneja todas las operaciones de base de datos para miembros

### 2. **Vista (View)**
- **Ubicación**: `views/`
- **Responsabilidad**: Presentación (HTML que ve el usuario)
- **Ejemplo**: `views/members/index.php` - Muestra la lista de miembros

### 3. **Controlador (Controller)**
- **Ubicación**: `controllers/`
- **Responsabilidad**: Lógica de negocio y coordinación
- **Ejemplo**: `MemberController.php` - Coordina entre Modelo y Vista

### Flujo MVC

```
CLIENTE → Controlador → Modelo → Base de Datos
                ↓
              Vista → CLIENTE
```

## 📁 Estructura del Proyecto

```
ClienteServidor/
├── config/
│   └── database.php          # Configuración de conexión (SERVIDOR)
├── models/                    # Modelos - Acceso a datos (SERVIDOR)
│   ├── Member.php
│   ├── Class.php
│   └── Payment.php
├── controllers/               # Controladores - Lógica (SERVIDOR)
│   ├── MemberController.php
│   ├── ClassController.php
│   └── PaymentController.php
├── views/                     # Vistas - Interfaz (CLIENTE)
│   ├── members/
│   ├── classes/
│   ├── payments/
│   └── layouts/
├── public/                    # Punto de entrada (SERVIDOR)
│   ├── index.php             # Router
│   └── assets/
│       ├── css/
│       └── js/
└── database/
    └── schema.sql            # Script de base de datos
```

## 🚀 Cómo Agregar una Nueva Funcionalidad

Vamos a crear un ejemplo completo: **Gestión de Instructores**

### Paso 1: Crear la Tabla en la Base de Datos (SERVIDOR)

**Archivo**: `database/schema.sql`

Agrega al final del archivo:

```sql
-- Tabla de Instructores
CREATE TABLE IF NOT EXISTS instructors (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    hire_date DATE NOT NULL DEFAULT CURRENT_DATE,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar datos de ejemplo
INSERT INTO instructors (name, email, phone, specialization, hire_date) VALUES
    ('Ana Martínez', 'ana.martinez@gimnasio.com', '0987654321', 'Yoga', '2023-01-15'),
    ('Pedro Rodríguez', 'pedro.rodriguez@gimnasio.com', '0987654322', 'CrossFit', '2023-02-20'),
    ('Laura Sánchez', 'laura.sanchez@gimnasio.com', '0987654323', 'Pilates', '2023-03-10')
ON CONFLICT (email) DO NOTHING;
```

**Ejecuta el script SQL** en PostgreSQL:
```bash
psql -U postgres -d gimnasio_db -f database/schema.sql
```

### Paso 2: Crear el Modelo (SERVIDOR)

**Archivo**: `models/Instructor.php`

Crea el archivo con el siguiente código:

```php
<?php
/**
 * Modelo de Instructor
 * 
 * Este modelo representa la capa de acceso a datos para los instructores.
 * Se ejecuta en el SERVIDOR y se comunica con la base de datos.
 */

require_once __DIR__ . '/../config/database.php';

class Instructor {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene todos los instructores desde el SERVIDOR de base de datos
     * 
     * @return array Lista de instructores
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM instructors ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un instructor por ID desde el SERVIDOR
     * 
     * @param int $id ID del instructor
     * @return array|false Datos del instructor o false si no existe
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM instructors WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Crea un nuevo instructor en el SERVIDOR de base de datos
     * 
     * @param array $data Datos del instructor
     * @return int ID del instructor creado
     */
    public function create($data) {
        $sql = "INSERT INTO instructors (name, email, phone, specialization, hire_date) 
                VALUES (:name, :email, :phone, :specialization, :hire_date)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'hire_date' => $data['hire_date']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un instructor existente en el SERVIDOR
     * 
     * @param int $id ID del instructor
     * @param array $data Datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $sql = "UPDATE instructors 
                SET name = :name, email = :email, phone = :phone, 
                    specialization = :specialization, hire_date = :hire_date, status = :status
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'hire_date' => $data['hire_date'],
            'status' => $data['status'] ?? 'active'
        ]);
    }
    
    /**
     * Elimina un instructor del SERVIDOR de base de datos
     * 
     * @param int $id ID del instructor
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM instructors WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verifica si un email ya existe en el SERVIDOR
     * 
     * @param string $email Email a verificar
     * @param int|null $excludeId ID a excluir de la verificación (para edición)
     * @return bool True si el email existe
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM instructors WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}
```

### Paso 3: Crear el Controlador (SERVIDOR)

**Archivo**: `controllers/InstructorController.php`

Crea el archivo con el siguiente código:

```php
<?php
/**
 * Controlador de Instructores
 * 
 * Este controlador maneja las peticiones HTTP del CLIENTE y coordina
 * la comunicación entre la VISTA (cliente) y el MODELO (servidor).
 */

require_once __DIR__ . '/../models/Instructor.php';

class InstructorController {
    private $instructorModel;
    
    public function __construct() {
        $this->instructorModel = new Instructor();
    }
    
    /**
     * Maneja la petición GET del CLIENTE para listar instructores
     */
    public function index() {
        // SERVIDOR: Obtiene datos de la base de datos
        $instructors = $this->instructorModel->getAll();
        
        // SERVIDOR: Genera respuesta HTML para el CLIENTE
        require_once __DIR__ . '/../views/instructors/index.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de creación
     */
    public function create() {
        $errors = [];
        require_once __DIR__ . '/../views/instructors/create.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para crear un nuevo instructor
     */
    public function store() {
        // SERVIDOR: Valida datos recibidos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Guarda en base de datos
            $id = $this->instructorModel->create($_POST);
            
            // SERVIDOR: Redirige al CLIENTE a la lista
            header('Location: /index.php?controller=instructor&action=index&success=created');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores al CLIENTE
        require_once __DIR__ . '/../views/instructors/create.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de edición
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=instructor&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Obtiene datos del instructor
        $instructor = $this->instructorModel->getById($id);
        
        if (!$instructor) {
            header('Location: /index.php?controller=instructor&action=index&error=not_found');
            exit;
        }
        
        $errors = [];
        require_once __DIR__ . '/../views/instructors/edit.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para actualizar un instructor
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=instructor&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Valida datos del CLIENTE
        $errors = $this->validate($_POST, $id);
        
        if (empty($errors)) {
            // SERVIDOR: Actualiza en base de datos
            $this->instructorModel->update($id, $_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=instructor&action=index&success=updated');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores
        $instructor = $this->instructorModel->getById($id);
        require_once __DIR__ . '/../views/instructors/edit.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para eliminar un instructor
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // SERVIDOR: Elimina de la base de datos
            $this->instructorModel->delete($id);
            header('Location: /index.php?controller=instructor&action=index&success=deleted');
        } else {
            header('Location: /index.php?controller=instructor&action=index&error=delete_failed');
        }
        exit;
    }
    
    /**
     * SERVIDOR: Valida los datos recibidos del CLIENTE
     * 
     * @param array $data Datos del formulario del CLIENTE
     * @param int|null $excludeId ID a excluir en validación de email
     * @return array Errores de validación
     */
    private function validate($data, $excludeId = null) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre es requerido';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'El email es requerido';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El email no es válido';
        } elseif ($this->instructorModel->emailExists($data['email'], $excludeId)) {
            $errors['email'] = 'El email ya está registrado';
        }
        
        if (empty($data['hire_date'])) {
            $errors['hire_date'] = 'La fecha de contratación es requerida';
        }
        
        return $errors;
    }
}
```

### Paso 4: Crear las Vistas (CLIENTE)

#### Vista: Lista de Instructores

**Archivo**: `views/instructors/index.php`

```php
<?php
/**
 * Vista: Lista de Instructores
 * 
 * CLIENTE: Esta vista se renderiza en el navegador del usuario.
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Gestión de Instructores</h2>

<div class="actions">
    <a href="/index.php?controller=instructor&action=create" class="btn btn-primary">➕ Nuevo Instructor</a>
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Especialización</th>
            <th>Fecha Contratación</th>
            <th>Estado</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($instructors)): ?>
            <tr>
                <td colspan="8" class="text-center">No hay instructores registrados</td>
            </tr>
        <?php else: ?>
            <?php foreach ($instructors as $instructor): ?>
                <tr>
                    <td><?php echo htmlspecialchars($instructor['id']); ?></td>
                    <td><?php echo htmlspecialchars($instructor['name']); ?></td>
                    <td><?php echo htmlspecialchars($instructor['email']); ?></td>
                    <td><?php echo htmlspecialchars($instructor['phone'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($instructor['specialization'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($instructor['hire_date']); ?></td>
                    <td>
                        <span class="badge badge-<?php echo $instructor['status'] === 'active' ? 'success' : 'warning'; ?>">
                            <?php echo htmlspecialchars($instructor['status']); ?>
                        </span>
                    </td>
                    <td class="actions-cell">
                        <a href="/index.php?controller=instructor&action=edit&id=<?php echo $instructor['id']; ?>" class="btn btn-sm btn-secondary">Editar</a>
                        <a href="/index.php?controller=instructor&action=delete&id=<?php echo $instructor['id']; ?>" 
                           class="btn btn-sm btn-danger" 
                           onclick="return confirm('¿Está seguro de eliminar este instructor?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
```

#### Vista: Crear Instructor

**Archivo**: `views/instructors/create.php`

```php
<?php
/**
 * Vista: Crear Instructor
 * 
 * CLIENTE: Formulario que captura datos del usuario y los envía al SERVIDOR
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Nuevo Instructor</h2>

<form method="POST" action="/index.php?controller=instructor&action=store" class="form">
    <div class="form-group">
        <label for="name">Nombre *</label>
        <input type="text" id="name" name="name" required 
               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
               class="<?php echo isset($errors['name']) ? 'error' : ''; ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['name']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required 
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" name="phone" 
               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="specialization">Especialización</label>
        <input type="text" id="specialization" name="specialization" 
               placeholder="Ej: Yoga, CrossFit, Pilates"
               value="<?php echo htmlspecialchars($_POST['specialization'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="hire_date">Fecha de Contratación *</label>
        <input type="date" id="hire_date" name="hire_date" required 
               value="<?php echo htmlspecialchars($_POST['hire_date'] ?? date('Y-m-d')); ?>"
               class="<?php echo isset($errors['hire_date']) ? 'error' : ''; ?>">
        <?php if (isset($errors['hire_date'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['hire_date']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="/index.php?controller=instructor&action=index" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
```

#### Vista: Editar Instructor

**Archivo**: `views/instructors/edit.php`

```php
<?php
/**
 * Vista: Editar Instructor
 * 
 * CLIENTE: Formulario que muestra datos del SERVIDOR y permite editarlos
 */

require_once __DIR__ . '/../layouts/header.php';
?>

<h2>Editar Instructor</h2>

<form method="POST" action="/index.php?controller=instructor&action=update" class="form">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($instructor['id']); ?>">
    
    <div class="form-group">
        <label for="name">Nombre *</label>
        <input type="text" id="name" name="name" required 
               value="<?php echo htmlspecialchars($instructor['name']); ?>"
               class="<?php echo isset($errors['name']) ? 'error' : ''; ?>">
        <?php if (isset($errors['name'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['name']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required 
               value="<?php echo htmlspecialchars($instructor['email']); ?>"
               class="<?php echo isset($errors['email']) ? 'error' : ''; ?>">
        <?php if (isset($errors['email'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['email']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="phone">Teléfono</label>
        <input type="tel" id="phone" name="phone" 
               value="<?php echo htmlspecialchars($instructor['phone'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="specialization">Especialización</label>
        <input type="text" id="specialization" name="specialization" 
               placeholder="Ej: Yoga, CrossFit, Pilates"
               value="<?php echo htmlspecialchars($instructor['specialization'] ?? ''); ?>">
    </div>
    
    <div class="form-group">
        <label for="hire_date">Fecha de Contratación *</label>
        <input type="date" id="hire_date" name="hire_date" required 
               value="<?php echo htmlspecialchars($instructor['hire_date']); ?>"
               class="<?php echo isset($errors['hire_date']) ? 'error' : ''; ?>">
        <?php if (isset($errors['hire_date'])): ?>
            <span class="error-message"><?php echo htmlspecialchars($errors['hire_date']); ?></span>
        <?php endif; ?>
    </div>
    
    <div class="form-group">
        <label for="status">Estado *</label>
        <select id="status" name="status" required>
            <option value="active" <?php echo $instructor['status'] === 'active' ? 'selected' : ''; ?>>Activo</option>
            <option value="inactive" <?php echo $instructor['status'] === 'inactive' ? 'selected' : ''; ?>>Inactivo</option>
        </select>
    </div>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="/index.php?controller=instructor&action=index" class="btn btn-secondary">Cancelar</a>
    </div>
</form>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
```

### Paso 5: Registrar el Controlador en el Router (SERVIDOR)

**Archivo**: `public/index.php`

Agrega el nuevo controlador al array `$controllers`:

```php
// SERVIDOR: Mapea nombres de controladores a clases
$controllers = [
    'member' => 'MemberController',
    'class' => 'ClassController',
    'payment' => 'PaymentController',
    'instructor' => 'InstructorController'  // ← Agregar esta línea
];
```

### Paso 6: Agregar Enlace en el Menú (CLIENTE)

**Archivo**: `views/layouts/header.php`

Agrega el enlace en la navegación:

```php
<nav class="nav">
    <a href="/index.php?controller=member&action=index" class="nav-link">Miembros</a>
    <a href="/index.php?controller=class&action=index" class="nav-link">Clases</a>
    <a href="/index.php?controller=payment&action=index" class="nav-link">Pagos</a>
    <a href="/index.php?controller=instructor&action=index" class="nav-link">Instructores</a>  <!-- ← Agregar esta línea -->
</nav>
```

## ✅ Resumen del Flujo Completo

1. **CLIENTE** hace clic en "Nuevo Instructor" → Envía petición GET
2. **SERVIDOR** (Router) recibe petición → Delega a `InstructorController::create()`
3. **Controlador** → Carga la vista `create.php`
4. **CLIENTE** ve el formulario → Usuario completa y envía (POST)
5. **SERVIDOR** recibe POST → `InstructorController::store()`
6. **Controlador** valida → Llama a `Instructor::create()`
7. **Modelo** → Ejecuta INSERT en base de datos
8. **Controlador** → Redirige al CLIENTE a la lista
9. **CLIENTE** ve la lista actualizada

## 🎓 Ejercicios Prácticos

1. **Ejercicio 1**: Crea la funcionalidad de "Equipos" siguiendo los mismos pasos

## 📝 Notas Importantes

- **Siempre valida en el SERVIDOR**: La validación del CLIENTE (JavaScript) es solo para UX
- **Usa prepared statements**: Previene SQL injection
- **Sanitiza salidas**: Usa `htmlspecialchars()` para prevenir XSS
- **Maneja errores**: Siempre valida que los datos existan antes de usarlos
- **Sigue el patrón**: Modelo → Controlador → Vista

## 🔍 Conceptos Clave

- **Cliente-Servidor**: Separación clara entre presentación (cliente) y lógica (servidor)
- **MVC**: Separación de responsabilidades (Modelo, Vista, Controlador)
- **HTTP**: Protocolo de comunicación entre cliente y servidor
- **CRUD**: Create, Read, Update, Delete - operaciones básicas de datos

¡Feliz programación! 🚀

