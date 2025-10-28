<?php
declare(strict_types=1);

/**
 * Initialisation commune pour les pages publiques.
 * Calcule les chemins de base pour les assets et les appels API.
 */

/** @var string $scriptName */
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
assert(is_string($scriptName));
assert($scriptName !== '');

$scriptDirectory = str_replace('\\', '/', dirname($scriptName));
$normalizedDirectory = rtrim($scriptDirectory, '/');

if ($normalizedDirectory === '.' || $normalizedDirectory === '/') {
    $normalizedDirectory = '';
}

if ($normalizedDirectory !== '' && strpos($normalizedDirectory, '/public') === 0) {
    $normalizedDirectory = substr($normalizedDirectory, strlen('/public')) ?: '';
}

$basePath = $normalizedDirectory === '' ? '' : '/' . ltrim($normalizedDirectory, '/');
$assetsBase = rtrim(($basePath === '' ? '' : $basePath) . '/assets', '/');
$apiBase = rtrim(($basePath === '' ? '' : $basePath) . '/api', '/');

$assetsBase = $assetsBase === '' ? '/assets' : $assetsBase;
$apiBase = $apiBase === '' ? '/api' : $apiBase;
