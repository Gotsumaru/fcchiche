<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../Utils/Logger.php';

/**
 * Client API FFF - Version complète avec gestion format coupes
 */
class FFFApiClient
{
    private string $base_url;
    private int $club_id;
    private int $timeout;
    private Logger $logger;
    
    /**
     * Constructeur
     *
     * @param int|null $club_id ID du club
     * @param Logger|null $logger Instance logger
     */
    public function __construct(?int $club_id = null, ?Logger $logger = null)
    {
        $this->base_url = API_FFF_BASE_URL;
        $this->club_id = $club_id ?? API_FFF_CLUB_ID;
        $this->timeout = API_FFF_TIMEOUT;
        $this->logger = $logger ?? new Logger('api.log');
        
        assert($this->club_id > 0, 'Club ID must be positive');
    }
    
    /**
     * Récupérer infos du club
     *
     * @return array|null Données ou null si erreur
     */
    public function getClubInfo(): ?array
    {
        $endpoint = sprintf('/clubs/%d', $this->club_id);
        return $this->makeRequest($endpoint);
    }
    
    /**
     * Récupérer équipes du club
     *
     * @return array|null Données ou null si erreur
     */
    public function getEquipes(): ?array
    {
        $endpoint = sprintf('/clubs/%d/equipes', $this->club_id);
        $data = $this->makeRequest($endpoint);
        
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }
    
    /**
     * Récupérer tous les engagements du club
     *
     * @return array|null Données ou null si erreur
     */
    public function getEngagements(): ?array
    {
        $endpoint = sprintf('/engagements?club.cl_no=%d', $this->club_id);
        $data = $this->makeRequest($endpoint);
        
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }

    /**
     * Récupérer classement d'une compétition/phase/poule
     * 
     * URL format: /api/compets/{cp_no}/phases/{phase_no}/poules/{poule_no}/classement_journees
     *
     * @param int $cp_no Numéro compétition
     * @param int $phase_no Numéro phase
     * @param int $poule_no Numéro poule
     * @return array|null Données classement ou null si erreur
     */
    public function getClassement(int $cp_no, int $phase_no, int $poule_no): ?array
    {
        assert($cp_no > 0, 'Competition number must be positive');
        assert($phase_no >= 0, 'Phase number must be >= 0');
        assert($poule_no >= 0, 'Poule number must be >= 0');
        
        $endpoint = sprintf(
            '/compets/%d/phases/%d/poules/%d/classement_journees',
            $cp_no,
            $phase_no,
            $poule_no
        );
        
        $data = $this->makeRequest($endpoint);
        
        if ($data !== null && isset($data['hydra:member'])) {
            return $data;
        }
        
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }

    /**
     * Récupérer TOUS les classements via engagements
     *
     * @return array Tableau de tous les classements disponibles
     */
    public function getAllClassements(): array
    {
        $all_classements = [];
        $max_iterations = 50;
        $counter = 0;
        
        $engagements = $this->getEngagements();
        
        if ($engagements === null || !isset($engagements['hydra:member'])) {
            $this->logger->error('Failed to fetch engagements for classements');
            return [];
        }
        
        foreach ($engagements['hydra:member'] as $engagement) {
            if ($counter >= $max_iterations) {
                break;
            }
            
            if (!isset($engagement['competition']['cp_no'])) {
                continue;
            }
            
            $cp_no = $engagement['competition']['cp_no'];
            $phase_no = $engagement['phase']['number'] ?? 1;
            $poule_no = $engagement['poule']['stage_number'] ?? 1;
            $competition_type = $engagement['competition']['type'] ?? 'CH';
            
            if ($competition_type === 'CH') {
                $classement = $this->getClassement($cp_no, $phase_no, $poule_no);
                
                if ($classement !== null && isset($classement['hydra:member']) && !empty($classement['hydra:member'])) {
                    foreach ($classement['hydra:member'] as $entry) {
                        $entry['competition'] = $engagement['competition'];
                        $all_classements[] = $entry;
                    }
                }
            }
            
            $counter++;
        }
        
        $this->logger->info('Fetched all classements via engagements', [
            'engagements_processed' => $counter,
            'classements_count' => count($all_classements)
        ]);
        
        return $all_classements;
    }
    
