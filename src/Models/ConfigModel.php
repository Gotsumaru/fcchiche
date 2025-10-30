<?php
declare(strict_types=1);

/**
 * Modèle Configuration Système
 */
class ConfigModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_config';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère une valeur de configuration
     *
     * @param string $key Clé de configuration
     * @return string|null Valeur de configuration
     * @throws PDOException Si erreur BDD
     */
    public function get(string $key): ?string
    {
        assert(!empty($key), 'Config key cannot be empty');
        assert(strlen($key) <= 255, 'Config key too long');

        $sql = "SELECT config_value FROM " . self::TABLE . "
                WHERE config_key = :key
                LIMIT 1";

        $stmt = $this->prepareAndExecute($sql, ['key' => $key]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid config fetch result');

        if ($result !== false) {
            assert(array_key_exists('config_value', $result), 'Config value missing');
            return (string)$result['config_value'];
        }

        return null;
    }

    /**
     * Récupère toutes les configurations
     *
     * @return array Tableau associatif [key => value]
     */
    public function getAll(): array
    {
        $sql = "SELECT config_key, config_value FROM " . self::TABLE;
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->prepareAndExecute($sql, []);
        assert($stmt->columnCount() >= 2, 'Expected columns missing');

        $configs = [];
        $counter = 0;
        $maxIterations = 1000;
        assert($maxIterations > 0, 'Invalid iteration guard');

        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            assert($counter++ < $maxIterations, 'Too many config entries');
            assert(isset($row['config_key']), 'Config key missing');
            assert(array_key_exists('config_value', $row), 'Config value missing');
            $configs[$row['config_key']] = $row['config_value'];
        }

        return $configs;
    }

    /**
     * Récupère plusieurs valeurs de configuration
     *
     * @param array $keys Liste des clés à récupérer
     * @return array Tableau associatif [key => value]
     */
    public function getMultiple(array $keys): array
    {
        assert(!empty($keys), 'Keys array cannot be empty');
        assert(count($keys) <= 100, 'Too many keys requested');
        assert(count(array_filter($keys, 'is_string')) === count($keys), 'Keys must be strings');

        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $sql = "SELECT config_key, config_value FROM " . self::TABLE . "
                WHERE config_key IN ($placeholders)";

        $stmt = $this->prepareAndExecute($sql, array_values($keys));
        assert($stmt->columnCount() >= 2, 'Expected columns missing');

        $configs = [];
        $counter = 0;
        $maxResults = count($keys);

        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            assert($counter++ < $maxResults, 'Unexpected number of results');
            assert(isset($row['config_key']), 'Config key missing');
            assert(array_key_exists('config_value', $row), 'Config value missing');
            $configs[$row['config_key']] = $row['config_value'];
        }

        return $configs;
    }

    /**
     * Récupère la saison actuelle
     *
     * @return int Année de la saison
     */
    public function getCurrentSeason(): int
    {
        $value = $this->get('current_season');
        
        if ($value !== null) {
            $season = (int)$value;
            assert($season > 2000 && $season < 3000, 'Invalid season value in config');
            return $season;
        }
        
        return (int)date('Y');
    }

    /**
     * Récupère la date de dernière synchronisation
     *
     * @param string $type Type de sync (club, equipes, calendrier, resultats, classements)
     * @return string|null Date de dernière sync
     */
    public function getLastSync(string $type): ?string
    {
        assert(!empty($type), 'Sync type cannot be empty');
        
        $key = 'last_sync_' . $type;
        return $this->get($key);
    }

    /**
     * Récupère toutes les dates de synchronisation
     *
     * @return array Tableau associatif [type => date]
     */
    public function getAllLastSync(): array
    {
        $sql = "SELECT config_key, config_value FROM " . self::TABLE . "
                WHERE config_key LIKE 'last_sync_%'";

        $stmt = $this->prepareAndExecute($sql, []);

        $syncs = [];
        $counter = 0;
        $maxIterations = 100;
        assert($maxIterations > 0, 'Invalid iteration guard');

        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            assert($counter++ < $maxIterations, 'Too many sync entries');
            assert(isset($row['config_key']), 'Sync key missing');
            assert(array_key_exists('config_value', $row), 'Sync value missing');
            $type = str_replace('last_sync_', '', $row['config_key']);
            $syncs[$type] = $row['config_value'];
        }

        return $syncs;
    }

    /**
     * Vérifie si une clé de configuration existe
     *
     * @param string $key Clé de configuration
     * @return bool True si la clé existe
     */
    public function exists(string $key): bool
    {
        assert(!empty($key), 'Config key cannot be empty');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " 
                WHERE config_key = :key";
        
        $stmt = $this->prepareAndExecute($sql, ['key' => $key]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Exists query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'] > 0;
    }

    /**
     * Récupère les configurations par préfixe
     *
     * @param string $prefix Préfixe de clé (ex: "last_sync_")
     * @return array Tableau associatif [key => value]
     */
    public function getByPrefix(string $prefix): array
    {
        assert(!empty($prefix), 'Prefix cannot be empty');
        
        $sql = "SELECT config_key, config_value FROM " . self::TABLE . " 
                WHERE config_key LIKE :prefix";
        
        $stmt = $this->prepareAndExecute($sql, ['prefix' => $prefix . '%']);

        $configs = [];
        $counter = 0;
        $maxIterations = 1000;
        assert($maxIterations > 0, 'Invalid iteration guard');

        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            assert($counter++ < $maxIterations, 'Too many config entries');
            assert(isset($row['config_key']), 'Config key missing');
            assert(array_key_exists('config_value', $row), 'Config value missing');
            $configs[$row['config_key']] = $row['config_value'];
        }

        return $configs;
    }

    /**
     * Compte le nombre de configurations
     *
     * @return int Nombre de configurations
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->prepareAndExecute($sql, []);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
    }

    /**
     * Récupère les informations de dernière mise à jour d'une config
     *
     * @param string $key Clé de configuration
     * @return array|null Données complètes incluant updated_at
     */
    public function getWithTimestamp(string $key): ?array
    {
        assert(!empty($key), 'Config key cannot be empty');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE config_key = :key 
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, ['key' => $key]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid config fetch result');

        if ($result !== false) {
            assert(isset($result['config_key']), 'Config key missing');
            assert(array_key_exists('config_value', $result), 'Config value missing');
            return $result;
        }

        return null;
    }

    /**
     * Prépare et exécute une requête préparée
     *
     * @param string $sql Requête SQL
     * @param array $params Paramètres à lier
     * @return PDOStatement Statement exécuté
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        assert($sql !== '', 'SQL query cannot be empty');
        assert(count($params) <= 200, 'Too many parameters provided');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }
}