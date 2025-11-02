<?php
declare(strict_types=1);
namespace iutnc\deefy\dispatch;

use iutnc\deefy\action;

class Dispatcher
{

    private string $action;

    public function __construct(string $action)
    {
        $this->action = $action;
    }

    public function run(): void
    {
        $html = '';
        switch ($this->action) {
            case 'playlist':
                $html = (new action\DisplayPlaylistAction())();
                break;
            case 'add-playlist':
                $html = (new action\AddPlaylistAction())();
                break;
            case 'add-track':
                $html = (new action\AddPodcastTrackAction())();
                break;
            case 'sign-in':
                $html = (new action\SignInAction())();
                break;
            case 'add-user':
                $html = (new action\AddUserAction())();
                break;
            case 'list-playlists':
                $html = (new action\ListPlaylistsAction())();
                break;
            default:
                $html = (new action\DefaultAction())();
        }
        $this->renderPage($html);
    }

    private function renderPage(string $html): void
    {
        echo <<<FIN
            <!DOCTYPE html>
            <html lang="fr">
                <head>
                    <title>Deefy App</title>
                </head>
                <body>
                    <ul>
                        <li><a href=".">Accueil</a></li>
                        <li><a href="?action=list-playlists">Mes playlists</a></li>
                        <li><a href="?action=playlist">Afficher la playlist</a></li>
                        <li><a href="?action=add-playlist">Créer une playlist en session</a></li>
                        <li><a href="?action=add-track">Ajouter un track dans la playlist</a></li>
                        <li><a href="?action=sign-in">Se connecter</a></li>
                        <li><a href="?action=add-user">Créer un compte</a></li>
                    </ul>
                    $html
                </body>
            </html>
        FIN;
    }
}
