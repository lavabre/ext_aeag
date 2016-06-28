<?php

/**
 * Description de Sous-Theme
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="sousTheme")
 * @ORM\Entity(repositoryClass="Aeag\DieBundle\Repository\SousThemeRepository")
 */
class SousTheme {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="soustheme_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theme" )
     */
    private $theme;

    /**
     * @ORM\Column(name="soustheme", type="string", length=150)
     * @Assert\NotBlank(message = "Le sous-thème est obligatoire")
     * @assert\Length(max=150)
     */
    private $sousTheme;

    /**
     * @ORM\Column(name="destinataire", type="string")
     * @Assert\NotBlank(message = "L'adresse mail du destinataire est obligatoire")
     * @assert\Email(message = "L'adresse mail du destinataire est invalide")
     */
    private $destinataire;

    /**
     * @ORM\Column(name="objet", type="string", length=200)
     * @Assert\NotBlank(message = "L'objet est obligatoire")
     * @assert\Length(max=200)
     */
    private $objet;

    /**
     * @ORM\Column(name="corps", type="text")
     * @Assert\NotBlank(message = "Le corps est obligatoire")
     */
    private $corps;

    /**
     * @ORM\Column(name="echeance", type="integer")
     */
    private $echeance;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public function getSousTheme() {
        return $this->sousTheme;
    }

    public function setSousTheme($sousTheme) {
        $this->sousTheme = $sousTheme;
    }

    public function getDestinataire() {
        return $this->destinataire;
    }

    public function setDestinataire($destinataire) {
        $this->destinataire = $destinataire;
    }

    public function getObjet() {
        return $this->objet;
    }

    public function setObjet($objet) {
        $this->objet = $objet;
    }

    public function getCorps() {
        return $this->corps;
    }

    public function setCorps($corps) {
        $this->corps = $corps;
    }

    public function getEcheance() {
        return $this->echeance;
    }

    public function setEcheance($echeance) {
        $this->echeance = $echeance;
    }

}
