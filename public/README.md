# Documentation du répertoire `public`

Ce dossier contient tous les points d'entrée HTTP ainsi que les ressources statiques exposées par le serveur OVH.

## Points d'entrée PHP

| Fichier | Rôle |
| --- | --- |
| `index.php` | Page d'accueil (widgets club, derniers résultats, CTA licences). |
| `calendrier.php` | Calendrier général des rencontres. |
| `classements.php` | Classements compétitions avec filtres. |
| `classement.php` | Alias historique du classement principal. |
| `contact.php` | Formulaire de contact et infos club. |
| `equipes.php` | Vue synthétique des équipes et staff. |
| `equipes/` | Pages détaillées par équipe (sous-dossier). |
| `galerie.php` | Listing des galeries médias. |
| `matchs.php` | Focus matchs à venir. |
| `resultats.php` | Historique des résultats terminés. |
| `partenaires.php` | Présentation sponsors. |
| `bootstrap.php` | Bootstrap public pour charger config + dépendances.
| `api/` | Endpoints JSON exposés (voir README dédié).

## Ressources statiques

- `assets/css/` : feuilles de styles compilées.
- `assets/js/` : scripts front vanilla (buildés avec validations).
- `assets/images/` : médias optimisés (WebP + fallback JPEG).
- `assets/docs/` : documents téléchargeables (PDF, etc.).
- `templates/` : fragments HTML partagés (header, footer, composants).
- `manifest.json` + `service-worker.js` : support PWA.

## Bonnes pratiques

- **Sécurité** : inclure `public/bootstrap.php` en ouverture de chaque page pour initialiser la configuration et activer les protections.
- **Cache** : référencer les assets avec hash (`?v=2025XXXX`) afin d'éviter le cache agressif OVH.
- **Accessibilité** : toutes les images doivent avoir un `alt` descriptif.
- **PWA** : maintenir la cohérence entre `manifest.json` et `service-worker.js` (icônes, scope, version de cache).

## Déploiement

- Le sous-dossier `public/` est la racine web publiée sur OVH lors de chaque push Git.
- Toute ressource ajoutée doit être testée via HTTPS avant validation.
- Utiliser `npm run build` (si pipeline front) avant de pousser afin d'avoir les assets minifiés.
