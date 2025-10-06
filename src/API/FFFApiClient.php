<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../Utils/Logger.php';

/**
 * Client API FFF - Version simplifiée sans cookies
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
        
        // Normaliser format : si tableau direct, créer structure Hydra
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }
    
    /**
     * Récupérer calendrier
     *
     * @param int $page Numéro de page
     * @return array|null Données ou null si erreur
     */
    public function getCalendrier(int $page = 1): ?array
    {
        assert($page > 0, 'Page must be positive');
        
        $endpoint = sprintf('/clubs/%d/calendrier?page=%d', $this->club_id, $page);
        $data = $this->makeRequest($endpoint);
        
        // Normaliser format
        if ($data !== null && isset($data[0])) {
            return [
                'hydra:member' => $data,
                'hydra:totalItems' => count($data)
            ];
        }
        
        return $data;
    }
    
    /**
     * Récupérer résultats
     *
     * @param int $page Numéro de page
     * @return array|null Données ou null si erreur
     */
    public function getResultats(int $page = 1): ?array
    {
        assert($page > 0, 'Page must be positive');
        
        $endpoint = sprintf('/clubs/%d/resultat?page=%d', $this->club_id, $page);
        $data = $this->makeRequest($endpoint);
        
        // Normaliser format
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
                    usleep(500000); // 0.5s
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