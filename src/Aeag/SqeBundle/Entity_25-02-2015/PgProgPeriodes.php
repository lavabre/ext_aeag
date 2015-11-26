<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgPeriodes
 *
 * @ORM\Table(name="pg_prog_periodes", indexes={@ORM\Index(name="IDX_EE0F2D88BBE24206", columns={"type_periode"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgPeriodesRepository")
 */
class PgProgPeriodes
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_periodes_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="num_periode", type="decimal", precision=3, scale=0, nullable=false)
     */
    private $numPeriode;

    /**
     * @var string
     *
     * @ORM\Column(name="label_periode", type="string", length=50, nullable=false)
     */
    private $labelPeriode;

    /**
     * @var string
     *
     * @ORM\Column(name="annee_prog", type="decimal", precision=4, scale=0, nullable=false)
     */
    private $anneeProg;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_deb", type="datetime", nullable=false)
     */
    private $dateDeb;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_fin", type="datetime", nullable=false)
     */
    private $dateFin;

    /**
     * @var \PgProgTypePeriode
     *
     * @ORM\ManyToOne(targetEntity="PgProgTypePeriode")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_periode", referencedColumnName="code_type_periode")
     * })
     */
    private $typePeriode;



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
     * Set numPeriode
     *
     * @param string $numPeriode
     * @return PgProgPeriodes
     */
    public function setNumPeriode($numPeriode)
    {
        $this->numPeriode = $numPeriode;

        return $this;
    }

    /**
     * Get numPeriode
     *
     * @return string 
     */
    public function getNumPeriode()
    {
        return $this->numPeriode;
    }

    /**
     * Set labelPeriode
     *
     * @param string $labelPeriode
     * @return PgProgPeriodes
     */
    public function setLabelPeriode($labelPeriode)
    {
        $this->labelPeriode = $labelPeriode;

        return $this;
    }

    /**
     * Get labelPeriode
     *
     * @return string 
     */
    public function getLabelPeriode()
    {
        return $this->labelPeriode;
    }

    /**
     * Set anneeProg
     *
     * @param string $anneeProg
     * @return PgProgPeriodes
     */
    public function setAnneeProg($anneeProg)
    {
        $this->anneeProg = $anneeProg;

        return $this;
    }

    /**
     * Get anneeProg
     *
     * @return string 
     */
    public function getAnneeProg()
    {
        return $this->anneeProg;
    }

    /**
     * Set dateDeb
     *
     * @param \DateTime $dateDeb
     * @return PgProgPeriodes
     */
    public function setDateDeb($dateDeb)
    {
        $this->dateDeb = $dateDeb;

        return $this;
    }

    /**
     * Get dateDeb
     *
     * @return \DateTime 
     */
    public function getDateDeb()
    {
        return $this->dateDeb;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     * @return PgProgPeriodes
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime 
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set typePeriode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgTypePeriode $typePeriode
     * @return PgProgPeriodes
     */
    public function setTypePeriode(\Aeag\SqeBundle\Entity\PgProgTypePeriode $typePeriode = null)
    {
        $this->typePeriode = $typePeriode;

        return $this;
    }

    /**
     * Get typePeriode
     *
     * @return \Aeag\SqeBundle\Entity\PgProgTypePeriode 
     */
    public function getTypePeriode()
    {
        return $this->typePeriode;
    }
}
