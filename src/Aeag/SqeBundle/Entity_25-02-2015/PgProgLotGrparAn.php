<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotGrparAn
 *
 * @ORM\Table(name="pg_prog_lot_grpar_an", indexes={@ORM\Index(name="IDX_AC748485979D571C", columns={"grpar_ref_id"}), @ORM\Index(name="IDX_AC748485E980DBA9", columns={"lotan_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotGrparAnRepository")
 */
class PgProgLotGrparAn {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_grpar_an_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="valide", type="string", length=1, nullable=false)
     */
    private $valide;

    /**
     * @var \PgProgGrpParamRef
     *
     * @ORM\ManyToOne(targetEntity="PgProgGrpParamRef")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="grpar_ref_id", referencedColumnName="id")
     * })
     */
    private $grparRef;

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
     * @var string
     *
     * @ORM\Column(name="statut", type="string", length=1, nullable=false)
     */
    private $statut;

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set valide
     *
     * @param string $valide
     * @return PgProgLotGrparAn
     */
    public function setValide($valide) {
        $this->valide = $valide;

        return $this;
    }

    /**
     * Get valide
     *
     * @return string 
     */
    public function getValide() {
        return $this->valide;
    }

    /**
     * Set grparRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef
     * @return PgProgLotGrparAn
     */
    public function setGrparRef(\Aeag\SqeBundle\Entity\PgProgGrpParamRef $grparRef = null) {
        $this->grparRef = $grparRef;

        return $this;
    }

    /**
     * Get grparRef
     *
     * @return \Aeag\SqeBundle\Entity\PgProgGrpParamRef 
     */
    public function getGrparRef() {
        return $this->grparRef;
    }

    /**
     * Set lotan
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotAn $lotan
     * @return PgProgLotGrparAn
     */
    public function setLotan(\Aeag\SqeBundle\Entity\PgProgLotAn $lotan = null) {
        $this->lotan = $lotan;

        return $this;
    }

    /**
     * Get lotan
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotAn 
     */
    public function getLotan() {
        return $this->lotan;
    }

    function getStatut() {
        return $this->statut;
    }

    function setStatut($statut) {
        $this->statut = $statut;
    }

}
