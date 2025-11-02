<?php
declare(strict_types=1);
namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\Authz;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;

class DisplayPlaylistAction extends Action {

    public function __invoke(): string {
        if ($this->http_method !== 'GET') {
            return "<p>Requête non prise en charge.</p>";
        }

        $repo = DeefyRepository::getInstance();

        // Si un id est fourni en GET -> récupérer la playlist correspondante
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];

            // Contrôle d'accès
            Authz::checkPlaylistOwner($id);

            // Récupérer la playlist depuis la BDD
            try {
                $playlist = $repo->findPlaylistById($id);
            } catch (\Exception $e) {
                return "<p>Playlist introuvable.</p>";
            }

            // Stocker la playlist courante en session
            $_SESSION['playlist'] = $playlist;
        }
        // Sinon, utiliser la playlist stockée en session
        elseif (isset($_SESSION['playlist'])) {
            $playlist = $_SESSION['playlist'];
        }
        else {
            return "<p>Aucune playlist à afficher.</p>";
        }

        // Affichage de la playlist
        $renderer = new AudioListRenderer($playlist);
        return $renderer->render(Renderer::LONG);
    }
}
