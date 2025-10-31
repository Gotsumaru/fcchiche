# Galeries photos (`public/assets/images/galeries`)

## Organisation

- Chaque sous-dossier correspond à une galerie (`aaaa-mm-dd_slug-evenement`).
- Contenu : photos compressées (WebP) + JSON de métadonnées si besoin.

## Publication

1. Créer le dossier avec slug unique.
2. Ajouter les photos optimisées (`800px` min côté long).
3. Mettre à jour l'entrée correspondante dans la source de données (table `pprod_galeries` si utilisée, ou JSON consommé par le frontend).
4. Tester l'affichage via `public/galerie.php?slug=xxxx`.

## Règles RGPD

- Obtenir l'accord des personnes photographiées.
- Supprimer toute demande de retrait sous 48h.
- Éviter les mineurs identifiables sans autorisation parentale écrite.
