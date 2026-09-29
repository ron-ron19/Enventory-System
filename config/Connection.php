<?php
/**
 * Database Connection Helper
 * Singleton pattern for database connection
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/db_config.php';

class Connection {
    private static $instance = null;
    private static $db = null;
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
            self::$db = self::$instance->connect($GLOBALS['db_config'] ?? [
                'host' => DB_HOST,
                'db_name' => DB_NAME,
                'db_user' => DB_USER,
                'db_pass' => DB_PASS
            ]);
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection object
     */
    public static function getConnection() {
        return self::getInstance()->getConnection();
    }
}

// Initialize connection
Connection::getInstance();
?>
