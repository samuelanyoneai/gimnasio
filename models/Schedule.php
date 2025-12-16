<?php
/**
 * Modelo de Horario
 * 
 * Este modelo representa la capa de acceso a datos para los horarios del gimnasio.
 * Se ejecuta en el SERVIDOR y se comunica con la base de datos.
 */

require_once __DIR__ . '/../config/database.php';

class Schedule {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene todos los horarios desde el SERVIDOR de base de datos
     * 
     * @return array Lista de horarios ordenados por día de la semana
     */
    public function getAll() {
        $sql = "SELECT * FROM schedules 
                ORDER BY 
                    CASE day_of_week
                        WHEN 'Lunes' THEN 1
                        WHEN 'Martes' THEN 2
                        WHEN 'Miércoles' THEN 3
                        WHEN 'Jueves' THEN 4
                        WHEN 'Viernes' THEN 5
                        WHEN 'Sábado' THEN 6
                        WHEN 'Domingo' THEN 7
                    END";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtiene un horario por ID desde el SERVIDOR
     * 
     * @param int $id ID del horario
     * @return array|false Datos del horario o false si no existe
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM schedules WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Obtiene horarios activos agrupados por día
     * 
     * @return array Lista de horarios activos
     */
    public function getActiveSchedules() {
        $sql = "SELECT * FROM schedules 
                WHERE is_active = true 
                ORDER BY 
                    CASE day_of_week
                        WHEN 'Lunes' THEN 1
                        WHEN 'Martes' THEN 2
                        WHEN 'Miércoles' THEN 3
                        WHEN 'Jueves' THEN 4
                        WHEN 'Viernes' THEN 5
                        WHEN 'Sábado' THEN 6
                        WHEN 'Domingo' THEN 7
                    END";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    /**
     * Crea un nuevo horario en el SERVIDOR de base de datos
     * 
     * @param array $data Datos del horario (day_of_week, opening_time, closing_time, is_active, notes)
     * @return int ID del horario creado
     */
    public function create($data) {
        $sql = "INSERT INTO schedules (day_of_week, opening_time, closing_time, is_active, notes) 
                VALUES (:day_of_week, :opening_time, :closing_time, :is_active, :notes)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'day_of_week' => $data['day_of_week'],
            'opening_time' => $data['opening_time'],
            'closing_time' => $data['closing_time'],
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null
        ]);
        return $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un horario existente en el SERVIDOR
     * 
     * @param int $id ID del horario
     * @param array $data Datos actualizados
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $data) {
        $sql = "UPDATE schedules 
                SET day_of_week = :day_of_week, 
                    opening_time = :opening_time, 
                    closing_time = :closing_time,
                    is_active = :is_active,
                    notes = :notes
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'day_of_week' => $data['day_of_week'],
            'opening_time' => $data['opening_time'],
            'closing_time' => $data['closing_time'],
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null
        ]);
    }
    
    /**
     * Elimina un horario del SERVIDOR de base de datos
     * 
     * @param int $id ID del horario
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM schedules WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * Verifica si ya existe un horario para un día específico
     * 
     * @param string $dayOfWeek Día de la semana
     * @param int|null $excludeId ID a excluir de la verificación (para edición)
     * @return bool True si el día ya tiene un horario
     */
    public function dayExists($dayOfWeek, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM schedules WHERE day_of_week = :day_of_week";
        $params = ['day_of_week' => $dayOfWeek];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Valida que la hora de cierre sea posterior a la hora de apertura
     * 
     * @param string $openingTime Hora de apertura (formato HH:MM)
     * @param string $closingTime Hora de cierre (formato HH:MM)
     * @return bool True si el rango es válido
     */
    public function validateTimeRange($openingTime, $closingTime) {
        return strtotime($closingTime) > strtotime($openingTime);
    }
}

