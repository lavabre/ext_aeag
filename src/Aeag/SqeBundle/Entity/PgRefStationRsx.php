<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgRefStationRsx
 *
 * @ORM\Table(name="pg_ref_station_rsx")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgRefStationRsxRepository")
 */
class PgRefStationRsx {

    /**
     * @var \PgRefStationMesure
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgRefStationMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ouv_fonc_id", referencedColumnName="ouv_fonc_id")
     * })
     */
    private $stationMesure;

    /**
     * @var \PgRefReseauMesure
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgRefReseauMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="groupement_id", referencedColumnName="groupement_id")
     * })
     */
    private $reseauMesure;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getStationMesure() {
        return $this->stationMesure;
    }

    function getReseauMesure() {
        return $this->reseauMesure;
    }

   


    /**
     * Set stationMesure
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $stationMesure
     * @return PgRefStationRsx
     */
    public function setStationMesure(\Aeag\SqeBundle\Entity\PgRefStationMesure $stationMesure)
    {
        $this->stationMesure = $stationMesure;

        return $this;
    }

    /**
     * Set reseauMesure
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $reseauMesure
     * @return PgRefStationRsx
     */
    public function setReseauMesure(\Aeag\SqeBundle\Entity\PgRefReseauMesure $reseauMesure)
    {
        $this->reseauMesure = $reseauMesure;

        return $this;
    }
}
