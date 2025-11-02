<?php
declare(strict_types=1);

namespace iutnc\deefy\auth;

use PDO;
use iutnc\deefy\exception\AuthnException;

class Authz {

    public static function checkRole(int $roleAttendu): void {
        $user = AuthnProvider::getSignedInUser();
        $email = $user['email'];

        $pdo = new PDO('mysql:host=localhost;dbname=deefy;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) throw new AuthnException("Utilisateur introuvable.");

        $roleUtilisateur = (int)$row['role'];
        if ($roleUtilisateur !== $roleAttendu && $roleUtilisateur !== 100) {
            throw new AuthnException("Accès refusé : rôle insuffisant");
        }
    }

    public static function checkPlaylistOwner(int $idPlaylist): void {
        $user = AuthnProvider::getSignedInUser();
        $email = $user['email'];

        $pdo = new PDO('mysql:host=localhost;dbname=deefy;charset=utf8', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupérer id et rôle de l'utilisateur
        $stmt = $pdo->prepare("SELECT id, role FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new AuthnException("Utilisateur introuvable.");
        }

        $idUser = (int)$row['id'];
        $role = (int)$row['role'];

        if ($role === 100) return; // Admin a accès

        // Vérifier que l'utilisateur est propriétaire de la playlist
        $stmt2 = $pdo->prepare("SELECT * FROM user2playlist WHERE id_user = ? AND id_pl = ?");
        $stmt2->execute([$idUser, $idPlaylist]);
        $check = $stmt2->fetch(PDO::FETCH_ASSOC);

        if (!$check) {
            throw new AuthnException("Accès refusé : vous n'êtes pas propriétaire de cette playlist");
        }
    }

}
