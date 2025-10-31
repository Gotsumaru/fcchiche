# Pages équipes (`public/equipes`)

Pages PHP dédiées à chaque équipe du club.

## Convention de nommage

- `senior-1.php`, `u13.php`, etc. (actuellement `fcchiche1.php`, `u15.php`, ...).
- Utiliser des slugs explicites (`categorie-niveau.php`).

## Structure type

```php
<?php
require_once __DIR__ . '/../bootstrap.php';
$pageTitle = 'FC Chiche - U15';
include __DIR__ . '/../templates/header.php';
// contenu spécifique équipe (effectif, calendrier)
include __DIR__ . '/../templates/footer.php';
```

## Données dynamiques

- Récupération via les modèles `src/Models/EquipesModel.php`, `MatchsModel.php`, `ClassementsModel.php`.
- Prévoir un cache court (5 min) côté PHP pour limiter la charge API FFF.

## Checklist

- Photo d'équipe optimisée (`public/assets/images/galeries/...`).
- Effectif mis à jour en début de saison.
- Liens vers calendrier, résultats, classement.
