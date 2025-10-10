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
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['competition_id' => $competitionId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipe_id' => $equipeId,
            'competition_id' => $competitionId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['category' => $category]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'equipe_id' => $equipeId,
            'competition_id' => $competitionId
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false && (int)$result['count'] > 0;
    }

    /**
     * Compte le nombre total d'engagements
     *
     * @return int Nombre d'engagements
     */
    public function countEngagements(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->pdo->query($sql);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['equipe_id' => $equipeId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}