# Documentation du répertoire `public/assets`

Regroupe tous les assets statiques versionnés et servis en production.

## Structure

- `css/` : feuilles de style par page (`common.css`, `index.css`, ...).
- `js/` : scripts JavaScript vanilla (un fichier par page/composant).
- `images/` : médias optimisés (logos, photos, pictos) organisés par sous-catégories.
- `docs/` : documents téléchargeables (PDF, plaquettes, formulaires).

## Guidelines

1. **Poids** : viser < 500 KB par image, préférer WebP. Utiliser `npm run optimize:images` (ou outil équivalent) avant commit.
2. **Naming** : snake_case et sans accents pour éviter les erreurs OVH.
3. **Cache-busting** : ajouter un query param versionné côté templates (`?v=20250115`) lors de modifications majeures.
4. **Accessibilité** : fournir un équivalent texte via `alt` côté templates.
5. **Droits** : vérifier les licences des médias avant publication.

## Synchronisation

- Les assets sont déployés automatiquement via Git (pas d'upload manuel SFTP).
- Nettoyer régulièrement les fichiers orphelins et conserver uniquement les versions utilisées.
