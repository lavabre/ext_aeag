<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Taux
 *
 * @author lavabre
 */

namespace Aeag\DecBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="producteurTauxSpecial",indexes={@ORM\Index(name="producteurTauxSpecial_idx", columns={"siret"})})
 * @ORM\Entity(repositoryClass="Aeag\DecBundle\Repository\ProducteurTauxSpecialRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class ProducteurTauxSpecial {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="producteurtauxspecial_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=14)
     *
     * @Assert\NotBlank()
     */
    private $siret;

    /**
     * @ORM\Column(type="string", length=100)
     *
     */
    private $raisonSociale;

    /**
     * @ORM\Column(type="string", length=100)
     *
     */
    private $localisation;

    /**
     * @ORM\Column(type="float",nullable=false)
     *
     */
    private $taux;

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

    function getId() {
        return $this->id;
    }

    function getSiret() {
        return $this->siret;
    }

    function getRaisonSociale() {
        return $this->raisonSociale;
    }

    function getLocalisation() {
        return $this->localisation;
    }

    function getTaux() {
        return $this->taux;
    }

    function getCreated() {
        return $this->created;
    }

    function getUpdated() {
        return $this->updated;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setSiret($siret) {
        $this->siret = $siret;
    }

    function setRaisonSociale($raisonSociale) {
        $this->raisonSociale = $raisonSociale;
    }

    function setLocalisation($localisation) {
        $this->localisation = $localisation;
    }

    function setTaux($taux) {
        $this->taux = $taux;
    }

    function setCreated($created) {
        $this->created = $created;
    }

    function setUpdated($updated) {
        $this->updated = $updated;
    }

}
