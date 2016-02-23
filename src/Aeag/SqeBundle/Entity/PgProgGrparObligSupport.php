<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgGrparObligSupport
 *
 * @ORM\Table(name="pg_prog_grpar_oblig_support")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgGrparObligSupportRepository")
 */
class PgProgGrparObligSupport
{
    /**
     * @var string
     *
     * @ORM\Column(name="grpar_ref_id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $grparRefId;

    /**
     * @var string
     *
     * @ORM\Column(name="code_support", type="string", length=3, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $codeSupport;



    /**
     * Set grparRefId
     *
     * @param string $grparRefId
     * @return PgProgGrparObligSupport
     */
    public function setGrparRefId($grparRefId)
    {
        $this->grparRefId = $grparRefId;

        return $this;
    }

    /**
     * Get grparRefId
     *
     * @return string 
     */
    public function getGrparRefId()
    {
        return $this->grparRefId;
    }

    /**
     * Set codeSupport
     *
     * @param string $codeSupport
     * @return PgProgGrparObligSupport
     */
    public function setCodeSupport($codeSupport)
    {
        $this->codeSupport = $codeSupport;

        return $this;
    }

    /**
     * Get codeSupport
     *
     * @return string 
     */
    public function getCodeSupport()
    {
        return $this->codeSupport;
    }
}
