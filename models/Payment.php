<?php
/**
 * Modelo de Pago
 * 
 * Este modelo representa la capa de acceso a datos para los pagos.
 * Se ejecuta en el SERVIDOR y se comunica con la base de datos.
 */

require_once __DIR__ . '/../config/database.php';

class Payment {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene todos los pagos desde el SERVIDOR de base de datos
     * 
     * @return array Lista de pagos con información de miembros y tipos de membresía
     */
    public function getAll() {
        $sql = "SELECT p.*, m.name as member_name, m.email as member_email,
                       mt.name as membership_type_name, mt.price as membership_price
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                INNER JOIN membership_types mt ON p.membership_type_id = mt.id
                ORDER BY p.payment_date DESC, p.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un pago por ID desde el SERVIDOR
     * 
     * @param int $id ID del pago
     * @return array|false Datos del pago o false si no existe
     */
    public function getById($id) {
        $sql = "SELECT p.*, m.name as member_name, m.email as member_email,
                       mt.name as membership_type_name
                FROM payments p
                INNER JOIN members m ON p.member_id = m.id
                INNER JOIN membership_types mt ON p.membership_type_id = mt.id
                WHERE p.id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene todos los pagos de un miembro específico desde el SERVIDOR
     * 
     * @param int $memberId ID del miembro
     * @return array Lista de pagos del miembro
     */
    public function getByMemberId($memberId) {
        $sql = "SELECT p.*, mt.name as membership_type_name, mt.price as membership_price
                FROM payments p
                INNER JOIN membership_types mt ON p.membership_type_id = mt.id
                WHERE p.member_id = :member_id
                ORDER BY p.payment_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo pago en el SERVIDOR de base de datos
     * 
     * @param array $data Datos del pago
     * @return int ID del pago creado
     */
    public function create($data) {
        $sql = "INSERT INTO payments (member_id, membership_type_id, amount, payment_date, payment_method, notes) 
                VALUES (:member_id, :membership_type_id, :amount, :payment_date, :payment_method, :notes)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'member_id' => $data['member_id'],
            'membership_type_id' => $data['membership_type_id'],
            'amount' => $data['amount'],
            'payment_date' => $data['payment_date'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'notes' => $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Obtiene todos los tipos de membresía desde el SERVIDOR
     * 
     * @return array Lista de tipos de membresía
     */
    public function getMembershipTypes() {
        $stmt = $this->db->query("SELECT * FROM membership_types ORDER BY price ASC");
        return $stmt->fetchAll();
    }
}

