# Clients API (`src/API`)

Interfaces pour consommer les services externes du projet.

## Fichier principal

- `FFFApiClient.php` : client HTTP vers l'API officielle FFF (`api-dofa.fff.fr`).

## Caractéristiques

- Utilise cURL en priorité (fallback `file_get_contents`).
- Timeout configurable via la constante `API_FFF_TIMEOUT` (30s par défaut).
- Journalisation centralisée via `src/Utils/Logger.php`.
- Retourne des tableaux associatifs normalisés prêts à être stockés.

## Exemple d'utilisation

```php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../src/API/FFFApiClient.php';

$client = new FFFApiClient();
$club = $client->getClubInfo();
```

## Bonnes pratiques

- Journaliser tous les appels via le logger fourni pour audit.
- Implémenter un mécanisme de retry limité (3 tentatives max) si instabilité réseau.
- Valider les schémas de réponse avant de propager aux modèles (assertions strictes).
