<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="annees")
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\AnneeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Annee {

    /**
     * @ORM\Id
     * @ORM\Column(name="annee", type="integer", length=4)
     * @Assert\NotBlank()
     */
    private $annee;

    public function getAnnee() {
        return $this->annee;
    }

    public function setAnnee($annee) {
        $this->annee = $annee;
    }

}
