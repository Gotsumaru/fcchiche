# API FC Chiche - Documentation Complète

## Vue d'ensemble

Architecture API REST complète pour le site FC Chiche. 
- **Format** : JSON standardisé
- **Sécurité** : GET public / POST/PUT/DELETE protégés admin
- **CORS** : Limité à fcchiche.fr
- **Base URL** : `https://fcchiche.fr/api/`

---

## Format de réponse standardisé

### Succès (HTTP 2xx)
```json
{
  "success": true,
  "data": {...},
  "meta": {
    "timestamp": "2025-10-10T14:30:00+02:00",
    "count": 10
  }
}
```

### Erreur (HTTP 4xx/5xx)
```json
{
  "success": false,
  "error": {
    "message": "Description de l'erreur",
    "code": 400
  },
  "meta": {
    "timestamp": "2025-10-10T14:30:00+02:00"
  }
}
```

---

## Authentification

### Endpoints publics (GET uniquement)
Tous les endpoints GET sont publics (sauf `/api/config` et `/api/sync-logs`).

### Endpoints protégés
- Toutes les méthodes POST/PUT/DELETE
- `/api/config` (lecture et écriture)
- `/api/sync-logs` (lecture uniquement)

### Login
```bash
POST /api/auth?action=login
Content-Type: application/json

{
  "username": "Administrateur",
  "password": "votre_mot_de_passe"
}
```

**Réponse :**
```json
{
  "success": true,
  "data": {
    "authenticated": true,
    "username": "Administrateur",
    "csrf_token": "abc123..."
  }
}
```

### Logout
```bash
POST /api/auth?action=logout
```

### Vérifier statut
```bash
GET /api/auth?action=status
```

### Token CSRF
```bash
GET /api/auth?action=csrf
```

---

## Endpoints disponibles

### 1. `/api/club` - Informations du club

**GET /api/club** - Toutes les infos
```bash
curl https://fcchiche.fr/api/club
```

**GET /api/club?essentials=true** - Infos essentielles uniquement
```bash
curl https://fcchiche.fr/api/club?essentials=true
```

**GET /api/club?logo=true** - Logo uniquement
```bash
curl https://fcchiche.fr/api/club?logo=true
```

---

### 2. `/api/equipes` - Équipes du club

**GET /api/equipes** - Toutes les équipes
```bash
curl https://fcchiche.fr/api/equipes
```

**GET /api/equipes?id=1** - Équipe par ID
```bash
curl https://fcchiche.fr/api/equipes?id=1
```

**GET /api/equipes?category=SEM** - Équipes par catégorie
```bash
curl https://fcchiche.fr/api/equipes?category=SEM
```

**GET /api/equipes?seniors=true** - Équipes seniors
```bash
curl https://fcchiche.fr/api/equipes?seniors=true
```

**GET /api/equipes?jeunes=true** - Équipes jeunes
```bash
curl https://fcchiche.fr/api/equipes?jeunes=true
```

**GET /api/equipes?categories=true** - Liste des catégories
```bash
curl https://fcchiche.fr/api/equipes?categories=true
```

---

### 3. `/api/matchs` ⭐ ENDPOINT PRINCIPAL

**GET /api/matchs?upcoming=10** - Prochains matchs
```bash
curl https://fcchiche.fr/api/matchs?upcoming=10
```

**GET /api/matchs?last_results=10** - Derniers résultats
```bash
curl https://fcchiche.fr/api/matchs?last_results=10
```

**GET /api/matchs?competition_id=123** - Matchs d'une compétition
```bash
curl https://fcchiche.fr/api/matchs?competition_id=123
```

**GET /api/matchs?equipe_id=12&is_result=true** - Résultats d'une équipe
```bash
curl https://fcchiche.fr/api/matchs?equipe_id=12&is_result=true&limit=5
```

**GET /api/matchs?category=SEM&is_result=false** - Calendrier d'une équipe
```bash
curl https://fcchiche.fr/api/matchs?category=SEM&is_result=false
```

**GET /api/matchs?home=true&is_result=false** - Prochains matchs à domicile
```bash
curl https://fcchiche.fr/api/matchs?home=true&is_result=false
```

**GET /api/matchs?journee=10&competition_id=123** - Matchs d'une journée
```bash
curl https://fcchiche.fr/api/matchs?journee=10&competition_id=123
```

**GET /api/matchs?date_from=2025-11-01&date_to=2025-11-30** - Par période
```bash
curl https://fcchiche.fr/api/matchs?date_from=2025-11-01&date_to=2025-11-30
```

