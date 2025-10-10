<?php
declare(strict_types=1);

/**
 * Modèle Club - Informations du FC Chiche
 */
class ClubModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_club';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère les informations du club
     *
     * @return array|null Données du club ou null si non trouvé
     * @throws PDOException Si erreur BDD
     */
    public function getClub(): ?array
    {
        $sql = "SELECT * FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère uniquement les infos essentielles du club
     *
     * @return array|null Données essentielles
     */
    public function getClubEssentials(): ?array
    {
        $sql = "SELECT 
                    id, cl_no, name, short_name, logo_url, 
                    address1, address2, address3, postal_code,
                    latitude, longitude, district_name
                FROM " . self::TABLE . " 
                WHERE cl_no = :cl_no 
                LIMIT 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère l'ID interne du club
     *
     * @return int|null ID du club
     */
    public function getClubId(): ?int
    {
        $sql = "SELECT id FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['id'] : null;
    }

    /**
     * Récupère le logo du club
     *
     * @return string|null URL du logo
     */
    public function getClubLogo(): ?string
    {
        $sql = "SELECT logo_url FROM " . self::TABLE . " WHERE cl_no = :cl_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result['logo_url'] : null;
    }

    /**
     * Vérifie si le club existe en BDD
     *
     * @return bool True si le club existe
     */
    public function exists(): bool
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE . " WHERE cl_no = :cl_no";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['cl_no' => API_FFF_CLUB_ID]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false && (int)$result['count'] > 0;
    }
}