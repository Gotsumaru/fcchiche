<?php
declare(strict_types=1);

/**
 * Initialisation commune pour les pages publiques.
 * Calcule les chemins de base pour les assets et les appels API.
 */

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
$trimmedDirectory = rtrim($scriptDirectory, '/');

if ($trimmedDirectory === '.' || $trimmedDirectory === '/') {
    $trimmedDirectory = '';
}

$basePath = $trimmedDirectory === '' ? '' : '/' . ltrim($trimmedDirectory, '/');
$assetsBase = rtrim(($basePath === '' ? '' : $basePath) . '/assets', '/');
$apiBase = rtrim(($basePath === '' ? '' : $basePath) . '/api', '/');

$assetsBase = $assetsBase === '' ? '/assets' : $assetsBase;
$apiBase = $apiBase === '' ? '/api' : $apiBase;
