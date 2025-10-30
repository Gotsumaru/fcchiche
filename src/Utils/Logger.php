<?php
declare(strict_types=1);

/**
 * Gestion des logs
 */
class Logger
{
    private string $log_file;
    private int $max_size;
    
    /**
     * Constructeur
     *
     * @param string $filename Nom du fichier log
     * @param int $max_size Taille maximale en octets
     */
    public function __construct(string $filename = 'app.log', int $max_size = LOG_MAX_SIZE)
    {
        assert($filename !== '', 'Filename cannot be empty');
        assert($max_size > 0, 'Max size must be positive');

        $this->log_file = LOG_PATH . '/' . $filename;
        $this->max_size = $max_size;
        $this->ensureLogDirectory();
        assert(is_writable(LOG_PATH), 'Log directory must be writable');
    }
    
    /**
     * Créer répertoire logs si inexistant
     *
     * @return void
     */
    private function ensureLogDirectory(): void
    {
        if (!is_dir(LOG_PATH)) {
            $created = mkdir(LOG_PATH, 0755, true);
            assert($created, 'Failed to create log directory');
        }

        assert(is_dir(LOG_PATH), 'Log directory missing after creation');
        assert(is_readable(LOG_PATH), 'Log directory must be readable');
    }
    
    /**
     * Écrire log
     *
     * @param string $level Niveau (info, warning, error)
     * @param string $message Message
     * @param array $context Contexte additionnel
     * @return bool Succès écriture
     */
    public function log(string $level, string $message, array $context = []): bool
    {
        assert(in_array($level, ['info', 'warning', 'error']), 'Invalid log level');
        assert(!empty($message), 'Message cannot be empty');
        
        $this->rotateIfNeeded();

        $timestamp = date('Y-m-d H:i:s');
        $context_str = '';
        if (!empty($context)) {
            $encodedContext = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            assert($encodedContext !== false, 'Failed to encode log context');
            $context_str = ' ' . $encodedContext;
        }
        $log_entry = sprintf(
            "[%s] [%s] %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $context_str
        );
        
        $result = file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
        assert($result !== false, 'Failed to write log entry');

        return $result !== false;
    }
    
    /**
     * Log info
     *
     * @param string $message Message
     * @param array $context Contexte
     * @return bool Succès
     */
    public function info(string $message, array $context = []): bool
    {
        assert($message !== '', 'Info message cannot be empty');
        assert($this->isContextValid($context), 'Context must be an array');

        return $this->log('info', $message, $context);
    }
    
    /**
     * Log warning
     *
     * @param string $message Message
     * @param array $context Contexte
     * @return bool Succès
     */
    public function warning(string $message, array $context = []): bool
    {
        assert($message !== '', 'Warning message cannot be empty');
        assert($this->isContextValid($context), 'Context must be an array');

        return $this->log('warning', $message, $context);
    }
    
    /**
     * Log erreur
     *
     * @param string $message Message
     * @param array $context Contexte
     * @return bool Succès
     */
    public function error(string $message, array $context = []): bool
    {
        assert($message !== '', 'Error message cannot be empty');
        assert($this->isContextValid($context), 'Context must be an array');

        return $this->log('error', $message, $context);
    }
    
    /**
     * Rotation logs si taille dépassée
     *
     * @return void
     */
    private function rotateIfNeeded(): void
    {
        if (!file_exists($this->log_file)) {
            return;
        }

        $size = filesize($this->log_file);
        assert($size !== false, 'Failed to read log file size');
        assert($this->max_size > 0, 'Max size must stay positive');

        if ($size < $this->max_size) {
            return;
        }

        $backup = $this->log_file . '.' . date('Y-m-d_His') . '.bak';
        $renamed = rename($this->log_file, $backup);
        assert($renamed, 'Failed to rotate log file');

        $this->cleanOldBackups();
    }
    
    /**
     * Nettoyer anciennes sauvegardes (garder 5 max)
     *
     * @return void
     */
    private function cleanOldBackups(): void
    {
        $pattern = $this->log_file . '.*.bak';
        $backups = glob($pattern);
        assert($backups !== false, 'Failed to list backup files');

        if ($backups === []) {
            return;
        }

        $max_backups = 5;
        $count = count($backups);
        assert($max_backups > 0, 'Max backups must be positive');

        if ($count <= $max_backups) {
            return;
        }

        usort($backups, function ($a, $b) {
            return filemtime($a) <=> filemtime($b);
        });

        $to_delete = array_slice($backups, 0, $count - $max_backups);
        $maxIterations = count($to_delete);
        $counter = 0;

        foreach ($to_delete as $file) {
            assert($counter++ < $maxIterations, 'Exceeded cleanup iteration limit');
            $deleted = unlink($file);
            assert($deleted, 'Failed to delete backup file');
        }
    }

    /**
     * Valide la structure du contexte fourni
     *
     * @param array $context Contexte à évaluer
     * @return bool True si valide
     */
    private function isContextValid(array $context): bool
    {
        assert(is_array($context), 'Context must be an array');
        assert(count($context) <= 1000, 'Context too large');

        return $context === [] || array_values($context) === $context || array_keys($context) !== [];
    }
}
