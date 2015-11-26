<?php

/**
 * Description of Ouvrage
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Entity\form;

use Symfony\Component\Validator\Constraints as Assert;
use Aeag\DecBundle\Entity\Commune;

class MajOuvrage {

    private $ouvId;
    private $numero;

    /**
     * @Assert\NotBlank(message="Le libelle est obligatoire")
     */
    private $libelle;
    private $dec;

    /**
     *
     * @Assert\Regex(pattern="/^[0-9]{14,14}?$/", message="Le siret doit faire 14 caractères"))
     * @Assert\NotBlank(message=" Le siret est obligatoire")
     */
    private $siret;

    public function getOuvId() {
        return $this->ouvId;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getDec() {
        return $this->dec;
    }

    public function getSiret() {
        return $this->siret;
    }

    public function setOuvId($ouvId) {
        $this->ouvId = $ouvId;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setDec($dec) {
        $this->dec = $dec;
    }

    public function setSiret($siret) {
        $this->siret = $siret;
    }

}
