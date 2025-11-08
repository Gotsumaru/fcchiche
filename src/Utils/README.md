# Utilitaires (`src/Utils`)

Fonctions transverses utilisées par l'ensemble du projet.

## Fichiers

| Fichier | Description |
| --- | --- |
| `Logger.php` | Gestion centralisée des logs (rotations, niveaux). |
| `ApiResponse.php` | Normalisation des réponses JSON (status, data, erreurs). |
| `ApiAuth.php` | Vérification des tokens API pour les endpoints publics. |

## Règles

- Toujours passer par `Logger` pour écrire dans `logs/` (éviter `error_log`).
- `ApiResponse` doit systématiquement valider la structure renvoyée (assertions sur `status`, `data`).
- `ApiAuth` doit récupérer les secrets depuis les variables d'environnement (`API_TOKEN`).

## Exemple

```php
require_once __DIR__ . '/../Utils/ApiResponse.php';

$response = ApiResponse::success(['message' => 'OK']);
header('Content-Type: application/json');
echo json_encode($response);
```
