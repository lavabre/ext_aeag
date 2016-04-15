<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepartementMe
 *
 * @ORM\Table(name="departement_me")
 * @ORM\Entity
 */
class DepartementMe
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee_departement", type="string", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $inseeDepartement;

    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;



    /**
     * Set inseeDepartement
     *
     * @param string $inseeDepartement
     *
     * @return departementMe
     */
    public function setInseeDepartement($inseeDepartement)
    {
        $this->inseeDepartement = $inseeDepartement;

        return $this;
    }

    /**
     * Get inseeDepartement
     *
     * @return string
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

    /**
     * Set euCd
     *
     * @param string $euCd
     *
     * @return departementMe
     */
    public function setEuCd($euCd)
    {
        $this->euCd = $euCd;

        return $this;
    }

    /**
     * Get euCd
     *
     * @return string
     */
    public function getEuCd()
    {
        return $this->euCd;
    }
}
