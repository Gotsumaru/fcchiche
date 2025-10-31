# Documentation du répertoire `src`

Code métier PHP du projet FC Chiche.

## Sous-dossiers

- `API/` : clients externes (FFF, futurs services tiers).
- `Database/` : logiques de synchronisation et accès bas niveau.
- `Models/` : couches métiers et accès aux tables `pprod_*`.
- `Utils/` : utilitaires transverses (logger, réponses API, auth).

## Standards

- PHP 8.1+, `declare(strict_types=1);` obligatoire.
- Pas de dépendances externes sans validation sécurité/perf.
- Application stricte des règles Power of 10 adaptées (fonctions < 60 lignes, assertions systématiques).

## Chargement type

```php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Database/Sync.php';

$pdo = Database::getInstance(); // Vérifie la connexion PDO
$sync = new Sync();
$report = $sync->syncAll();
```

## Tests

- Tests unitaires via PHPUnit (dossier `tests/` à créer) avec base dédiée.
- Utiliser `ENV=testing` pour isoler les configs sensibles.
