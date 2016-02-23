<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotPeriodeAn
 *
 * @ORM\Table(name="pg_prog_lot_periode_an",
 *                        indexes={@ORM\Index(name="IDX_PgProgLotPeriodeAn_periode", columns={"periode_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLotPeriodeAn_lotan", columns={"lotan_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLotPeriodeAn_codeStatut", columns={"code_statut"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotPeriodeAnRepository")
 */
class PgProgLotPeriodeAn
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_periode_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \PgProgPeriodes
     *
     * @ORM\ManyToOne(targetEntity="PgProgPeriodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="periode_id", referencedColumnName="id")
     * })
     */
    private $periode;

    /**
     * @var \PgProgLotAn
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotAn")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lotan_id", referencedColumnName="id")
     * })
     */
    private $lotan;

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
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set periode
     *
     * @param \Aeag\SqeBundle\Entity\PgProgPeriodes $periode
     * @return PgProgLotPeriodeAn
     */
    public function setPeriode(\Aeag\SqeBundle\Entity\PgProgPeriodes $periode = null)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return \Aeag\SqeBundle\Entity\PgProgPeriodes 
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set lotan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotan
     * @return PgProgLotPeriodeAn
     */
    public function setLotan(\Aeag\SqeBundle\Entity\PgProgLotAn $lotan = null)
    {
        $this->lotan = $lotan;

        return $this;
    }

    /**
     * Get lotan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn 
     */
    public function getLotan()
    {
        return $this->lotan;
    }

    /**
     * Set codeStatut
     *
     * @param \Aeag\SqeBundle\Entity\PgProgStatut $codeStatut
     * @return PgProgLotPeriodeAn
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
}
