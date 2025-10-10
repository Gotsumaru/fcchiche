<?php
declare(strict_types=1);

/**
 * Classe utilitaire pour réponses API standardisées
 */
class ApiResponse
{
    /**
     * Envoyer une réponse de succès
     *
     * @param mixed $data Données à retourner
     * @param array $meta Métadonnées optionnelles
     * @param int $http_code Code HTTP
     */
    public static function success($data, array $meta = [], int $http_code = 200): void
    {
        assert($http_code >= 200 && $http_code < 300, 'HTTP code must be 2xx for success');
        
        http_response_code($http_code);
        
        $response = [
            'success' => true,
            'data' => $data,
            'meta' => array_merge([
                'timestamp' => date('c'),
                'count' => is_array($data) ? count($data) : 1
            ], $meta)
        ];
        
        self::send($response);
    }
    
    /**
     * Envoyer une réponse d'erreur
     *
     * @param string $message Message d'erreur
     * @param int $http_code Code HTTP
     * @param array $details Détails additionnels
     */
    public static function error(string $message, int $http_code = 400, array $details = []): void
    {
        assert($http_code >= 400 && $http_code < 600, 'HTTP code must be 4xx or 5xx for error');
        assert(!empty($message), 'Error message cannot be empty');
        
        http_response_code($http_code);
        
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $http_code
            ],
            'meta' => [
                'timestamp' => date('c')
            ]
        ];
        
        if (!empty($details) && DEBUG_MODE) {
            $response['error']['details'] = $details;
        }
        
        self::send($response);
    }
    
    /**
     * Envoyer une réponse JSON
     *
     * @param array $response Données à encoder
     */
    private static function send(array $response): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Valider et récupérer paramètres GET
     *
     * @param array $allowed Paramètres autorisés avec valeurs par défaut
     * @return array Paramètres validés
     */
    public static function getParams(array $allowed): array
    {
        assert(!empty($allowed), 'Allowed parameters cannot be empty');
        
        $params = [];
        $counter = 0;
        $max_params = 20;
        
        foreach ($allowed as $key => $default) {
            assert($counter++ < $max_params, 'Too many parameters');
            $params[$key] = $_GET[$key] ?? $default;
        }
        
        return $params;
    }
    
    /**
     * Valider et récupérer corps POST/PUT
     *
     * @return array|null Données ou null si erreur
     */
    public static function getBody(): ?array
    {
        $input = file_get_contents('php://input');
        
        if (empty($input)) {
            return null;
        }
        
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        
        return $data;
    }
    
    /**
     * Définir les en-têtes CORS
     *
     * @param string $allowed_origin Origine autorisée
     */
    public static function setCorsHeaders(string $allowed_origin = 'https://fcchiche.fr'): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        if (strpos($origin, 'fcchiche.fr') !== false || ENV === 'development') {
            header('Access-Control-Allow-Origin: ' . ($origin ?: $allowed_origin));
        } else {
            header('Access-Control-Allow-Origin: ' . $allowed_origin);
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Max-Age: 3600');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }
}