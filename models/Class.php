<?php
/**
 * Modelo de Clase
 * 
 * Este modelo representa la capa de acceso a datos para las clases de entrenamiento.
 * Se ejecuta en el SERVIDOR y se comunica con la base de datos.
 */

require_once __DIR__ . '/../config/database.php';

class ClassModel {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene todas las clases desde el SERVIDOR de base de datos
     * 
     * @return array Lista de clases
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM classes ORDER BY schedule_time ASC");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene una clase por ID desde el SERVIDOR
     * 
     * @param int $id ID de la clase
     * @return array|false Datos de la clase o false si no existe
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Crea una nueva clase en el SERVIDOR de base de datos
     * 
     * @param array $data Datos de la clase
     * @return int ID de la clase creada
     */
    public function create($data) {
        $sql = "INSERT INTO classes (name, instructor, schedule_time, schedule_days, capacity, description) 
                VALUES (:name, :instructor, :schedule_time, :schedule_days, :capacity, :description)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'name' => $data['name'],
            'instructor' => $data['instructor'],
            'schedule_time' => $data['schedule_time'],
            'schedule_days' => $data['schedule_days'],
            'capacity' => $data['capacity'],
            'description' => $data['description'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza una clase existente en el SERVIDOR
     * 
     * @param int $id ID de la clase
     * @param array $data Datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $sql = "UPDATE classes 
                SET name = :name, instructor = :instructor, schedule_time = :schedule_time, 
                    schedule_days = :schedule_days, capacity = :capacity, description = :description
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'],
            'instructor' => $data['instructor'],
            'schedule_time' => $data['schedule_time'],
            'schedule_days' => $data['schedule_days'],
            'capacity' => $data['capacity'],
            'description' => $data['description'] ?? null
        ]);
    }
    
    /**
     * Elimina una clase del SERVIDOR de base de datos
     * 
     * @param int $id ID de la clase
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM classes WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}