    /**
     * Récupérer matchs d'une compétition/phase/poule spécifique
     *
     * @param int $cp_no Numéro compétition
     * @param int $phase_no Numéro phase
     * @param int $poule_no Numéro poule
     * @return array|null Données ou null si erreur
     */
    public function getMatchsByCompetition(int $cp_no, int $phase_no, int $poule_no): ?array
    {
        assert($cp_no > 0, 'Competition number must be positive');
        assert($phase_no >= 0, 'Phase number must be >= 0');
        assert($poule_no >= 0, 'Poule number must be >= 0');
        
        $endpoint = sprintf(
            '/compets/%d/phases/%d/poules/%d/matchs?clNo=%d',
            $cp_no,
            $phase_no,
            $poule_no,
            $this->club_id
        );
        
        $data = $this->makeRequest($endpoint);
        
        // Normaliser le format de réponse
        return $this->normalizeMatchesResponse($data);
    }
    
    /**
     * Normaliser format réponse matchs (gère Hydra et tableaux directs)
     *
     * @param mixed $data Données API
     * @return array|null Format normalisé avec hydra:member
     */
    private function normalizeMatchesResponse($data): ?array
    {
        if ($data === null) {
            return null;
        }
        
        // Format Hydra standard
        if (isset($data['hydra:member'])) {
            return $data;
        }
        
        // Tableau direct [match1, match2, ...]
        if (is_array($data) && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        // Tableau vide []
        if (is_array($data) && empty($data)) {
            return [
                'hydra:member' => [],
                'hydra:totalItems' => 0
            ];
        }
        
        // Objet avec clés non-Hydra (certaines coupes)
        if (is_array($data) && !isset($data[0]) && !isset($data['hydra:member'])) {
            // Peut contenir un seul match ou des clés spécifiques
            // Essayer de convertir en tableau
            $matches = [];
            foreach ($data as $key => $value) {
                if (is_array($value) && isset($value['ma_no'])) {
                    $matches[] = $value;
                }
            }
            
            if (!empty($matches)) {
                return [
                    'hydra:member' => $matches,
                    'hydra:totalItems' => count($matches)
                ];
            }
        }
        
        // Format non reconnu
        $this->logger->warning('Unknown API response format', [
            'data_type' => gettype($data),
            'has_hydra' => isset($data['hydra:member']),
            'is_array' => is_array($data),
            'keys' => is_array($data) ? array_keys($data) : 'N/A'
        ]);
        
        return null;
    }
    
    /**
     * Récupérer TOUS les matchs via engagements (méthode recommandée)
     *
     * @return array ['calendrier' => [...], 'resultats' => [...]]
     */
    public function getAllMatchs(): array
    {
        $all_calendrier = [];
        $all_resultats = [];
        $max_iterations = 50;
        $counter = 0;
        
        $engagements = $this->getEngagements();
        
        if ($engagements === null || !isset($engagements['hydra:member'])) {
            $this->logger->error('Failed to fetch engagements');
            return ['calendrier' => [], 'resultats' => []];
        }
        
        foreach ($engagements['hydra:member'] as $engagement) {
            if ($counter >= $max_iterations) {
                break;
            }
            
            if (!isset($engagement['competition']['cp_no'])) {
                continue;
            }
            
            $cp_no = $engagement['competition']['cp_no'];
            $phase_no = $engagement['phase']['number'] ?? 1;
            $poule_no = $engagement['poule']['stage_number'] ?? 1;
            $competition_type = $engagement['competition']['type'] ?? 'CH';
            
            $matchs = $this->getMatchsByCompetition($cp_no, $phase_no, $poule_no);
            
            if ($matchs !== null && isset($matchs['hydra:member'])) {
                foreach ($matchs['hydra:member'] as $match) {
                    $has_score = isset($match['home_score']) && $match['home_score'] !== null;
                    
                    if ($has_score) {
                        // Pour les coupes, garder uniquement le dernier résultat
                        if ($competition_type === 'CP') {
                            // Supprimer les anciens résultats de cette coupe
                            $all_resultats = array_filter($all_resultats, function($m) use ($cp_no) {
                                return ($m['competition']['cp_no'] ?? 0) !== $cp_no;
                            });
                        }
                        $all_resultats[] = $match;
                    } else {
                        $all_calendrier[] = $match;
                    }
                }
            }
            
            $counter++;
        }
        
        $this->logger->info('Fetched all matchs via engagements', [
            'engagements_processed' => $counter,
            'calendrier_count' => count($all_calendrier),
            'resultats_count' => count($all_resultats)
        ]);
        
        return [
            'calendrier' => $all_calendrier,
            'resultats' => $all_resultats
        ];
    }
    
    /**
     * DEPRECATED - Ancienne méthode calendrier (gardée pour compatibilité)
     *
     * @param int $page Numéro de page
     * @return array|null Données ou null si erreur
     */
    public function getCalendrier(int $page = 1): ?array
    {
        assert($page > 0, 'Page must be positive');
        
        $endpoint = sprintf('/clubs/%d/calendrier?page=%d', $this->club_id, $page);
        $data = $this->makeRequest($endpoint);
        
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }
    
    /**
     * DEPRECATED - Ancienne méthode résultats (gardée pour compatibilité)
     *
     * @param int $page Numéro de page
     * @return array|null Données ou null si erreur
     */
    public function getResultats(int $page = 1): ?array
    {
        assert($page > 0, 'Page must be positive');
        
        $endpoint = sprintf('/clubs/%d/resultat?page=%d', $this->club_id, $page);
        $data = $this->makeRequest($endpoint);
        
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }
    
    /**
     * Effectuer requête HTTP avec cURL
     *
     * @param string $endpoint Endpoint API
     * @return array|null Données décodées ou null
     */
    private function makeRequest(string $endpoint): ?array
    {
        assert(!empty($endpoint), 'Endpoint cannot be empty');
        
        $url = $this->base_url . $endpoint;
        $start_time = microtime(true);
        
        $max_retries = 3;
        $retry_count = 0;
        $response = false;
        
        while ($retry_count < $max_retries && $response === false) {
            $ch = curl_init($url);
            
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_TIMEOUT => $this->timeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Accept-Language: fr-FR,fr;q=0.9',
                    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
                ]
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($response === false || $http_code < 200 || $http_code >= 300) {
                $retry_count++;
                if ($retry_count < $max_retries) {
                    usleep(500000);
                }
                $response = false;
            }
        }
        
        $execution_time = round((microtime(true) - $start_time) * 1000);
        
        if ($response === false) {
            $this->logger->error('API request failed', [
                'url' => $url,
                'retries' => $retry_count,
                'http_code' => $http_code ?? 0,
                'error' => $curl_error ?? 'Unknown error',
                'execution_time_ms' => $execution_time
            ]);
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->error('JSON decode failed', [
                'url' => $url,
                'error' => json_last_error_msg(),
                'response_preview' => substr($response, 0, 200)
            ]);
            return null;
        }
        
        $this->logger->info('API request success', [
            'endpoint' => $endpoint,
            'execution_time_ms' => $execution_time
        ]);
        
        return $data;
    }
    
    /**
     * Vérifier disponibilité API
     *
     * @return bool API disponible
     */
    public function isApiAvailable(): bool
    {
        $result = $this->getClubInfo();
        return $result !== null;
    }
}