<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Aeag\EdlBundle\Entity\Station
 *
 * @ORM\Table(name="station")
 * @ORM\Entity
 */
class Station
{

    /**
     * @var string $codeStation
     *
     * @ORM\Column(name="code_station", type="string", length=8, nullable=false)
     * @ORM\Id
       */
    private $codeStation;

    /**
     * @var MasseEau
     *
     * @ORM\ManyToMany(targetEntity="MasseEau", mappedBy="codeStation")
     */
    private $euCd;

    public function __construct()
    {
        $this->euCd = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

    /**
     * Get codeStation
     *
     * @return string 
     */
    public function getCodeStation()
    {
        return $this->codeStation;
    }

    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addEuCd(\Aeag\EdlBundle\Entity\MasseEau $euCd)
    {
        $this->euCd[] = $euCd;
    }

    /**
     * Get euCd
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEuCd()
    {
        return $this->euCd;
    }


    /**
     * Add euCd
     *
     * @param Aeag\EdlBundle\Entity\MasseEau $euCd
     */
    public function addMasseEau(\Aeag\EdlBundle\Entity\MasseEau $euCd)
    {
        $this->euCd[] = $euCd;
    }
}