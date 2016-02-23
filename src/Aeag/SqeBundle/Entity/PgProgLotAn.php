<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotAn
 *
 * @ORM\Table(name="pg_prog_lot_an", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_lotan", columns={"lot_id", "annee_prog"})}, 
 *                       indexes={@ORM\Index(name="IDX_PgProgLotAn_lot", columns={"lot_id"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotAn_phase", columns={"phase_id"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotAn_utilModif", columns={"util_modif"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotAn_codeStatut", columns={"code_statut"}), 
 *                                     @ORM\Index(name="IDX_PgProgLotAn_lotanPere", columns={"lotan_pere_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotAnRepository")
 */
class PgProgLotAn
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="annee_prog", type="decimal", precision=4, scale=0, nullable=false)
     */
    private $anneeProg;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_modif", type="datetime", nullable=false)
     */
    private $dateModif;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="decimal", precision=2, scale=0, nullable=false)
     */
    private $version;

    /**
     * @var \PgProgLot
     *
     * @ORM\ManyToOne(targetEntity="PgProgLot")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lot_id", referencedColumnName="id")
     * })
     */
    private $lot;

    /**
     * @var \PgProgPhases
     *
     * @ORM\ManyToOne(targetEntity="PgProgPhases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="phase_id", referencedColumnName="id")
     * })
     */
    private $phase;

    /**
     * @var \PgProgWebusers
     *
     * @ORM\ManyToOne(targetEntity="PgProgWebusers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="util_modif", referencedColumnName="id")
     * })
     */
    private $utilModif;

    /**
     * @var \PgProgStatut
     *
     * @ORM\ManyToOne(targetEntity="PgProgStatut")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_statut", referencedColumnName="code_statut")
     * })
     */
    private $codeStatut;

    /**
     * @var \PgProgLotAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lotan_pere_id", referencedColumnName="id")
     * })
     */
    private $lotanPere;



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
     * Set anneeProg
     *
     * @param string $anneeProg
     * @return PgProgLotAn
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
     * Set dateModif
     *
     * @param \DateTime $dateModif
     * @return PgProgLotAn
     */
    public function setDateModif($dateModif)
    {
        $this->dateModif = $dateModif;

        return $this;
    }

    /**
     * Get dateModif
     *
     * @return \DateTime 
     */
    public function getDateModif()
    {
        return $this->dateModif;
    }

    /**
     * Set version
     *
     * @param string $version
     * @return PgProgLotAn
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string 
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set lot
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLot $lot
     * @return PgProgLotAn
     */
    public function setLot(\Aeag\SqeBundle\Entity\PgProgLot $lot = null)
    {
        $this->lot = $lot;

        return $this;
    }

    /**
     * Get lot
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLot 
     */
    public function getLot()
    {
        return $this->lot;
    }

    /**
     * Set phase
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPhases $phase
     * @return PgProgLotAn
     */
    public function setPhase(\Aeag\SqeBundle\Entity\PgProgPhases $phase = null)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPhases 
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Set utilModif
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $utilModif
     * @return PgProgLotAn
     */
    public function setUtilModif(\Aeag\SqeBundle\Entity\PgProgWebusers $utilModif = null)
    {
        $this->utilModif = $utilModif;

        return $this;
    }

    /**
     * Get utilModif
     *
     * @return \Aeag\SqeBundle\Entity\PgProgWebusers 
     */
    public function getUtilModif()
    {
        return $this->utilModif;
    }

    /**
     * Set codeStatut
     *
     * @param \Aeag\SqeBundle\Entity\PgProgStatut $codeStatut
     * @return PgProgLotAn
     */
    public function setCodeStatut(\Aeag\SqeBundle\Entity\PgProgStatut $codeStatut = null)
    {
        $this->codeStatut = $codeStatut;

        return $this;
    }

    /**
     * Get codeStatut
     *
     * @return \Aeag\SqeBundle\Entity\PgProgStatut 
     */
    public function getCodeStatut()
    {
        return $this->codeStatut;
    }

    /**
     * Set lotanPere
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotanPere
     * @return PgProgLotAn
     */
    public function setLotanPere(\Aeag\SqeBundle\Entity\PgProgLotAn $lotanPere = null)
    {
        $this->lotanPere = $lotanPere;

        return $this;
    }

    /**
     * Get lotanPere
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn 
     */
    public function getLotanPere()
    {
        return $this->lotanPere;
    }
}
