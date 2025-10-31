# Templates globaux (`templates`)

Ce dossier héberge les maquettes HTML utilisées pour le prototypage ou le rendu côté serveur (hors racine web).

## Contenu actuel

- `index.html` : prototype statique de la page d'accueil.

## Utilisation

- Servir de référence UX/UI pour le développement des vues PHP.
- Tester les évolutions design avant intégration dans `public/templates/`.
- Peut être consommé par un générateur statique pour A/B testing.

## Bonnes pratiques

- Garder les assets relatifs (`/public/assets/...`) pour correspondance directe.
- Documenter tout composant ajouté via des commentaires HTML succincts.
- Synchroniser les modifications avec l'équipe design via Git (pull request dédiée).
