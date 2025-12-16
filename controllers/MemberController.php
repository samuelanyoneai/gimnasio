<?php
/**
 * Controlador de Miembros
 * 
 * Este controlador maneja las peticiones HTTP del CLIENTE y coordina
 * la comunicación entre la VISTA (cliente) y el MODELO (servidor).
 * 
 * Flujo Cliente-Servidor:
 * 1. CLIENTE envía petición HTTP → SERVIDOR (este controlador)
 * 2. SERVIDOR procesa → MODELO accede a base de datos
 * 3. SERVIDOR genera respuesta → CLIENTE recibe HTML
 */

require_once __DIR__ . '/../models/Member.php';

class MemberController {
    private $memberModel;
    
    public function __construct() {
        $this->memberModel = new Member();
    }
    
    /**
     * Maneja la petición GET del CLIENTE para listar miembros
     * El SERVIDOR procesa y devuelve la vista con los datos
     */
    public function index() {
        // SERVIDOR: Obtiene datos de la base de datos
        $members = $this->memberModel->getAll();
        
        // SERVIDOR: Genera respuesta HTML para el CLIENTE
        require_once __DIR__ . '/../views/members/index.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de creación
     * El SERVIDOR devuelve el formulario HTML al CLIENTE
     */
    public function create() {
        $errors = [];
        require_once __DIR__ . '/../views/members/create.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para crear un nuevo miembro
     * El SERVIDOR procesa los datos y responde al CLIENTE
     */
    public function store() {
        // SERVIDOR: Valida datos recibidos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Guarda en base de datos
            $id = $this->memberModel->create($_POST);
            
            // SERVIDOR: Redirige al CLIENTE a la lista
            header('Location: /index.php?controller=member&action=index&success=created');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores al CLIENTE
        require_once __DIR__ . '/../views/members/create.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de edición
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=member&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Obtiene datos del miembro
        $member = $this->memberModel->getById($id);
        
        if (!$member) {
            header('Location: /index.php?controller=member&action=index&error=not_found');
            exit;
        }
        
        $errors = [];
        require_once __DIR__ . '/../views/members/edit.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para actualizar un miembro
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=member&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Valida datos del CLIENTE
        $errors = $this->validate($_POST, $id);
        
        if (empty($errors)) {
            // SERVIDOR: Actualiza en base de datos
            $this->memberModel->update($id, $_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=member&action=index&success=updated');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores
        $member = $this->memberModel->getById($id);
        require_once __DIR__ . '/../views/members/edit.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para eliminar un miembro
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // SERVIDOR: Elimina de la base de datos
            $this->memberModel->delete($id);
            header('Location: /index.php?controller=member&action=index&success=deleted');
        } else {
            header('Location: /index.php?controller=member&action=index&error=delete_failed');
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
        } elseif ($this->memberModel->emailExists($data['email'], $excludeId)) {
            $errors['email'] = 'El email ya está registrado';
        }
        
        if (empty($data['registration_date'])) {
            $errors['registration_date'] = 'La fecha de inscripción es requerida';
        }
        
        return $errors;
    }
}

