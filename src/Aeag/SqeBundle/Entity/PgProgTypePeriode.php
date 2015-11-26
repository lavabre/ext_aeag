<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgTypePeriode
 *
 * @ORM\Table(name="pg_prog_type_periode")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgTypePeriodeRepository")
 */
class PgProgTypePeriode
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_type_periode", type="string", length=4, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_type_periode_code_type_periode_seq", allocationSize=1, initialValue=1)
     */
    private $codeTypePeriode;

    /**
     * @var string
     *
     * @ORM\Column(name="lib_type_periode", type="string", length=255, nullable=false)
     */
    private $libTypePeriode;

    /**
     * @var string
     *
     * @ORM\Column(name="nb_periodes", type="decimal", precision=3, scale=0, nullable=true)
     */
    private $nbPeriodes;



    /**
     * Get codeTypePeriode
     *
     * @return string 
     */
    public function getCodeTypePeriode()
    {
        return $this->codeTypePeriode;
    }

    /**
     * Set libTypePeriode
     *
     * @param string $libTypePeriode
     * @return PgProgTypePeriode
     */
    public function setLibTypePeriode($libTypePeriode)
    {
        $this->libTypePeriode = $libTypePeriode;

        return $this;
    }

    /**
     * Get libTypePeriode
     *
     * @return string 
     */
    public function getLibTypePeriode()
    {
        return $this->libTypePeriode;
    }

    /**
     * Set nbPeriodes
     *
     * @param string $nbPeriodes
     * @return PgProgTypePeriode
     */
    public function setNbPeriodes($nbPeriodes)
    {
        $this->nbPeriodes = $nbPeriodes;

        return $this;
    }

    /**
     * Get nbPeriodes
     *
     * @return string 
     */
    public function getNbPeriodes()
    {
        return $this->nbPeriodes;
    }
}
