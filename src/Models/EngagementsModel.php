<?php
declare(strict_types=1);

/**
 * Modèle Engagements - Pivot Équipes-Compétitions
 */
class EngagementsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_engagements';
    private const TABLE_EQUIPES = 'pprod_equipes';
    private const TABLE_COMPETITIONS = 'pprod_competitions';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les engagements avec jointures
     *
     * @return array Liste des engagements enrichis
     * @throws PDOException Si erreur BDD
     */
    public function getAllEngagements(): array
    {
        $sql = "SELECT
                    e.*,
                    eq.short_name as equipe_name,
                    eq.category_code,
                    eq.number as equipe_number,
                    c.name as competition_name,
                    c.type as competition_type,
                    c.level as competition_level
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                ORDER BY eq.category_code ASC, eq.number ASC";

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch engagements');
        assert(count($results) <= 500, 'Too many engagements fetched');

        return $results;
    }

    /**
     * Récupère un engagement par son ID
     *
     * @param int $id ID de l'engagement
     * @return array|null Données de l'engagement
     */
    public function getEngagementById(int $id): ?array
    {
        assert($id > 0, 'Engagement ID must be positive');
        
        $sql = "SELECT 
                    e.*,
                    eq.short_name as equipe_name,
                    eq.category_code,
                    c.name as competition_name,
                    c.type as competition_type
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE e.id = :id
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid engagement result');

        if ($result !== false) {
            assert(isset($result['id']), 'Engagement id missing');
            assert(isset($result['competition_id']), 'Competition id missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère les engagements d'une équipe
     *
     * @param int $equipeId ID de l'équipe
     * @return array Liste des compétitions de l'équipe
     */
    public function getEngagementsByEquipe(int $equipeId): array
    {
        assert($equipeId > 0, 'Equipe ID must be positive');
        
        $sql = "SELECT 
                    e.*,
                    c.name as competition_name,
                    c.type as competition_type,
                    c.level as competition_level,
                    c.cp_no
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE e.equipe_id = :equipe_id
                ORDER BY c.type ASC, c.name ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['equipe_id' => $equipeId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch engagements by equipe');
        assert(count($results) <= 100, 'Too many engagements fetched');

        return $results;
    }

    /**
     * Récupère les équipes engagées dans une compétition
     *
     * @param int $competitionId ID de la compétition
     * @return array Liste des équipes
     */
    public function getEquipesByCompetition(int $competitionId): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    e.*,
                    eq.short_name as equipe_name,
                    eq.category_code,
                    eq.number as equipe_number,
                    eq.code as equipe_code
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                WHERE e.competition_id = :competition_id
                ORDER BY eq.category_code ASC, eq.number ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['competition_id' => $competitionId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch equipes by competition');
        assert(count($results) <= 200, 'Too many equipes fetched');

        return $results;
    }

    /**
     * Récupère un engagement par équipe et compétition
     *
     * @param int $equipeId ID de l'équipe
     * @param int $competitionId ID de la compétition
     * @return array|null Données de l'engagement
     */
    public function getEngagementByEquipeAndCompetition(int $equipeId, int $competitionId): ?array
    {
        assert($equipeId > 0, 'Equipe ID must be positive');
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    e.*,
                    eq.short_name as equipe_name,
                    c.name as competition_name
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE e.equipe_id = :equipe_id 
                AND e.competition_id = :competition_id
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, [
            'equipe_id' => $equipeId,
            'competition_id' => $competitionId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid engagement result');

        if ($result !== false) {
            assert(isset($result['equipe_id']), 'Equipe id missing');
            assert(isset($result['competition_id']), 'Competition id missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère les engagements par catégorie d'équipe
     *
     * @param string $category Code catégorie (SEM, U17, etc.)
     * @return array Liste des engagements de la catégorie
     */
    public function getEngagementsByCategory(string $category): array
    {
        assert(!empty($category), 'Category cannot be empty');
        
        $sql = "SELECT 
                    e.*,
                    eq.short_name as equipe_name,
                    eq.number as equipe_number,
                    c.name as competition_name,
                    c.type as competition_type
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE eq.category_code = :category
                ORDER BY eq.number ASC, c.type ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['category' => $category]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch engagements by category');
        assert(count($results) <= 200, 'Too many engagements fetched');

        return $results;
    }

    /**
     * Récupère tous les engagements en championnats uniquement
     *
     * @return array Liste des engagements en championnat
     */
    public function getChampionnatEngagements(): array
    {
        $sql = "SELECT
                    e.*,
                    eq.short_name as equipe_name,
                    eq.category_code,
                    eq.number as equipe_number,
                    c.name as competition_name,
                    c.cp_no
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE c.type = 'CH'
                ORDER BY eq.category_code ASC, eq.number ASC";

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch championnat engagements');
        assert(count($results) <= 200, 'Too many engagements fetched');

        return $results;
    }

    /**
     * Récupère tous les engagements en coupes uniquement
     *
     * @return array Liste des engagements en coupe
     */
    public function getCoupeEngagements(): array
    {
        $sql = "SELECT
                    e.*,
                    eq.short_name as equipe_name,
                    eq.category_code,
                    eq.number as equipe_number,
                    c.name as competition_name,
                    c.cp_no
                FROM " . self::TABLE . " e
                LEFT JOIN " . self::TABLE_EQUIPES . " eq ON e.equipe_id = eq.id
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON e.competition_id = c.id
                WHERE c.type = 'CDF'
                ORDER BY eq.category_code ASC, eq.number ASC";

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch coupe engagements');
        assert(count($results) <= 200, 'Too many engagements fetched');

        return $results;
    }

    /**
     * Vérifie si une équipe est engagée dans une compétition
     *
     * @param int $equipeId ID de l'équipe
     * @param int $competitionId ID de la compétition
     * @return bool True si engagée
     */
    public function isEngaged(int $equipeId, int $competitionId): bool
    {
        assert($equipeId > 0, 'Equipe ID must be positive');
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " 
                WHERE equipe_id = :equipe_id 
                AND competition_id = :competition_id";
        
        $stmt = $this->prepareAndExecute($sql, [
            'equipe_id' => $equipeId,
            'competition_id' => $competitionId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Engagement existence query failed');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'] > 0;
    }

    /**
     * Compte le nombre total d'engagements
     *
     * @return int Nombre d'engagements
     */
    public function countEngagements(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->prepareAndExecute($sql, []);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
    }

    /**
     * Compte le nombre d'engagements par équipe
     *
     * @param int $equipeId ID de l'équipe
     * @return int Nombre d'engagements
     */
    public function countEngagementsByEquipe(int $equipeId): int
    {
        assert($equipeId > 0, 'Equipe ID must be positive');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " 
                WHERE equipe_id = :equipe_id";
        
        $stmt = $this->prepareAndExecute($sql, ['equipe_id' => $equipeId]);

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