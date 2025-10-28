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

$publicSegment = '/public';
if ($trimmedDirectory === $publicSegment) {
    $trimmedDirectory = '';
} elseif ($trimmedDirectory !== '' && substr($trimmedDirectory, -strlen($publicSegment)) === $publicSegment) {
    $trimmedDirectory = substr($trimmedDirectory, 0, -strlen($publicSegment));
    $trimmedDirectory = rtrim($trimmedDirectory, '/');
}

$basePath = $trimmedDirectory === '' ? '' : '/' . ltrim($trimmedDirectory, '/');

$assetsBase = ($basePath === '' ? '' : $basePath) . '/assets';
$apiBase = ($basePath === '' ? '' : $basePath) . '/api';
