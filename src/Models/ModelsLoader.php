<?php
declare(strict_types=1);

/**
 * Chargeur de Modèles - Autoloader pour tous les modèles
 * 
 * Usage:
 * require_once 'config/database.php';
 * require_once 'src/Models/ModelsLoader.php';
 * 
 * $models = ModelsLoader::loadAll($pdo);
 * $club = $models['club']->getClub();
 */
class ModelsLoader
{
    private static array $modelsPath = [
        'club' => 'ClubModel.php',
        'terrains' => 'TerrainsModel.php',
        'membres' => 'MembresModel.php',
        'competitions' => 'CompetitionsModel.php',
        'equipes' => 'EquipesModel.php',
        'matchs' => 'MatchsModel.php',
        'clubs_cache' => 'ClubsCacheModel.php',
        'classements' => 'ClassementsModel.php',
        'engagements' => 'EngagementsModel.php',
        'config' => 'ConfigModel.php',
        'sync_logs' => 'SyncLogsModel.php'
    ];

    /**
     * Charge tous les modèles
     *
     * @param PDO $pdo Connexion PDO
     * @return array Tableau associatif [nom => instance]
     * @throws RuntimeException Si un modèle ne peut être chargé
     */
    public static function loadAll(PDO $pdo): array
    {
        assert($pdo instanceof PDO, 'Invalid PDO instance');
        
        $models = [];
        $counter = 0;
        $maxModels = 20;
        
        foreach (self::$modelsPath as $key => $filename) {
            assert($counter++ < $maxModels, 'Too many models');
            
            $filepath = __DIR__ . '/' . $filename;
            
            if (!file_exists($filepath)) {
                throw new RuntimeException("Model file not found: $filepath");
            }
            
            require_once $filepath;
            
            $className = str_replace('.php', '', $filename);
            
            if (!class_exists($className)) {
                throw new RuntimeException("Model class not found: $className");
            }
            
            $models[$key] = new $className($pdo);
        }
        
        return $models;
    }

    /**
     * Charge un modèle spécifique
     *
     * @param string $modelName Nom du modèle
     * @param PDO $pdo Connexion PDO
     * @return object Instance du modèle
     * @throws RuntimeException Si le modèle n'existe pas
     */
    public static function load(string $modelName, PDO $pdo): object
    {
        assert(!empty($modelName), 'Model name cannot be empty');
        assert($pdo instanceof PDO, 'Invalid PDO instance');
        
        if (!isset(self::$modelsPath[$modelName])) {
            throw new RuntimeException("Unknown model: $modelName");
        }
        
        $filename = self::$modelsPath[$modelName];
        $filepath = __DIR__ . '/' . $filename;
        
        if (!file_exists($filepath)) {
            throw new RuntimeException("Model file not found: $filepath");
        }
        
        require_once $filepath;
        
        $className = str_replace('.php', '', $filename);
        
        if (!class_exists($className)) {
            throw new RuntimeException("Model class not found: $className");
        }
        
        return new $className($pdo);
    }

    /**
     * Charge plusieurs modèles spécifiques
     *
     * @param array $modelNames Liste des noms de modèles
     * @param PDO $pdo Connexion PDO
     * @return array Tableau associatif [nom => instance]
     */
    public static function loadMultiple(array $modelNames, PDO $pdo): array
    {
        assert(!empty($modelNames), 'Model names array cannot be empty');
        assert(count($modelNames) <= 20, 'Too many models requested');
        assert($pdo instanceof PDO, 'Invalid PDO instance');
        
        $models = [];
        $counter = 0;
        
        foreach ($modelNames as $name) {
            assert($counter++ < count($modelNames), 'Unexpected iteration count');
            $models[$name] = self::load($name, $pdo);
        }
        
        return $models;
    }

    /**
     * Liste tous les modèles disponibles
     *
     * @return array Liste des noms de modèles
     */
    public static function getAvailableModels(): array
    {
        return array_keys(self::$modelsPath);
    }

    /**
     * Vérifie si un modèle existe
     *
     * @param string $modelName Nom du modèle
     * @return bool True si le modèle existe
     */
    public static function exists(string $modelName): bool
    {
        return isset(self::$modelsPath[$modelName]);
    }
}