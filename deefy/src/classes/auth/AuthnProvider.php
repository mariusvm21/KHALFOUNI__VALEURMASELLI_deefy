<?php
declare(strict_types=1);

namespace iutnc\deefy\auth;

use iutnc\deefy\exception\AuthnException;
use iutnc\deefy\repository\DeefyRepository;
use PDO;

class AuthnProvider {

    private static function getPDO(): PDO {
        return DeefyRepository::getInstance()->getPDO();
    }

    public static function signin(string $email, string $passwdVerif): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        try {
            $pdo = self::getPDO();

            $stmt = $pdo->prepare("SELECT passwd, role FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) throw new AuthnException("Utilisateur inconnu.");
            if (!password_verify($passwdVerif, $row['passwd'])) throw new AuthnException("Mot de passe incorrect.");

            $_SESSION['user'] = $email;
            $_SESSION['user_role'] = (int)$row['role'];

        } catch (\PDOException $e) {
            throw new AuthnException("Erreur BDD : " . $e->getMessage());
        }
    }

    public static function register(string $email, string $pass): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new AuthnException("Email invalide.");
        if (strlen($pass) < 10) throw new AuthnException("Mot de passe trop court.");

        try {
            $pdo = self::getPDO();

            $stmt = $pdo->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) throw new AuthnException("Email déjà utilisé.");

            $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost'=>12]);
            $stmt = $pdo->prepare("INSERT INTO user (email, passwd, role) VALUES (?, ?, 1)");
            $stmt->execute([$email, $hash]);

        } catch (\PDOException $e) {
            throw new AuthnException("Erreur BDD : " . $e->getMessage());
        }
    }

    public static function getSignedInUser(): array {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user']) || !isset($_SESSION['user_role'])) {
            throw new AuthnException("Aucun utilisateur connecté.");
        }
        return [
            'email' => $_SESSION['user'],
            'role' => (int)$_SESSION['user_role']
        ];
    }
}
