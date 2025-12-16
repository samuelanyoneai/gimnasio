<?php
/**
 * Modelo de Miembro
 * 
 * Este modelo representa la capa de acceso a datos para los miembros.
 * Se ejecuta en el SERVIDOR y se comunica con la base de datos.
 */

require_once __DIR__ . '/../config/database.php';

class Member {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene todos los miembros desde el SERVIDOR de base de datos
     * 
     * @return array Lista de miembros
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM members ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un miembro por ID desde el SERVIDOR
     * 
     * @param int $id ID del miembro
     * @return array|false Datos del miembro o false si no existe
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Crea un nuevo miembro en el SERVIDOR de base de datos
     * 
     * @param array $data Datos del miembro (name, email, phone, registration_date)
     * @return int ID del miembro creado
     */
    public function create($data) {
        $sql = "INSERT INTO members (name, email, phone, registration_date) 
                VALUES (:name, :email, :phone, :registration_date)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'registration_date' => $data['registration_date']
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un miembro existente en el SERVIDOR
     * 
     * @param int $id ID del miembro
     * @param array $data Datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $sql = "UPDATE members 
                SET name = :name, email = :email, phone = :phone, 
                    registration_date = :registration_date, status = :status
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'registration_date' => $data['registration_date'],
            'status' => $data['status'] ?? 'active'
        ]);
    }
    
    /**
     * Elimina un miembro del SERVIDOR de base de datos
     * 
     * @param int $id ID del miembro
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM members WHERE id = :id");
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
        $sql = "SELECT COUNT(*) FROM members WHERE email = :email";
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

