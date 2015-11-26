<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgStatut
 *
 * @ORM\Table(name="pg_prog_statut")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgStatutRepository")
 */
class PgProgStatut
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_statut", type="string", length=3, nullable=false)
     * @ORM\Id
    */
    private $codeStatut;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_statut", type="string", length=50, nullable=false)
     */
    private $libelleStatut;



    /**
     * Get codeStatut
     *
     * @return string 
     */
    public function getCodeStatut()
    {
        return $this->codeStatut;
    }

    /**
     * Set libelleStatut
     *
     * @param string $libelleStatut
     * @return PgProgStatut
     */
    public function setLibelleStatut($libelleStatut)
    {
        $this->libelleStatut = $libelleStatut;

        return $this;
    }

    /**
     * Get libelleStatut
     *
     * @return string 
     */
    public function getLibelleStatut()
    {
        return $this->libelleStatut;
    }
}