**Paramètres disponibles :**
- `id` - Match par ID
- `ma_no` - Match par numéro API
- `upcoming` - Prochains matchs (limite)
- `last_results` - Derniers résultats (limite)
- `competition_id` - Filtrer par compétition
- `category` - Filtrer par catégorie d'équipe (SEM, U19, etc.)
- `is_result` - true=résultats, false=calendrier, null=tous
- `home` - Matchs à domicile uniquement
- `away` - Matchs à l'extérieur uniquement
- `journee` - Numéro de journée (+ competition_id requis)
- `date_from` + `date_to` - Période de dates
- `limit` - Limite de résultats (défaut: 20, max: 100)

---

### 4. `/api/classements` - Classements historisés

**GET /api/classements?competitions=true** - Liste des compétitions avec classement
```bash
curl https://fcchiche.fr/api/classements?competitions=true
```

**GET /api/classements?competition_id=123** - Classement actuel
```bash
curl https://fcchiche.fr/api/classements?competition_id=123
```

**GET /api/classements?competition_id=123&journee=10** - Journée spécifique
```bash
curl https://fcchiche.fr/api/classements?competition_id=123&journee=10
```

**GET /api/classements?competition_id=123&position=true** - Position FC Chiche
```bash
curl https://fcchiche.fr/api/classements?competition_id=123&position=true
```

**GET /api/classements?competition_id=123&history=true** - Évolution position
```bash
curl https://fcchiche.fr/api/classements?competition_id=123&history=true
```

**GET /api/classements?competition_id=123&stats=true** - Statistiques club
```bash
curl https://fcchiche.fr/api/classements?competition_id=123&stats=true
```

**GET /api/classements?competition_id=123&compare=5,10** - Comparer journées
```bash
curl https://fcchiche.fr/api/classements?competition_id=123&compare=5,10
```

---

### 5. `/api/competitions` - Compétitions

**GET /api/competitions** - Toutes les compétitions (saison actuelle)
```bash
curl https://fcchiche.fr/api/competitions
```

**GET /api/competitions?season=2024** - Compétitions d'une saison
```bash
curl https://fcchiche.fr/api/competitions?season=2024
```

**GET /api/competitions?championnats=true** - Championnats uniquement
```bash
curl https://fcchiche.fr/api/competitions?championnats=true
```

**GET /api/competitions?coupes=true** - Coupes uniquement
```bash
curl https://fcchiche.fr/api/competitions?coupes=true
```

---

### 6. `/api/terrains` - Terrains

**GET /api/terrains** - Tous les terrains
```bash
curl https://fcchiche.fr/api/terrains
```

**GET /api/terrains?gps=true** - Terrains avec coordonnées GPS
```bash
curl https://fcchiche.fr/api/terrains?gps=true
```

---

### 7. `/api/membres` - Membres du bureau

**GET /api/membres** - Tous les membres
```bash
curl https://fcchiche.fr/api/membres
```

**GET /api/membres?titre=Président** - Membres par titre
```bash
curl https://fcchiche.fr/api/membres?titre=Président
```

**GET /api/membres?search=Martin** - Recherche
```bash
curl https://fcchiche.fr/api/membres?search=Martin
```

---

### 8. `/api/clubs-cache` - Clubs adverses

**GET /api/clubs-cache** - Tous les clubs en cache
```bash
curl https://fcchiche.fr/api/clubs-cache
```

**GET /api/clubs-cache?cl_no=12345** - Club par cl_no
```bash
curl https://fcchiche.fr/api/clubs-cache?cl_no=12345
```

**GET /api/clubs-cache?search=Saint** - Recherche
```bash
curl https://fcchiche.fr/api/clubs-cache?search=Saint
```

---

### 9. `/api/engagements` - Pivot équipes-compétitions

**GET /api/engagements** - Tous les engagements
```bash
curl https://fcchiche.fr/api/engagements
```

**GET /api/engagements?equipe_id=1** - Engagements d'une équipe
```bash
curl https://fcchiche.fr/api/engagements?equipe_id=1
```

**GET /api/engagements?competition_id=123** - Équipes dans une compétition
```bash
curl https://fcchiche.fr/api/engagements?competition_id=123
```

---

### 10. `/api/config` 🔒 ADMIN ONLY

**GET /api/config** - Toutes les configurations
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/config
```

**GET /api/config?key=current_season** - Config spécifique
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/config?key=current_season
```

