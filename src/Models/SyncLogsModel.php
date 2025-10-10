<?php
declare(strict_types=1);

/**
 * Modèle Logs de Synchronisation
 */
class SyncLogsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_sync_logs';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les logs de synchronisation
     *
     * @param int $limit Nombre de logs max
     * @return array Liste des logs
     * @throws PDOException Si erreur BDD
     */
    public function getAllLogs(int $limit = 100): array
    {
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un log par son ID
     *
     * @param int $id ID du log
     * @return array|null Données du log
     */
    public function getLogById(int $id): ?array
    {
        assert($id > 0, 'Log ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère les logs par endpoint
     *
     * @param string $endpoint Nom de l'endpoint
     * @param int $limit Nombre de logs max
     * @return array Liste des logs de l'endpoint
     */
    public function getLogsByEndpoint(string $endpoint, int $limit = 50): array
    {
        assert(!empty($endpoint), 'Endpoint cannot be empty');
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE endpoint = :endpoint 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les logs par statut
     *
     * @param string $status Statut (success, error, warning)
     * @param int $limit Nombre de logs max
     * @return array Liste des logs avec ce statut
     */
    public function getLogsByStatus(string $status, int $limit = 50): array
    {
        assert(in_array($status, ['success', 'error', 'warning']), 'Invalid status');
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE status = :status 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les erreurs uniquement
     *
     * @param int $limit Nombre de logs max
     * @return array Liste des erreurs
     */
    public function getErrors(int $limit = 50): array
    {
        return $this->getLogsByStatus('error', $limit);
    }

    /**
     * Récupère les succès uniquement
     *
     * @param int $limit Nombre de logs max
     * @return array Liste des succès
     */
    public function getSuccesses(int $limit = 50): array
    {
        return $this->getLogsByStatus('success', $limit);
    }

    /**
     * Récupère les logs par période
     *
     * @param string $dateStart Date début (Y-m-d)
     * @param string $dateEnd Date fin (Y-m-d)
     * @param int $limit Nombre de logs max
     * @return array Liste des logs dans la période
     */
    public function getLogsByDateRange(string $dateStart, string $dateEnd, int $limit = 100): array
    {
        assert(!empty($dateStart), 'Start date cannot be empty');
        assert(!empty($dateEnd), 'End date cannot be empty');
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE DATE(created_at) BETWEEN :date_start AND :date_end 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':date_start', $dateStart, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $dateEnd, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les logs du jour
     *
     * @return array Liste des logs d'aujourd'hui
     */
    public function getTodayLogs(): array
    {
        $today = date('Y-m-d');
        return $this->getLogsByDateRange($today, $today, 500);
    }

    /**
     * Récupère les derniers logs par endpoint et statut
     *
     * @param string $endpoint Nom de l'endpoint
     * @param string $status Statut
     * @param int $limit Nombre de logs max
     * @return array Liste des logs
     */
    public function getLogsByEndpointAndStatus(string $endpoint, string $status, int $limit = 20): array
    {
        assert(!empty($endpoint), 'Endpoint cannot be empty');
        assert(in_array($status, ['success', 'error', 'warning']), 'Invalid status');
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE endpoint = :endpoint 
                AND status = :status 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère des statistiques de synchronisation
     *
     * @return array Statistiques globales
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_logs,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as total_success,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as total_errors,
                    SUM(CASE WHEN status = 'warning' THEN 1 ELSE 0 END) as total_warnings,
                    SUM(records_processed) as total_records,
                    AVG(execution_time_ms) as avg_execution_time,
                    MAX(execution_time_ms) as max_execution_time,
                    MIN(execution_time_ms) as min_execution_time
                FROM " . self::TABLE;
        
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result !== false ? $result : [];
    }

    /**
     * Récupère des statistiques par endpoint
     *
     * @param string $endpoint Nom de l'endpoint
     * @return array Statistiques de l'endpoint
     */
    public function getStatsByEndpoint(string $endpoint): array
    {
        assert(!empty($endpoint), 'Endpoint cannot be empty');
        
        $sql = "SELECT 
                    endpoint,
                    COUNT(*) as total_calls,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count,
                    SUM(CASE WHEN status = 'warning' THEN 1 ELSE 0 END) as warning_count,
                    SUM(records_processed) as total_records,
                    AVG(execution_time_ms) as avg_execution_time,
                    MAX(created_at) as last_call
                FROM " . self::TABLE . " 
                WHERE endpoint = :endpoint
                GROUP BY endpoint";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['endpoint' => $endpoint]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : [];
    }

    /**
     * Récupère les statistiques de tous les endpoints
     *
     * @return array Statistiques par endpoint
     */
    public function getAllEndpointsStats(): array
    {
        $sql = "SELECT 
                    endpoint,
                    COUNT(*) as total_calls,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success_count,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count,
                    SUM(records_processed) as total_records,
                    AVG(execution_time_ms) as avg_execution_time,
                    MAX(created_at) as last_call
                FROM " . self::TABLE . " 
                GROUP BY endpoint
                ORDER BY last_call DESC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le dernier log de synchronisation
     *
     * @param string|null $endpoint Endpoint spécifique (null = tous)
     * @return array|null Dernier log
     */
    public function getLastLog(?string $endpoint = null): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE;
        
        if ($endpoint !== null) {
            assert(!empty($endpoint), 'Endpoint cannot be empty');
            $sql .= " WHERE endpoint = :endpoint";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($endpoint !== null) {
            $stmt->execute(['endpoint' => $endpoint]);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Compte le nombre total de logs
     *
     * @param string|null $status Filtrer par statut (null = tous)
     * @return int Nombre de logs
     */
    public function countLogs(?string $status = null): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        
        if ($status !== null) {
            assert(in_array($status, ['success', 'error', 'warning']), 'Invalid status');
            $sql .= " WHERE status = :status";
        }
        
        $stmt = $this->pdo->prepare($sql);
        
        if ($status !== null) {
            $stmt->execute(['status' => $status]);
        } else {
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }

    /**
     * Recherche dans les logs par message
     *
     * @param string $search Terme de recherche
     * @param int $limit Nombre de logs max
     * @return array Liste des logs correspondants
     */
    public function searchLogs(string $search, int $limit = 50): array
    {
        assert(!empty($search), 'Search term cannot be empty');
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        
        $search = '%' . $search . '%';
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE message LIKE :search 
                OR endpoint LIKE :search
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':search', $search, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les logs les plus lents (temps d'exécution)
     *
     * @param int $limit Nombre de logs
     * @return array Liste des logs les plus lents
     */
    public function getSlowestLogs(int $limit = 20): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE execution_time_ms IS NOT NULL
                ORDER BY execution_time_ms DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les logs les plus rapides (temps d'exécution)
     *
     * @param int $limit Nombre de logs
     * @return array Liste des logs les plus rapides
     */
    public function getFastestLogs(int $limit = 20): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE execution_time_ms IS NOT NULL
                AND execution_time_ms > 0
                ORDER BY execution_time_ms ASC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}