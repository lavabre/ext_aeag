<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgWebuserTypmil
 *
 * @ORM\Table(name="pg_prog_user_typmil")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgWebuserTypmilRepository")
 */
class PgProgWebuserTypmil {

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
     * @var \PgProgTypeMilieu
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgProgTypeMilieu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_milieu", referencedColumnName="code_milieu")
     * })
     */
    private $typmil;

    /*
     * Constructor
     */

    public function __construct() {
        
    }

    function getWebuser() {
        return $this->webuser;
    }

    function getTypmil() {
        return $this->typmil;
    }

  

    /**
     * Set webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgProgWebuserTypmil
     */
    public function setWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser)
    {
        $this->webuser = $webuser;

        return $this;
    }

    /**
     * Set typmil
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypeMilieu $typmil
     * @return PgProgWebuserTypmil
     */
    public function setTypmil(\Aeag\SqeBundle\Entity\PgProgTypeMilieu $typmil)
    {
        $this->typmil = $typmil;

        return $this;
    }
}
