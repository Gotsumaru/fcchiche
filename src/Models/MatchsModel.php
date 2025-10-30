<?php
declare(strict_types=1);

/**
 * Modèle Matchs - Calendrier et Résultats
 * FIX: Ajout calcul home_name / away_name pour affichage
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
     * Enrichit les données d'un match avec les noms d'affichage
     *
     * @param array $match Données brutes du match
     * @return array Match enrichi avec home_name, away_name, is_home
     */
    private function enrichMatchData(array $match): array
    {
        assert(array_key_exists('home_club_id', $match), 'Match must contain home_club_id');
        assert(array_key_exists('away_club_id', $match), 'Match must contain away_club_id');
        $clubId = API_FFF_CLUB_ID;

        // Déterminer si FC Chiche joue à domicile
        $match['is_home'] = ($match['home_club_id'] == $clubId);
        assert(is_bool($match['is_home']), 'is_home flag must be boolean');

        // Construire les noms d'affichage
        if ($match['is_home']) {
            // FC Chiche à domicile
            $match['home_name'] = 'FC CHICHE';
            $match['away_name'] = $match['opponent_name'] ?? 'Adversaire à définir';
        } else {
            // FC Chiche à l'extérieur
            $match['home_name'] = $match['opponent_name'] ?? 'Adversaire à définir';
            $match['away_name'] = 'FC CHICHE';
        }

        assert(isset($match['home_name']), 'Home name must be defined');
        assert(isset($match['away_name']), 'Away name must be defined');

        return $match;
    }

    /**
     * Valide la structure d'un match issu de la BDD.
     *
     * @param array $match Données du match
     * @return void
     */
    private function assertMatchShape(array $match): void
    {
        assert(isset($match['ma_no']), 'Match row must contain ma_no');
        assert(array_key_exists('home_club_id', $match), 'Match row must contain home_club_id');
        assert(array_key_exists('away_club_id', $match), 'Match row must contain away_club_id');
    }

    /**
     * Enrichit un tableau de matchs
     *
     * @param array $matchs Liste de matchs
     * @return array Liste enrichie
     */
    private function enrichMatchsData(array $matchs): array
    {
        assert(array_is_list($matchs) || $matchs === [], 'Match list must be sequential array');
        assert(empty($matchs) || is_array($matchs[0]), 'Match list must contain arrays');
        return array_map([$this, 'enrichMatchData'], $matchs);
    }

    /**
     * Récupère tous les matchs avec jointures
     *
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs enrichis
     * @throws PDOException Si erreur BDD
     */
    public function getAllMatchs(?bool $isResult = null, ?int $limit = null): array
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
                    END) = cc.cl_no";
        
        if ($isResult !== null) {
            $sql .= " WHERE m.is_result = :is_result";
        }
        
        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':club_id', API_FFF_CLUB_ID, PDO::PARAM_INT);

        if ($isResult !== null) {
            $stmt->bindValue(':is_result', $isResult ? 1 : 0, PDO::PARAM_INT);
        }

        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $executed = $stmt->execute();
        assert($executed === true, 'Match query execution must succeed');
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($matchs), 'Fetched matchs must be an array');

        return $this->enrichMatchsData($matchs);
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
        assert(is_int($limit), 'Limit must be an integer');

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
        assert(is_int($limit), 'Limit must be an integer');

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
        assert(is_int($id), 'Match ID must be integer');

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
        $executed = $stmt->execute([
            'id' => $id,
            'club_id' => API_FFF_CLUB_ID
        ]);
        assert($executed === true, 'Match fetch must succeed');

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            return null;
        }

        $this->assertMatchShape($result);

        return $this->enrichMatchData($result);
    }

    /**
     * Récupère un match par ma_no (ID API FFF)
     *
     * @param int $maNo Numéro match API
     * @return array|null Données du match
     */
    public function getMatchByMaNo(int $maNo): ?array
    {
        assert($maNo > 0, 'Match ma_no must be positive');
        
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
                WHERE m.ma_no = :ma_no
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'ma_no' => $maNo,
            'club_id' => API_FFF_CLUB_ID
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result === false) {
            return null;
        }
        
        return $this->enrichMatchData($result);
    }

    /**
     * Récupère les matchs par compétition
     *
     * @param int $competitionId ID de la compétition
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs
     */
    public function getMatchsByCompetition(int $competitionId, ?bool $isResult = null, ?int $limit = null): array
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
        
        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'competition_id' => $competitionId,
            'club_id' => API_FFF_CLUB_ID
        ];
        
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
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
    }

    /**
     * Récupère les matchs par équipe (catégorie)
     *
     * @param string $category Catégorie d'équipe (SEM, U17, etc.)
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs
     */
    public function getMatchsByTeamCategory(string $category, ?bool $isResult = null, ?int $limit = null): array
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
        
        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        $stmt->bindValue(':club_id', API_FFF_CLUB_ID, PDO::PARAM_INT);
        
        if ($isResult !== null) {
            $stmt->bindValue(':is_result', $isResult ? 1 : 0, PDO::PARAM_INT);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
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
        
        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':club_id', API_FFF_CLUB_ID, PDO::PARAM_INT);
        
        if ($isResult !== null) {
            $stmt->bindValue(':is_result', $isResult ? 1 : 0, PDO::PARAM_INT);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
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
        
        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':club_id', API_FFF_CLUB_ID, PDO::PARAM_INT);
        
        if ($isResult !== null) {
            $stmt->bindValue(':is_result', $isResult ? 1 : 0, PDO::PARAM_INT);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
    }

    /**
     * Récupère les matchs par équipe ID
     *
     * @param int $equipeId ID de l'équipe
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs de l'équipe
     */
    public function getMatchsByEquipeId(
        int $equipeId,
        ?bool $isResult = null,
        ?int $limit = null,
        ?string $competitionType = null
    ): array
    {
        assert($equipeId > 0, 'Equipe ID must be positive');

        if ($competitionType !== null) {
            $competitionType = strtoupper(trim($competitionType));
            assert($competitionType !== '', 'Competition type cannot be empty string');
            assert(in_array($competitionType, ['CH', 'CP'], true), 'Invalid competition type');
        }

        $teamStmt = $this->pdo->prepare(
            'SELECT category_code, number
            FROM pprod_equipes
            WHERE id = :id
            LIMIT 1'
        );
        $teamStmt->execute(['id' => $equipeId]);
        $team = $teamStmt->fetch(PDO::FETCH_ASSOC);

        if ($team === false) {
            return [];
        }

        assert(isset($team['category_code']), 'Equipe must provide category_code');
        assert(isset($team['number']), 'Equipe must provide number');

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
                        WHEN m.home_club_id != :club_id_case THEN m.home_club_id
                        ELSE m.away_club_id
                    END) = cc.cl_no
                WHERE (
                    (m.home_club_id = :club_id_home AND m.home_team_category = :home_category AND m.home_team_number = :home_team_number)
                    OR (m.away_club_id = :club_id_away AND m.away_team_category = :away_category AND m.away_team_number = :away_team_number)
                )";

        if ($isResult !== null) {
            $sql .= " AND m.is_result = :is_result";
        }

        if ($competitionType !== null) {
            $sql .= " AND c.type = :competition_type";
        }

        $sql .= " ORDER BY m.date " . ($isResult === true ? 'DESC' : 'ASC');

        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->pdo->prepare($sql);

        $params = [
            'club_id_case' => API_FFF_CLUB_ID,
            'club_id_home' => API_FFF_CLUB_ID,
            'club_id_away' => API_FFF_CLUB_ID,
            'home_category' => $team['category_code'],
            'home_team_number' => (int)$team['number'],
            'away_category' => $team['category_code'],
            'away_team_number' => (int)$team['number'],
        ];

        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }

        if ($competitionType !== null) {
            $params['competition_type'] = $competitionType;
        }

        foreach ($params as $name => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $name, $value, $type);
        }

        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
    }

    /**
     * Récupère les matchs par journée
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
        
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
    }

    /**
     * Recherche matchs par période de dates
     *
     * @param string $dateStart Date début (Y-m-d)
     * @param string $dateEnd Date fin (Y-m-d)
     * @param bool|null $isResult null = tous, true = résultats, false = calendrier
     * @param int|null $limit Limite de résultats
     * @return array Liste des matchs dans la période
     */
    public function getMatchsByDateRange(string $dateStart, string $dateEnd, ?bool $isResult = null, ?int $limit = null): array
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
        
        if ($limit !== null && $limit > 0) {
            assert($limit <= 1000, 'Limit too high');
            $sql .= " LIMIT :limit";
        }
        
        $stmt = $this->pdo->prepare($sql);
        $params = [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'club_id' => API_FFF_CLUB_ID
        ];
        
        if ($isResult !== null) {
            $params['is_result'] = $isResult ? 1 : 0;
        }
        
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue(':' . $key, $value, $type);
        }
        
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $matchs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $this->enrichMatchsData($matchs);
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