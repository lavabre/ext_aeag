<?php

/**
 * Description of Departement
 *
 * @author lavabre
 */

namespace Aeag\AeagBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Departement",indexes={@ORM\Index(name="dept_idx", columns={"dept"})})
 * @ORM\Entity(repositoryClass="Aeag\AeagBundle\Repository\DepartementRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Departement {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=3)
     *
     * @Assert\NotBlank()
     */
    private $dept;

    /**
     * @ORM\Column(type="string", length=25)
     *
     * @Assert\NotBlank()
     */
    private $libelle;

    /**
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Aeag\AeagBundle\Entity\Region")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reg", referencedColumnName="reg")
     * })
     */
    private $Region;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
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

    public function getDept() {
        return $this->dept;
    }

    public function getLibelle() {
        return $this->libelle;
    }

    public function getRegion() {
        return $this->Region;
    }

    public function getDec() {
        return $this->dec;
    }

    public function getCreated() {
        return $this->created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setDept($dept) {
        $this->dept = $dept;
    }

    public function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

    public function setRegion($Region) {
        $this->Region = $Region;
    }

    public function setDec($dec) {
        $this->dec = $dec;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

    /**
     * @return string
     */
    public function getDeptLibelle() {
        return $this->dept . ' : ' . $this->libelle;
    }

}
