<?php
declare(strict_types=1);

/**
 * Modèle Classements - Historisation des classements
 * FIX: Ajout paramètre $season optionnel partout
 */
class ClassementsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_classements';
    private const TABLE_COMPETITIONS = 'pprod_competitions';
    private const TABLE_CLUBS_CACHE = 'pprod_clubs_cache';

    public function __construct(PDO $pdo)
    {
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

        $this->pdo = $pdo;
    }

    /**
     * Récupère le classement actuel d'une compétition (dernière journée)
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $season Saison (null = actuelle)
     * @return array Classement actuel
     * @throws PDOException Si erreur BDD
     */
    public function getCurrentClassement(int $competitionId, ?int $season = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');

        $sql = "SELECT
                    cl.*,
                    CASE
                        WHEN cl.cl_no = :club_id THEN 'FC CHICHE'
                        ELSE COALESCE(cc.name, cl.team_short_name, 'Club')
                    END as club_name,
                    CASE 
                        WHEN cl.cl_no = :club_id2 THEN 'FC CHICHE'
                        ELSE COALESCE(cc.short_name, cl.team_short_name, 'Club')
                    END as club_short_name,
                    cc.logo_url as club_logo,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON cl.cl_no = cc.cl_no
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.season = :season
                AND cl.cj_no = (
                    SELECT MAX(cj_no) 
                    FROM " . self::TABLE . " 
                    WHERE competition_id = :competition_id2
                    AND season = :season2
                )
                ORDER BY cl.ranking ASC";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'season' => $season,
            'competition_id2' => $competitionId,
            'season2' => $season,
            'club_id' => API_FFF_CLUB_ID,
            'club_id2' => API_FFF_CLUB_ID
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch current classement');
        assert(count($results) <= 100, 'Classement result too large');

        return $results;
    }

    /**
     * Récupère le classement d'une journée spécifique
     *
     * @param int $competitionId ID de la compétition
     * @param int $journeeNumber Numéro de journée
     * @param int|null $season Saison (null = actuelle)
     * @return array Classement de la journée
     */
    public function getClassementByJournee(int $competitionId, int $journeeNumber, ?int $season = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        assert($journeeNumber > 0, 'Journee number must be positive');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');

        $sql = "SELECT
                    cl.*,
                    CASE
                        WHEN cl.cl_no = :club_id THEN 'FC CHICHE'
                        ELSE COALESCE(cc.name, cl.team_short_name, 'Club')
                    END as club_name,
                    CASE 
                        WHEN cl.cl_no = :club_id2 THEN 'FC CHICHE'
                        ELSE COALESCE(cc.short_name, cl.team_short_name, 'Club')
                    END as club_short_name,
                    cc.logo_url as club_logo,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON cl.cl_no = cc.cl_no
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.cj_no = :cj_no
                AND cl.season = :season
                ORDER BY cl.ranking ASC";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'cj_no' => $journeeNumber,
            'season' => $season,
            'club_id' => API_FFF_CLUB_ID,
            'club_id2' => API_FFF_CLUB_ID
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch classement by journee');
        assert(count($results) <= 100, 'Classement result too large');

        return $results;
    }

    /**
     * Récupère la position du FC Chiche dans une compétition
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $season Saison (null = actuelle)
     * @return array|null Position actuelle du club
     */
    public function getClubPosition(int $competitionId, ?int $season = null): ?array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');
        assert(strlen((string)$season) === 4, 'Season must be four digits');

        $sql = "SELECT
                    cl.*,
                    CASE
                        WHEN cl.cl_no = :club_id3 THEN 'FC CHICHE'
                        ELSE 'Club'
                    END as club_name,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.cl_no = :club_id
                AND cl.season = :season
                AND cl.cj_no = (
                    SELECT MAX(cj_no)
                    FROM " . self::TABLE . "
                    WHERE competition_id = :competition_id2
                    AND season = :season2
                )
                LIMIT 1";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'club_id' => API_FFF_CLUB_ID,
            'season' => $season,
            'competition_id2' => $competitionId,
            'season2' => $season,
            'club_id3' => API_FFF_CLUB_ID
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid club position result');

        if ($result !== false) {
            assert(isset($result['ranking']), 'Ranking missing in result');
            assert(isset($result['competition_id']), 'Competition id missing in result');
            return $result;
        }

        return null;
    }

    /**
     * Récupère l'évolution de position d'un club
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $clNo Numéro club API (null = FC Chiche)
     * @param int|null $season Saison (null = actuelle)
     * @return array Historique des positions
     */
    public function getPositionHistory(int $competitionId, ?int $clNo = null, ?int $season = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($clNo === null) {
            $clNo = API_FFF_CLUB_ID;
        }

        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($clNo > 0, 'Club cl_no must be positive');
        assert($season > 2000 && $season < 3000, 'Invalid season year');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE competition_id = :competition_id
                AND cl_no = :cl_no
                AND season = :season
                ORDER BY cj_no ASC";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'cl_no' => $clNo,
            'season' => $season
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch position history');
        assert(count($results) <= 100, 'Position history too large');

        return $results;
    }

    /**
     * Récupère les statistiques du club dans une compétition
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $season Saison (null = actuelle)
     * @return array|null Statistiques actuelles
     */
    public function getClubStats(int $competitionId, ?int $season = null): ?array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        assert($season === null || ($season > 2000 && $season < 3000), 'Invalid season year');

        return $this->getClubPosition($competitionId, $season);
    }

    /**
     * Récupère toutes les compétitions avec classement
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des compétitions
     */
    public function getCompetitionsWithClassement(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');

        $sql = "SELECT DISTINCT
                    c.id,
                    c.cp_no,
                    c.name,
                    c.type,
                    c.level,
                    c.season
                FROM " . self::TABLE_COMPETITIONS . " c
                INNER JOIN " . self::TABLE . " cl ON c.id = cl.competition_id
                WHERE c.season = :season
                AND c.type = 'CH'
                ORDER BY c.name ASC";

        $stmt = $this->prepareAndExecute($sql, ['season' => $season]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch competitions with classement');
        assert(count($results) <= 200, 'Too many competitions fetched');

        return $results;
    }

    /**
     * Compte le nombre de journées d'une compétition
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $season Saison (null = actuelle)
     * @return int Nombre de journées
     */
    public function getJourneesCount(int $competitionId, ?int $season = null): int
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');

        $sql = "SELECT MAX(cj_no) as max_journee
                FROM " . self::TABLE . "
                WHERE competition_id = :competition_id
                AND season = :season";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'season' => $season
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid journees count result');

        if ($result !== false) {
            assert(array_key_exists('max_journee', $result), 'max_journee missing');
            return (int)($result['max_journee'] ?? 0);
        }

        return 0;
    }

    /**
     * Compare deux journées d'une compétition
     *
     * @param int $competitionId ID de la compétition
     * @param int $journeeStart Journée de départ
     * @param int $journeeEnd Journée de fin
     * @param int|null $season Saison (null = actuelle)
     * @return array Évolution entre les deux journées
     */
    public function compareJournees(int $competitionId, int $journeeStart, int $journeeEnd, ?int $season = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        assert($journeeStart > 0, 'Start journee must be positive');
        assert($journeeEnd > 0, 'End journee must be positive');
        assert($journeeStart < $journeeEnd, 'Start journee must be before end journee');
        
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');

        $sql = "SELECT
                    start.cl_no,
                    start.ranking as ranking_start,
                    start.point_count as points_start,
                    end.ranking as ranking_end,
                    end.point_count as points_end,
                    (end.ranking - start.ranking) as ranking_evolution,
                    (end.point_count - start.point_count) as points_evolution,
                    CASE 
                        WHEN start.cl_no = :club_id THEN 'FC CHICHE'
                        ELSE COALESCE(cc.name, start.team_short_name, 'Club')
                    END as club_name,
                    cc.logo_url as club_logo
                FROM " . self::TABLE . " start
                INNER JOIN " . self::TABLE . " end 
                    ON start.competition_id = end.competition_id 
                    AND start.cl_no = end.cl_no
                    AND start.season = end.season
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON start.cl_no = cc.cl_no
                WHERE start.competition_id = :competition_id
                AND start.season = :season
                AND start.cj_no = :journee_start
                AND end.cj_no = :journee_end
                ORDER BY end.ranking ASC";

        $stmt = $this->prepareAndExecute($sql, [
            'competition_id' => $competitionId,
            'season' => $season,
            'journee_start' => $journeeStart,
            'journee_end' => $journeeEnd,
            'club_id' => API_FFF_CLUB_ID
        ]);

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to compare journees');
        assert(count($results) <= 100, 'Journee comparison too large');

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
     * Compte le nombre total d'entrées de classement
     *
     * @param int|null $competitionId ID de la compétition (null = toutes)
     * @param int|null $season Saison (null = actuelle)
     * @return int Nombre d'entrées
     */
    public function countClassements(?int $competitionId = null, ?int $season = null): int
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }

        assert($season > 2000 && $season < 3000, 'Invalid season year');

        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE season = :season";

        if ($competitionId !== null) {
            assert($competitionId > 0, 'Competition ID must be positive');
            $sql .= " AND competition_id = :competition_id";
        }

        $params = ['season' => $season];
        if ($competitionId !== null) {
            $params['competition_id'] = $competitionId;
        }

        $stmt = $this->prepareAndExecute($sql, $params);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
    }

    /**
     * Prépare et exécute une requête préparée avec contrôles
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