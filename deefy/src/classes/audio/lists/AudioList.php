<?php
declare(strict_types=1);

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\audio\tracks\AudioTrack;

class AudioList {
    protected string $nom;
    protected int $nbPistes = 0;
    protected int $dureeTotale = 0;
    protected array $pistes = [];

    public function __construct(string $nom, ?array $pistes = null) {
        $this->nom = $nom;
        $this->pistes = is_array($pistes) ? $pistes : [];
        $this->nbPistes = count($this->pistes);
        $this->dureeTotale = 0;

        foreach ($this->pistes as $piste) {
            if ($piste instanceof AudioTrack) {
                $this->dureeTotale += $piste->duree;
            }
        }
    }

    public function __get($name) {
        if (!is_string($name) || $name === '' || !property_exists($this, $name)) {
            throw new \Exception("invalid property : $name");
        }
        return $this->$name;
    }
}
