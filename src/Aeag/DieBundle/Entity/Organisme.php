<?php

/**
 * Description de Organisme
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="organisme")
 * @ORM\Entity(repositoryClass="Aeag\DieBundle\Repository\OrganismeRepository")
 */
class Organisme {

     /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="organisme_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @ORM\Column(name="organisme", type="string", length=100)
     * @Assert\NotBlank(message = "L'organisme est obligatoire")
     * @assert\LessThan(100)
     */
    private $organisme;

    /**
     * @ORM\Column(name="ordre", type="integer", length=2, nullable=true)
     */
    private $ordre;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getOrganisme() {
        return $this->organisme;
    }

    public function setOrganisme($organisme) {
        $this->organisme = $organisme;
    }

    public function getOrdre() {
        return $this->ordre;
    }

    public function setOrdre($ordre) {
        $this->ordre = $ordre;
    }

}