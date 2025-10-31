# Documentation du répertoire `cron`

Ce dossier regroupe les scripts d'automatisation exécutés par CRON.

## Scripts

| Script | Description | Fréquence recommandée |
| --- | --- | --- |
| `sync_data.php` | Synchronise les données FFF (club, équipes, matchs, classements) et journalise les opérations | 2 fois par jour : `0 8,20 * * *` |

## Exécution manuelle

```bash
php cron/sync_data.php
```

- Le script charge `config/bootstrap.php` et `config/database.php`.
- Les logs détaillés sont envoyés vers `logs/sync.log` et `logs/cron.log`.
- Un timeout de sécurité de 5 minutes est activé (`SYNC_TIMEOUT`).

## Mise en place CRON OVH

1. Interface OVH > Hébergement > Planificateur de tâches.
2. Commande : `/usr/local/php8.1/bin/php /homez.xxx/fcchice/www/cron/sync_data.php`.
3. Fréquence : `0 8,20 * * *`.
4. Notification email en cas d'échec.

## Surveillance

- Consulter `logs/cron.log` pour vérifier les déclenchements.
- Surveiller la taille des logs (`du -h logs/`).
- Mettre en place une alerte si aucun log de succès depuis 24h.
