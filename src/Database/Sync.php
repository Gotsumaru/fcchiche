<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../API/FFFApiClient.php';
require_once __DIR__ . '/../Utils/Logger.php';

/**
 * Synchronisation données API vers BDD
 */
class Sync
{
    private PDO $pdo;
    private FFFApiClient $api;
    private Logger $logger;
    private int $start_time;

    /**
     * Vérifie qu'un tableau contient l'ensemble des clés attendues.
     *
     * @param array $data Données à contrôler
     * @param array $keys Liste des clés requises
     * @param string $context Contexte d'appel pour le message d'erreur
     * @return void
     */
    private function assertArrayHasKeys(array $data, array $keys, string $context): void
    {
        assert(!empty($context), 'Assertion context must be provided');
        assert(!empty($keys), 'Assertion keys list cannot be empty');

        foreach ($keys as $key) {
            assert(array_key_exists($key, $data), sprintf('%s missing key %s', $context, $key));
        }
    }

    /**
     * Exécute une requête préparée en vérifiant le succès.
     *
     * @param PDOStatement $stmt Requête préparée
     * @param array $params Paramètres à lier
     * @param string $context Contexte d'appel
     * @return bool Succès d'exécution
     */
    private function executeStatement(\PDOStatement $stmt, array $params, string $context): bool
    {
        assert(!empty($context), 'Execution context must be provided');
        $result = $stmt->execute($params);
        assert($result === true, sprintf('PDO execution failed for %s', $context));

        return $result;
    }
    
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->api = new FFFApiClient();
        $this->logger = new Logger('sync.log');
        $this->start_time = time();
    }
    
    /**
     * Exécuter synchronisation complète
     *
     * @return array Statistiques synchronisation
     */
    public function syncAll(): array
    {
        assert($this->pdo instanceof PDO, 'PDO instance must be initialised');
        assert($this->api instanceof FFFApiClient, 'API client must be available');

        $stats = [
            'success' => true,
            'club' => false,
            'equipes' => 0,
            'calendrier' => 0,
            'resultats' => 0,
            'clubs_cache' => 0,
            'classements' => 0,  // ⭐ NOUVEAU
            'errors' => []
        ];
        
        try {
            $this->pdo->beginTransaction();

            $stats['club'] = $this->syncClub();
            $stats['equipes'] = $this->syncEquipes();

            $matchs_data = $this->syncAllMatchs();
            assert(isset($matchs_data['calendrier'], $matchs_data['resultats'], $matchs_data['clubs_cache']), 'Match sync must return all counters');
            assert(is_int($matchs_data['calendrier']) && is_int($matchs_data['resultats']), 'Match counters must be integers');
            $stats['calendrier'] = $matchs_data['calendrier'];
            $stats['resultats'] = $matchs_data['resultats'];
            $stats['clubs_cache'] = $matchs_data['clubs_cache'];

            // ⭐ NOUVEAU - Synchroniser classements
            $stats['classements'] = $this->syncClassements();

            assert(is_int($stats['classements']), 'Classement counter must be integer');
            assert(is_bool($stats['club']), 'Club synchronisation must return boolean');

            $this->pdo->commit();
            $this->logger->info('Synchronisation complète réussie', $stats);
            
        } catch (Exception $e) {
            $this->pdo->rollBack();
            $stats['success'] = false;
            $stats['errors'][] = $e->getMessage();
            $this->logger->error('Synchronisation échouée', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        
        $this->logSync('sync_all', $stats['success'] ? 'success' : 'error', $stats);
        return $stats;
    }
    
    /**
     * Synchroniser infos club
     *
     * @return bool Succès
     * @throws PDOException Si erreur BDD
     */
    private function syncClub(): bool
    {
        $data = $this->api->getClubInfo();

        assert($data === null || is_array($data), 'Club info response must be array or null');
        if ($data === null) {
            throw new Exception('Failed to fetch club info from API');
        }

        $this->assertArrayHasKeys($data, ['cl_no', 'name', 'short_name'], 'Club info response');
        assert((int)$data['cl_no'] > 0, 'Club cl_no must be positive');

        $sql = "INSERT INTO " . DB_PREFIX . "club (
            cl_no, affiliation_number, name, short_name, location, colors,
            address1, address2, address3, postal_code, distributor_office,
            latitude, longitude, logo_url, district_name, district_cg_no
        ) VALUES (
            :cl_no, :affiliation_number, :name, :short_name, :location, :colors,
            :address1, :address2, :address3, :postal_code, :distributor_office,
            :latitude, :longitude, :logo_url, :district_name, :district_cg_no
        ) ON DUPLICATE KEY UPDATE
            affiliation_number = VALUES(affiliation_number),
            name = VALUES(name),
            short_name = VALUES(short_name),
            location = VALUES(location),
            colors = VALUES(colors),
            address1 = VALUES(address1),
            address2 = VALUES(address2),
            address3 = VALUES(address3),
            postal_code = VALUES(postal_code),
            distributor_office = VALUES(distributor_office),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude),
            logo_url = VALUES(logo_url),
            district_name = VALUES(district_name),
            district_cg_no = VALUES(district_cg_no)";
        
        $stmt = $this->pdo->prepare($sql);
        $result = $this->executeStatement($stmt, [
            'cl_no' => $data['cl_no'],
            'affiliation_number' => $data['affiliation_number'],
            'name' => $data['name'],
            'short_name' => $data['short_name'],
            'location' => $data['location'] ?? null,
            'colors' => $data['colors'] ?? null,
            'address1' => $data['address1'] ?? null,
            'address2' => $data['address2'] ?? null,
            'address3' => $data['address3'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'distributor_office' => $data['distributor_office'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'logo_url' => $data['logo'] ?? null,
            'district_name' => $data['district']['name'] ?? null,
            'district_cg_no' => $data['district']['cg_no'] ?? null
        ], 'club upsert');

        if ($result) {
            $terrainsCount = $this->syncTerrains($data['terrains'] ?? []);
            $membresCount = $this->syncMembres($data['membres'] ?? []);
            assert($terrainsCount >= 0, 'Terrains count must be positive');
            assert($membresCount >= 0, 'Membres count must be positive');
        }

        $this->updateConfigValue('last_sync_club', date('Y-m-d H:i:s'));
        return $result;
    }
    
    /**
     * Synchroniser terrains
     *
     * @param array $terrains Tableau terrains
     * @return int Nombre terrains synchronisés
     */
    private function syncTerrains(array $terrains): int
    {
        assert(is_array($terrains), 'Terrains payload must be an array');
        $club_id = $this->getClubId();
        assert($club_id > 0, 'Invalid club ID');

        $count = 0;
        $max_iterations = 50;
        assert($max_iterations > 0, 'Terrains iteration bound must be positive');

        foreach ($terrains as $terrain) {
            if ($count >= $max_iterations) {
                break;
            }

            if (!isset($terrain['te_no'])) {
                continue;
            }

            $this->assertArrayHasKeys($terrain, ['name'], 'Terrain entry');
            assert(is_string($terrain['name']) && $terrain['name'] !== '', 'Terrain name must be provided');

            $sql = "INSERT INTO " . DB_PREFIX . "terrains (
                te_no, club_id, name, zip_code, city, address,
                latitude, longitude, surface_type
            ) VALUES (
                :te_no, :club_id, :name, :zip_code, :city, :address,
                :latitude, :longitude, :surface_type
            ) ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                zip_code = VALUES(zip_code),
                city = VALUES(city),
                address = VALUES(address),
                latitude = VALUES(latitude),
                longitude = VALUES(longitude),
                surface_type = VALUES(surface_type)";
            
            $stmt = $this->pdo->prepare($sql);
            $this->executeStatement($stmt, [
                'te_no' => $terrain['te_no'],
                'club_id' => $club_id,
                'name' => $terrain['name'],
                'zip_code' => $terrain['zip_code'] ?? null,
                'city' => $terrain['city'] ?? null,
                'address' => $terrain['address'] ?? null,
                'latitude' => $terrain['latitude'] ?? null,
                'longitude' => $terrain['longitude'] ?? null,
                'surface_type' => $terrain['libelle_surface'] ?? null
            ], 'terrain upsert');

            $count++;
        }

        return $count;
    }
    
    /**
     * Synchroniser membres
     *
     * @param array $membres Tableau membres
     * @return int Nombre membres synchronisés
     */
    private function syncMembres(array $membres): int
    {
        assert(is_array($membres), 'Membres payload must be an array');
        $club_id = $this->getClubId();
        assert($club_id > 0, 'Invalid club ID');

        $deleteStmt = $this->pdo->prepare("DELETE FROM " . DB_PREFIX . "membres WHERE club_id = :club_id");
        $this->executeStatement($deleteStmt, ['club_id' => $club_id], 'purge membres');

        $count = 0;
        $max_iterations = 100;
        assert($max_iterations > 0, 'Membres iteration bound must be positive');

        foreach ($membres as $membre) {
            if ($count >= $max_iterations) {
                break;
            }

            if (!isset($membre['in_nom'], $membre['in_prenom'], $membre['ti_lib'])) {
                continue;
            }

            assert(is_string($membre['in_nom']) && $membre['in_nom'] !== '', 'Member last name must be provided');
            assert(is_string($membre['in_prenom']) && $membre['in_prenom'] !== '', 'Member first name must be provided');

            $sql = "INSERT INTO " . DB_PREFIX . "membres (
                club_id, nom, prenom, titre
            ) VALUES (
                :club_id, :nom, :prenom, :titre
            )";

            $stmt = $this->pdo->prepare($sql);
            $this->executeStatement($stmt, [
                'club_id' => $club_id,
                'nom' => $membre['in_nom'],
                'prenom' => $membre['in_prenom'],
                'titre' => $membre['ti_lib']
            ], 'membre insert');

            $count++;
        }

        return $count;
    }
    
    /**
     * Synchroniser équipes
     *
     * @return int Nombre équipes synchronisées
     * @throws PDOException Si erreur BDD
     */
    private function syncEquipes(): int
    {
        $data = $this->api->getEquipes();

        assert($data === null || is_array($data), 'Equipes response must be array or null');
        if ($data === null || !isset($data['hydra:member'])) {
            throw new Exception('Failed to fetch equipes from API');
        }

        $club_id = $this->getClubId();
        assert($club_id > 0, 'Club ID must exist before syncing equipes');
        $equipes = $data['hydra:member'];
        assert(is_array($equipes), 'Equipes hydra:member must be array');
        $count = 0;
        $max_iterations = 50;
        assert($max_iterations > 0, 'Equipes iteration bound must be positive');

        foreach ($equipes as $equipe) {
            if ($count >= $max_iterations) {
                break;
            }

            $equipe_id = $this->syncEquipe($club_id, $equipe);
            assert($equipe_id >= 0, 'Equipe ID must be non-negative');

            if ($equipe_id > 0 && isset($equipe['engagements'])) {
                $this->syncEngagements($equipe_id, $equipe['engagements']);
            }

            $count++;
        }
        
        $this->updateConfigValue('last_sync_equipes', date('Y-m-d H:i:s'));
        return $count;
    }
    
    /**
     * Synchroniser une équipe
     *
     * @param int $club_id ID club
     * @param array $equipe Données équipe
     * @return int ID équipe
     */
    private function syncEquipe(int $club_id, array $equipe): int
    {
        assert($club_id > 0, 'Club ID must be positive for equipe sync');
        $this->assertArrayHasKeys($equipe, ['category_code', 'number', 'code', 'short_name', 'type', 'season'], 'Equipe payload');

        $sql = "INSERT INTO " . DB_PREFIX . "equipes (
            club_id, category_code, number, code, short_name, type,
            season, category_label, category_gender, diffusable
        ) VALUES (
            :club_id, :category_code, :number, :code, :short_name, :type,
            :season, :category_label, :category_gender, :diffusable
        ) ON DUPLICATE KEY UPDATE
            short_name = VALUES(short_name),
            type = VALUES(type),
            category_label = VALUES(category_label),
            category_gender = VALUES(category_gender),
            diffusable = VALUES(diffusable)";
        
        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, [
            'club_id' => $club_id,
            'category_code' => $equipe['category_code'],
            'number' => $equipe['number'],
            'code' => $equipe['code'],
            'short_name' => $equipe['short_name'],
            'type' => $equipe['type'],
            'season' => $equipe['season'],
            'category_label' => $equipe['category_label'] ?? null,
            'category_gender' => $equipe['category_gender'] ?? null,
            'diffusable' => $equipe['diffusable'] ? 1 : 0
        ], 'equipe upsert');

        $equipe_id = (int)$this->pdo->lastInsertId();

        if ($equipe_id === 0) {
            $stmt = $this->pdo->prepare(
                "SELECT id FROM " . DB_PREFIX . "equipes
                WHERE club_id = :club_id
                AND category_code = :category_code
                AND number = :number
                AND season = :season"
            );
            $this->executeStatement($stmt, [
                'club_id' => $club_id,
                'category_code' => $equipe['category_code'],
                'number' => $equipe['number'],
                'season' => $equipe['season']
            ], 'equipe lookup');
            $result = $stmt->fetch();
            $equipe_id = $result ? (int)$result['id'] : 0;
        }

        assert($equipe_id >= 0, 'Equipe ID must be >=0 after sync');

        return $equipe_id;
    }
    
    /**
     * Synchroniser engagements d'une équipe
     *
     * @param int $equipe_id ID équipe
     * @param array $engagements Tableau engagements
     * @return int Nombre engagements synchronisés
     */
    private function syncEngagements(int $equipe_id, array $engagements): int
    {
        assert($equipe_id > 0, 'Equipe ID must be positive for engagements');
        assert(is_array($engagements), 'Engagements payload must be an array');
        $count = 0;
        $max_iterations = 20;
        assert($max_iterations > 0, 'Engagement iteration bound must be positive');

        foreach ($engagements as $engagement) {
            if ($count >= $max_iterations) {
                break;
            }

            if (!isset($engagement['competition']['cp_no'])) {
                continue;
            }

            $this->assertArrayHasKeys($engagement, ['competition'], 'Engagement payload');

            $competition_id = $this->getOrCreateCompetition($engagement['competition']);
            $terrain_id = $this->getTerrainId($engagement['terrain'] ?? null);

            $sql = "INSERT INTO " . DB_PREFIX . "engagements (
                equipe_id, competition_id, terrain_id, statut, forfait_general,
                tour_no, elimine, phase_number, poule_stage_number
            ) VALUES (
                :equipe_id, :competition_id, :terrain_id, :statut, :forfait_general,
                :tour_no, :elimine, :phase_number, :poule_stage_number
            ) ON DUPLICATE KEY UPDATE
                terrain_id = VALUES(terrain_id),
                statut = VALUES(statut),
                forfait_general = VALUES(forfait_general),
                tour_no = VALUES(tour_no),
                elimine = VALUES(elimine),
                phase_number = VALUES(phase_number),
                poule_stage_number = VALUES(poule_stage_number)";
            
            $stmt = $this->pdo->prepare($sql);
            $this->executeStatement($stmt, [
                'equipe_id' => $equipe_id,
                'competition_id' => $competition_id,
                'terrain_id' => $terrain_id,
                'statut' => $engagement['en_statut'] ?? null,
                'forfait_general' => $engagement['en_forf_gene'] ?? 'N',
                'tour_no' => $engagement['en_tour_no'] ?? null,
                'elimine' => $engagement['en_elimine'] ?? 'N',
                'phase_number' => $engagement['phase']['number'] ?? null,
                'poule_stage_number' => $engagement['poule']['stage_number'] ?? null
            ], 'engagement upsert');

            $count++;
        }

        return $count;
    }

    /**
     * Synchroniser classements de toutes les compétitions
     *
     * @return int Nombre classements synchronisés
     * @throws PDOException Si erreur BDD
     */
    private function syncClassements(): int
    {
        $classements = $this->api->getAllClassements();
        assert(is_array($classements), 'Classements payload must be an array');
        assert($this->pdo instanceof PDO, 'PDO instance is required for classements');

        if (empty($classements)) {
            $this->logger->warning('No classements data retrieved from API');
            return 0;
        }

        $count = 0;
        $max_iterations = 500;
        assert($max_iterations > 0, 'Classements iteration bound must be positive');

        foreach ($classements as $entry) {
            if ($count >= $max_iterations) {
                break;
            }

            if (!isset($entry['competition'], $entry['cj_no'], $entry['equipe']['club']['cl_no'])) {
                continue;
            }

            $competition_id = $this->getOrCreateCompetition($entry['competition']);
            if ($competition_id === 0) {
                continue;
            }

            $payload = $this->buildClassementPayload($entry, $competition_id);
            $this->persistClassement($payload);
            $count++;
        }

        $this->updateConfigValue('last_sync_classements', date('Y-m-d H:i:s'));
        assert($count <= $max_iterations, 'Classements processed exceeds maximum iterations');

        return $count;
    }

    /**
     * Prépare les données d'un classement pour insertion.
     *
     * @param array $entry Données API
     * @param int $competitionId ID compétition
     * @return array Payload normalisé
     */
    private function buildClassementPayload(array $entry, int $competitionId): array
    {
        assert($competitionId > 0, 'Competition ID must be positive');
        $this->assertArrayHasKeys($entry, ['season', 'date', 'cj_no', 'type', 'rank', 'point_count', 'total_games_count'], 'Classement entry');

        $date = $this->parseDate($entry['date']);
        assert($date !== null, 'Classement date must be valid');

        return [
            'competition_id' => $competitionId,
            'season' => $entry['season'],
            'date' => $date,
            'cj_no' => $entry['cj_no'],
            'type' => $entry['type'],
            'cl_no' => $entry['equipe']['club']['cl_no'],
            'team_category' => $entry['equipe']['category_code'] ?? null,
            'team_number' => $entry['equipe']['number'] ?? null,
            'team_short_name' => $entry['equipe']['short_name'] ?? null,
            'ranking' => $entry['rank'],
            'point_count' => $entry['point_count'],
            'penalty_point_count' => $entry['penalty_point_count'] ?? 0,
            'total_games_count' => $entry['total_games_count'],
            'won_games_count' => $entry['won_games_count'] ?? 0,
            'draw_games_count' => $entry['draw_games_count'] ?? 0,
            'lost_games_count' => $entry['lost_games_count'] ?? 0,
            'forfeits_games_count' => $entry['forfeits_games_count'] ?? 0,
            'goals_for_count' => $entry['goals_for_count'] ?? 0,
            'goals_against_count' => $entry['goals_against_count'] ?? 0,
            'goals_diff' => $entry['goals_diff'] ?? 0,
            'phase_number' => $entry['poule']['cdg']['cg_no'] ?? null,
            'poule_stage_number' => $entry['poule']['stage_number'] ?? null,
            'poule_name' => $entry['poule']['name'] ?? null,
            'is_forfait' => ($entry['is_forfait'] ?? false) ? 1 : 0,
            'external_updated_at' => $this->parseDateTime($entry['external_updated_at'] ?? null)
        ];
    }

    /**
     * Persiste un classement en base.
     *
     * @param array $payload Données prêtes à insérer
     * @return void
     */
    private function persistClassement(array $payload): void
    {
        $this->assertArrayHasKeys($payload, ['competition_id', 'season', 'date', 'cj_no', 'type', 'cl_no'], 'Classement payload');
        assert(is_int($payload['competition_id']) && $payload['competition_id'] > 0, 'Competition ID must be strictly positive');

        $sql = "INSERT INTO " . DB_PREFIX . "classements (
            competition_id, season, date, cj_no, type,
            cl_no, team_category, team_number, team_short_name,
            ranking, point_count, penalty_point_count,
            total_games_count, won_games_count, draw_games_count,
            lost_games_count, forfeits_games_count,
            goals_for_count, goals_against_count, goals_diff,
            phase_number, poule_stage_number, poule_name,
            is_forfait, external_updated_at
        ) VALUES (
            :competition_id, :season, :date, :cj_no, :type,
            :cl_no, :team_category, :team_number, :team_short_name,
            :ranking, :point_count, :penalty_point_count,
            :total_games_count, :won_games_count, :draw_games_count,
            :lost_games_count, :forfeits_games_count,
            :goals_for_count, :goals_against_count, :goals_diff,
            :phase_number, :poule_stage_number, :poule_name,
            :is_forfait, :external_updated_at
        ) ON DUPLICATE KEY UPDATE
            ranking = VALUES(ranking),
            point_count = VALUES(point_count),
            penalty_point_count = VALUES(penalty_point_count),
            total_games_count = VALUES(total_games_count),
            won_games_count = VALUES(won_games_count),
            draw_games_count = VALUES(draw_games_count),
            lost_games_count = VALUES(lost_games_count),
            forfeits_games_count = VALUES(forfeits_games_count),
            goals_for_count = VALUES(goals_for_count),
            goals_against_count = VALUES(goals_against_count),
            goals_diff = VALUES(goals_diff),
            team_short_name = VALUES(team_short_name),
            is_forfait = VALUES(is_forfait),
            external_updated_at = VALUES(external_updated_at)";

        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, $payload, 'classement upsert');
    }
    
    /**
     * Synchroniser TOUS les matchs via nouvelle méthode API
     *
     * @return array ['calendrier' => count, 'resultats' => count, 'clubs_cache' => count]
     */
    private function syncAllMatchs(): array
    {
        $matchs_data = $this->api->getAllMatchs();
        assert(is_array($matchs_data), 'All matchs payload must be array');
        $this->assertArrayHasKeys($matchs_data, ['calendrier', 'resultats'], 'All matchs payload');
        assert(is_array($matchs_data['calendrier']) && is_array($matchs_data['resultats']), 'Match arrays must be valid');

        $calendrier_count = $this->syncMatchs($matchs_data['calendrier'], false);
        $resultats_count = $this->syncMatchs($matchs_data['resultats'], true);

        // Mettre à jour le cache des clubs adverses
        $clubs_cache_count = $this->updateClubsCache($matchs_data['calendrier'], $matchs_data['resultats']);

        $this->updateConfigValue('last_sync_calendrier', date('Y-m-d H:i:s'));
        $this->updateConfigValue('last_sync_resultats', date('Y-m-d H:i:s'));

        assert($calendrier_count >= 0, 'Calendrier count must be positive');
        assert($resultats_count >= 0, 'Resultats count must be positive');

        return [
            'calendrier' => $calendrier_count,
            'resultats' => $resultats_count,
            'clubs_cache' => $clubs_cache_count
        ];
    }
    
    /**
     * Mettre à jour cache des clubs adverses
     *
     * @param array $calendrier_matchs Matchs calendrier
     * @param array $resultats_matchs Matchs résultats
     * @return int Nombre clubs mis à jour
     */
    private function updateClubsCache(array $calendrier_matchs, array $resultats_matchs): int
    {
        assert(is_array($calendrier_matchs), 'Calendrier matchs must be array');
        assert(is_array($resultats_matchs), 'Resultats matchs must be array');
        $all_matchs = array_merge($calendrier_matchs, $resultats_matchs);
        $clubs_data = [];

        foreach ($all_matchs as $match) {
            // Club domicile
            if (isset($match['home']['club']['cl_no']) && $match['home']['club']['cl_no'] != API_FFF_CLUB_ID) {
                $cl_no = $match['home']['club']['cl_no'];
                $clubs_data[$cl_no] = [
                    'cl_no' => $cl_no,
                    'name' => $match['home']['short_name'] ?? null,
                    'short_name' => $match['home']['short_name'] ?? null,
                    'logo_url' => $match['home']['club']['logo'] ?? null
                ];
            }
            
            // Club extérieur
            if (isset($match['away']['club']['cl_no']) && $match['away']['club']['cl_no'] != API_FFF_CLUB_ID) {
                $cl_no = $match['away']['club']['cl_no'];
                $clubs_data[$cl_no] = [
                    'cl_no' => $cl_no,
                    'name' => $match['away']['short_name'] ?? null,
                    'short_name' => $match['away']['short_name'] ?? null,
                    'logo_url' => $match['away']['club']['logo'] ?? null
                ];
            }
        }

        $count = 0;
        $max_iterations = 100;
        assert($max_iterations > 0, 'Club cache iteration bound must be positive');

        foreach ($clubs_data as $club) {
            if ($count >= $max_iterations) {
                break;
            }

            $this->assertArrayHasKeys($club, ['cl_no', 'name', 'short_name'], 'Club cache entry');
            assert((int)$club['cl_no'] > 0, 'Club cache cl_no must be positive');

            $sql = "INSERT INTO " . DB_PREFIX . "clubs_cache (
                cl_no, name, short_name, logo_url
            ) VALUES (
                :cl_no, :name, :short_name, :logo_url
            ) ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                short_name = VALUES(short_name),
                logo_url = VALUES(logo_url)";

            $stmt = $this->pdo->prepare($sql);
            $this->executeStatement($stmt, [
                'cl_no' => $club['cl_no'],
                'name' => $club['name'],
                'short_name' => $club['short_name'],
                'logo_url' => $club['logo_url']
            ], 'club cache upsert');

            $count++;
        }

        return $count;
    }
    
    /**
     * Synchroniser matchs
     *
     * @param array $matchs Tableau matchs
     * @param bool $is_result Match terminé ou à venir
     * @return int Nombre matchs synchronisés
     */
    private function syncMatchs(array $matchs, bool $is_result): int
    {
        assert(is_array($matchs), 'Match payload must be an array');
        assert(is_bool($is_result), 'is_result flag must be boolean');

        $count = 0;
        $max_iterations = 200;
        assert($max_iterations > 0, 'Matches iteration bound must be positive');

        foreach ($matchs as $match) {
            if ($count >= $max_iterations) {
                break;
            }

            if (!isset($match['ma_no'], $match['competition'])) {
                continue;
            }

            $competition_id = $this->getOrCreateCompetition($match['competition']);
            if ($competition_id === 0) {
                continue;
            }

            $terrain_reference = $match['terrain']['te_no'] ?? ($match['terrain'] ?? null);
            $terrain_id = $this->getTerrainId($terrain_reference);
            $payload = $this->buildMatchPayload($match, $competition_id, $terrain_id, $is_result);
            $this->persistMatch($payload);
            $count++;
        }

        assert($count <= $max_iterations, 'Matches processed exceeds maximum iterations');

        return $count;
    }

    /**
     * Construit le payload d'un match pour insertion.
     *
     * @param array $match Données API match
     * @param int $competitionId ID compétition
     * @param int|null $terrainId ID terrain
     * @param bool $isResult Indique s'il s'agit d'un résultat
     * @return array Payload normalisé
     */
    private function buildMatchPayload(array $match, int $competitionId, ?int $terrainId, bool $isResult): array
    {
        assert($competitionId > 0, 'Competition ID must be positive for matches');
        $this->assertArrayHasKeys($match, ['ma_no', 'season', 'date', 'status'], 'Match entry');

        $date = $this->parseDate($match['date']);
        assert($date !== null, 'Match date must be valid');

        return [
            'ma_no' => $match['ma_no'],
            'competition_id' => $competitionId,
            'terrain_id' => $terrainId,
            'season' => $match['season'],
            'date' => $date,
            'time' => $match['time'] ?? null,
            'initial_date' => $this->parseDate($match['initial_date'] ?? null),
            'phase_number' => $match['phase']['number'] ?? null,
            'phase_type' => $match['phase']['type'] ?? null,
            'phase_name' => $match['phase']['name'] ?? null,
            'poule_stage_number' => $match['poule']['stage_number'] ?? null,
            'poule_name' => $match['poule']['name'] ?? null,
            'poule_journee_number' => $match['poule_journee']['number'] ?? null,
            'home_club_id' => $match['home']['club']['cl_no'] ?? null,
            'home_team_category' => $match['home']['category_code'] ?? null,
            'home_team_number' => $match['home']['number'] ?? null,
            'home_team_name' => $match['home']['short_name'] ?? null,
            'home_score' => $match['home_score'] ?? null,
            'home_is_forfeit' => $match['home_is_forfeit'] ?? 'N',
            'away_club_id' => $match['away']['club']['cl_no'] ?? null,
            'away_team_category' => $match['away']['category_code'] ?? null,
            'away_team_number' => $match['away']['number'] ?? null,
            'away_team_name' => $match['away']['short_name'] ?? null,
            'away_score' => $match['away_score'] ?? null,
            'away_is_forfeit' => $match['away_is_forfeit'] ?? 'N',
            'status' => $match['status'],
            'status_label' => $match['status_label'] ?? null,
            'is_overtime' => $match['is_overtime'] ?? 'N',
            'seems_postponed' => $match['seems_postponed'] ?? null,
            'is_result' => $isResult ? 1 : 0,
            'external_updated_at' => $this->parseDateTime($match['external_updated_at'] ?? null)
        ];
    }

    /**
     * Persiste un match en base.
     *
     * @param array $payload Données préparées
     * @return void
     */
    private function persistMatch(array $payload): void
    {
        $this->assertArrayHasKeys($payload, ['ma_no', 'competition_id', 'season', 'date', 'status', 'is_result'], 'Match payload');
        assert(is_int($payload['competition_id']) && $payload['competition_id'] > 0, 'Match competition ID must be positive');

        $sql = "INSERT INTO " . DB_PREFIX . "matchs (
            ma_no, competition_id, terrain_id, season, date, time, initial_date,
            phase_number, phase_type, phase_name, poule_stage_number, poule_name,
            poule_journee_number, home_club_id, home_team_category, home_team_number,
            home_team_name, home_score, home_is_forfeit, away_club_id, away_team_category,
            away_team_number, away_team_name, away_score, away_is_forfeit,
            status, status_label, is_overtime, seems_postponed, is_result, external_updated_at
        ) VALUES (
            :ma_no, :competition_id, :terrain_id, :season, :date, :time, :initial_date,
            :phase_number, :phase_type, :phase_name, :poule_stage_number, :poule_name,
            :poule_journee_number, :home_club_id, :home_team_category, :home_team_number,
            :home_team_name, :home_score, :home_is_forfeit, :away_club_id, :away_team_category,
            :away_team_number, :away_team_name, :away_score, :away_is_forfeit,
            :status, :status_label, :is_overtime, :seems_postponed, :is_result, :external_updated_at
        ) ON DUPLICATE KEY UPDATE
            terrain_id = VALUES(terrain_id),
            date = VALUES(date),
            time = VALUES(time),
            initial_date = VALUES(initial_date),
            phase_number = VALUES(phase_number),
            phase_type = VALUES(phase_type),
            phase_name = VALUES(phase_name),
            poule_stage_number = VALUES(poule_stage_number),
            poule_name = VALUES(poule_name),
            poule_journee_number = VALUES(poule_journee_number),
            home_score = VALUES(home_score),
            home_is_forfeit = VALUES(home_is_forfeit),
            away_score = VALUES(away_score),
            away_is_forfeit = VALUES(away_is_forfeit),
            status = VALUES(status),
            status_label = VALUES(status_label),
            is_overtime = VALUES(is_overtime),
            seems_postponed = VALUES(seems_postponed),
            is_result = VALUES(is_result),
            external_updated_at = VALUES(external_updated_at)";

        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, $payload, 'match upsert');
    }
    
    /**
     * Obtenir ou créer compétition
     *
     * @param array $competition Données compétition
     * @return int ID compétition
     */
    private function getOrCreateCompetition(array $competition): int
    {
        $this->assertArrayHasKeys($competition, ['cp_no'], 'Competition payload');
        assert(isset($competition['season']) || defined('CURRENT_SEASON'), 'Competition season must be provided or default defined');

        if (!isset($competition['cp_no'])) {
            throw new Exception('Missing cp_no in competition data');
        }

        $stmt = $this->pdo->prepare(
            "SELECT id FROM " . DB_PREFIX . "competitions WHERE cp_no = :cp_no"
        );
        $this->executeStatement($stmt, ['cp_no' => $competition['cp_no']], 'competition lookup');
        $result = $stmt->fetch();

        if ($result) {
            return (int)$result['id'];
        }
        
        $sql = "INSERT INTO " . DB_PREFIX . "competitions (
            cp_no, season, type, name, level, cdg_cg_no, cdg_name, external_updated_at
        ) VALUES (
            :cp_no, :season, :type, :name, :level, :cdg_cg_no, :cdg_name, :external_updated_at
        )";
        
        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, [
            'cp_no' => $competition['cp_no'],
            'season' => $competition['season'] ?? CURRENT_SEASON,
            'type' => $competition['type'] ?? null,
            'name' => $competition['name'] ?? 'Unknown',
            'level' => $competition['level'] ?? null,
            'cdg_cg_no' => $competition['cdg']['cg_no'] ?? null,
            'cdg_name' => $competition['cdg']['name'] ?? null,
            'external_updated_at' => $this->parseDateTime($competition['external_updated_at'] ?? null)
        ], 'competition insert');

        return (int)$this->pdo->lastInsertId();
    }
    
    /**
     * Obtenir ID club
     *
     * @return int ID club
     */
    private function getClubId(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT id FROM " . DB_PREFIX . "club WHERE cl_no = :cl_no"
        );
        $this->executeStatement($stmt, ['cl_no' => API_FFF_CLUB_ID], 'club lookup');
        $result = $stmt->fetch();

        assert($result === false || isset($result['id']), 'Club lookup must return id field');

        return $result ? (int)$result['id'] : 0;
    }
    
    /**
     * Obtenir ID terrain
     *
     * @param mixed $terrain_ref Référence terrain (te_no ou chemin API)
     * @return int|null ID terrain ou null
     */
    private function getTerrainId($terrain_ref): ?int
    {
        if ($terrain_ref === null) {
            return null;
        }

        $te_no = null;

        if (is_numeric($terrain_ref)) {
            $te_no = (int)$terrain_ref;
        } elseif (is_string($terrain_ref) && strpos($terrain_ref, '/api/terrains/') === 0) {
            $te_no = (int)str_replace('/api/terrains/', '', $terrain_ref);
        }

        if ($te_no === null || $te_no === 0) {
            return null;
        }

        assert($te_no > 0, 'Terrain number must be positive when resolved');

        $stmt = $this->pdo->prepare(
            "SELECT id FROM " . DB_PREFIX . "terrains WHERE te_no = :te_no"
        );
        $this->executeStatement($stmt, ['te_no' => $te_no], 'terrain lookup');
        $result = $stmt->fetch();

        assert($result === false || isset($result['id']), 'Terrain lookup must return id field');

        return $result ? (int)$result['id'] : null;
    }
    
    /**
     * Parser date ISO en format MySQL
     *
     * @param string|null $date_str Date ISO
     * @return string|null Date MySQL ou null
     */
    private function parseDate(?string $date_str): ?string
    {
        if ($date_str === null) {
            return null;
        }

        assert(is_string($date_str), 'Date string must be of type string');
        assert(strlen($date_str) >= 8, 'Date string must contain at least 8 characters');
        $timestamp = strtotime($date_str);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }
    
    /**
     * Parser datetime ISO en format MySQL
     *
     * @param string|null $datetime_str Datetime ISO
     * @return string|null Datetime MySQL ou null
     */
    private function parseDateTime(?string $datetime_str): ?string
    {
        if ($datetime_str === null) {
            return null;
        }

        assert(is_string($datetime_str), 'Datetime string must be of type string');
        assert(strlen($datetime_str) >= 8, 'Datetime string must contain at least 8 characters');
        $timestamp = strtotime($datetime_str);
        return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
    }
    
    /**
     * Mettre à jour valeur config
     *
     * @param string $key Clé
     * @param string $value Valeur
     * @return bool Succès
     */
    private function updateConfigValue(string $key, string $value): bool
    {
        assert($key !== '', 'Config key cannot be empty');
        assert(is_string($value), 'Config value must be string');
        $sql = "INSERT INTO " . DB_PREFIX . "config (config_key, config_value)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)";

        $stmt = $this->pdo->prepare($sql);
        $result = $this->executeStatement($stmt, ['key' => $key, 'value' => $value], 'config upsert');
        assert($result === true, 'Config update must succeed');

        return $result;
    }
    
    /**
     * Logger synchronisation dans BDD
     *
     * @param string $endpoint Endpoint
     * @param string $status Statut
     * @param array $data Données contextuelles
     * @return void
     */
    private function logSync(string $endpoint, string $status, array $data): void
    {
        assert($endpoint !== '', 'Log endpoint cannot be empty');
        assert(in_array($status, ['success', 'error'], true), 'Log status must be success or error');
        $execution_time = (time() - $this->start_time) * 1000;
        assert($execution_time >= 0, 'Execution time must be non negative');

        $sql = "INSERT INTO " . DB_PREFIX . "sync_logs (
            endpoint, status, message, records_processed, execution_time_ms
        ) VALUES (
            :endpoint, :status, :message, :records, :execution_time
        )";

        $stmt = $this->pdo->prepare($sql);
        $this->executeStatement($stmt, [
            'endpoint' => $endpoint,
            'status' => $status,
            'message' => json_encode($data),
            'records' => array_sum(array_filter($data, 'is_numeric')),
            'execution_time' => $execution_time
        ], 'sync log insert');
    }
}