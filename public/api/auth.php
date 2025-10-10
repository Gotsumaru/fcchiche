<?php
declare(strict_types=1);

/**
 * API Authentication - Login/Logout
 * 
 * POST /api/auth?action=login
 * Body: {"username": "Administrateur", "password": "..."}
 * 
 * POST /api/auth?action=logout
 * 
 * GET /api/auth?action=status - Vérifier statut authentification
 * 
 * GET /api/auth?action=csrf - Obtenir token CSRF
 */

$basePath = dirname(__DIR__, 2);
require_once $basePath . '/config/bootstrap.php';
require_once $basePath . '/src/Utils/ApiResponse.php';
require_once $basePath . '/src/Utils/ApiAuth.php';

ApiResponse::setCorsHeaders();

$method = $_SERVER['REQUEST_METHOD'];

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
            
        case 'logout':
            handleLogout();
            break;
            
        case 'status':
            handleStatus();
            break;
            
        case 'csrf':
            handleCsrf();
            break;
            
        default:
            ApiResponse::error('Invalid action. Use: login, logout, status, csrf', 400);
    }
    
} catch (Exception $e) {
    ApiResponse::error(
        DEBUG_MODE ? $e->getMessage() : 'Internal server error',
        500,
        DEBUG_MODE ? ['trace' => $e->getTraceAsString()] : []
    );
}

/**
 * Gérer connexion
 */
function handleLogin(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ApiResponse::error('Method not allowed. Use POST', 405);
    }
    
    $data = ApiResponse::getBody();
    
    if ($data === null || !isset($data['username']) || !isset($data['password'])) {
        ApiResponse::error('Missing username or password', 400);
    }
    
    $username = $data['username'];
    $password = $data['password'];
    
    if (ApiAuth::login($username, $password)) {
        ApiResponse::success([
            'authenticated' => true,
            'username' => $username,
            'csrf_token' => ApiAuth::generateCsrfToken()
        ], ['message' => 'Login successful']);
    } else {
        ApiResponse::error('Invalid credentials', 401);
    }
}

/**
 * Gérer déconnexion
 */
function handleLogout(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        ApiResponse::error('Method not allowed. Use POST', 405);
    }
    
    ApiAuth::logout();
    ApiResponse::success(['authenticated' => false], ['message' => 'Logout successful']);
}

/**
 * Vérifier statut authentification
 */
function handleStatus(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        ApiResponse::error('Method not allowed. Use GET', 405);
    }
    
    $authenticated = ApiAuth::isAuthenticated();
    
    $data = [
        'authenticated' => $authenticated
    ];
    
    if ($authenticated) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $data['username'] = $_SESSION['username'] ?? null;
        $data['login_time'] = $_SESSION['login_time'] ?? null;
    }
    
    ApiResponse::success($data);
}

/**
 * Obtenir token CSRF
 */
function handleCsrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        ApiResponse::error('Method not allowed. Use GET', 405);
    }
    
    if (!ApiAuth::isAuthenticated()) {
        ApiResponse::error('Authentication required', 401);
    }
    
    $token = ApiAuth::generateCsrfToken();
    ApiResponse::success(['csrf_token' => $token]);
}