<?php
declare(strict_types=1);

namespace iutnc\deefy\repository;

use PDO;
use iutnc\deefy\audio\lists\Playlist;

class DeefyRepository
{
    private PDO $pdo;
    private static ?DeefyRepository $instance = null;

    // Méthode pour configurer le repository depuis le fichier .ini
    public static function setConfig(string $file): void
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new \Exception("Impossible de lire le fichier de config : $file");
        }

        $conf = parse_ini_file($file);
        if ($conf === false) {
            throw new \Exception("Erreur lecture du fichier de config");
        }

        $dbName = $conf['dbname'] ?? $conf['database'] ?? null;
        $user = $conf['username'] ?? $conf['user'] ?? '';
        $pass = $conf['password'] ?? $conf['pass'] ?? '';
        $dsn = $conf['driver'] . ':host=' . $conf['host'] . ';dbname=' . $dbName . ';charset=utf8';

        self::$instance = new self($dsn, $user, $pass);
    }

    private function __construct(string $dsn, string $user, string $pass)
    {
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            throw new \Exception("DeefyRepository non configuré !");
        }
        return self::$instance;
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }

    public function findPlaylistsByUser(string $email): array
    {
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
            $pl->setID((int) $row['id']);
            $playlists[] = $pl;
        }
        return $playlists;
    }

    public function saveEmptyPlaylist(\iutnc\deefy\audio\lists\Playlist $pl, int $id_user): \iutnc\deefy\audio\lists\Playlist
    {
        // Insertion dans playlist
        $stmt = $this->pdo->prepare("INSERT INTO playlist (nom) VALUES (:nom)");
        $stmt->execute(['nom' => $pl->nom]);

        // Récupération de l'ID de la playlist
        $id_pl = (int) $this->pdo->lastInsertId();
        $pl->setID($id_pl);

        // Liaison avec l'utilisateur
        $stmt2 = $this->pdo->prepare("INSERT INTO user2playlist (id_user, id_pl) VALUES (:id_user, :id_pl)");
        $stmt2->execute(['id_user' => $id_user, 'id_pl' => $id_pl]);

        return $pl;
    }
}
