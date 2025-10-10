<?php
declare(strict_types=1);

/**
 * Modèle Matchs - Calendrier et Résultats
 */
class MatchsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_matchs';
    private const TABLE_COMPETITIONS = 'pprod_competitions';
    private const TABLE_TERRAINS = 'pprod_terrains';
    private const TABLE_CLUBS_CACHE = 'pprod_clubs_cache';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les matchs avec jointures (compétition, terrain, club adverse)
     *
     * @param bool $isResult True = résultats, False = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs enrichis
     * @throws PDOException Si erreur BDD
     */
    public function getAllMatchs(bool $isResult = false, ?int $limit = null): array
    {
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    c.type as competition_type,
                    c.level as competition_level,
                    t.name as terrain_name,
                    t.address as terrain_address,
                    t.city as terrain_city,
                    cc.name as opponent_name,
                    cc.short_name as opponent_short_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.is_result = :is_result
                ORDER BY m.date " . ($isResult ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':club_id', API_FFF_CLUB_ID, PDO::PARAM_INT);
        $stmt->bindValue(':is_result', $isResult ? 1 : 0, PDO::PARAM_INT);
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matchs à venir (calendrier)
     *
     * @param int $limit Nombre de matchs max
     * @return array Liste des prochains matchs
     */
    public function getUpcomingMatchs(int $limit = 10): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        return $this->getAllMatchs(false, $limit);
    }

    /**
     * Récupère les derniers résultats
     *
     * @param int $limit Nombre de résultats max
     * @return array Liste des derniers résultats
     */
    public function getLastResults(int $limit = 10): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        return $this->getAllMatchs(true, $limit);
    }

    /**
     * Récupère un match par son ID
     *
     * @param int $id ID du match
     * @return array|null Données du match enrichies
     */
    public function getMatchById(int $id): ?array
    {
        assert($id > 0, 'Match ID must be positive');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    c.type as competition_type,
                    c.level as competition_level,
                    t.name as terrain_name,
                    t.address as terrain_address,
                    t.city as terrain_city,
                    t.latitude as terrain_latitude,
                    t.longitude as terrain_longitude,
                    cc.name as opponent_name,
                    cc.short_name as opponent_short_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.id = :id
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id' => $id,
            'club_id' => API_FFF_CLUB_ID
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère un match par son numéro API (ma_no)
     *
     * @param int $maNo Numéro match API
     * @return array|null Données du match enrichies
     */
    public function getMatchByMaNo(int $maNo): ?array
    {
        assert($maNo > 0, 'Match ma_no must be positive');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    c.type as competition_type,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.ma_no = :ma_no
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ma_no' => $maNo,
            'club_id' => API_FFF_CLUB_ID
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère les matchs par compétition
     *
     * @param int $competitionId ID de la compétition
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @return array Liste des matchs
     */
    public function getMatchsByCompetition(int $competitionId, ?bool $isResult = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.competition_id = :competition_id";
        
        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date " . ($isResult ? 'DESC' : 'ASC');
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'competition_id' => $competitionId,
            'club_id' => API_FFF_CLUB_ID
        ];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matchs par équipe (catégorie)
     *
     * @param string $category Catégorie d'équipe (SEM, U17, etc.)
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @return array Liste des matchs
     */
    public function getMatchsByTeamCategory(string $category, ?bool $isResult = null): array
    {
        assert(!empty($category), 'Category cannot be empty');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    c.type as competition_type,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE (
                    (m.home_club_id = :club_id AND m.home_team_category = :category)
                    OR (m.away_club_id = :club_id AND m.away_team_category = :category)
                )";
        
        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date " . ($isResult ? 'DESC' : 'ASC');
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'category' => $category,
            'club_id' => API_FFF_CLUB_ID
        ];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matchs à domicile
     *
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs à domicile
     */
    public function getHomeMatchs(?bool $isResult = null, ?int $limit = null): array
    {
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON m.away_club_id = cc.cl_no
                WHERE m.home_club_id = :club_id";
        
        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date " . ($isResult ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = ['club_id' => API_FFF_CLUB_ID];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matchs à l'extérieur
     *
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs à l'extérieur
     */
    public function getAwayMatchs(?bool $isResult = null, ?int $limit = null): array
    {
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON m.home_club_id = cc.cl_no
                WHERE m.away_club_id = :club_id";
        
        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date " . ($isResult ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = ['club_id' => API_FFF_CLUB_ID];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les matchs d'une journée spécifique
     *
     * @param int $journeeNumber Numéro de journée
     * @param int $competitionId ID de la compétition
     * @return array Liste des matchs de la journée
     */
    public function getMatchsByJournee(int $journeeNumber, int $competitionId): array
    {
        assert($journeeNumber > 0, 'Journee number must be positive');
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.poule_journee_number = :journee_number 
                AND m.competition_id = :competition_id
                ORDER BY m.date ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'journee_number' => $journeeNumber,
            'competition_id' => $competitionId,
            'club_id' => API_FFF_CLUB_ID
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche matchs par période de dates
     *
     * @param string $dateStart Date début (Y-m-d)
     * @param string $dateEnd Date fin (Y-m-d)
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @return array Liste des matchs dans la période
     */
    public function getMatchsByDateRange(string $dateStart, string $dateEnd, ?bool $isResult = null): array
    {
        assert(!empty($dateStart), 'Start date cannot be empty');
        assert(!empty($dateEnd), 'End date cannot be empty');
        
        $sql = "SELECT 
                    m.*,
                    c.name as competition_name,
                    t.name as terrain_name,
                    cc.name as opponent_name,
                    cc.logo_url as opponent_logo
                FROM " . self::TABLE . " m
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON m.competition_id = c.id
                LEFT JOIN " . self::TABLE_TERRAINS . " t ON m.terrain_id = t.id
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON 
                    (CASE 
                        WHEN m.home_club_id != :club_id THEN m.home_club_id 
                        ELSE m.away_club_id 
                    END) = cc.cl_no
                WHERE m.date BETWEEN :date_start AND :date_end";
        
        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'club_id' => API_FFF_CLUB_ID
        ];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre de matchs
     *
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @return int Nombre de matchs
     */
    public function countMatchs(?bool $isResult = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        
        if ($isResult !== null) {
            $sql .= " WHERE is_result = :is_result";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($isResult !== null) {
            $stmt->execute(['is_result' => $isResult ? 1 : 0]);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }

    /**
     * Récupère le prochain match à domicile
     *
     * @return array|null Prochain match à domicile
     */
    public function getNextHomeMatch(): ?array
    {
        $matchs = $this->getHomeMatchs(false, 1);
        return !empty($matchs) ? $matchs[0] : null;
    }

    /**
     * Récupère le dernier résultat à domicile
     *
     * @return array|null Dernier résultat à domicile
     */
    public function getLastHomeResult(): ?array
    {
        $matchs = $this->getHomeMatchs(true, 1);
        return !empty($matchs) ? $matchs[0] : null;
    }
}