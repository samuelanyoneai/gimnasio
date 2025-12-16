<?php
/**
 * Controlador de Horarios
 * 
 * Este controlador maneja las peticiones HTTP del CLIENTE y coordina
 * la comunicación entre la VISTA (cliente) y el MODELO (servidor).
 * 
 * Flujo Cliente-Servidor:
 * 1. CLIENTE envía petición HTTP → SERVIDOR (este controlador)
 * 2. SERVIDOR procesa → MODELO accede a base de datos
 * 3. SERVIDOR genera respuesta → CLIENTE recibe HTML
 */

require_once __DIR__ . '/../models/Schedule.php';

class ScheduleController {
    private $scheduleModel;
    
    public function __construct() {
        $this->scheduleModel = new Schedule();
    }
    
    /**
     * Maneja la petición GET del CLIENTE para listar horarios
     * El SERVIDOR procesa y devuelve la vista con los datos
     */
    public function index() {
        // SERVIDOR: Obtiene datos de la base de datos
        $schedules = $this->scheduleModel->getAll();
        
        // SERVIDOR: Genera respuesta HTML para el CLIENTE
        require_once __DIR__ . '/../views/schedules/index.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de creación
     * El SERVIDOR devuelve el formulario HTML al CLIENTE
     */
    public function create() {
        $errors = [];
        require_once __DIR__ . '/../views/schedules/create.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para crear un nuevo horario
     * El SERVIDOR procesa los datos y responde al CLIENTE
     */
    public function store() {
        // SERVIDOR: Valida datos recibidos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Guarda en base de datos
            $id = $this->scheduleModel->create($_POST);
            
            // SERVIDOR: Redirige al CLIENTE a la lista
            header('Location: /index.php?controller=schedule&action=index&success=created');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores al CLIENTE
        require_once __DIR__ . '/../views/schedules/create.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de edición
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=schedule&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Obtiene datos del horario
        $schedule = $this->scheduleModel->getById($id);
        
        if (!$schedule) {
            header('Location: /index.php?controller=schedule&action=index&error=not_found');
            exit;
        }
        
        $errors = [];
        require_once __DIR__ . '/../views/schedules/edit.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para actualizar un horario
     */
    public function update() {
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            header('Location: /index.php?controller=schedule&action=index&error=not_found');
            exit;
        }
        
        // SERVIDOR: Valida datos del CLIENTE
        $errors = $this->validate($_POST, $id);
        
        if (empty($errors)) {
            // SERVIDOR: Actualiza en base de datos
            $this->scheduleModel->update($id, $_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=schedule&action=index&success=updated');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores
        $schedule = $this->scheduleModel->getById($id);
        require_once __DIR__ . '/../views/schedules/edit.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para eliminar un horario
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        
        if ($id) {
            // SERVIDOR: Elimina de la base de datos
            $this->scheduleModel->delete($id);
            header('Location: /index.php?controller=schedule&action=index&success=deleted');
        } else {
            header('Location: /index.php?controller=schedule&action=index&error=delete_failed');
        }
        exit;
    }
    
    /**
     * SERVIDOR: Valida los datos recibidos del CLIENTE
     * 
     * @param array $data Datos del formulario del CLIENTE
     * @param int|null $excludeId ID a excluir en validación de día
     * @return array Errores de validación
     */
    private function validate($data, $excludeId = null) {
        $errors = [];
        
        // Validar día de la semana
        if (empty($data['day_of_week'])) {
            $errors['day_of_week'] = 'El día de la semana es requerido';
        } else {
            $validDays = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            if (!in_array($data['day_of_week'], $validDays)) {
                $errors['day_of_week'] = 'Día de la semana inválido';
            } elseif ($this->scheduleModel->dayExists($data['day_of_week'], $excludeId)) {
                $errors['day_of_week'] = 'Ya existe un horario para este día';
            }
        }
        
        // Validar hora de apertura
        if (empty($data['opening_time'])) {
            $errors['opening_time'] = 'La hora de apertura es requerida';
        }
        
        // Validar hora de cierre
        if (empty($data['closing_time'])) {
            $errors['closing_time'] = 'La hora de cierre es requerida';
        }
        
        // Validar que la hora de cierre sea posterior a la de apertura
        if (!empty($data['opening_time']) && !empty($data['closing_time'])) {
            if (!$this->scheduleModel->validateTimeRange($data['opening_time'], $data['closing_time'])) {
                $errors['closing_time'] = 'La hora de cierre debe ser posterior a la hora de apertura';
            }
        }
        
        // Convertir checkbox is_active a boolean
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = true;
        }
        
        return $errors;
    }
}

