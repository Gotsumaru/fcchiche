# Scripts front (`public/assets/js`)

JavaScript vanilla utilisé sur les pages publiques.

## Fichiers principaux

- `common.js` : initialisation globale (navigation, service worker, helpers).
- `index.js` : composants spécifiques page d'accueil.
- `matchs.js` : interactions calendrier / filtres matchs.
- `resultats.js` : affichage résultats et filtres.
- `classements.js` : widgets classement.
- `api.js` : appels fetch mutualisés pour les endpoints JSON.

## Règles de développement

1. Toujours encapsuler dans une IIFE et activer `'use strict';`.
2. Minimum deux `console.assert` par fonction critique (cf. Power of 10 adapté).
3. Limiter le chaînage de méthodes (max 3) et préférer les variables intermédiaires.
4. Vérifier la présence du DOM (`DOMContentLoaded`) avant manipulation.
5. Exposer uniquement les fonctions nécessaires sur `window.FCCHICHE` si partage inter-fichiers.

## Process de mise à jour

```bash
# Lint (ESLint recommandé)
npx eslint public/assets/js --max-warnings=0

# Minification facultative
npx terser public/assets/js/index.js -o public/assets/js/index.min.js
```

## Chargement côté PHP

Les fichiers sont inclus depuis `public/templates/footer.php` via des balises `<script src="/assets/js/xxx.js" defer></script>`.
