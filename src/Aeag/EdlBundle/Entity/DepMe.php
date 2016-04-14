<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\DepMe
 *
 * @ORM\Table(name="departement_me")
 * @ORM\Entity
 */
class DepMe
{
    /**
     * @var string $inseeDepartement
     *
     * @ORM\Column(name="insee_departement", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $inseeDepartement;

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    public function getInseeDepartement() {
        return $this->inseeDepartement;
    }

    public function setInseeDepartement($inseeDepartement) {
        $this->inseeDepartement = $inseeDepartement;
    }

    public function getEuCd() {
        return $this->euCd;
    }

    public function setEuCd($euCd) {
        $this->euCd = $euCd;
    }


}