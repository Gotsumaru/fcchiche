<?php
declare(strict_types=1);

/**
 * Modèle Classements - Historisation des classements
 */
class ClassementsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_classements';
    private const TABLE_COMPETITIONS = 'pprod_competitions';
    private const TABLE_CLUBS_CACHE = 'pprod_clubs_cache';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère le classement actuel d'une compétition (dernière journée)
     *
     * @param int $competitionId ID de la compétition
     * @return array Classement actuel
     * @throws PDOException Si erreur BDD
     */
    public function getCurrentClassement(int $competitionId): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    cl.*,
                    cc.name as club_name,
                    cc.short_name as club_short_name,
                    cc.logo_url as club_logo,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON cl.cl_no = cc.cl_no
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.cj_no = (
                    SELECT MAX(cj_no) 
                    FROM " . self::TABLE . " 
                    WHERE competition_id = :competition_id
                )
                ORDER BY cl.ranking ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['competition_id' => $competitionId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le classement d'une journée spécifique
     *
     * @param int $competitionId ID de la compétition
     * @param int $journeeNumber Numéro de journée
     * @return array Classement de la journée
     */
    public function getClassementByJournee(int $competitionId, int $journeeNumber): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        assert($journeeNumber > 0, 'Journee number must be positive');
        
        $sql = "SELECT 
                    cl.*,
                    cc.name as club_name,
                    cc.short_name as club_short_name,
                    cc.logo_url as club_logo,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON cl.cl_no = cc.cl_no
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.cj_no = :cj_no
                ORDER BY cl.ranking ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competition_id' => $competitionId,
            'cj_no' => $journeeNumber
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la position du FC Chiche dans une compétition
     *
     * @param int $competitionId ID de la compétition
     * @return array|null Position actuelle du club
     */
    public function getClubPosition(int $competitionId): ?array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT 
                    cl.*,
                    c.name as competition_name
                FROM " . self::TABLE . " cl
                LEFT JOIN " . self::TABLE_COMPETITIONS . " c ON cl.competition_id = c.id
                WHERE cl.competition_id = :competition_id
                AND cl.cl_no = :club_id
                AND cl.cj_no = (
                    SELECT MAX(cj_no) 
                    FROM " . self::TABLE . " 
                    WHERE competition_id = :competition_id
                )
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competition_id' => $competitionId,
            'club_id' => API_FFF_CLUB_ID
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère l'évolution de position d'un club
     *
     * @param int $competitionId ID de la compétition
     * @param int $clNo Numéro club API (default = FC Chiche)
     * @return array Historique des positions
     */
    public function getPositionHistory(int $competitionId, ?int $clNo = null): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($clNo === null) {
            $clNo = API_FFF_CLUB_ID;
        }
        
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE competition_id = :competition_id
                AND cl_no = :cl_no
                ORDER BY cj_no ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competition_id' => $competitionId,
            'cl_no' => $clNo
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les compétitions avec classement disponible
     *
     * @param int|null $season Saison (null = actuelle)
     * @return array Liste des compétitions avec classement
     */
    public function getCompetitionsWithClassement(?int $season = null): array
    {
        if ($season === null) {
            $season = $this->getCurrentSeason();
        }
        
        assert($season > 2000 && $season < 3000, 'Invalid season year');
        
        $sql = "SELECT DISTINCT 
                    c.id, c.cp_no, c.name, c.type, c.level,
                    COUNT(DISTINCT cl.cj_no) as nb_journees
                FROM " . self::TABLE_COMPETITIONS . " c
                INNER JOIN " . self::TABLE . " cl ON c.id = cl.competition_id
                WHERE c.season = :season
                AND c.type = 'CH'
                GROUP BY c.id
                ORDER BY c.name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['season' => $season]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le nombre de journées disponibles pour une compétition
     *
     * @param int $competitionId ID de la compétition
     * @return int Nombre de journées
     */
    public function getJourneesCount(int $competitionId): int
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        $sql = "SELECT COUNT(DISTINCT cj_no) as count 
                FROM " . self::TABLE . " 
                WHERE competition_id = :competition_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['competition_id' => $competitionId]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }

    /**
     * Récupère les statistiques globales d'un club dans une compétition
     *
     * @param int $competitionId ID de la compétition
     * @param int|null $clNo Numéro club API (null = FC Chiche)
     * @return array|null Statistiques actuelles
     */
    public function getClubStats(int $competitionId, ?int $clNo = null): ?array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        
        if ($clNo === null) {
            $clNo = API_FFF_CLUB_ID;
        }
        
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT 
                    ranking,
                    points,
                    games_played,
                    wins,
                    draws,
                    losses,
                    goals_for,
                    goals_against,
                    goal_difference,
                    cj_no as last_journee
                FROM " . self::TABLE . " 
                WHERE competition_id = :competition_id
                AND cl_no = :cl_no
                AND cj_no = (
                    SELECT MAX(cj_no) 
                    FROM " . self::TABLE . " 
                    WHERE competition_id = :competition_id
                )
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competition_id' => $competitionId,
            'cl_no' => $clNo
        ]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Compare deux journées pour voir l'évolution du classement
     *
     * @param int $competitionId ID de la compétition
     * @param int $journeeStart Journée de départ
     * @param int $journeeEnd Journée d'arrivée
     * @return array Évolution des positions
     */
    public function compareJournees(int $competitionId, int $journeeStart, int $journeeEnd): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        assert($journeeStart > 0 && $journeeEnd > 0, 'Journee numbers must be positive');
        assert($journeeStart < $journeeEnd, 'Start journee must be before end journee');
        
        $sql = "SELECT 
                    start.cl_no,
                    cc.name as club_name,
                    cc.logo_url as club_logo,
                    start.ranking as ranking_start,
                    start.points as points_start,
                    end.ranking as ranking_end,
                    end.points as points_end,
                    (start.ranking - end.ranking) as ranking_change,
                    (end.points - start.points) as points_change
                FROM " . self::TABLE . " start
                INNER JOIN " . self::TABLE . " end 
                    ON start.competition_id = end.competition_id 
                    AND start.cl_no = end.cl_no
                LEFT JOIN " . self::TABLE_CLUBS_CACHE . " cc ON start.cl_no = cc.cl_no
                WHERE start.competition_id = :competition_id
                AND start.cj_no = :journee_start
                AND end.cj_no = :journee_end
                ORDER BY end.ranking ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'competition_id' => $competitionId,
            'journee_start' => $journeeStart,
            'journee_end' => $journeeEnd
        ]);
        
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
     * Compte le nombre total d'entrées de classement
     *
     * @param int|null $competitionId ID de la compétition (null = toutes)
     * @return int Nombre d'entrées
     */
    public function countClassements(?int $competitionId = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        
        if ($competitionId !== null) {
            assert($competitionId > 0, 'Competition ID must be positive');
            $sql .= " WHERE competition_id = :competition_id";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($competitionId !== null) {
            $stmt->execute(['competition_id' => $competitionId]);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}