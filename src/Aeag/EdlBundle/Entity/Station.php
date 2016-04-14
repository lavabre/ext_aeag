<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Station
 *
 * @ORM\Table(name="station")
 * @ORM\Entity
 */
class Station
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_station", type="string", length=8, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="station_code_station_seq", allocationSize=1, initialValue=1)
     */
    private $codeStation;



    /**
     * Get codeStation
     *
     * @return string
     */
    public function getCodeStation()
    {
        return $this->codeStation;
    }
}
