<?php

/**
 * Description of  OuvrageCorrespondant
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="OuvrageCorrespondant", indexes={@ORM\Index(name="ouvragecorrespondant_idx", columns={"ouvrage_id"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\OuvrageCorrespondantRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class OuvrageCorrespondant {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="ouvragecorrespondant_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @var Ouvrage
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Ouvrage" )
     * @ORM\JoinColumn(name="ouvrage_id", referencedColumnName="id")
     */
    private $Ouvrage;

    /**
     * @var Correspondant
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Correspondant" )
     * @ORM\JoinColumn(name="correspondant_id", referencedColumnName="id")
     */
    private $Correspondant;

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

    public function getOuvrage() {
        return $this->Ouvrage;
    }

    public function setOuvrage(Ouvrage $Ouvrage) {
        $this->Ouvrage = $Ouvrage;
    }

    public function getCorrespondant() {
        return $this->Correspondant;
    }

    public function setCorrespondant(Correspondant $Correspondant) {
        $this->Correspondant = $Correspondant;
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

}

