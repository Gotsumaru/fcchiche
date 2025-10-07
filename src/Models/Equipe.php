<?php
declare(strict_types=1);


/**
 * Model Equipe - Gestion des équipes du club
 */
class Equipe
{
    private PDO $pdo;
    private int $club_id;
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->club_id = API_FFF_CLUB_ID;
    }
    
    /**
     * Récupérer toutes les équipes du club
     *
     * @return array Liste des équipes
     */
    public function getAllEquipes(): array
    {
        $sql = "SELECT 
            id,
            category_code,
            number,
            code,
            short_name,
            type,
            season,
            category_label,
            category_gender,
            CONCAT(category_code, ' ', number) AS display_name
        FROM " . DB_PREFIX . "equipes
        WHERE club_id = :club_id
        ORDER BY 
            CASE category_code
                WHEN 'SEM' THEN 1
                WHEN 'U19' THEN 2
                WHEN 'U18' THEN 3
                WHEN 'U17' THEN 4
                WHEN 'U16' THEN 5
                WHEN 'U15' THEN 6
                WHEN 'U14' THEN 7
                WHEN 'U13' THEN 8
                WHEN 'U12' THEN 9
                WHEN 'U11' THEN 10
                ELSE 99
            END,
            number ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['club_id' => $this->club_id]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer une équipe spécifique
     *
     * @param string $category Catégorie (SEM, U17, U15, U13)
     * @param int $number Numéro équipe
     * @return array|null Données équipe
     */
    public function getEquipe(string $category, int $number): ?array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        
        $sql = "SELECT 
            id,
            category_code,
            number,
            code,
            short_name,
            type,
            season,
            category_label,
            category_gender,
            diffusable,
            CONCAT(category_code, ' ', number) AS display_name
        FROM " . DB_PREFIX . "equipes
        WHERE club_id = :club_id
          AND category_code = :category
          AND number = :number
        LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'club_id' => $this->club_id,
            'category' => $category,
            'number' => $number
        ]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Récupérer les compétitions d'une équipe
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return array Liste des compétitions
     */
    public function getEquipeCompetitions(string $category, int $number): array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        
        $sql = "SELECT DISTINCT
            c.id,
            c.cp_no,
            c.name,
            c.type,
            c.level,
            eng.phase_number,
            eng.poule_stage_number,
            eng.statut,
            eng.elimine
        FROM " . DB_PREFIX . "equipes e
        JOIN " . DB_PREFIX . "engagements eng ON e.id = eng.equipe_id
        JOIN " . DB_PREFIX . "competitions c ON eng.competition_id = c.id
        WHERE e.club_id = :club_id
          AND e.category_code = :category
          AND e.number = :number
        ORDER BY 
            CASE c.type
                WHEN 'CH' THEN 1
                WHEN 'CP' THEN 2
                ELSE 3
            END,
            c.name";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'club_id' => $this->club_id,
            'category' => $category,
            'number' => $number
        ]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer équipes par catégorie
     *
     * @param string $category Catégorie (SEM, U17, U15, U13)
     * @return array Liste équipes de cette catégorie
     */
    public function getEquipesByCategory(string $category): array
    {
        assert(!empty($category), 'Category cannot be empty');
        
        $sql = "SELECT 
            id,
            category_code,
            number,
            code,
            short_name,
            type,
            season,
            category_label,
            category_gender,
            CONCAT(category_code, ' ', number) AS display_name
        FROM " . DB_PREFIX . "equipes
        WHERE club_id = :club_id
          AND category_code = :category
        ORDER BY number ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'club_id' => $this->club_id,
            'category' => $category
        ]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifier si une équipe existe
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return bool Existe ou non
     */
    public function exists(string $category, int $number): bool
    {
        $equipe = $this->getEquipe($category, $number);
        return $equipe !== null;
    }
    
    /**
     * Récupérer nombre total d'équipes
     *
     * @return int Nombre d'équipes
     */
    public function count(): int
    {
        $sql = "SELECT COUNT(*) FROM " . DB_PREFIX . "equipes WHERE club_id = :club_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['club_id' => $this->club_id]);
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Récupérer résumé pour menu de navigation
     *
     * @return array Catégories avec nombre d'équipes
     */
    public function getNavigationSummary(): array
    {
        $sql = "SELECT 
            category_code,
            category_label,
            COUNT(*) AS nb_equipes,
            GROUP_CONCAT(number ORDER BY number SEPARATOR ', ') AS numeros
        FROM " . DB_PREFIX . "equipes
        WHERE club_id = :club_id
        GROUP BY category_code, category_label
        ORDER BY 
            CASE category_code
                WHEN 'SEM' THEN 1
                WHEN 'U19' THEN 2
                WHEN 'U18' THEN 3
                WHEN 'U17' THEN 4
                WHEN 'U16' THEN 5
                WHEN 'U15' THEN 6
                WHEN 'U14' THEN 7
                WHEN 'U13' THEN 8
                WHEN 'U12' THEN 9
                WHEN 'U11' THEN 10
                ELSE 99
            END";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['club_id' => $this->club_id]);
        
        return $stmt->fetchAll();
    }
}