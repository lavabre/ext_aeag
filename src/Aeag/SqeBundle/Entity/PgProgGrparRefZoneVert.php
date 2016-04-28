<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgGrparRefZoneVert
 *
 * @ORM\Table(name="pg_prog_grpar_ref_zone_vert")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgGrparRefZoneVertRepository")
 */
class PgProgGrparRefZoneVert {

    /**
     * @var \PgProgGrpParamRef
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgGrpParamRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     * })
     */
    private $pgProgGrpParamRef;

    /**
     * @var \PgSandreZoneVerticaleProspectee
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgSandreZoneVerticaleProspectee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_zone_vert", referencedColumnName="codeZone")
     * })
     */
    private $pgSandreZoneVerticaleProspectee;

    /**
     * @var string
     *
     * @ORM\Column(name="typ_class_prof", type="string", length=3, nullable=false)
     */
    private $typClassProf;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getPgProgGrpParamRef() {
        return $this->pgProgGrpParamRef;
    }

    function getPgSandreZoneVerticaleProspectee() {
        return $this->pgSandreZoneVerticaleProspectee;
    }

    function setPgProgGrpParamRef($pgProgGrpParamRef) {
        $this->pgProgGrpParamRef = $pgProgGrpParamRef;
    }

    function setPgSandreZoneVerticaleProspectee($pgSandreZoneVerticaleProspectee) {
        $this->pgSandreZoneVerticaleProspectee = $pgSandreZoneVerticaleProspectee;
    }

    function getTypClassProf() {
        return $this->typClassProf;
    }

    function setTypClassProf($typClassProf) {
        $this->typClassProf = $typClassProf;
    }

}
