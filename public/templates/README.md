# Templates publics (`public/templates`)

Fragments HTML partagés par toutes les pages publiques.

## Fichiers

- `header.php` : ouverture du document (`<!DOCTYPE html>`, balises `<head>`, navigation principale).
- `footer.php` : fermeture du layout, scripts JS partagés, balise `<footer>`.

## Utilisation

```php
<?php
require_once __DIR__ . '/bootstrap.php';
include __DIR__ . '/templates/header.php';
// ... contenu page ...
include __DIR__ . '/templates/footer.php';
```

## Règles

- Tous les points d'entrée doivent passer par ces fragments pour garantir la cohérence (meta SEO, PWA, scripts).
- Injecter les feuilles de style / scripts via variables définies avant inclusion (`$pageTitle`, `$extraScripts`, etc.).
- Éviter d'insérer de la logique métier dans les templates : uniquement présentation.
