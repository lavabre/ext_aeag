<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgPhases
 *
 * @ORM\Table(name="pg_prog_phases")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgPhasesRepository")
 */
class PgProgPhases
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_phases_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_phase", type="string", length=3, nullable=false)
     */
    private $codePhase;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle_phase", type="string", length=50, nullable=false)
     */
    private $libellePhase;

   



    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set codePhase
     *
     * @param string $codePhase
     * @return PgProgPhases
     */
    public function setCodePhase($codePhase)
    {
        $this->codePhase = $codePhase;

        return $this;
    }

    /**
     * Get codePhase
     *
     * @return string 
     */
    public function getCodePhase()
    {
        return $this->codePhase;
    }

    /**
     * Set libellePhase
     *
     * @param string $libellePhase
     * @return PgProgPhases
     */
    public function setLibellePhase($libellePhase)
    {
        $this->libellePhase = $libellePhase;

        return $this;
    }

    /**
     * Get libellePhase
     *
     * @return string 
     */
    public function getLibellePhase()
    {
        return $this->libellePhase;
    }

  
}
