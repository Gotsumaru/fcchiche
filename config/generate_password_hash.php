<?php
declare(strict_types=1);

/**
 * Script pour générer un hash de mot de passe admin
 * Usage: php config/generate_password_hash.php
 */

echo "\n=== Générateur de Hash Mot de Passe Admin ===\n\n";

// Demander le mot de passe
echo "Entrez le mot de passe admin: ";
$password = trim(fgets(STDIN));

if (empty($password)) {
    die("Erreur: Le mot de passe ne peut pas être vide\n");
}

// Vérifier longueur minimale
if (strlen($password) < 8) {
    echo "⚠️  Attention: Mot de passe court (min 8 caractères recommandé)\n";
}

// Générer le hash
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\n✅ Hash généré avec succès!\n\n";
echo "Copiez ce hash dans src/Utils/ApiAuth.php :\n";
echo "----------------------------------------\n";
echo $hash . "\n";
echo "----------------------------------------\n\n";

// Vérification
echo "Vérification du hash... ";
if (password_verify($password, $hash)) {
    echo "✅ OK\n";
} else {
    echo "❌ ERREUR\n";
}

echo "\nÀ copier dans ApiAuth.php:\n";
echo "private static function getPasswordHash(): string\n";
echo "{\n";
echo "    return '" . $hash . "';\n";
echo "}\n\n";

// Générer aussi un token API aléatoire
echo "Token API suggéré (optionnel):\n";
echo "----------------------------------------\n";
echo bin2hex(random_bytes(32)) . "\n";
echo "----------------------------------------\n\n";

echo "Installation terminée!\n\n";