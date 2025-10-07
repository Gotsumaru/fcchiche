<?php
declare(strict_types=1);

if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Classe de gestion de la connexion PDO
 */
class Database
{
    private static ?PDO $instance = null;
    
    /**
     * Obtenir l'instance PDO unique (Singleton)
     *
     * @return PDO Instance PDO configurée
     * @throws PDOException Si connexion échoue
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        
        return self::$instance;
    }
    
    /**
     * Créer connexion PDO
     *
     * @return PDO Connexion configurée
     * @throws PDOException Si échec connexion
     */
    private static function createConnection(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_PERSISTENT => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            assert($pdo instanceof PDO, 'PDO instance creation failed');
            return $pdo;
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                throw new PDOException(
                    "Database connection failed: " . $e->getMessage(),
                    (int)$e->getCode()
                );
            }
            throw new PDOException("Database connection failed", (int)$e->getCode());
        }
    }
    
    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}