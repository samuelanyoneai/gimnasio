<?php
/**
 * Controlador de Clases
 * 
 * Este controlador maneja las peticiones HTTP del CLIENTE y coordina
 * la comunicación entre la VISTA (cliente) y el MODELO (servidor).
 */

require_once __DIR__ . '/../models/Class.php';

class ClassController {
    private $classModel;
    
    public function __construct() {
        $this->classModel = new ClassModel();
    }
    
    /**
     * Maneja la petición GET del CLIENTE para listar clases
     */
    public function index() {
        // SERVIDOR: Obtiene datos de la base de datos
        $classes = $this->classModel->getAll();
        
        // SERVIDOR: Genera respuesta HTML para el CLIENTE
        require_once __DIR__ . '/../views/classes/index.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de creación
     */
    public function create() {
        $errors = [];
        require_once __DIR__ . '/../views/classes/create.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para crear una nueva clase
     */
    public function store() {
        // SERVIDOR: Valida datos recibidos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Guarda en base de datos
            $id = $this->classModel->create($_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=class&action=index&success=created');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores al CLIENTE
        require_once __DIR__ . '/../views/classes/create.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de edición
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=class&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Obtiene datos de la clase
        $class = $this->classModel->getById($id);
        
        if (!$class) {
            header('Location: /index.php?controller=class&action=index&error=not_found');
            exit;
        }
        
        $errors = [];
        require_once __DIR__ . '/../views/classes/edit.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para actualizar una clase
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=class&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Valida datos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Actualiza en base de datos
            $this->classModel->update($id, $_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=class&action=index&success=updated');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores
        $class = $this->classModel->getById($id);
        require_once __DIR__ . '/../views/classes/edit.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para eliminar una clase
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // SERVIDOR: Elimina de la base de datos
            $this->classModel->delete($id);
            header('Location: /index.php?controller=class&action=index&success=deleted');
        } else {
            header('Location: /index.php?controller=class&action=index&error=delete_failed');
        }
        exit;
    }
    
    /**
     * SERVIDOR: Valida los datos recibidos del CLIENTE
     */
    private function validate($data) {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'El nombre de la clase es requerido';
        }
        
        if (empty($data['instructor'])) {
            $errors['instructor'] = 'El instructor es requerido';
        }
        
        if (empty($data['schedule_time'])) {
            $errors['schedule_time'] = 'El horario es requerido';
        }
        
        if (empty($data['schedule_days'])) {
            $errors['schedule_days'] = 'Los días de la semana son requeridos';
        }
        
        if (empty($data['capacity']) || !is_numeric($data['capacity']) || $data['capacity'] <= 0) {
            $errors['capacity'] = 'La capacidad debe ser un número mayor a 0';
        }
        
        return $errors;
    }
}

