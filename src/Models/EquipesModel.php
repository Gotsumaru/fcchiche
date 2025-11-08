<?php
declare(strict_types=1);

/**
 * Modèle Équipes
 */
class EquipesModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_equipes';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère toutes les équipes du club
     *
     * @param int|null $season Saison (null = actuelle)
     * @param bool $diffusableOnly Uniquement les équipes diffusables
     * @return array Liste des équipes
     * @throws PDOException Si erreur BDD
     */
    public function getAllEquipes(?int $season = null, bool $diffusableOnly = true): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');
        assert(is_bool($diffusableOnly), 'Diffusable flag must be boolean');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE season = :season";

        if ($diffusableOnly) {
            $sql .= " AND diffusable = 1";
        }

        $sql .= " ORDER BY category_code ASC, number ASC";

        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch equipes');
        assert(count($results) <= 500, 'Too many equipes fetched');

        return $results;
    }

    /**
     * Récupère une équipe par son ID
     *
     * @param int $id ID de l'équipe
     * @return array|null Données de l'équipe
     */
    public function getEquipeById(int $id): ?array
    {
        assert($id > 0, 'Equipe ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid equipe fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Equipe id missing');
            assert(isset($result['short_name']), 'Equipe short_name missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère les équipes par catégorie
     *
     * @param string $category Code catégorie (SEM, U17, U15, etc.)
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des équipes de la catégorie
     */
    public function getEquipesByCategory(string $category, ?int $season = null): array
    {
        assert(!empty($category), 'Category cannot be empty');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE category_code = :category 
                AND season = :season 
                AND diffusable = 1
                ORDER BY number ASC";
        
        $stmt = $this->prepareAndExecute($sql, [
            'category' => $category,
            'season' => $season
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch equipes by category');
        assert(count($results) <= 200, 'Too many equipes fetched');

        return $results;
    }

    /**
     * Récupère une équipe par son short_name
     *
     * @param string $shortName Nom court de l'équipe
     * @param int|null $season Saison (null = actuelle)
     * @return array|null Données de l'équipe
     */
    public function getEquipeByShortName(string $shortName, ?int $season = null): ?array
    {
        assert(!empty($shortName), 'Short name cannot be empty');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE short_name = :short_name 
                AND season = :season 
                LIMIT 1";
        
        $stmt = $this->prepareAndExecute($sql, [
            'short_name' => $shortName,
            'season' => $season
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid equipe fetch result');

        if ($result !== false) {
            assert(isset($result['short_name']), 'Equipe short_name missing');
            assert(isset($result['category_code']), 'Equipe category missing');
            return $result;
        }

        return null;
    }

    /**
     * Récupère toutes les catégories disponibles
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des catégories uniques
     */
    public function getCategories(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT DISTINCT category_code, category_label, category_gender 
                FROM " . self::TABLE . " 
                WHERE season = :season 
                AND diffusable = 1
                ORDER BY category_code ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch categories');
        assert(count($results) <= 200, 'Too many categories fetched');

        return $results;
    }

    /**
     * Récupère les équipes seniors (SEM, SEF)
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des équipes seniors
     */
    public function getEquipesSeniors(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE (category_code = 'SEM' OR category_code = 'SEF')
                AND season = :season 
                AND diffusable = 1
                ORDER BY category_code ASC, number ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch senior equipes');
        assert(count($results) <= 100, 'Too many senior equipes fetched');

        return $results;
    }

    /**
     * Récupère les équipes jeunes (U13, U15, U17, etc.)
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des équipes jeunes
     */
    public function getEquipesJeunes(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE category_code LIKE 'U%'
                AND season = :season 
                AND diffusable = 1
                ORDER BY category_code DESC, number ASC";
        
        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch youth equipes');
        assert(count($results) <= 200, 'Too many youth equipes fetched');

        return $results;
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
     * Compte le nombre d'équipes
     *
     * @param int|null $season Saison (null = actuelle)
     * @param bool $diffusableOnly Uniquement les équipes diffusables
     * @return int Nombre d'équipes
     */
    public function countEquipes(?int $season = null, bool $diffusableOnly = true): int
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " 
                WHERE season = :season";
        
        if ($diffusableOnly) {
            $sql .= " AND diffusable = 1";
        }
        
        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
    }

    /**
     * Prépare et exécute une requête PDO avec contrôles
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