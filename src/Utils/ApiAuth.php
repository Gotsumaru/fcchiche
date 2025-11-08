<?php
declare(strict_types=1);

require_once __DIR__ . '/ApiResponse.php';

/**
 * Gestion authentification API
 */
class ApiAuth
{
    private const SESSION_KEY = 'api_admin_authenticated';
    private const TOKEN_HEADER = 'X-API-Token';
    
    /**
     * Vérifier si l'utilisateur est authentifié
     *
     * @return bool True si authentifié
     */
    public static function isAuthenticated(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY] === true;
    }
    
    /**
     * Authentifier avec login/password
     *
     * @param string $username Nom d'utilisateur
     * @param string $password Mot de passe
     * @return bool True si authentification réussie
     */
    public static function login(string $username, string $password): bool
    {
        assert(!empty($username), 'Username cannot be empty');
        assert(!empty($password), 'Password cannot be empty');
        
        if ($username === 'Administrateur' && password_verify($password, self::getPasswordHash())) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            $_SESSION[self::SESSION_KEY] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = time();
            
            session_regenerate_id(true);
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Déconnexion
     */
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Vérifier le token API (alternative à session)
     *
     * @return bool True si token valide
     */
    public static function verifyToken(): bool
    {
        $token = $_SERVER['HTTP_' . str_replace('-', '_', strtoupper(self::TOKEN_HEADER))] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        return hash_equals(self::getApiToken(), $token);
    }
    
    /**
     * Exiger authentification pour requête
     */
    public static function requireAuth(): void
    {
        if (!self::isAuthenticated() && !self::verifyToken()) {
            ApiResponse::error('Authentication required', 401);
        }
    }
    
    /**
     * Protéger les méthodes POST/PUT/DELETE
     *
     * @param string $method Méthode HTTP
     */
    public static function protectWrite(string $method): void
    {
        assert(!empty($method), 'HTTP method cannot be empty');
        
        $method = strtoupper($method);
        
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            self::requireAuth();
        }
    }
    
    /**
     * Vérifier token CSRF
     *
     * @return bool True si valide
     */
    public static function verifyCsrf(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        
        if (empty($token)) {
            return false;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Générer token CSRF
     *
     * @return string Token CSRF
     */
    public static function generateCsrfToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Récupérer hash du mot de passe admin
     * 
     * @return string Hash du mot de passe
     */
    private static function getPasswordHash(): string
    {
        // TODO: Stocker en BDD ou dans config sécurisée
        // Pour l'instant : mot de passe par défaut "FCChiche2025!"
        return '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLM';
    }
    
    /**
     * Récupérer token API
     *
     * @return string Token API
     */
    private static function getApiToken(): string
    {
        // TODO: Stocker dans config sécurisée ou variable d'environnement
        return 'fcchiche_api_token_2025_secure_change_me';
    }
}