**GET /api/config?current_season=true** - Saison actuelle
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/config?current_season=true
```

---

### 11. `/api/sync-logs` 🔒 ADMIN ONLY

**GET /api/sync-logs** - Tous les logs
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/sync-logs?limit=50
```

**GET /api/sync-logs?errors=true** - Erreurs uniquement
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/sync-logs?errors=true
```

**GET /api/sync-logs?endpoint=club** - Logs d'un endpoint
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/sync-logs?endpoint=club
```

**GET /api/sync-logs?stats=true** - Statistiques globales
```bash
curl -H "Authorization: Bearer token" https://fcchiche.fr/api/sync-logs?stats=true
```

---

## Codes HTTP

- **200** : Succès
- **201** : Créé (POST)
- **400** : Requête invalide
- **401** : Non authentifié
- **403** : Non autorisé
- **404** : Ressource non trouvée
- **405** : Méthode non autorisée
- **500** : Erreur serveur
- **501** : Non implémenté

---

## Exemples d'utilisation

### JavaScript (fetch)
```javascript
// GET simple
const response = await fetch('https://fcchiche.fr/api/matchs?upcoming=5');
const data = await response.json();

// POST avec authentification
const loginResponse = await fetch('https://fcchiche.fr/api/auth?action=login', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    username: 'Administrateur',
    password: 'password'
  }),
  credentials: 'include' // Important pour les sessions
});
```

### cURL
```bash
# GET
curl https://fcchiche.fr/api/matchs?upcoming=5

# POST avec auth
curl -X POST https://fcchiche.fr/api/auth?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"Administrateur","password":"password"}' \
  -c cookies.txt

# Utiliser session
curl https://fcchiche.fr/api/config \
  -b cookies.txt
```

---

## Fichiers créés

### Utilitaires
1. `src/Utils/ApiResponse.php` - Réponses standardisées
2. `src/Utils/ApiAuth.php` - Authentification

### Endpoints
3. `public/api/auth.php` - Authentification
4. `public/api/club.php` - Club
5. `public/api/equipes.php` - Équipes
6. `public/api/matchs.php` - Matchs ⭐
7. `public/api/classements.php` - Classements
8. `public/api/competitions.php` - Compétitions
9. `public/api/terrains.php` - Terrains
10. `public/api/membres.php` - Membres
11. `public/api/clubs-cache.php` - Clubs adverses
12. `public/api/engagements.php` - Engagements
13. `public/api/config.php` 🔒 - Configuration
14. `public/api/sync-logs.php` 🔒 - Logs sync

---

## Configuration requise

### 1. Configurer le mot de passe admin

Éditer `src/Utils/ApiAuth.php` :
```php
private static function getPasswordHash(): string
{
    // Générer un hash :
    // echo password_hash('VotreMotDePasse', PASSWORD_DEFAULT);
    return '$2y$10$VotreHashIci...';
}
```

### 2. Configurer le token API (optionnel)

Éditer `src/Utils/ApiAuth.php` :
```php
private static function getApiToken(): string
{
    return 'votre_token_securise_ici';
}
```

### 3. Tester l'API

```bash
# Test basique
curl https://fcchiche.fr/api/club

# Test avec authentification
curl -X POST https://fcchiche.fr/api/auth?action=login \
  -H "Content-Type: application/json" \
  -d '{"username":"Administrateur","password":"FCChiche2025!"}'
```

---

## Sécurité

✅ **Implémenté** :
- CORS limité à fcchiche.fr
- Authentification par session PHP
- Protection CSRF pour modifications
- GET public / POST/PUT/DELETE protégés
- Validation des paramètres
- Assertions pour éviter bugs
- Limits de résultats (protection DoS)

⚠️ **À configurer** :
- Hash mot de passe admin dans `ApiAuth.php`
- Token API optionnel dans `ApiAuth.php`
- Rotation logs API (à implémenter si nécessaire)

---

## Notes de développement

- Toutes les API utilisent `ModelsLoader` pour charger les modèles
- Format JSON avec `JSON_UNESCAPED_UNICODE` pour caractères spéciaux
- Mode DEBUG affiche stack traces (désactiver en production)
- Limites par défaut : 20-50 résultats (max 100-500 selon endpoint)
- CORS en développement : autorise tous les domaines
- Assertions actives partout (désactivables via `zend.assertions=0`)

---

**Version** : 1.0  
**Dernière mise à jour** : Octobre 2025  
**Auteur** : FC Chiche Dev Team