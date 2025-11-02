<?php
declare(strict_types=1);
namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class SignInAction extends Action {

    public function __invoke() : string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Méthode GET : affiche le formulaire
        if ($this->http_method === 'GET') {
            return <<<FIN
                <h2>Connexion</h2>
                <form method="post" action="?action=sign-in">
                    <label for="email">Email :</label>
                    <input type="email" name="email" id="email" required>
                    <br>
                    <label for="password">Mot de passe :</label>
                    <input type="password" name="password" id="password" required>
                    <br>
                    <button type="submit">Se connecter</button>
                </form>
            FIN;
        } 

        // Méthode POST : vérifie les identifiants
        else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return "<p>Email invalide.</p>";
            }

            try {
                AuthnProvider::signin($email, $password);
                return "<p>Authentification réussie ! Bienvenue, " . htmlspecialchars($email) . ".</p>";
            } catch (AuthnException $e) {
                return "<p>Erreur : " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
