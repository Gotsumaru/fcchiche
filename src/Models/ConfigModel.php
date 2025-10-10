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
        
        $sql = "SELECT config_value FROM " . self::TABLE . " 
                WHERE config_key = :key 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['key' => $key]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result['config_value'] : null;
    }

    /**
     * Récupère toutes les configurations
     *
     * @return array Tableau associatif [key => value]
     */
    public function getAll(): array
    {
        $sql = "SELECT config_key, config_value FROM " . self::TABLE;
        $stmt = $this->pdo->query($sql);
        
        $configs = [];
        $counter = 0;
        $maxIterations = 1000;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            assert($counter++ < $maxIterations, 'Too many config entries');
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
        
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $sql = "SELECT config_key, config_value FROM " . self::TABLE . " 
                WHERE config_key IN ($placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($keys);
        
        $configs = [];
        $counter = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            assert($counter++ < count($keys), 'Unexpected number of results');
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
        
        $stmt = $this->pdo->query($sql);
        
        $syncs = [];
        $counter = 0;
        $maxIterations = 100;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            assert($counter++ < $maxIterations, 'Too many sync entries');
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['key' => $key]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false && (int)$result['count'] > 0;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['prefix' => $prefix . '%']);
        
        $configs = [];
        $counter = 0;
        $maxIterations = 1000;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            assert($counter++ < $maxIterations, 'Too many config entries');
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
        $stmt = $this->pdo->query($sql);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['key' => $key]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }
}