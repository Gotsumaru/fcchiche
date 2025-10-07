<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
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
        $stats = [
            'success' => true,
            'club' => false,
            'equipes' => 0,
            'calendrier' => 0,
            'resultats' => 0,
            'clubs_cache' => 0,
            'errors' => []
        ];
        
        try {
            $this->pdo->beginTransaction();
            
            $stats['club'] = $this->syncClub();
            $stats['equipes'] = $this->syncEquipes();
            
            $matchs_data = $this->syncAllMatchs();
            $stats['calendrier'] = $matchs_data['calendrier'];
            $stats['resultats'] = $matchs_data['resultats'];
            $stats['clubs_cache'] = $matchs_data['clubs_cache'];
            
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
        
        if ($data === null) {
            throw new Exception('Failed to fetch club info from API');
        }
        
        assert(isset($data['cl_no']), 'Missing cl_no in API response');
        
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
        $result = $stmt->execute([
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
        ]);
        
        if ($result) {
            $this->syncTerrains($data['terrains'] ?? []);
            $this->syncMembres($data['membres'] ?? []);
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
        $club_id = $this->getClubId();
        assert($club_id > 0, 'Invalid club ID');
        
        $count = 0;
        $max_iterations = 50;
        
        foreach ($terrains as $terrain) {
            if ($count >= $max_iterations) {
                break;
            }
            
            if (!isset($terrain['te_no'])) {
                continue;
            }
            
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
            $stmt->execute([
                'te_no' => $terrain['te_no'],
                'club_id' => $club_id,
                'name' => $terrain['name'],
                'zip_code' => $terrain['zip_code'] ?? null,
                'city' => $terrain['city'] ?? null,
                'address' => $terrain['address'] ?? null,
                'latitude' => $terrain['latitude'] ?? null,
                'longitude' => $terrain['longitude'] ?? null,
                'surface_type' => $terrain['libelle_surface'] ?? null
            ]);
            
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
        $club_id = $this->getClubId();
        assert($club_id > 0, 'Invalid club ID');
        
        $this->pdo->exec("DELETE FROM " . DB_PREFIX . "membres WHERE club_id = " . $club_id);
        
        $count = 0;
        $max_iterations = 100;
        
        foreach ($membres as $membre) {
            if ($count >= $max_iterations) {
                break;
            }
            
            if (!isset($membre['in_nom'], $membre['in_prenom'], $membre['ti_lib'])) {
                continue;
            }
            
            $sql = "INSERT INTO " . DB_PREFIX . "membres (
                club_id, nom, prenom, titre
            ) VALUES (
                :club_id, :nom, :prenom, :titre
            )";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'club_id' => $club_id,
                'nom' => $membre['in_nom'],
                'prenom' => $membre['in_prenom'],
                'titre' => $membre['ti_lib']
            ]);
            
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
        
        if ($data === null || !isset($data['hydra:member'])) {
            throw new Exception('Failed to fetch equipes from API');
        }
        
        $club_id = $this->getClubId();
        $equipes = $data['hydra:member'];
        $count = 0;
        $max_iterations = 50;
        
        foreach ($equipes as $equipe) {
            if ($count >= $max_iterations) {
                break;
            }
            
            $equipe_id = $this->syncEquipe($club_id, $equipe);
            
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
        $stmt->execute([
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
        ]);
        
        $equipe_id = (int)$this->pdo->lastInsertId();
        
        if ($equipe_id === 0) {
            $stmt = $this->pdo->prepare(
                "SELECT id FROM " . DB_PREFIX . "equipes 
                WHERE club_id = :club_id 
                AND category_code = :category_code 
                AND number = :number 
                AND season = :season"
            );
            $stmt->execute([
                'club_id' => $club_id,
                'category_code' => $equipe['category_code'],
                'number' => $equipe['number'],
                'season' => $equipe['season']
            ]);
            $result = $stmt->fetch();
            $equipe_id = $result ? (int)$result['id'] : 0;
        }
        
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
        $count = 0;
        $max_iterations = 20;
        
        foreach ($engagements as $engagement) {
            if ($count >= $max_iterations) {
                break;
            }
            
            if (!isset($engagement['competition']['cp_no'])) {
                continue;
            }
            
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
            $stmt->execute([
                'equipe_id' => $equipe_id,
                'competition_id' => $competition_id,
                'terrain_id' => $terrain_id,
                'statut' => $engagement['en_statut'] ?? null,
                'forfait_general' => $engagement['en_forf_gene'] ?? 'N',
                'tour_no' => $engagement['en_tour_no'] ?? null,
                'elimine' => $engagement['en_elimine'] ?? 'N',
                'phase_number' => $engagement['phase']['number'] ?? null,
                'poule_stage_number' => $engagement['poule']['stage_number'] ?? null
            ]);
            
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Synchroniser TOUS les matchs via nouvelle méthode API
     *
     * @return array ['calendrier' => count, 'resultats' => count, 'clubs_cache' => count]
     */
    private function syncAllMatchs(): array
    {
        $matchs_data = $this->api->getAllMatchs();
        
        $calendrier_count = $this->syncMatchs($matchs_data['calendrier'], false);
        $resultats_count = $this->syncMatchs($matchs_data['resultats'], true);
        
        // Mettre à jour le cache des clubs adverses
        $clubs_cache_count = $this->updateClubsCache($matchs_data['calendrier'], $matchs_data['resultats']);
        
        $this->updateConfigValue('last_sync_calendrier', date('Y-m-d H:i:s'));
        $this->updateConfigValue('last_sync_resultats', date('Y-m-d H:i:s'));
        
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
        
        foreach ($clubs_data as $club) {
            if ($count >= $max_iterations) {
                break;
            }
            
            $sql = "INSERT INTO " . DB_PREFIX . "clubs_cache (
                cl_no, name, short_name, logo_url
            ) VALUES (
                :cl_no, :name, :short_name, :logo_url
            ) ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                short_name = VALUES(short_name),
                logo_url = VALUES(logo_url)";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'cl_no' => $club['cl_no'],
                'name' => $club['name'],
                'short_name' => $club['short_name'],
                'logo_url' => $club['logo_url']
            ]);
            
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
        $count = 0;
        $max_iterations = 200;
        
        foreach ($matchs as $match) {
            if ($count >= $max_iterations) {
                break;
            }
            
            if (!isset($match['ma_no'])) {
                continue;
            }
            
            $competition_id = $this->getOrCreateCompetition($match['competition']);
            $terrain_id = $this->getTerrainId($match['terrain']['te_no'] ?? null);
            
            $date = $this->parseDate($match['date']);
            assert($date !== null, 'Invalid match date');
            
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
            $stmt->execute([
                'ma_no' => $match['ma_no'],
                'competition_id' => $competition_id,
                'terrain_id' => $terrain_id,
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
                'is_result' => $is_result ? 1 : 0,
                'external_updated_at' => $this->parseDateTime($match['external_updated_at'] ?? null)
            ]);
            
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Obtenir ou créer compétition
     *
     * @param array $competition Données compétition
     * @return int ID compétition
     */
    private function getOrCreateCompetition(array $competition): int
    {
        if (!isset($competition['cp_no'])) {
            throw new Exception('Missing cp_no in competition data');
        }
        
        $stmt = $this->pdo->prepare(
            "SELECT id FROM " . DB_PREFIX . "competitions WHERE cp_no = :cp_no"
        );
        $stmt->execute(['cp_no' => $competition['cp_no']]);
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
        $stmt->execute([
            'cp_no' => $competition['cp_no'],
            'season' => $competition['season'] ?? CURRENT_SEASON,
            'type' => $competition['type'] ?? null,
            'name' => $competition['name'] ?? 'Unknown',
            'level' => $competition['level'] ?? null,
            'cdg_cg_no' => $competition['cdg']['cg_no'] ?? null,
            'cdg_name' => $competition['cdg']['name'] ?? null,
            'external_updated_at' => $this->parseDateTime($competition['external_updated_at'] ?? null)
        ]);
        
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
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        $result = $stmt->fetch();
        
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
        
        $stmt = $this->pdo->prepare(
            "SELECT id FROM " . DB_PREFIX . "terrains WHERE te_no = :te_no"
        );
        $stmt->execute(['te_no' => $te_no]);
        $result = $stmt->fetch();
        
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
        $sql = "INSERT INTO " . DB_PREFIX . "config (config_key, config_value) 
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['key' => $key, 'value' => $value]);
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
        $execution_time = (time() - $this->start_time) * 1000;
        
        $sql = "INSERT INTO " . DB_PREFIX . "sync_logs (
            endpoint, status, message, records_processed, execution_time_ms
        ) VALUES (
            :endpoint, :status, :message, :records, :execution_time
        )";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'endpoint' => $endpoint,
            'status' => $status,
            'message' => json_encode($data),
            'records' => array_sum(array_filter($data, 'is_numeric')),
            'execution_time' => $execution_time
        ]);
    }
}