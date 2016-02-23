<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgWebuserZgeoref
 *
 * @ORM\Table(name="pg_prog_webuser_zgeoref")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgWebuserZgeorefRepository")
 */
class PgProgWebuserZgeoref {

    /**
     * @var \PgProgWebusers
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;

    /**
     * @var \PgProgZoneGeoRef
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgZoneGeoRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     * })
     */
    private $zgeoref;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getWebuser() {
        return $this->webuser;
    }

    function getZgeoref() {
        return $this->zgeoref;
    }

  

    /**
     * Set webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgWebuserZgeoref
     */
    public function setWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Set zgeoref
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoref
     * @return PgProgWebuserZgeoref
     */
    public function setZgeoref(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoref)
    {
        $this->zgeoref = $zgeoref;

        return $this;
    }
}
