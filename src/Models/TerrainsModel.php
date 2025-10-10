<?php
declare(strict_types=1);

/**
 * Modèle Terrains
 */
class TerrainsModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_terrains';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les terrains du club
     *
     * @return array Liste des terrains
     * @throws PDOException Si erreur BDD
     */
    public function getAllTerrains(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY name ASC";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un terrain par son ID
     *
     * @param int $id ID du terrain
     * @return array|null Données du terrain
     */
    public function getTerrainById(int $id): ?array
    {
        assert($id > 0, 'Terrain ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère un terrain par son numéro API (te_no)
     *
     * @param int $teNo Numéro terrain API
     * @return array|null Données du terrain
     */
    public function getTerrainByTeNo(int $teNo): ?array
    {
        assert($teNo > 0, 'Terrain te_no must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE te_no = :te_no LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['te_no' => $teNo]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère les terrains avec coordonnées GPS
     *
     * @return array Liste des terrains avec GPS
     */
    public function getTerrainsWithGPS(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE latitude IS NOT NULL 
                AND longitude IS NOT NULL 
                ORDER BY name ASC";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre de terrains
     *
     * @return int Nombre de terrains
     */
    public function countTerrains(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->pdo->query($sql);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}