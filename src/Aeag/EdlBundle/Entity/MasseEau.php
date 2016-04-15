<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MasseEau
 *
 * @ORM\Table(name="masse_eau")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\MasseEauRepository")
 */
class MasseEau
{
    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
      */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_masse_eau", type="string", length=250, nullable=false)
     */
    private $nomMasseEau;

    /**
     * @var string
     *
     * @ORM\Column(name="type_me", type="string", length=2, nullable=false)
     */
    private $typeMe;



    /**
     * Get euCd
     *
     * @return string
     */
    public function getEuCd()
    {
        return $this->euCd;
    }

    /**
     * Set nomMasseEau
     *
     * @param string $nomMasseEau
     *
     * @return masseEau
     */
    public function setNomMasseEau($nomMasseEau)
    {
        $this->nomMasseEau = $nomMasseEau;

        return $this;
    }

    /**
     * Get nomMasseEau
     *
     * @return string
     */
    public function getNomMasseEau()
    {
        return $this->nomMasseEau;
    }

    /**
     * Set typeMe
     *
     * @param string $typeMe
     *
     * @return masseEau
     */
    public function setTypeMe($typeMe)
    {
        $this->typeMe = $typeMe;

        return $this;
    }

    /**
     * Get typeMe
     *
     * @return string
     */
    public function getTypeMe()
    {
        return $this->typeMe;
    }
}
