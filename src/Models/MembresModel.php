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
        assert($pdo instanceof PDO, 'PDO instance required');
        assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use exception mode');

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
        assert($sql !== '', 'SQL cannot be empty');

        $stmt = $this->prepareAndExecute($sql, []);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch membres');
        assert(count($results) <= 200, 'Too many membres fetched');

        return $results;
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
        $stmt = $this->prepareAndExecute($sql, ['id' => $id]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result === false || is_array($result), 'Invalid membre fetch result');

        if ($result !== false) {
            assert(isset($result['id']), 'Membre id missing');
            assert(isset($result['nom']), 'Membre nom missing');
            return $result;
        }

        return null;
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
        
        $stmt = $this->prepareAndExecute($sql, ['titre' => $titre]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to fetch membres by titre');
        assert(count($results) <= 100, 'Too many membres fetched');

        return $results;
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
        
        $searchTerm = '%' . $search . '%';
        assert(strlen($searchTerm) <= 300, 'Search term too long');

        $sql = "SELECT * FROM " . self::TABLE . "
                WHERE nom LIKE :search
                OR prenom LIKE :search
                OR titre LIKE :search
                ORDER BY nom ASC, prenom ASC";

        $stmt = $this->prepareAndExecute($sql, ['search' => $searchTerm]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        assert(is_array($results), 'Failed to search membres');
        assert(count($results) <= 200, 'Too many membres fetched');

        return $results;
    }

    /**
     * Compte le nombre de membres du bureau
     *
     * @return int Nombre de membres
     */
    public function countMembres(): int
    {
        $sql = "SELECT COUNT(*) as count FROM " . self::TABLE;
        $stmt = $this->prepareAndExecute($sql, []);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        assert($result !== false, 'Count query must return a row');
        assert(isset($result['count']), 'Count field missing');

        return (int)$result['count'];
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

        $stmt = $this->pdo->prepare($sql);
        assert($stmt instanceof PDOStatement, 'Failed to prepare statement');

        $executed = $stmt->execute($params);
        assert($executed, 'Failed to execute statement');

        return $stmt;
    }
}