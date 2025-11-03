<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\Playlist;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\repository\DeefyRepository;

class AddPlaylistAction extends Action {

    public function __invoke(): string {

        if ($this->http_method === 'GET') {
            return <<<HTML
                <form method="post" action="?action=add-playlist">
                    <label for="nom">Nom de la playlist :</label>
                    <input type="text" name="nom" id="nom" required>
                    <button type="submit">Créer</button>
                </form>
            HTML;
        }

        // Traitement du formulaire POST
        else {
            $nom = filter_var($_POST['nom'], FILTER_SANITIZE_SPECIAL_CHARS);

            // Récupération de l'utilisateur connecté
            $user = AuthnProvider::getSignedInUser();
            $email = $user['email'];

            // Récupération du PDO via DeefyRepository
            $repo = DeefyRepository::getInstance();
            $pdo = $repo->getPDO();

            // Récupération de l'id_user depuis la base
            $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) return "<p>Erreur : utilisateur introuvable.</p>";
            $id_user = (int)$row['id'];

            // Création de la playlist
            $playlist = new Playlist($nom);

            // Sauvegarde dans la BD et liaison utilisateur
            $repo->saveEmptyPlaylist($playlist, $id_user);

            // Sauvegarde en session
            $_SESSION['playlist'] = $playlist;

            // Affichage
            $renderer = new AudioListRenderer($playlist);
            $html = $renderer->render(Renderer::COMPACT);
            $html .= '<br><a href="?action=add-track">Ajouter une piste</a>';

            return $html;
        }
    }
}
