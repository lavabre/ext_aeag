<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotGrparRef
 *
 * @ORM\Table(name="pg_prog_lot_grpar_ref", indexes={@ORM\Index(name="IDX_PgProgGrpParamRef", columns={"grpar_ref_id"}), @ORM\Index(name="IDX_PgProgLot", columns={"lot_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotGrparRefRepository")
 */
class PgProgLotGrparRef {

   
    /**
     * @var \PgProgLot
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgLot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;

    /**
     * @var \PgProgGrpParamRef
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgGrpParamRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     * })
     */
    private $grpparref;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getLot() {
        return $this->lot;
    }

    function getGrpparref() {
        return $this->grpparref;
    }
 
    function setLot($lot) {
        $this->lot = $lot;
    }

    function setGrpparref($grpparref) {
        $this->grpparref = $grpparref;
    }


   

}
