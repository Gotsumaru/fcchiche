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
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE season = :season";
        
        if ($diffusableOnly) {
            $sql .= " AND diffusable = 1";
        }
        
        $sql .= " ORDER BY category_code ASC, number ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'category' => $category,
            'season' => $season
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'short_name' => $shortName,
            'season' => $season
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result !== false && !empty($result['config_value'])) {
            return (int)$result['config_value'];
        }
        
        return (int)date('Y');
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}