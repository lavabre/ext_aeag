<?php

/**
 * Description of Ouvrage
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity\Form\Collecteur;

use Symfony\Component\Validator\Constraints as Assert;
use Aeag\DecBundle\Entity\Commune;

class MajProducteur {

    private $ouvId;

     private $numero;

    /**
     * @Assert\NotBlank(message="Le libelle est obligatoire")
     */
    private $libelle;

    private $adresse;

    /**
     * @Assert\Regex(pattern="/^[0-9]{5,5}?$/", message="Le code postal doit faire 5 caractères"))
     * @Assert\Type(type="numeric", message="le code postal doit être une valeur numerique")
     */
    private $cp;

    /**
     *  @Assert\LessThanOrEqual(100,message=" La ville ne doit pas être dépassé 100 caractères")
     *
     */
    private $ville;

     private $Commune;

    /**
     *
     * @Assert\Regex(pattern="/^[0-9]{14,14}?$/", message="Le siret doit faire 14 caractères"))
     * @Assert\NotBlank(message=" Le siret est obligatoire")
     */
    private $siret;

    public function getOuvId() {
        return $this->ouvId;
    }

    public function setOuvId($ouvId) {
        $this->ouvId = $ouvId;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function getAdresse() {
        return $this->adresse;
    }

    public function setAdresse($adresse) {
        $this->adresse = $adresse;
    }

    public function getCp() {
        return $this->cp;
    }

    public function getVille() {
        return $this->ville;
    }

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function setVille($ville) {
        $this->ville = $ville;
    }

    public function getCommune() {
        return $this->Commune;
    }

    public function setCommune($Commune) {
        $this->Commune = $Commune;
    }

    public function getSiret() {
        return $this->siret;
    }

    public function setSiret($siret) {
        $this->siret = $siret;
    }

}
