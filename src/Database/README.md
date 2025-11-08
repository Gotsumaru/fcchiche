# Couche base de données (`src/Database`)

## Fichiers

- `Sync.php` : orchestrateur de synchronisation BDD <-> API FFF.

## Responsabilités

- Préparer les requêtes PDO (transactions, upsert, nettoyage).
- Garantir l'idempotence des synchronisations (pas de doublons).
- Tracer toutes les opérations (succès / erreurs) dans `pprod_sync_logs`.

## Cycle de synchro

1. Récupération des données via `src/API/FFFApiClient.php`.
2. Normalisation + validation (assertions sur schémas attendus).
3. Insertion/Update dans les tables `pprod_*`.
4. Mise à jour des métadonnées (`pprod_config.last_sync_*`).

## Tests conseillés

- `php cron/sync_data.php --dry-run` (option à implémenter si absente).
- Comparaison des compteurs (avant/après) via `COUNT(*)`.
