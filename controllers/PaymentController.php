<?php
/**
 * Controlador de Pagos
 * 
 * Este controlador maneja las peticiones HTTP del CLIENTE y coordina
 * la comunicación entre la VISTA (cliente) y el MODELO (servidor).
 */

require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Member.php';

class PaymentController {
    private $paymentModel;
    private $memberModel;
    
    public function __construct() {
        $this->paymentModel = new Payment();
        $this->memberModel = new Member();
    }
    
    /**
     * Maneja la petición GET del CLIENTE para listar pagos
     */
    public function index() {
        // SERVIDOR: Obtiene datos de la base de datos
        $payments = $this->paymentModel->getAll();
        
        // SERVIDOR: Genera respuesta HTML para el CLIENTE
        require_once __DIR__ . '/../views/payments/index.php';
    }
    
    /**
     * Maneja la petición GET del CLIENTE para mostrar formulario de creación
     */
    public function create() {
        // SERVIDOR: Obtiene datos necesarios para el formulario
        $members = $this->memberModel->getAll();
        $membershipTypes = $this->paymentModel->getMembershipTypes();
        
        $errors = [];
        require_once __DIR__ . '/../views/payments/create.php';
    }
    
    /**
     * Maneja la petición POST del CLIENTE para crear un nuevo pago
     */
    public function store() {
        // SERVIDOR: Valida datos recibidos del CLIENTE
        $errors = $this->validate($_POST);
        
        if (empty($errors)) {
            // SERVIDOR: Guarda en base de datos
            $id = $this->paymentModel->create($_POST);
            
            // SERVIDOR: Redirige al CLIENTE
            header('Location: /index.php?controller=payment&action=index&success=created');
            exit;
        }
        
        // SERVIDOR: Devuelve formulario con errores al CLIENTE
        $members = $this->memberModel->getAll();
        $membershipTypes = $this->paymentModel->getMembershipTypes();
        require_once __DIR__ . '/../views/payments/create.php';
    }
    
    /**
     * SERVIDOR: Valida los datos recibidos del CLIENTE
     */
    private function validate($data) {
        $errors = [];
        
        if (empty($data['member_id']) || !is_numeric($data['member_id'])) {
            $errors['member_id'] = 'Debe seleccionar un miembro';
        }
        
        if (empty($data['membership_type_id']) || !is_numeric($data['membership_type_id'])) {
            $errors['membership_type_id'] = 'Debe seleccionar un tipo de membresía';
        }
        
        if (empty($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'El monto debe ser un número mayor a 0';
        }
        
        if (empty($data['payment_date'])) {
            $errors['payment_date'] = 'La fecha de pago es requerida';
        }
        
        return $errors;
    }
}

