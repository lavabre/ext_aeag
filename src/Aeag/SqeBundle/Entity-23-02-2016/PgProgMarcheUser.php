<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgMarcheUser
 *
 * @ORM\Table(name="pg_prog_marche_user")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgMarcheUserRepository")
 */
class PgProgMarcheUser {

   
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
     * @var \PgProgMarche
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgMarche")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="marche_id", referencedColumnName="id")
     * })
     */
    private $marche;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getWebuser() {
        return $this->webuser;
    }

    function getMarche() {
        return $this->marche;
    }

  


    /**
     * Set webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgMarcheUser
     */
    public function setWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Set marche
     *
     * @param \Aeag\SqeBundle\Entity\PgProgMarche $marche
     * @return PgProgMarcheUser
     */
    public function setMarche(\Aeag\SqeBundle\Entity\PgProgMarche $marche)
    {
        $this->marche = $marche;

        return $this;
    }
}
