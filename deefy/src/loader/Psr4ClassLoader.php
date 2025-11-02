<?php
declare(strict_types=1);
namespace loader;

class Psr4ClassLoader {

    private string $prefixe;
    private string $racine;

    //prefixe des namespaces
    //répertoire racine
    public function __construct(string $prefixe, string $racine) {
        $this->prefixe = $prefixe;
        $this->racine = $racine;
    }

    public function loadClass() {
        // Fonctionne comme autoloader PSR-4
        return function ($class) {
            // Vérifie si la classe commence par le préfixe
            $len = strlen($this->prefixe);
            if (strncmp($this->prefixe, $class, $len) !== 0) {
                return;
            }

            // Récupère le nom relatif de la classe
            $relativeClass = substr($class, $len);

            // Construit le chemin du fichier
            $file = $this->racine . DIRECTORY_SEPARATOR .
                str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass) . '.php';

            // Si le fichier existe, on le charge
            if (file_exists($file)) {
                require_once $file;
            }
        };
    }

    //enregistrer l'autoloader
    public function registrer() {
    spl_autoload_register($this->loadClass());
    }
}