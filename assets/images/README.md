# Documentation `public/assets/images`

Banque d'images optimisées pour le site FC Chiche.

## Sous-dossiers

- `sponsors/` : logos partenaires (fond transparent, largeur max 600px).
- `boutique/` : visuels produits boutique officielle.
- `galeries/` : photos d'événements (classées par slug de galerie).

## Bonnes pratiques

- Format prioritaire : WebP (qualité 85). Fournir un fallback JPG si nécessaire.
- Compression obligatoire via l'outil d'optimisation standard de l'équipe (TinyPNG CLI ou `npm run optimize:images`) avant commit.
- Renommer les fichiers `yyyy-mm-dd_evenement_description.webp`.
- Stocker les originaux hors dépôt (drive interne) pour éviter les surcharges Git.
- Vérifier les droits d'image (autorisation parents / joueurs) avant publication.
