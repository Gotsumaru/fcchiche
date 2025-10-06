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
        assert(!empty($filename), 'Filename cannot be empty');
        assert($max_size > 0, 'Max size must be positive');
        
        $this->log_file = LOG_PATH . '/' . $filename;
        $this->max_size = $max_size;
        $this->ensureLogDirectory();
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
        $context_str = !empty($context) ? ' ' . json_encode($context) : '';
        $log_entry = sprintf(
            "[%s] [%s] %s%s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $context_str
        );
        
        $result = file_put_contents($this->log_file, $log_entry, FILE_APPEND | LOCK_EX);
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
        if ($size === false || $size < $this->max_size) {
            return;
        }
        
        $backup = $this->log_file . '.' . date('Y-m-d_His') . '.bak';
        rename($this->log_file, $backup);
        
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
        
        if ($backups === false) {
            return;
        }
        
        $max_backups = 5;
        $count = count($backups);
        
        if ($count <= $max_backups) {
            return;
        }
        
        usort($backups, function($a, $b) {
            return filemtime($a) <=> filemtime($b);
        });
        
        $to_delete = array_slice($backups, 0, $count - $max_backups);
        foreach ($to_delete as $file) {
            unlink($file);
        }
    }
}