<?php
declare(strict_types=1);

/**
 * Modèle Cache Clubs Adverses (logos et infos)
 */
class ClubsCacheModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_clubs_cache';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les clubs en cache
     *
     * @return array Liste des clubs adverses
     * @throws PDOException Si erreur BDD
     */
    public function getAllClubs(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY name ASC";
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch clubs');
        assert(count($results) <= 500, 'Too many clubs fetched');

        return $results;
    }

    /**
     * Récupère un club par son cl_no
     *
     * @param int $clNo Numéro club API
     * @return array|null Données du club
     */
    public function getClubByClNo(int $clNo): ?array
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        assert($clNo !== API_FFF_CLUB_ID, 'Cannot fetch FC Chiche from cache');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => $clNo]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club cache result');

        if ($result !== false) {
            assert(isset($result['cl_no']), 'Club cl_no missing');
            assert(isset($result['name']), 'Club name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère un club par son ID interne
     *
     * @param int $id ID du club en cache
     * @return array|null Données du club
     */
    public function getClubById(int $id): ?array
    {
        assert($id > 0, 'Club ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club cache result');

        if ($result !== false) {
            assert(isset($result['id']), 'Club id missing');
            assert(isset($result['name']), 'Club name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère le logo d'un club adverse
     *
     * @param int $clNo Numéro club API
     * @return string|null URL du logo
     */
    public function getClubLogo(int $clNo): ?string
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT logo_url FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => $clNo]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club cache result');

        if ($result !== false) {
            assert(array_key_exists('logo_url', $result), 'Logo url missing');
            return (string)$result['logo_url'];
        }

        return null;
    }

    /**
     * Recherche des clubs par nom
     *
     * @param string $search Terme de recherche
     * @return array Liste des clubs correspondants
     */
    public function searchClubs(string $search): array
    {
        assert(!empty($search), 'Search term cannot be empty');
        
        $searchTerm = '%' . $search . '%';
        assert(strlen($searchTerm) <= 300, 'Search term too long');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE name LIKE :search
                OR short_name LIKE :search
                ORDER BY name ASC";

        $stmt = $this->prepareAndExecute($sql, ['search' => $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to search clubs');
        assert(count($results) <= 200, 'Too many clubs fetched');

        return $results;
    }

    /**
     * Vérifie si un club existe en cache
     *
     * @param int $clNo Numéro club API
     * @return bool True si le club existe en cache
     */
    public function exists(int $clNo): bool
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE cl_no = :cl_no";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => $clNo]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Exists query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'] > 0;
    }

    /**
     * Compte le nombre de clubs en cache
     *
     * @return int Nombre de clubs
     */
    public function countClubs(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->prepareAndExecute($sql, []);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
    }

    /**
     * Récupère les clubs les plus récemment ajoutés
     *
     * @param int $limit Nombre de clubs
     * @return array Liste des clubs récents
     */
    public function getRecentClubs(int $limit = 10): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute recent clubs query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch recent clubs');
        assert(count($results) <= $limit, 'Fetched more clubs than limit');

        return $results;
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
        assert(count($params) <= 10, 'Too many parameters provided');

        $stmt = $this->prepareStatement($sql);

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }

    /**
     * Prépare une requête PDO avec vérifications
     *
     * @param string $sql Requête SQL
     * @return PDOStatement Statement préparé
     */
    private function prepareStatement(string $sql): PDOStatement
    {
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        return $stmt;
    }
}