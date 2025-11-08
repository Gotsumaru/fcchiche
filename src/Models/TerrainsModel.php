<?php
declare(strict_types=1);

/**
 * Modèle Terrains
 */
class TerrainsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_terrains';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les terrains du club
     *
     * @return array Liste des terrains
     * @throws PDOException Si erreur BDD
     */
    public function getAllTerrains(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY name ASC";
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch terrains');
        assert(count($results) <= 200, 'Too many terrains fetched');

        return $results;
    }

    /**
     * Récupère un terrain par son ID
     *
     * @param int $id ID du terrain
     * @return array|null Données du terrain
     */
    public function getTerrainById(int $id): ?array
    {
        assert($id > 0, 'Terrain ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid terrain fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Terrain id missing');
            assert(isset($result['name']), 'Terrain name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère un terrain par son numéro API (te_no)
     *
     * @param int $teNo Numéro terrain API
     * @return array|null Données du terrain
     */
    public function getTerrainByTeNo(int $teNo): ?array
    {
        assert($teNo > 0, 'Terrain te_no must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE te_no = :te_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['te_no' => $teNo]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid terrain fetch result');

        if ($result !== false) {
            assert(isset($result['te_no']), 'Terrain te_no missing');
            assert(isset($result['name']), 'Terrain name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère les terrains avec coordonnées GPS
     *
     * @return array Liste des terrains avec GPS
     */
    public function getTerrainsWithGPS(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE latitude IS NOT NULL 
                AND longitude IS NOT NULL 
                ORDER BY name ASC";
        
        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch terrains with GPS');
        assert(count($results) <= 200, 'Too many terrains fetched');

        return $results;
    }

    /**
     * Compte le nombre de terrains
     *
     * @return int Nombre de terrains
     */
    public function countTerrains(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->prepareAndExecute($sql, []);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
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
        assert(count($params) <= 20, 'Too many parameters provided');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }
}