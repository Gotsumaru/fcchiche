<?php
declare(strict_types=1);

/**
 * MatchModel - Gestion des matchs (résultats et calendrier)
 */
class MatchModel
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
     * Récupérer les derniers résultats
     *
     * @param int $limit Nombre de résultats
     * @param string|null $category Catégorie équipe (SEM, U17, U15, U13)
     * @param int|null $number Numéro équipe
     * @return array Liste des résultats
     */
    public function getLastResults(int $limit = 5, ?string $category = null, ?int $number = null): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');
        assert($limit > 0, 'Limit must be positive');
        assert($limit <= 100, 'Limit must be 100 or less');

        if (($category === null) !== ($number === null)) {
            throw new \InvalidArgumentException('Category and number must be provided together');
        }
        
        $sql = "SELECT
            m.id,
            m.ma_no,
            DATE_FORMAT(m.date, '%d/%m/%Y') AS date_fr,
            m.date,
            m.time,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN 'DOM'
                ELSE 'EXT'
            END AS lieu,
            m.home_team_name AS domicile,
            m.home_score,
            m.away_score,
            m.away_team_name AS exterieur,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN
                    CASE
                        WHEN m.home_score > m.away_score THEN 'V'
                        WHEN m.home_score < m.away_score THEN 'D'
                        ELSE 'N'
                    END
                ELSE
                    CASE
                        WHEN m.away_score > m.home_score THEN 'V'
                        WHEN m.away_score < m.home_score THEN 'D'
                        ELSE 'N'
                    END
            END AS resultat,
            c.name AS competition,
            c.type AS competition_type,
            CONCAT(COALESCE(e.category_code,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_category ELSE m.away_team_category END
            ), ' ',
            COALESCE(e.number,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_number ELSE m.away_team_number END
            )) AS equipe_chiche,
            m.poule_journee_number AS journee
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        JOIN (SELECT :club_id AS club_id_ref) AS params
        LEFT JOIN " . DB_PREFIX . "equipes e ON (
            (m.home_club_id = params.club_id_ref AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
            OR (m.away_club_id = params.club_id_ref AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
        )";

        if ($category !== null && $number !== null) {
            $sql .= "
        JOIN (SELECT :category AS category_ref, :number AS number_ref) AS filters";
        }

        $sql .= "
        WHERE m.is_result = 1
          AND (m.home_club_id = params.club_id_ref OR m.away_club_id = params.club_id_ref)";

        $params = [':club_id' => $this->club_id];

        if ($category !== null && $number !== null) {
            $sql .= "
          AND (
                (m.home_club_id = params.club_id_ref AND m.home_team_category = filters.category_ref AND m.home_team_number = filters.number_ref)
                OR (m.away_club_id = params.club_id_ref AND m.away_team_category = filters.category_ref AND m.away_team_number = filters.number_ref)
            )";
            $params[':category'] = $category;
            $params[':number'] = $number;
        }

        $sql .= " ORDER BY m.date DESC, m.time DESC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $placeholder => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($placeholder, $value, $type);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer les prochains matchs
     *
     * @param int $limit Nombre de matchs
     * @param string|null $category Catégorie équipe
     * @param int|null $number Numéro équipe
     * @return array Liste des matchs à venir
     */
    public function getUpcomingMatches(int $limit = 5, ?string $category = null, ?int $number = null): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');
        assert($limit > 0, 'Limit must be positive');
        assert($limit <= 100, 'Limit must be 100 or less');

        if (($category === null) !== ($number === null)) {
            throw new \InvalidArgumentException('Category and number must be provided together');
        }
        
        $sql = "SELECT
            m.id,
            m.ma_no,
            DATE_FORMAT(m.date, '%d/%m/%Y') AS date_fr,
            DATE_FORMAT(m.date, '%W') AS jour_semaine,
            m.date,
            m.time,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN 'DOM'
                ELSE 'EXT'
            END AS lieu,
            m.home_team_name AS domicile,
            m.away_team_name AS exterieur,
            c.name AS competition,
            c.type AS competition_type,
            t.name AS terrain,
            t.city AS ville_terrain,
            t.address AS adresse_terrain,
            CONCAT(COALESCE(e.category_code,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_category ELSE m.away_team_category END
            ), ' ',
            COALESCE(e.number,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_number ELSE m.away_team_number END
            )) AS equipe_chiche,
            m.poule_journee_number AS journee
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        LEFT JOIN " . DB_PREFIX . "terrains t ON m.terrain_id = t.id
        JOIN (SELECT :club_id AS club_id_ref) AS params
        LEFT JOIN " . DB_PREFIX . "equipes e ON (
            (m.home_club_id = params.club_id_ref AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
            OR (m.away_club_id = params.club_id_ref AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
        )";

        if ($category !== null && $number !== null) {
            $sql .= "
        JOIN (SELECT :category AS category_ref, :number AS number_ref) AS filters";
        }

        $sql .= "
        WHERE m.is_result = 0
          AND (m.home_club_id = params.club_id_ref OR m.away_club_id = params.club_id_ref)
          AND m.date >= CURDATE()";

        $params = [':club_id' => $this->club_id];

        if ($category !== null && $number !== null) {
            $sql .= "
          AND (
                (m.home_club_id = params.club_id_ref AND m.home_team_category = filters.category_ref AND m.home_team_number = filters.number_ref)
                OR (m.away_club_id = params.club_id_ref AND m.away_team_category = filters.category_ref AND m.away_team_number = filters.number_ref)
            )";
            $params[':category'] = $category;
            $params[':number'] = $number;
        }

        $sql .= " ORDER BY m.date ASC, m.time ASC LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $placeholder => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($placeholder, $value, $type);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer le calendrier complet d'une équipe
     *
     * @param string $category Catégorie (SEM, U17, U15, U13)
     * @param int $number Numéro équipe
     * @param bool $only_upcoming Seulement matchs à venir
     * @return array Calendrier complet
     */
    public function getTeamSchedule(string $category, int $number, bool $only_upcoming = false): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');

        $sql = "SELECT
            m.id,
            m.ma_no,
            DATE_FORMAT(m.date, '%d/%m/%Y') AS date_fr,
            m.date,
            m.time,
            CASE WHEN m.home_club_id = params.club_id_ref THEN 'DOM' ELSE 'EXT' END AS lieu,
            m.home_team_name AS domicile,
            m.away_team_name AS exterieur,
            m.home_score,
            m.away_score,
            c.name AS competition,
            c.type AS competition_type,
            m.poule_journee_number AS journee,
            m.is_result,
            t.name AS terrain,
            t.city AS ville_terrain
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        LEFT JOIN " . DB_PREFIX . "terrains t ON m.terrain_id = t.id
        JOIN (SELECT :club_id AS club_id_ref) AS params
        WHERE (
            (m.home_club_id = params.club_id_ref AND m.home_team_category = :category AND m.home_team_number = :number)
            OR (m.away_club_id = params.club_id_ref AND m.away_team_category = :category AND m.away_team_number = :number)
        )";

        if ($only_upcoming) {
            $sql .= " AND m.is_result = 0 AND m.date >= CURDATE()";
        }

        $sql .= " ORDER BY m.date ASC, m.time ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':club_id' => $this->club_id,
            ':category' => $category,
            ':number' => $number
        ]);

        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer planning de la semaine (7 jours)
     *
     * @return array Matchs dans les 7 prochains jours
     */
    public function getWeekSchedule(): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');

        $sql = "SELECT
            m.id,
            DATE_FORMAT(m.date, '%a %d/%m') AS jour,
            m.date,
            m.time,
            CONCAT(COALESCE(e.category_code,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_category ELSE m.away_team_category END
            ), ' ',
            COALESCE(e.number,
                CASE WHEN m.home_club_id = params.club_id_ref THEN m.home_team_number ELSE m.away_team_number END
            )) AS equipe,
            CASE WHEN m.home_club_id = params.club_id_ref THEN 'DOM' ELSE 'EXT' END AS lieu,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN m.away_team_name
                ELSE m.home_team_name
            END AS adversaire,
            c.name AS competition,
            t.name AS terrain,
            t.city AS ville_terrain
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        LEFT JOIN " . DB_PREFIX . "terrains t ON m.terrain_id = t.id
        JOIN (SELECT :club_id AS club_id_ref) AS params
        LEFT JOIN " . DB_PREFIX . "equipes e ON (
            (m.home_club_id = params.club_id_ref AND e.category_code = m.home_team_category AND e.number = m.home_team_number)
            OR (m.away_club_id = params.club_id_ref AND e.category_code = m.away_team_category AND e.number = m.away_team_number)
        )
        WHERE m.is_result = 0
          AND (m.home_club_id = params.club_id_ref OR m.away_club_id = params.club_id_ref)
          AND m.date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ORDER BY m.date ASC, m.time ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':club_id' => $this->club_id]);

        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer dernier résultat de chaque coupe
     *
     * @return array Derniers résultats des coupes
     */
    public function getLastCupResults(): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');

        $sql = "SELECT
            c.name AS coupe,
            DATE_FORMAT(m.date, '%d/%m/%Y') AS date_fr,
            m.date,
            m.home_team_name AS domicile,
            m.home_score,
            m.away_score,
            m.away_team_name AS exterieur,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN 'DOM'
                ELSE 'EXT'
            END AS lieu,
            CASE
                WHEN m.home_club_id = params.club_id_ref THEN
                    CASE
                        WHEN m.home_score > m.away_score THEN 'Qualifié'
                        WHEN m.home_score = m.away_score THEN 'Prolongations'
                        ELSE 'Éliminé'
                    END
                ELSE
                    CASE
                        WHEN m.away_score > m.home_score THEN 'Qualifié'
                        WHEN m.away_score = m.home_score THEN 'Prolongations'
                        ELSE 'Éliminé'
                    END
            END AS statut
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        JOIN (SELECT :club_id AS club_id_ref) AS params
        WHERE m.is_result = 1
          AND c.type = 'CP'
          AND (m.home_club_id = params.club_id_ref OR m.away_club_id = params.club_id_ref)
          AND m.id IN (
            SELECT MAX(m2.id)
            FROM " . DB_PREFIX . "matchs m2
            WHERE m2.competition_id = m.competition_id
              AND m2.is_result = 1
              AND (m2.home_club_id = params.club_id_ref OR m2.away_club_id = params.club_id_ref)
            GROUP BY m2.competition_id
          )
        ORDER BY m.date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':club_id' => $this->club_id]);

        return $stmt->fetchAll();
    }
    
    /**
     * Récupérer un match spécifique
     *
     * @param int $ma_no Numéro match API
     * @return array|null Détails du match
     */
    public function getMatchById(int $ma_no): ?array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->club_id > 0, 'Club ID must be positive');
        assert(is_int($ma_no), 'Match number must be an integer');
        assert($ma_no > 0, 'Match number must be positive');
        
        $sql = "SELECT 
            m.*,
            c.name AS competition_name,
            c.type AS competition_type,
            t.name AS terrain_name,
            t.address AS terrain_address,
            t.city AS terrain_city
        FROM " . DB_PREFIX . "matchs m
        JOIN " . DB_PREFIX . "competitions c ON m.competition_id = c.id
        LEFT JOIN " . DB_PREFIX . "terrains t ON m.terrain_id = t.id
        WHERE m.ma_no = :ma_no";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['ma_no' => $ma_no]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
