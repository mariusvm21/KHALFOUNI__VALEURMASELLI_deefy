<?php
declare(strict_types=1);

namespace iutnc\deefy\repository;

use iutnc\deefy\audio\lists\Playlist;
use PDO;

class DeefyRepository {
    private PDO $pdo;
    private static ?DeefyRepository $instance = null;

    private function __construct() {
        $this->pdo = new PDO('mysql:host=localhost;dbname=deefy;charset=utf8', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function findPlaylistsByUser(string $email): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.nom
            FROM playlist p
            JOIN user2playlist up ON up.id_pl = p.id
            JOIN user u ON u.id = up.id_user
            WHERE u.email = ?
        ");
        $stmt->execute([$email]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $playlists = [];
        foreach ($rows as $row) {
            $pl = new Playlist($row['nom']);
            $pl->setID((int)$row['id']);
            $playlists[] = $pl;
        }
        return $playlists;
    }

    public function saveEmptyPlaylist(Playlist $pl, int $id_user): Playlist {
        // Insertion dans playlist
        $stmt = $this->pdo->prepare("INSERT INTO playlist (nom) VALUES (:nom)");
        $stmt->execute(['nom' => $pl->nom]);

        // Récupération de l'ID de la playlist
        $id_pl = (int)$this->pdo->lastInsertId();
        $pl->setID($id_pl);

        // Liaison avec l'utilisateur
        $stmt2 = $this->pdo->prepare("INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :id_pl)");
        $stmt2->execute(['id_user' => $id_user, 'id_pl' => $id_pl]);

        return $pl;
    }

    public function findPlaylistById(int $id): Playlist {
    $stmt = $this->pdo->prepare("
        SELECT p.id, p.nom
        FROM playlist p
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new \Exception("Playlist introuvable");
    }

    $playlist = new Playlist($row['nom']);
    $playlist->setID((int)$row['id']);

    return $playlist;
}

}
