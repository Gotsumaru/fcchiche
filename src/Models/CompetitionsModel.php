<?php
declare(strict_types=1);

/**
 * Modèle Compétitions
 */
class CompetitionsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_competitions';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les compétitions
     *
     * @param int|null $season Saison spécifique (null = saison actuelle)
     * @return array Liste des compétitions
     * @throws PDOException Si erreur BDD
     */
    public function getAllCompetitions(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE season = :season
                ORDER BY type ASC, name ASC";

        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch competitions');
        assert(count($results) <= 500, 'Too many competitions fetched');

        return $results;
    }

    /**
     * Récupère une compétition par son ID
     *
     * @param int $id ID de la compétition
     * @return array|null Données de la compétition
     */
    public function getCompetitionById(int $id): ?array
    {
        assert($id > 0, 'Competition ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid competition fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Competition id missing');
            assert(isset($result['name']), 'Competition name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère une compétition par son numéro API (cp_no)
     *
     * @param int $cpNo Numéro compétition API
     * @return array|null Données de la compétition
     */
    public function getCompetitionByCpNo(int $cpNo): ?array
    {
        assert($cpNo > 0, 'Competition cp_no must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE cp_no = :cp_no LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['cp_no' => $cpNo]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid competition fetch result');

        if ($result !== false) {
            assert(isset($result['cp_no']), 'Competition cp_no missing');
            assert(isset($result['name']), 'Competition name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère les compétitions par type
     *
     * @param string $type Type de compétition (CH, CDF, etc.)
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des compétitions
     */
    public function getCompetitionsByType(string $type, ?int $season = null): array
    {
        assert(!empty($type), 'Type cannot be empty');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE type = :type AND season = :season
                ORDER BY name ASC";

        $stmt = $this->prepareAndExecute($sql, [
            'type' => $type,
            'season' => $season
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch competitions by type');
        assert(count($results) <= 200, 'Too many competitions fetched');

        return $results;
    }

    /**
     * Récupère uniquement les championnats (type = CH)
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des championnats
     */
    public function getChampionnats(?int $season = null): array
    {
        assert($season === null || is_int($season), 'Season must be int or null');
        assert($season === null || ($season > 2000 && $season < 3000), 'Invalid season year');

        return $this->getCompetitionsByType('CH', $season);
    }

    /**
     * Récupère uniquement les coupes (type = CDF)
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des coupes
     */
    public function getCoupes(?int $season = null): array
    {
        assert($season === null || is_int($season), 'Season must be int or null');
        assert($season === null || ($season > 2000 && $season < 3000), 'Invalid season year');

        return $this->getCompetitionsByType('CDF', $season);
    }

    /**
     * Récupère la saison actuelle depuis la config
     *
     * @return int Année de la saison actuelle
     */
    private function getCurrentSeason(): int
    {
        $sql = "SELECT config_value FROM pprod_config 
                WHERE config_key = 'current_season' 
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, []);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid season fetch result');

        if ($result !== false && !empty($result['config_value'])) {
            $season = (int)$result['config_value'];
            assert($season > 2000 && $season < 3000, 'Stored season invalid');
            return $season;
        }

        $fallbackSeason = (int)date('Y');
        assert($fallbackSeason > 2000 && $fallbackSeason < 3000, 'Fallback season invalid');
        return $fallbackSeason;
    }

    /**
     * Compte le nombre de compétitions
     *
     * @param int|null $season Saison (null = actuelle)
     * @return int Nombre de compétitions
     */
    public function countCompetitions(?int $season = null): int
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " 
                WHERE season = :season";
        
        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);

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
        assert(count($params) <= 50, 'Too many parameters provided');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }
}