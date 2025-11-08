# 🔧 EXPLICATION COMPLÈTE DU BACKEND PHP

**Pour comprendre comment le backend fonctionne et comment il s'intègre avec React**

---

## 📊 TABLE DES MATIÈRES

1. [Structure du Backend](#1-structure-du-backend)
2. [La Base de Données](#2-la-base-de-données)
3. [Les 14 API Endpoints](#3-les-14-api-endpoints)
4. [Comment fonctionne une requête](#4-comment-fonctionne-une-requête)
5. [Modèles et Logique Métier](#5-modèles-et-logique-métier)
6. [Authentification JWT](#6-authentification-jwt)
7. [Synchronisation FFF](#7-synchronisation-fff)
8. [CORS Configuration](#8-cors-configuration)
9. [Déploiement Backend](#9-déploiement-backend)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. STRUCTURE DU BACKEND

### Vue d'ensemble

Votre backend est un **ensemble d'endpoints PHP qui retournent du JSON**.

```
Utilisateur
    │
    ├─ Visite https://fcchiche.fr/api/matchs
    │
    ▼
Serveur Web (Apache/Nginx sur OVH)
    │
    ├─ Reçoit requête GET /api/matchs
    │
    ▼
PHP Interpréteur
    │
    ├─ Exécute: public/api/matchs.php
    │
    ▼
PHP Code
    ├─ Appelle MatchsModel::getMatchs()
    │
    ▼
MySQL Query
    ├─ SELECT * FROM pprod_matchs
    │
    ▼
MySQL Database
    └─ Retourne les matchs

    ▲
    │
PHP Code
    ├─ Formate en JSON
    ├─ Ajoute headers (Content-Type: application/json)
    │
    ▼
Réponse HTTP
    └─ { "matchs": [...], "success": true }
```

### Fichiers clés

#### 1. **public/api/** - Les endpoints

```
public/api/
├── matchs.php          → GET/POST/PUT/DELETE /api/matchs
├── classements.php     → GET /api/classements
├── equipes.php         → GET /api/equipes
├── competitions.php    → GET /api/competitions
├── club.php            → GET /api/club
├── engagements.php     → GET /api/engagements
├── terrains.php        → GET /api/terrains
├── membres.php         → GET /api/membres
├── config.php          → GET /api/config
├── sync-logs.php       → GET /api/sync-logs
├── clubs-cache.php     → GET /api/clubs-cache
├── auth.php            → POST /api/auth (login)
└── docs.html           → Documentation API
```

**Exemple: public/api/matchs.php**

```php
<?php
// public/api/matchs.php

// 1. Charger bootstrap (config, connexion DB, etc)
require_once '../bootstrap.php';

// 2. Importer les classes nécessaires
use Source\API\ApiAuth;
use Source\API\ApiResponse;
use Source\Models\MatchsModel;

// 3. Vérifier la méthode HTTP
$method = $_SERVER['REQUEST_METHOD'];
$response = new ApiResponse();

try {
    switch ($method) {
        // GET /api/matchs - récupérer matchs
        case 'GET':
            $params = $_GET;  // Paramètres de query string
            $matchs = MatchsModel::getMatchs($params);
            $response->success(['matchs' => $matchs]);
            break;

        // POST /api/matchs - créer match (authentifiéécuté)
        case 'POST':
            ApiAuth::protectWrite();  // Vérifie JWT token
            $data = json_decode(file_get_contents('php://input'), true);
            $id = MatchsModel::create($data);
            $response->success(['id' => $id]);
            break;

        // PUT /api/matchs/{id} - mettre à jour match
        case 'PUT':
            ApiAuth::protectWrite();
            $id = explode('/', $_SERVER['REQUEST_URI'])[3];  // /api/matchs/123
            $data = json_decode(file_get_contents('php://input'), true);
            MatchsModel::update($id, $data);
            $response->success();
            break;

        // DELETE /api/matchs/{id} - supprimer match
        case 'DELETE':
            ApiAuth::protectWrite();
            $id = explode('/', $_SERVER['REQUEST_URI'])[3];
            MatchsModel::delete($id);
            $response->success();
            break;

        default:
            $response->error('Method not allowed', 405);
    }
} catch (Exception $e) {
    // Log l'erreur et retourne message d'erreur
    $response->error($e->getMessage(), 500);
}

// 4. Envoyer la réponse JSON
$response->send();
```

#### 2. **src/Models/** - Logique métier

Chaque table MySQL a un **Model** correspondant:

```
src/Models/
├── MatchsModel.php        → Logique pour matchs
├── ClassementsModel.php   → Logique pour classements
├── EquipesModel.php       → Logique pour équipes
├── CompetitionsModel.php  → Logique pour compétitions
├── EngagementsModel.php   → Logique pour engagements
├── ClubModel.php          → Logique pour club
├── TerrainsModel.php      → Logique pour terrains
├── MembresModel.php       → Logique pour membres
├── ConfigModel.php        → Logique pour config
├── SyncLogsModel.php      → Logique pour sync logs
├── ClubsCacheModel.php    → Logique pour cache clubs
└── BaseModel.php          → Classe parente
```

**Exemple: src/Models/MatchsModel.php**

```php
<?php
namespace Source\Models;

use Source\Database\Connection;

class MatchsModel extends BaseModel {
    protected $table = 'pprod_matchs';

    /**
     * Récupérer tous les matchs avec filtres
     */
    public static function getMatchs($filters = []) {
        $pdo = Connection::pdo();

        // Construire query
        $sql = "SELECT * FROM pprod_matchs WHERE 1=1";
        $params = [];

        // Filtres optionnels
        if (!empty($filters['competition_id'])) {
            $sql .= " AND competition_id = ?";
            $params[] = $filters['competition_id'];
        }

        if (!empty($filters['home_team'])) {
            $sql .= " AND home_team = ?";
            $params[] = $filters['home_team'];
        }

        // Limit
        $limit = $filters['limit'] ?? 50;
        $sql .= " LIMIT " . intval($limit);

        // Exécuter requête
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Créer un match
     */
    public static function create($data) {
        $pdo = Connection::pdo();

        $sql = "INSERT INTO pprod_matchs
                (home_team, away_team, date, time, location, competition_id)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['home_team'],
            $data['away_team'],
            $data['date'],
            $data['time'],
            $data['location'],
            $data['competition_id']
        ]);

        return $pdo->lastInsertId();
    }

    /**
     * Mettre à jour un match
     */
    public static function update($id, $data) {
        $pdo = Connection::pdo();

        $updates = [];
        $params = [];

        foreach ($data as $key => $value) {
            $updates[] = "$key = ?";
            $params[] = $value;
        }

        $params[] = $id;  // WHERE id = ?

        $sql = "UPDATE pprod_matchs SET " . implode(', ', $updates) . " WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Supprimer un match
     */
    public static function delete($id) {
        $pdo = Connection::pdo();
        $stmt = $pdo->prepare("DELETE FROM pprod_matchs WHERE id = ?");
        $stmt->execute([$id]);
    }
}
```

#### 3. **src/API/** - Logique API

```
src/API/
├── ApiResponse.php      → Formate les réponses JSON
├── ApiAuth.php          → Vérifie JWT tokens
└── FFFApiClient.php     → Client pour API FFF
```

**ApiResponse.php:**
```php
<?php
namespace Source\API;

class ApiResponse {
    private $headers = [];

    public function __construct() {
        // Headers CORS (permet React d'appeler API)
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    /**
     * Réponse succès
     */
    public function success($data = []) {
        echo json_encode(array_merge([
            'success' => true
        ], $data));
        exit;
    }

    /**
     * Réponse erreur
     */
    public function error($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message
        ]);
        exit;
    }
}
```

**ApiAuth.php:**
```php
<?php
namespace Source\API;

class ApiAuth {
    /**
     * Protéger un endpoint (vérifie JWT token)
     */
    public static function protectWrite() {
        $headers = getallheaders();

        // Récupérer token du header Authorization
        $token = $headers['Authorization'] ?? null;

        if (!$token) {
            throw new \Exception('Unauthorized', 401);
        }

        // Supprimer "Bearer " prefix
        $token = str_replace('Bearer ', '', $token);

        // Vérifier et décoder token JWT
        // (logique JWT complexe ici)
        $user = self::verifyJWT($token);

        if (!$user) {
            throw new \Exception('Invalid token', 401);
        }

        return $user;
    }

    private static function verifyJWT($token) {
        // Logique JWT avec clé secrète
        // À implémenter dans votre codebase
        // Retourne user data si valide, false sinon
    }
}
```

#### 4. **src/Database/** - Accès base de données

```
src/Database/
├── Connection.php    → Connexion MySQL
├── Sync.php         → Synchronisation FFF
└── Logger.php       → Logs
```

**Connection.php:**
```php
<?php
namespace Source\Database;

class Connection {
    private static $pdo = null;

    /**
     * Récupérer connexion PDO
     */
    public static function pdo() {
        if (self::$pdo === null) {
            // Credentials depuis config/
            $dsn = getenv('DB_DSN');      // mysql:host=localhost;dbname=fcchiche
            $user = getenv('DB_USER');    // user OVH
            $pass = getenv('DB_PASS');    // password OVH

            self::$pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        }

        return self::$pdo;
    }
}
```

---

## 2. LA BASE DE DONNÉES

### Vue d'ensemble

```
MySQL Database: fcchiche79
├── 11 tables (préfixe pprod_)
├── Normalisée (3NF)
├── Indexée pour performance
└── Sauvegardée quotidiennement
```

### Les 11 tables

#### 1. **pprod_matchs** (Les matchs)

```sql
CREATE TABLE pprod_matchs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    home_team VARCHAR(255),         -- Team A
    away_team VARCHAR(255),         -- Team B
    date DATE,                      -- 2025-11-08
    time TIME,                      -- 19:00
    score_home INT NULL,            -- Null = pas joué, 3 = scores
    score_away INT NULL,
    location VARCHAR(255),          -- Stade/Terrain
    competition_id INT,             -- FK competitions
    phase VARCHAR(100),             -- Phase play
    poule VARCHAR(100),             -- Group
    referee VARCHAR(255),           -- Arbitre
    notes TEXT,                     -- Notes
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- 50+ colonnes au total (structure complexe FFF)
```

#### 2. **pprod_classements** (Classements/Standings)

```sql
CREATE TABLE pprod_classements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    competition_id INT,             -- FK competitions
    team_code VARCHAR(50),          -- Code équipe
    team_name VARCHAR(255),         -- Nom équipe
    position INT,                   -- 1er, 2e, etc
    played INT,                     -- Matchs joués
    wins INT,                       -- Victoires
    draws INT,                      -- Nuls
    losses INT,                     -- Défaites
    points INT,                     -- Points totaux
    for INT,                        -- Buts pour
    against INT,                    -- Buts contre
    difference INT,                 -- Différence
    updated_at TIMESTAMP
);
```

#### 3. **pprod_equipes** (Équipes du club)

```sql
CREATE TABLE pprod_equipes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,        -- A, B, U15, etc
    name VARCHAR(255),              -- Nom équipe
    category VARCHAR(100),          -- Senior, U15, etc
    coach_id INT,                   -- FK membres
    created_at TIMESTAMP
);
```

#### 4. **pprod_competitions** (Compétitions)

```sql
CREATE TABLE pprod_competitions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,        -- LR, CDT, etc
    name VARCHAR(255),              -- Nom compétition
    type VARCHAR(100),              -- League, Cup, etc
    season VARCHAR(20),             -- 2024-2025
    div_number INT,                 -- Division 1, 2, etc
    created_at TIMESTAMP
);
```

#### 5-11. **Autres tables**

```
pprod_engagements   → Équipes engagées dans compétitions
pprod_terrains      → Terrains/Stades
pprod_membres       → Bureau du club
pprod_club          → Info club
pprod_sync_logs     → Logs synchronisation
pprod_clubs_cache   → Cache clubs adversaires
pprod_config        → Config système
```

### Relationships (Relations)

```
pprod_matchs
├─ FK competition_id → pprod_competitions.id
└─ FK terrain_id → pprod_terrains.id

pprod_classements
└─ FK competition_id → pprod_competitions.id

pprod_equipes
├─ FK coach_id → pprod_membres.id
└─ Engagée dans pprod_engagements

pprod_engagements
├─ FK equipe_id → pprod_equipes.id
└─ FK competition_id → pprod_competitions.id
```

---

## 3. LES 14 API ENDPOINTS

### GET Endpoints (Lecture - Publique)

#### 1. **GET /api/matchs** - Récupérer matchs

```bash
# Request
GET https://preprod.fcchiche.fr/api/matchs?limit=50&competition_id=1

# Response
{
  "matchs": [
    {
      "id": 1,
      "home_team": "FC Chiché A",
      "away_team": "Équipe Adverse",
      "date": "2025-11-08",
      "time": "19:00",
      "score_home": null,
      "score_away": null,
      "location": "Stade Principal",
      "competition_id": 1,
      ...
    },
    {...}
  ],
  "success": true
}
```

**Dans React:**
```jsx
const { data: matchs } = useApi(() => api.getMatchs({ limit: 50 }));
```

#### 2. **GET /api/classements** - Récupérer classements

```bash
# Request
GET https://preprod.fcchiche.fr/api/classements?competition_id=1

# Response
{
  "classements": [
    {
      "id": 1,
      "position": 1,
      "team_name": "FC Chiché A",
      "played": 15,
      "wins": 12,
      "draws": 2,
      "losses": 1,
      "points": 38,
      "for": 45,
      "against": 12,
      "difference": 33
    },
    {...}
  ],
  "success": true
}
```

**Dans React:**
```jsx
const { data: classements } = useApi(() => api.getClassements(1));
```

#### 3-10. **Autres GET endpoints**

```
GET /api/equipes          → { equipes: [...] }
GET /api/competitions     → { competitions: [...] }
GET /api/club             → { club: {...} }
GET /api/engagements      → { engagements: [...] }
GET /api/terrains         → { terrains: [...] }
GET /api/membres          → { membres: [...] }
GET /api/config           → { config: {...} }
GET /api/sync-logs        → { logs: [...] }
```

### POST/PUT/DELETE Endpoints (Écriture - Authentifiée)

#### 11. **POST /api/auth** - Authentification

```bash
# Request
POST https://preprod.fcchiche.fr/api/auth
Content-Type: application/json

{
  "email": "admin@fcchiche.fr",
  "password": "password123"
}

# Response (Success)
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "email": "admin@fcchiche.fr",
    "name": "Admin"
  },
  "success": true
}

# Response (Failure)
{
  "success": false,
  "error": "Invalid credentials"
}
```

**Dans React:**
```jsx
const { login } = useAuth();
const success = await login('admin@fcchiche.fr', 'password123');
```

#### 12-14. **CRUD Matchs** - Authentifiée

```bash
# POST /api/matchs - Créer match
POST /api/matchs
Authorization: Bearer {token}
{
  "home_team": "FC Chiché A",
  "away_team": "Adversaire",
  "date": "2025-11-15",
  "time": "19:00",
  "competition_id": 1
}
← { "id": 123, "success": true }

# PUT /api/matchs/{id} - Mettre à jour match
PUT /api/matchs/123
Authorization: Bearer {token}
{
  "score_home": 3,
  "score_away": 1
}
← { "success": true }

# DELETE /api/matchs/{id} - Supprimer match
DELETE /api/matchs/123
Authorization: Bearer {token}
← { "success": true }
```

**Dans React:**
```jsx
// Créer
await api.createMatch({home_team: "A", away_team: "B"}, token);

// Mettre à jour
await api.updateMatch(123, {score_home: 3, score_away: 1}, token);

// Supprimer
await api.deleteMatch(123, token);
```

---

## 4. COMMENT FONCTIONNE UNE REQUÊTE

### Flux complet: GET /api/matchs

#### Étape 1: Frontend envoie requête

```jsx
// React (frontend)
const { data: matchs } = useApi(() => api.getMatchs());

// Cela appelle:
fetch('https://fcchiche.fr/api/matchs', {
  method: 'GET',
  headers: {
    'Content-Type': 'application/json'
  }
})
```

#### Étape 2: Serveur web (Apache/Nginx) reçoit

```
GET /api/matchs HTTP/1.1
Host: fcchiche.fr
User-Agent: Mozilla/5.0...
...
```

**Le serveur:**
1. Vérifie que le fichier `/api/matchs.php` existe
2. Appelle PHP interpréteur avec ce fichier

#### Étape 3: PHP exécute matchs.php

```php
<?php
// public/api/matchs.php

require_once '../bootstrap.php';

use Source\Models\MatchsModel;
use Source\API\ApiResponse;

$response = new ApiResponse();  // Prépare réponse JSON

try {
    // Récupérer paramètres GET
    $params = $_GET;  // Exemple: limit=50, competition_id=1

    // Appeler le model
    $matchs = MatchsModel::getMatchs($params);

    // Retourner succès
    $response->success(['matchs' => $matchs]);

} catch (Exception $e) {
    // Erreur
    $response->error($e->getMessage());
}
```

#### Étape 4: MatchsModel exécute requête SQL

```php
// src/Models/MatchsModel.php

public static function getMatchs($filters = []) {
    $pdo = Connection::pdo();  // Connexion MySQL

    // Construire requête SQL
    $sql = "SELECT * FROM pprod_matchs WHERE 1=1";
    $params = [];

    // Ajouter filtres
    if (!empty($filters['limit'])) {
        $sql .= " LIMIT " . intval($filters['limit']);
    }

    // Exécuter via PDO (prepared statement)
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Récupérer résultats (array de matchs)
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
}
```

#### Étape 5: MySQL retourne les données

```sql
-- Requête exécutée
SELECT * FROM pprod_matchs LIMIT 50;

-- Résultat (exemple)
id | home_team     | away_team     | date       | score_home | score_away
---|---------------|---------------|------------|------------|----------
1  | FC Chiché A   | Équipe X      | 2025-11-08 | NULL       | NULL
2  | FC Chiché B   | Équipe Y      | 2025-11-10 | 2          | 1
3  | FC Chiché A   | Équipe Z      | 2025-11-15 | NULL       | NULL
...
```

#### Étape 6: PHP formate en JSON

```php
// MatchsModel::getMatchs() retourne:
[
    ['id' => 1, 'home_team' => 'FC Chiché A', ...],
    ['id' => 2, 'home_team' => 'FC Chiché B', ...],
    ...
]

// ApiResponse::success() le convertit en JSON:
{
  "matchs": [
    {"id": 1, "home_team": "FC Chiché A", ...},
    {"id": 2, "home_team": "FC Chiché B", ...},
    ...
  ],
  "success": true
}
```

#### Étape 7: Réponse HTTP envoyée

```
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Access-Control-Allow-Origin: *
Content-Length: 2048

{
  "matchs": [...],
  "success": true
}
```

#### Étape 8: Frontend reçoit et affiche

```jsx
// useApi hook:
const { data, loading, error } = useApi(() => api.getMatchs());

// State mis à jour avec les données
data = { matchs: [...] }

// Component re-render
return (
  <div>
    {data?.matchs?.map(match => (
      <MatchCard key={match.id} match={match} />
    ))}
  </div>
)
```

---

## 5. MODÈLES ET LOGIQUE MÉTIER

Chaque Model correspond à une table et contient la logique:

```php
// Exemple pattern complet

namespace Source\Models;

class MonModel {
    protected $table = 'pprod_ma_table';

    /**
     * Récupérer tous les éléments
     */
    public static function getAll($filters = []) {
        $pdo = Connection::pdo();
        $sql = "SELECT * FROM pprod_ma_table WHERE 1=1";

        // Ajouter filtres
        if (!empty($filters['search'])) {
            $sql .= " AND nom LIKE ?";
        }

        // Paramètres
        $params = [];

        // Exécuter
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Récupérer par ID
     */
    public static function getById($id) {
        $pdo = Connection::pdo();
        $stmt = $pdo->prepare("SELECT * FROM pprod_ma_table WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Créer un élément
     */
    public static function create($data) {
        $pdo = Connection::pdo();

        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO pprod_ma_table ($columns) VALUES ($placeholders)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return $pdo->lastInsertId();
    }

    /**
     * Mettre à jour
     */
    public static function update($id, $data) {
        $pdo = Connection::pdo();

        $updates = array_map(fn($k) => "$k = ?", array_keys($data));
        $sql = "UPDATE pprod_ma_table SET " . implode(', ', $updates) . " WHERE id = ?";

        $params = array_merge(array_values($data), [$id]);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    /**
     * Supprimer
     */
    public static function delete($id) {
        $pdo = Connection::pdo();
        $stmt = $pdo->prepare("DELETE FROM pprod_ma_table WHERE id = ?");
        $stmt->execute([$id]);
    }
}
```

---

## 6. AUTHENTIFICATION JWT

### Flux JWT

```
1. Utilisateur saisit email/password
   └─ Frontend: api.login('email@', 'password')

2. Frontend envoie POST /api/auth
   {
     "email": "email@fcchiche.fr",
     "password": "password123"
   }

3. Backend valide credentials
   ├─ Cherche user dans pprod_membres
   ├─ Compare password hashé
   └─ Si valide: crée JWT token

4. JWT Token = données signées
   ├─ Contient: { user_id: 1, email: 'email@', exp: 1699430400 }
   ├─ Signé avec clé secrète (SECRET_KEY)
   └─ Encodé en base64

5. Backend retourne token
   {
     "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJlbWFpbCI6ImFkbWluQGZjY2hpY2hlLmZyIiwiZXhwIjoxNjk5NDMwNDAwfQ.abc123...",
     "user": { "id": 1, "email": "email@" },
     "success": true
   }

6. Frontend sauvegarde token
   └─ localStorage.setItem('auth_token', token)

7. Requêtes authentifiées utilisent token
   GET /api/matchs
   Authorization: Bearer eyJhbGciOiJIUzI1NiIs...

8. Backend vérifie token
   ├─ Extrait token du header Authorization
   ├─ Décode avec clé secrète
   ├─ Vérifie signature (pas modifié)
   ├─ Vérifie expiration (pas expiré)
   └─ Si valide: exécute requête

9. Logout
   └─ localStorage.removeItem('auth_token')
   └─ Token supprimé (plus de validation possible)
```

### Code PHP (simplifié)

```php
// src/API/ApiAuth.php

public static function verifyJWT($token) {
    $secret = getenv('JWT_SECRET');  // Clé secrète

    // Diviser token en 3 parties
    $parts = explode('.', $token);  // header.payload.signature

    if (count($parts) !== 3) {
        return false;
    }

    $header = $parts[0];
    $payload = $parts[1];
    $signature = $parts[2];

    // Recalculer signature
    $data = "$header.$payload";
    $expectedSignature = hash_hmac('sha256', $data, $secret, true);
    $expectedSignature = rtrim(strtr(base64_encode($expectedSignature), '+/', '-_'), '=');

    // Vérifier signature
    if (!hash_equals($expectedSignature, $signature)) {
        return false;  // Token tamperisé!
    }

    // Décoder payload
    $decoded = json_decode(base64_decode($payload), true);

    // Vérifier expiration
    if ($decoded['exp'] < time()) {
        return false;  // Token expiré!
    }

    return $decoded;  // Token valide!
}
```

---

## 7. SYNCHRONISATION FFF

### Qu'est-ce que c'est?

```
FFF = Fédération Française de Football
API FFF = https://api-dofa.fff.fr/api

Synchronisation = Copier les données FFF dans votre BDD
```

### Flux automatique

```
CRON (Tâche planifiée)
    │
    ├─ Déclenché 2x/jour (8h00 et 20h00)
    │
    ▼
cron/sync_data.php s'exécute
    │
    ├─ Appelle FFFApiClient::getClubInfo()
    │
    ▼
API FFF consulté via cURL
    │
    ├─ GET https://api-dofa.fff.fr/api/clubs/79115 (ID club)
    │
    ▼
FFF retourne données JSON
    │
    ├─ Infos club, équipes, matchs, classements, etc.
    │
    ▼
src/Database/Sync.php transforme données
    │
    ├─ Normalise formats
    ├─ Gère insertions/mises à jour
    ├─ Gère suppressions
    │
    ▼
MySQL mise à jour
    │
    ├─ INSERT/UPDATE/DELETE sur pprod_*
    │
    ▼
pprod_sync_logs enregistre succès/erreur
    │
    └─ Timestamp: 2025-11-08 20:00:45, Status: success
```

### Code (simplifié)

```php
// cron/sync_data.php

<?php
require_once __DIR__ . '/../bootstrap.php';

use Source\Database\Sync;

try {
    Sync::syncAll();  // Lance la synchronisation complète

    // Logs
    Logger::info('Synchronisation réussie');

} catch (Exception $e) {
    Logger::error('Erreur sync: ' . $e->getMessage());
}

// src/Database/Sync.php

public static function syncAll() {
    Sync::syncClub();           // Infos club
    Sync::syncEquipes();        // Équipes
    Sync::syncCompetitions();   // Compétitions
    Sync::syncEngagements();    // Engagements
    Sync::syncMatchs();         // Matchs
    Sync::syncClassements();    // Classements
}

public static function syncClub() {
    // 1. Appeler API FFF
    $client = new FFFApiClient();
    $clubData = $client->getClubInfo();  // Données FFF

    // 2. Mettre à jour BDD
    $pdo = Connection::pdo();
    $stmt = $pdo->prepare("
        UPDATE pprod_club
        SET name = ?, code = ?, updated_at = NOW()
        WHERE id = 1
    ");

    $stmt->execute([
        $clubData['name'],
        $clubData['code']
    ]);

    // 3. Logger
    Logger::info('Club synced: ' . $clubData['name']);
}
```

---

## 8. CORS CONFIGURATION

### Qu'est-ce que CORS?

```
CORS = Cross-Origin Resource Sharing
= Permission pour une origine différente d'accéder à votre API
```

### Problème sans CORS

```
Frontend: http://localhost:5173
Backend: https://fcchiche.fr/api

fetch('https://fcchiche.fr/api/matchs')
  └─ ERREUR: "Access to XMLHttpRequest blocked by CORS policy"
  └─ Navigateur refuse la requête
```

### Solution: Headers CORS

```php
// Ajouter dans chaque endpoint (ou bootstrap.php)

header('Access-Control-Allow-Origin: *');
// ↑ Permet toutes les origines

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
// ↑ Méthodes HTTP autorisées

header('Access-Control-Allow-Headers: Content-Type, Authorization');
// ↑ Headers autorisés

// Gérer OPTIONS (preflight request)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

### Dans votre ApiResponse.php

```php
class ApiResponse {
    public function __construct() {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }
}
```

---

## 9. DÉPLOIEMENT BACKEND

### Backend est DÉJÀ déployé

Votre backend PHP est **déjà sur OVH** et **déjà en production**!

```
Aucune modification du backend requise!
```

### Vérifier que backend fonctionne

```bash
# Test 1: Vérifier API répond
curl https://preprod.fcchiche.fr/api/club

# Output attendu:
# {"club": {...}, "success": true}

# Test 2: Vérifier erreur 404
curl https://preprod.fcchiche.fr/api/nonexistent

# Output attendu:
# {"success": false, "error": "..."}

# Test 3: Tester authentification
curl -X POST https://preprod.fcchiche.fr/api/auth \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@","password":"..."}'

# Output attendu:
# {"token": "...", "user": {...}, "success": true}
```

### Variables d'environnement (OVH)

```
Backend utilise variables:
├─ DB_DSN         = "mysql:host=localhost;dbname=fcchiche"
├─ DB_USER        = "utilisateur OVH"
├─ DB_PASS        = "password OVH"
├─ JWT_SECRET     = "clé secrète JWT"
└─ API_FFF_KEY    = "clé API FFF"

Stockées dans config/ (fichier .env ou config.php)
```

---

## 10. TROUBLESHOOTING

### Problème: API retourne 500 Error

```
Cause: Erreur PHP (base de données, logique, etc)

Solution:
1. Vérifier logs serveur OVH
   └─ FTP → /logs/error.log

2. Vérifier requête SQL
   └─ Tester directement en MySQL CLI

3. Vérifier permissions fichiers
   └─ Les fichiers PHP doivent être exécutables
```

### Problème: CORS error au frontend

```
Erreur: "Access to XMLHttpRequest blocked by CORS policy"

Cause: Headers CORS manquants

Solution:
├─ Ajouter headers dans ApiResponse.php
├─ Ou dans bootstrap.php (au début)
└─ Vérifier Access-Control-Allow-Origin: *
```

### Problème: Authentification échoue

```
Cause: Token JWT invalide ou API login cassée

Debug:
1. Tester API login directement
   curl -X POST https://preprod.fcchiche.fr/api/auth \
     -H "Content-Type: application/json" \
     -d '{"email":"...","password":"..."}'

2. Vérifier JWT_SECRET existe

3. Vérifier user existe en BDD
   SELECT * FROM pprod_membres WHERE email = 'email@'
```

### Problème: Synchronisation FFF ne fonctionne pas

```
Cause: CRON pas déclenché ou API FFF down

Vérifier:
1. CRON configuré sur OVH?
   └─ Deve être: 0 8,20 * * * php /path/to/cron/sync_data.php

2. API FFF joignable?
   curl https://api-dofa.fff.fr/api/clubs/79115

3. Logs de sync?
   FTP → /logs/sync.log
```

---

## 📚 RÉSUMÉ

### Backend Architecture
```
Frontend (React)
    ↓ fetch()
API Endpoints (PHP)
    ↓
Models + Logic (PHP)
    ↓
MySQL Database
```

### Flux requête
```
1. Frontend envoie requête GET/POST
2. Serveur web appelle PHP
3. PHP appelle Model
4. Model exécute SQL
5. MySQL retourne données
6. Model formate JSON
7. PHP retourne réponse
8. Frontend reçoit et affiche
```

### Les 14 endpoints
```
10 × GET (lecture)
1 × POST /api/auth (login)
3 × CRUD /api/matchs (créer/modifier/supprimer)
```

### Authentification
```
1. Login email/password → JWT token
2. Token stocké en localStorage
3. Token envoyé dans Authorization header
4. Backend vérifie token → exécute requête
5. Logout → token supprimé
```

---

Voilà! Vous comprenez maintenant comment le backend fonctionne! 🎉
