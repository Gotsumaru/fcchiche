# Logos partenaires (`public/assets/images/sponsors`)

## Spécifications

- Fond transparent obligatoire (PNG ou WebP alpha).
- Largeur maximale : 600 px, hauteur maximale : 300 px.
- Palette optimisée pour éviter les banding (utiliser TinyPNG/TinyWebP).
- Nommer `sponsor-nom-marque.webp` (minuscules, tirets).

## Intégration

- Les logos sont consommés par `public/partenaires.php` et le carousel sponsors du footer.
- Ajouter toute nouvelle entrée dans la source de données (table dédiée ou configuration JSON utilisée par le frontend).
- Prévoir une version monochrome si usage sur fond sombre.
