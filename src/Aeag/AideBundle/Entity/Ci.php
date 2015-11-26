<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="ci",indexes={@ORM\Index(name="ci_idx", columns={"annee","numero"})})
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\CiRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Ci {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="ci_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=4)
     * 
     * @Assert\NotBlank()
     */
    private $annee;

    /**
     * @ORM\Column(type="integer", length=8)
     * 
     * @Assert\NotBlank()
      */
    private $numero;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank()
     */
    private $date;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function setNumero($numero) {
        $this->numero = $numero;
    }

    public function getDate() {
        return $this->date;
    }

    public function setDate($date) {
        $this->date = $date;
    }

}
