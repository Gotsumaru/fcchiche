<?php
declare(strict_types=1);

/**
 * Modèle Club - Informations du FC Chiche
 */
class ClubModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_club';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère les informations du club
     *
     * @return array|null Données du club ou null si non trouvé
     * @throws PDOException Si erreur BDD
     */
    public function getClub(): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => API_FFF_CLUB_ID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Club record missing id');
            assert(isset($result['name']), 'Club record missing name');
            return $result;
        }

        return null;
    }

    /**
     * Récupère uniquement les infos essentielles du club
     *
     * @return array|null Données essentielles
     */
    public function getClubEssentials(): ?array
    {
        $sql = "SELECT 
                    id, cl_no, name, short_name, logo_url, 
                    address1, address2, address3, postal_code,
                    latitude, longitude, district_name
                FROM " . self::TABLE . " 
                WHERE cl_no = :cl_no 
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => API_FFF_CLUB_ID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid essentials fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Essential club record missing id');
            assert(isset($result['name']), 'Essential club record missing name');
            return $result;
        }

        return null;
    }

    /**
     * Récupère l'ID interne du club
     *
     * @return int|null ID du club
     */
    public function getClubId(): ?int
    {
        $sql = "SELECT id FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => API_FFF_CLUB_ID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club id fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Club id missing');
            return (int)$result['id'];
        }

        return null;
    }

    /**
     * Récupère le logo du club
     *
     * @return string|null URL du logo
     */
    public function getClubLogo(): ?string
    {
        $sql = "SELECT logo_url FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => API_FFF_CLUB_ID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club logo fetch result');

        if ($result !== false) {
            assert(array_key_exists('logo_url', $result), 'Club logo missing');
            return (string)$result['logo_url'];
        }

        return null;
    }

    /**
     * Vérifie si le club existe en BDD
     *
     * @return bool True si le club existe
     */
    public function exists(): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE cl_no = :cl_no";
        $stmt = $this->prepareAndExecute($sql, ['cl_no' => API_FFF_CLUB_ID]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'] > 0;
    }

    /**
     * Prépare et exécute une requête préparée avec contrôles
     *
     * @param string $sql Requête SQL
     * @param array $params Paramètres associés
     * @return PDOStatement Statement prêt à l'emploi
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        assert($sql !== '', 'SQL query cannot be empty');
        assert(count($params) <= 25, 'Too many parameters provided');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }
}