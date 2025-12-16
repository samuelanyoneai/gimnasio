<?php
/**
 * Configuración de Base de Datos
 * 
 * Este archivo maneja la conexión al servidor de base de datos PostgreSQL.
 * Demuestra la parte SERVIDOR de la arquitectura Cliente-Servidor.
 * Agregar configuraciones adicionales según sea necesario.
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Configuración de conexión - SERVIDOR
    // Nota: En macOS con Homebrew, PostgreSQL usa el usuario actual como superusuario
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'gimnasio_db';
    private const DB_USER = 'postgres';  // Usuario actual (cambiar si es necesario)
    private const DB_PASS = '1704';           // Sin contraseña para usuario local
    private const DB_PORT = '5432';
    
    /**
     * Constructor privado para implementar patrón Singleton
     * Solo permite una conexión a la base de datos
     */
    private function __construct() {
        try {
            $dsn = "pgsql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_NAME . ";options='--client_encoding=UTF8'";
            $this->connection = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            // Asegurar codificación UTF-8
            $this->connection->exec("SET NAMES 'UTF8'");
        } catch (PDOException $e) {
            die("Error de conexión al servidor de base de datos: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene la instancia única de la conexión (Singleton)
     * 
     * @return Database Instancia de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtiene la conexión PDO
     * 
     * @return PDO Conexión a la base de datos
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Previene la clonación de la instancia
     */
    private function __clone() {}
    
    /**
     * Previene la deserialización de la instancia
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

