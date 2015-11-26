<?php

/**
 * Description of Ouvrage
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Ouvrage",indexes={@ORM\Index(name="numero_idx", columns={"numero"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\OuvrageRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Ouvrage {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="ouvrage_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=10, nullable=true)
     *
     */
    private $ouvId;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     */
    private $numero;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     */
    private $adresse;

    /**
     *
     * @ORM\Column(name="cp", type="string", length=5, nullable=true)
     */
    private $cp;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     *
     */
    private $ville;

    /**
     * @ORM\Column(type="string", length=10)
     *
     * @Assert\NotBlank()
     */
    private $type;

    /**
     *
     * @ORM\Column(name="siret", type="string", length=14, nullable=true, unique=false)
     * @Assert\Length(min=14,max=14)
     */
    private $siret;

    /**
     * @var Commune
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Commune" )
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    private $Commune;

    /**
     *
     * @ORM\Column(name="naf", type="string", length=10, nullable=true, unique=false)
     * @Assert\Length(min=0,max=10)
     */
    private $naf;

    /**
     * @ORM\Column(type="string",nullable=true)
     *
     */
    private $dec;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     *
     */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

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

    public function setCp($cp) {
        $this->cp = $cp;
    }

    public function getVille() {
        return $this->ville;
    }

    public function setVille($ville) {
        $this->ville = $ville;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    public function getSiret() {
        return $this->siret;
    }

    public function setSiret($siret) {
        $this->siret = $siret;
    }

    public function getCommune() {
        return $this->Commune;
    }

    public function setCommune(Commune $Commune) {
        $this->Commune = $Commune;
    }

    function getNaf() {
        return $this->naf;
    }

    function setNaf($naf) {
        $this->naf = $naf;
    }

    public function getDec() {
        return $this->dec;
    }

    public function setDec($dec) {
        $this->dec = $dec;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

    public function getNumeroLibelle() {
        return $this->numero . ' : ' . $this->libelle;
    }

    public function getLibelleNumero() {
        return $this->libelle . ' (' . $this->numero . ')';
    }

    public function getSiretLibelle() {
        return $this->siret . ' : ' . $this->libelle;
    }

    public function getLibelleSiret() {
        return $this->libelle . ' (' . $this->siret . ')';
    }

    public function getSiretLibelleCpVille() {
        return $this->siret . ' : ' . $this->libelle . '      ' . $this->getCp() . ' ' . $this->getVille();
    }

    public function getLibelleSiretCpVille() {
        return $this->libelle . ' (' . $this->siret . '      ' . $this->getCp() . ' ' . $this->getVille() . ')';
        ;
    }

}
