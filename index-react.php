<?php
/**
 * React Frontend Router - Sert le HTML du build React
 * Redirige toutes les requêtes non-API vers index.html du React
 */

// Vérifier si c'est une requête API
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0) {
    // Les requêtes API vont vers les fichiers PHP normaux
    return false; // Laisse PHP servir les fichiers normalement
}

// Vérifier si c'est un fichier statique existant (CSS, JS, images, etc)
$distPath = __DIR__ . '/dist';
$requestUri = $_SERVER['REQUEST_URI'];
$filePath = $distPath . $requestUri;

if (is_file($filePath) && file_exists($filePath)) {
    // Servir le fichier statique
    return false;
}

// Sinon, servir le HTML du React (pour que React Router fonctionne)
include $distPath . '/index.html';
