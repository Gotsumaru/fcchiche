# Feuilles de style (`public/assets/css`)

CSS produit pour les pages publiques.

## Fichiers

- `common.css` : base globale (reset, variables CSS, layout). Utilisé sur toutes les pages.
- `index.css` : styles spécifiques page d'accueil.

## Bonnes pratiques

1. Mobile-first avec breakpoints principaux : 480px, 768px, 1024px, 1280px.
2. Limiter la profondeur des sélecteurs à 3 niveaux pour garder la lisibilité.
3. Utiliser les variables CSS définies dans `:root` (couleurs, espacements) plutôt que des valeurs magiques.
4. Prévoir des fallbacks pour `prefers-reduced-motion`.
5. Documenter les sections majeures via commentaires normalisés `/* === Section === */`.

## Process de mise à jour

```bash
# Vérifier la qualité CSS
npx stylelint "public/assets/css/**/*.css"

# Minifier (optionnel)
npx clean-css-cli -o public/assets/css/common.min.css public/assets/css/common.css
```

## Intégration PHP

Les feuilles de style sont référencées depuis `public/templates/header.php` via `<link rel="stylesheet" href="/assets/css/common.css">`.
