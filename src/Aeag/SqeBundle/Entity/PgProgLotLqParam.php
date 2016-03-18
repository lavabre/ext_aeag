<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotLqParam
 *
 * @ORM\Table(name="pg_prog_lot_lq_param")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotLqParamRepository")
 */
class PgProgLotLqParam {
    
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     */
    private $id;
    
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
     * @var \PgSandreParametres
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgSandreParametres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     * })
     */
    private $codeParametre;
    
    /**
     * @var \PgSandreFractions
     *
     * @ORM\ManyToOne(targetEntity="PgSandreFractions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_fraction", referencedColumnName="code_fraction")
     * })
     */
    private $codeFraction;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lq_cible", type="decimal", precision=20, scale=10, nullable=false)
     */
    private $lqCible;
    
    /**
     * @var string
     *
     * @ORM\Column(name="lq_prestataire", type="decimal", precision=20, scale=10, nullable=false)
     */
    private $lqPrestataire;
    
    public function getId() {
        return $this->id;
    }

    public function getLot() {
        return $this->lot;
    }

    public function getCodeParametre() {
        return $this->codeParametre;
    }

    public function getCodeFraction() {
        return $this->codeFraction;
    }

    public function getLqCible() {
        return $this->lqCible;
    }

    public function getLqPrestataire() {
        return $this->lqPrestataire;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setLot(\Aeag\SqeBundle\Entity\PgProgLot $lot) {
        $this->lot = $lot;
    }

    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    public function setCodeFraction(\Aeag\SqeBundle\Entity\PgSandreFractions $codeFraction) {
        $this->codeFraction = $codeFraction;
    }

    public function setLqCible($lqCible) {
        $this->lqCible = $lqCible;
    }

    public function setLqPrestataire($lqPrestataire) {
        $this->lqPrestataire = $lqPrestataire;
    }


}
