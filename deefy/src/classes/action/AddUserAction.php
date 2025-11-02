<?php
declare(strict_types=1);
namespace iutnc\deefy\action;

use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\exception\AuthnException;

class AddUserAction extends Action {

    public function __invoke(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if ($this->http_method === 'GET') {
            return <<<FIN
                <h2>Créer un compte</h2>
                <form method="post" action="?action=add-user">
                    <label for="email">Adresse e-mail :</label>
                    <input type="email" name="email" id="email" required><br><br>

                    <label for="password1">Mot de passe :</label>
                    <input type="password" name="password1" id="password1" required><br><br>

                    <label for="password2">Confirmez le mot de passe :</label>
                    <input type="password" name="password2" id="password2" required><br><br>

                    <button type="submit">S’inscrire</button>
                </form>
            FIN;
        } else {
            $email = trim($_POST['email']);
            $pass1 = $_POST['password1'];
            $pass2 = $_POST['password2'];

            // Vérifie que les deux mots de passe sont identiques
            if ($pass1 !== $pass2) {
                return "<p style='color:red;'>Erreur : les mots de passe ne correspondent pas.</p>";
            }

            try {
                AuthnProvider::register($email, $pass1);
                return "<p>Inscription réussie ! Vous pouvez maintenant vous connecter.</p>";
            } catch (AuthnException $e) {
                return "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
}
