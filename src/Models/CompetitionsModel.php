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
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE season = :season 
                ORDER BY type ASC, name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cp_no' => $cpNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
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
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE type = :type AND season = :season 
                ORDER BY name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'type' => $type,
            'season' => $season
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère uniquement les championnats (type = CH)
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des championnats
     */
    public function getChampionnats(?int $season = null): array
    {
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
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result !== false && !empty($result['config_value'])) {
            return (int)$result['config_value'];
        }
        
        return (int)date('Y');
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
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}