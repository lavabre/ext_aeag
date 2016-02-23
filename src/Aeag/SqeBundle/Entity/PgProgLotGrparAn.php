<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotGrparAn
 *
 * @ORM\Table(name="pg_prog_lot_grpar_an",
 *                        indexes={@ORM\Index(name="IDX_PgProgLotGrparAn_grparRef", columns={"grpar_ref_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLotGrparAn_lotan", columns={"lotan_id"}), 
 *                                      @ORM\Index(name="IDX_PgProgLotGrparAn_prestaDft", columns={"presta_dft_id"})})
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
     * @ORM\Column(name="origine", type="string", length=2, nullable=true)
     */
    private $origine;

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
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_dft_id", referencedColumnName="adr_cor_id", nullable=true)
     * })
     */
    private $prestaDft;

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set origine
     *
     * @param string $origine
     * @return PgProgLotGrparAn
     */
    public function setOrigine($origine) {
        $this->origine = $origine;

        return $this;
    }

    /**
     * Get origine
     *
     * @return string 
     */
    public function getOrigine() {
        return $this->origine;
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

    /**
     * Set prestaDft
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestaDft
     * @return PgProgLotGrparAn
     */
    public function setPrestaDft(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestaDft = null) {
        $this->prestaDft = $prestaDft;

        return $this;
    }

    /**
     * Get prestaDft
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPrestaDft() {
        return $this->prestaDft;
    }

}
