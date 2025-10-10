<?php
declare(strict_types=1);

/**
 * Modèle Membres du Bureau
 */
class MembresModel
{
    private PDO $pdo;
    private const TABLE = 'pprod_membres';

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Récupère tous les membres du bureau
     *
     * @return array Liste des membres
     * @throws PDOException Si erreur BDD
     */
    public function getAllMembres(): array
    {
        $sql = "SELECT * FROM " . self::TABLE . " ORDER BY nom ASC, prenom ASC";
        $stmt = $this->pdo->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un membre par son ID
     *
     * @param int $id ID du membre
     * @return array|null Données du membre
     */
    public function getMembreById(int $id): ?array
    {
        assert($id > 0, 'Membre ID must be positive');
        
        $sql = "SELECT * FROM " . self::TABLE . " WHERE id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    /**
     * Récupère les membres par titre/fonction
     *
     * @param string $titre Titre du membre (ex: "Président")
     * @return array Liste des membres avec ce titre
     */
    public function getMembresByTitre(string $titre): array
    {
        assert(!empty($titre), 'Titre cannot be empty');
        
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE titre = :titre 
                ORDER BY nom ASC, prenom ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['titre' => $titre]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Recherche des membres par nom ou prénom
     *
     * @param string $search Terme de recherche
     * @return array Liste des membres correspondants
     */
    public function searchMembres(string $search): array
    {
        assert(!empty($search), 'Search term cannot be empty');
        
        $search = '%' . $search . '%';
        $sql = "SELECT * FROM " . self::TABLE . " 
                WHERE nom LIKE :search 
                OR prenom LIKE :search 
                OR titre LIKE :search
                ORDER BY nom ASC, prenom ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['search' => $search]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre de membres du bureau
     *
     * @return int Nombre de membres
     */
    public function countMembres(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->pdo->query($sql);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? (int)$result['count'] : 0;
    }
}