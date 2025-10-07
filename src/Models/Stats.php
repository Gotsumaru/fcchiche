<?php
declare(strict_types=1);


/**
 * Model Stats - Statistiques des équipes
 */
class Stats
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
     * Récupérer statistiques d'une équipe
     *
     * @param string $category Catégorie (SEM, U17, U15, U13)
     * @param int $number Numéro équipe
     * @param int|null $competition_id ID compétition (optionnel)
     * @return array Statistiques
     */
    public function getTeamStats(string $category, int $number, ?int $competition_id = null): array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        
        $sql = "SELECT 
            COUNT(*) AS matchs_joues,
            SUM(CASE 
                WHEN m.home_club_id = :club_id THEN
                    CASE WHEN m.home_score > m.away_score THEN 1 ELSE 0 END
                ELSE
                    CASE WHEN m.away_score > m.home_score THEN 1 ELSE 0 END
            END) AS victoires,
            SUM(CASE 
                WHEN m.home_score = m.away_score THEN 1 ELSE 0 
            END) AS nuls,
            SUM(CASE 
                WHEN m.home_club_id = :club_id THEN
                    CASE WHEN m.home_score < m.away_score THEN 1 ELSE 0 END
                ELSE
                    CASE WHEN m.away_score < m.home_score THEN 1 ELSE 0 END
            END) AS defaites,
            SUM(CASE WHEN m.home_club_id = :club_id THEN m.home_score ELSE m.away_score END) AS buts_pour,
            SUM(CASE WHEN m.home_club_id = :club_id THEN m.away_score ELSE m.home_score END) AS buts_contre,
            SUM(CASE WHEN m.home_club_id = :club_id THEN m.home_score ELSE m.away_score END) - 
            SUM(CASE WHEN m.home_club_id = :club_id THEN m.away_score ELSE m.home_score END) AS diff_buts
        FROM " . DB_PREFIX . "matchs m
        WHERE m.is_result = 1
          AND (
            (m.home_club_id = :club_id AND m.home_team_category = :category AND m.home_team_number = :number)
            OR (m.away_club_id = :club_id AND m.away_team_category = :category AND m.away_team_number = :number)
          )";
        
        $params = [
            'club_id' => $this->club_id,
            'category' => $category,
            'number' => $number
        ];
        
        if ($competition_id !== null) {
            $sql .= " AND m.competition_id = :competition_id";
            $params['competition_id'] = $competition_id;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        $stats = $stmt->fetch();
        
        if ($stats && $stats['matchs_joues'] > 0) {
            $stats['points'] = ($stats['victoires'] * 3) + $stats['nuls'];
            $stats['pourcentage_victoires'] = round(($stats['victoires'] / $stats['matchs_joues']) * 100, 1);
        } else {
            $stats['points'] = 0;
            $stats['pourcentage_victoires'] = 0;
        }
        
        return $stats ?: [
            'matchs_joues' => 0,
            'victoires' => 0,
            'nuls' => 0,
            'defaites' => 0,
            'buts_pour' => 0,
            'buts_contre' => 0,
            'diff_buts' => 0,
            'points' => 0,
            'pourcentage_victoires' => 0
        ];
    }
    
    /**
     * Récupérer statistiques domicile/extérieur
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return array ['domicile' => [...], 'exterieur' => [...]]
     */
    public function getHomeAwayStats(string $category, int $number): array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        
        $sql_home = "SELECT 
            COUNT(*) AS matchs,
            SUM(CASE WHEN m.home_score > m.away_score THEN 1 ELSE 0 END) AS victoires,
            SUM(CASE WHEN m.home_score = m.away_score THEN 1 ELSE 0 END) AS nuls,
            SUM(CASE WHEN m.home_score < m.away_score THEN 1 ELSE 0 END) AS defaites,
            SUM(m.home_score) AS buts_pour,
            SUM(m.away_score) AS buts_contre
        FROM " . DB_PREFIX . "matchs m
        WHERE m.is_result = 1
          AND m.home_club_id = :club_id
          AND m.home_team_category = :category
          AND m.home_team_number = :number";
        
        $sql_away = "SELECT 
            COUNT(*) AS matchs,
            SUM(CASE WHEN m.away_score > m.home_score THEN 1 ELSE 0 END) AS victoires,
            SUM(CASE WHEN m.away_score = m.home_score THEN 1 ELSE 0 END) AS nuls,
            SUM(CASE WHEN m.away_score < m.home_score THEN 1 ELSE 0 END) AS defaites,
            SUM(m.away_score) AS buts_pour,
            SUM(m.home_score) AS buts_contre
        FROM " . DB_PREFIX . "matchs m
        WHERE m.is_result = 1
          AND m.away_club_id = :club_id
          AND m.away_team_category = :category
          AND m.away_team_number = :number";
        
        $params = [
            'club_id' => $this->club_id,
            'category' => $category,
            'number' => $number
        ];
        
        $stmt_home = $this->pdo->prepare($sql_home);
        $stmt_home->execute($params);
        $home = $stmt_home->fetch();
        
        $stmt_away = $this->pdo->prepare($sql_away);
        $stmt_away->execute($params);
        $away = $stmt_away->fetch();
        
        return [
            'domicile' => $home ?: [
                'matchs' => 0, 'victoires' => 0, 'nuls' => 0, 
                'defaites' => 0, 'buts_pour' => 0, 'buts_contre' => 0
            ],
            'exterieur' => $away ?: [
                'matchs' => 0, 'victoires' => 0, 'nuls' => 0, 
                'defaites' => 0, 'buts_pour' => 0, 'buts_contre' => 0
            ]
        ];
    }
    
    /**
     * Récupérer série en cours (V/N/D)
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @param int $limit Nombre matchs à analyser
     * @return array Série actuelle
     */
    public function getCurrentStreak(string $category, int $number, int $limit = 5): array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        assert($limit > 0 && $limit <= 20, 'Limit must be between 1 and 20');
        
        $sql = "SELECT 
            CASE 
                WHEN m.home_club_id = :club_id THEN
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
            m.date
        FROM " . DB_PREFIX . "matchs m
        WHERE m.is_result = 1
          AND (
            (m.home_club_id = :club_id AND m.home_team_category = :category AND m.home_team_number = :number)
            OR (m.away_club_id = :club_id AND m.away_team_category = :category AND m.away_team_number = :number)
          )
        ORDER BY m.date DESC, m.time DESC
        LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':club_id', $this->club_id);
        $stmt->bindValue(':category', $category);
        $stmt->bindValue(':number', $number);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($results)) {
            return ['serie' => [], 'type' => null, 'count' => 0];
        }
        
        $serie = array_reverse($results);
        $current = $serie[count($serie) - 1];
        $count = 1;
        
        for ($i = count($serie) - 2; $i >= 0; $i--) {
            if ($serie[$i] === $current) {
                $count++;
            } else {
                break;
            }
        }
        
        return [
            'serie' => $serie,
            'type' => $current,
            'count' => $count
        ];
    }
    
    /**
     * Récupérer meilleurs buteurs (pas dispo dans API FFF)
     * Cette fonction est un placeholder pour extension future
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return array Liste buteurs (vide pour l'instant)
     */
    public function getTopScorers(string $category, int $number): array
    {
        // L'API FFF ne fournit pas de stats individuelles
        // Cette méthode est un placeholder pour une future extension
        return [];
    }
    
    /**
     * Récupérer moyennes de buts
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return array Moyennes marqués/encaissés
     */
    public function getGoalsAverages(string $category, int $number): array
    {
        $stats = $this->getTeamStats($category, $number);
        
        if ($stats['matchs_joues'] === 0) {
            return [
                'moy_marques' => 0,
                'moy_encaisses' => 0,
                'moy_diff' => 0
            ];
        }
        
        return [
            'moy_marques' => round($stats['buts_pour'] / $stats['matchs_joues'], 2),
            'moy_encaisses' => round($stats['buts_contre'] / $stats['matchs_joues'], 2),
            'moy_diff' => round($stats['diff_buts'] / $stats['matchs_joues'], 2)
        ];
    }
    
    /**
     * Récupérer comparaison avec adversaires
     *
     * @param string $category Catégorie
     * @param int $number Numéro équipe
     * @return array Adversaires les plus rencontrés
     */
    public function getOpponentsStats(string $category, int $number): array
    {
        assert(!empty($category), 'Category cannot be empty');
        assert($number > 0, 'Number must be positive');
        
        $sql = "SELECT 
            CASE 
                WHEN m.home_club_id = :club_id THEN m.away_team_name
                ELSE m.home_team_name
            END AS adversaire,
            COUNT(*) AS nb_matchs,
            SUM(CASE 
                WHEN m.home_club_id = :club_id THEN
                    CASE WHEN m.home_score > m.away_score THEN 1 ELSE 0 END
                ELSE
                    CASE WHEN m.away_score > m.home_score THEN 1 ELSE 0 END
            END) AS victoires,
            SUM(CASE WHEN m.home_score = m.away_score THEN 1 ELSE 0 END) AS nuls,
            SUM(CASE 
                WHEN m.home_club_id = :club_id THEN
                    CASE WHEN m.home_score < m.away_score THEN 1 ELSE 0 END
                ELSE
                    CASE WHEN m.away_score < m.home_score THEN 1 ELSE 0 END
            END) AS defaites
        FROM " . DB_PREFIX . "matchs m
        WHERE m.is_result = 1
          AND (
            (m.home_club_id = :club_id AND m.home_team_category = :category AND m.home_team_number = :number)
            OR (m.away_club_id = :club_id AND m.away_team_category = :category AND m.away_team_number = :number)
          )
        GROUP BY adversaire
        HAVING nb_matchs > 1
        ORDER BY nb_matchs DESC, victoires DESC
        LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'club_id' => $this->club_id,
            'category' => $category,
            'number' => $number
        ]);
        
        return $stmt->fetchAll();
    }
}