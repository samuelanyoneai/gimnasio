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