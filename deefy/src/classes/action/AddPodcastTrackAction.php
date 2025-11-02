<?php
declare(strict_types=1);
namespace iutnc\deefy\action;

use iutnc\deefy\audio\lists\PlayList;
use iutnc\deefy\audio\tracks\PodcastTrack;
use iutnc\deefy\render\AudioListRenderer;
use iutnc\deefy\render\Renderer;

class AddPodcastTrackAction extends Action {

    public function __invoke() : string {

        // affichage du formulaire 
        if ($this->http_method === 'GET') {
            $html = <<<FIN
                <form method="post" action="?action=add-track">
                    <label for="titre">Titre :</label>
                    <input type="text" name="titre" id="titre" required>

                    <label for="auteur">Auteur :</label>
                    <input type="text" name="auteur" id="auteur" required>

                    <label for="duree">Durée (en secondes) :</label>
                    <input type="number" name="duree" id="duree" required>

                    <label for="userfile">Fichier audio (MP3) :</label>
                    <input type="file" name="userfile" id="userfile" required>

                    <button type="submit">Ajouter la piste</button>
                </form>
                FIN;
            return $html;

        // traitement du formulaire
        } else {
            // nettoyage des champs
            $titre = filter_var($_POST['titre'], FILTER_SANITIZE_SPECIAL_CHARS);
            $auteur = filter_var($_POST['auteur'], FILTER_SANITIZE_SPECIAL_CHARS);
            $duree = filter_var($_POST['duree'], FILTER_SANITIZE_NUMBER_INT);

            $upload_dir = 'C:\xampp\htdocs\td12\src\mp3';
            $filename=uniqid();
            $tmp=$_FILES['fichier']['tmp_name'];
            if (
                $_FILES['fichier']['tmp_name'] === UPLOAD_ERR_OK
                && substr($_FILES['fichier']['tmp_name'], -4) === '.mp3'
                && $_FILES['fichier']['tmp_name'] === 'audio/mpeg'
            ) {
                $chemin=$upload_dir.$filename.'.mp3' ;
                if (move_uploaded_file($tmp, $chemin)) {
                    print "téléchargement terminé avec succès<br>";
                    $_SESSION['playlist']->ajouterPiste(new ALbumTrack($titre, $chemin));
                }
            }

            // création de la track
            $track = new PodcastTrack($titre, $auteur, (int)$duree);

            // récupérer la playlist dans la session
            $playlist = $_SESSION['playlist'];


            if ($playlist instanceof PlayList) {
                // ajouter la nouvelle track    
                $playlist->ajouterPiste($track);

                // sauvegarde dans la session
                $_SESSION['playlist'] = $playlist;

                // afficher la playlist avec AudioListRenderer
                $renderer = new AudioListRenderer($playlist);
                $html_playlist = $renderer->render(Renderer::COMPACT);

                // ajout du lien pour encore ajouter une piste
                $html_playlist .= '<a href="?action=add-track">Ajouter encore une piste</a>';

                return $html_playlist;
            } else {
                return "<p>Erreur : aucune playlist en session</p>";
            }

        }    
        
    }
}
