<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgWebuserRsx
 *
 * @ORM\Table(name="pg_prog_webuser_rsx")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgWebuserRsxRepository")
 */
class PgProgWebuserRsx {

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
     * @var \PgRefReseauMesure
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgRefReseauMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rsx_id", referencedColumnName="groupement_id")
     * })
     */
    private $reseauMesure;

    /**
     * @ORM\Column(name="rsx_defaut", type="string", length=1, nullable=true)
     */
    private $defaut;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getWebuser() {
        return $this->webuser;
    }

    function getReseauMesure() {
        return $this->reseauMesure;
    }

    function getDefaut() {
        return $this->defaut;
    }

   


    /**
     * Set defaut
     *
     * @param string $defaut
     * @return PgProgWebuserRsx
     */
    public function setDefaut($defaut)
    {
        $this->defaut = $defaut;

        return $this;
    }

    /**
     * Set webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgWebuserRsx
     */
    public function setWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Set reseauMesure
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $reseauMesure
     * @return PgProgWebuserRsx
     */
    public function setReseauMesure(\Aeag\SqeBundle\Entity\PgRefReseauMesure $reseauMesure)
    {
        $this->reseauMesure = $reseauMesure;

        return $this;
    }
}
