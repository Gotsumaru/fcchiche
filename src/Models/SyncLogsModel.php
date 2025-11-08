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
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute logs query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
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
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid log result');

        if ($result !== false) {
            assert(isset($result['id']), 'Log id missing');
            assert(isset($result['endpoint']), 'Log endpoint missing');
            return $result;
        }

        return null;
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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute logs by endpoint');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch endpoint logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute logs by status');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch status logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
    }

    /**
     * Récupère les erreurs uniquement
     *
     * @param int $limit Nombre de logs max
     * @return array Liste des erreurs
     */
    public function getErrors(int $limit = 50): array
    {
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        assert($limit === (int)$limit, 'Limit must be integer');

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
        assert($limit > 0 && $limit <= 1000, 'Invalid limit');
        assert($limit === (int)$limit, 'Limit must be integer');

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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':date_start', $dateStart, PDO::PARAM_STR);
        $stmt->bindValue(':date_end', $dateEnd, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute date range query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch date range logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
    }

    /**
     * Récupère les logs du jour
     *
     * @return array Liste des logs d'aujourd'hui
     */
    public function getTodayLogs(): array
    {
        $today = date('Y-m-d');
        assert(preg_match('/^\d{4}-\d{2}-\d{2}$/', $today) === 1, 'Invalid today format');

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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute endpoint+status query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch endpoint status logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
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
        
        $stmt = $this->prepareAndExecute($sql, []);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid stats result');

        if ($result === false) {
            return [];
        }

        assert(isset($result['total_logs']), 'total_logs missing');
        assert(isset($result['total_success']), 'total_success missing');

        return $result;
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
        
        $stmt = $this->prepareAndExecute($sql, ['endpoint' => $endpoint]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid endpoint stats result');

        if ($result === false) {
            return [];
        }

        assert(isset($result['total_calls']), 'total_calls missing');
        assert(isset($result['success_count']), 'success_count missing');

        return $result;
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
        
        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch endpoint stats');
        assert(count($results) <= 500, 'Too many endpoint stats');

        return $results;
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
        
        $stmt = $this->prepareStatement($sql);

        $params = [];
        if ($endpoint !== null) {
            $params['endpoint'] = $endpoint;
        }

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute last log query');

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid last log result');

        if ($result !== false) {
            assert(isset($result['id']), 'Last log missing id');
            return $result;
        }

        return null;
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
        
        $stmt = $this->prepareStatement($sql);

        $params = [];
        if ($status !== null) {
            $params['status'] = $status;
        }

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute count logs query');

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
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
        
        $searchTerm = '%' . $search . '%';
        assert(strlen($searchTerm) <= 500, 'Search term too long');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE message LIKE :search
                OR endpoint LIKE :search
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute logs search');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch logs search results');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute slowest logs query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch slowest logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
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

        $stmt = $this->prepareStatement($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $executed = $stmt->execute();
        assert($executed, 'Failed to execute fastest logs query');

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch fastest logs');
        assert(count($results) <= $limit, 'Fetched more logs than limit');

        return $results;
    }

    /**
     * Prépare et exécute une requête préparée
     *
     * @param string $sql Requête SQL
     * @param array $params Paramètres à lier
     * @return PDOStatement Statement exécuté
     */
    private function prepareAndExecute(string $sql, array $params): PDOStatement
    {
        assert($sql !== '', 'SQL query cannot be empty');
        assert(count($params) <= 10, 'Too many parameters provided');

        $stmt = $this->prepareStatement($sql);

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }

    /**
     * Prépare une requête PDO avec vérifications
     *
     * @param string $sql Requête SQL
     * @return PDOStatement Statement préparé
     */
    private function prepareStatement(string $sql): PDOStatement
    {
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        return $stmt;
    }
}