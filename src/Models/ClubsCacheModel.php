<?php
declare(strict_types=1);

/**
 * Modèle Cache Clubs Adverses (logos et infos)
 */
class ClubsCacheModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_clubs_cache';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les clubs en cache
     *
     * @return array Liste des clubs adverses
     * @throws PDOException Si erreur BDD
     */
    public function getAllClubs(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un club par son cl_no
     *
     * @param int $clNo Numéro club API
     * @return array|null Données du club
     */
    public function getClubByClNo(int $clNo): ?array
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        assert($clNo !== API_FFF_CLUB_ID, 'Cannot fetch FC Chiche from cache');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => $clNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère un club par son ID interne
     *
     * @param int $id ID du club en cache
     * @return array|null Données du club
     */
    public function getClubById(int $id): ?array
    {
        assert($id > 0, 'Club ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère le logo d'un club adverse
     *
     * @param int $clNo Numéro club API
     * @return string|null URL du logo
     */
    public function getClubLogo(int $clNo): ?string
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT logo_url FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => $clNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result['logo_url'] : null;
    }

    /**
     * Recherche des clubs par nom
     *
     * @param string $search Terme de recherche
     * @return array Liste des clubs correspondants
     */
    public function searchClubs(string $search): array
    {
        assert(!empty($search), 'Search term cannot be empty');
        
        $search = '%' . $search . '%';
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE name LIKE :search 
                OR short_name LIKE :search
                ORDER BY name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['search' => $search]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un club existe en cache
     *
     * @param int $clNo Numéro club API
     * @return bool True si le club existe en cache
     */
    public function exists(int $clNo): bool
    {
        assert($clNo > 0, 'Club cl_no must be positive');
        
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE cl_no = :cl_no";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => $clNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false && (int)$result['count'] > 0;
    }

    /**
     * Compte le nombre de clubs en cache
     *
     * @return int Nombre de clubs
     */
    public function countClubs(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->pdo->query($sql);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }

    /**
     * Récupère les clubs les plus récemment ajoutés
     *
     * @param int $limit Nombre de clubs
     * @return array Liste des clubs récents
     */
    public function getRecentClubs(int $limit = 10): array
    {
        assert($limit > 0 && $limit <= 100, 'Invalid limit');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}