<?php
declare(strict_types=1);

namespace iutnc\deefy\action;

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\auth\AuthnProvider;
use iutnc\deefy\auth\Authz;

class ListPlaylistsAction extends Action {

    public function __invoke(): string {
        if ($this->http_method !== 'GET') return "<p>Requête non prise en charge.</p>";

        $user = AuthnProvider::getSignedInUser();
        Authz::checkRole(1);

        $repo = DeefyRepository::getInstance();
        $playlists = $repo->findPlaylistsByUser($user['email']);

        if (empty($playlists)) return "<p>Vous n'avez aucune playlist.</p>";

        $html = "<ul>";
        for ($i = 0; $i < count($playlists); $i++) {
            $html .= '<li><a href="?action=playlist&id=' . $playlists[$i]->getID() . '">'
                  . htmlspecialchars($playlists[$i]->nom) . '</a></li>';
        }
        $html .= "</ul>";

        return $html;
    }
}
