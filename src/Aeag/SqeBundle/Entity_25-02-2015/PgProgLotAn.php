<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotAn
 *
 * @ORM\Table(name="pg_prog_lot_an", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_lotan", columns={"lot_id", "annee_prog"})}, indexes={@ORM\Index(name="IDX_B749CD64A8CBA5F7", columns={"lot_id"}), @ORM\Index(name="IDX_B749CD6499091188", columns={"phase_id"})})
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
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_dft_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prelevDft;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="labo_dft_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $laboDft;




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
    
    function getPrelevDft() {
        return $this->prelevDft;
    }

    function getLaboDft() {
        return $this->laboDft;
    }

    function setPrelevDft($prelevDft) {
        $this->prelevDft = $prelevDft;
    }

    function setLaboDft($laboDft) {
        $this->laboDft = $laboDft;
    }


}
