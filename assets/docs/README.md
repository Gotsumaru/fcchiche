# Documents téléchargeables (`public/assets/docs`)

## Rôle

Stocker les PDF et documents officiels mis à disposition (formulaires d'inscription, règlements, chartes...).

## Contraintes

- Formats acceptés : PDF, DOCX convertis en PDF, XLSX si indispensable.
- Nommer `aaaa-mm_titre-document.pdf`.
- Vérifier que chaque document possède une date de validité.
- Ajouter le lien correspondant dans la section concernée (footer, page dédiée) en éditant `public/templates/header.php`/`footer.php` ou les pages associées.

## Sécurité

- Désactiver les permissions d'écriture publique (`chmod 640`).
- Vérifier l'absence de données personnelles sensibles avant publication.
