<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgBornesParams
 *
 * @ORM\Table(name="pg_prog_bornes_params")
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgBornesParamsRepository")
 */
class PgProgBornesParams {
    
    /**
     * @var \PgProgTypeMilieu
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="PgProgTypeMilieu")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_milieu", referencedColumnName="code_milieu")
     * })
     */
    private $codeMilieu;

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
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="PgSandreFractions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_fraction", referencedColumnName="code_fraction")
     * })
     */
    private $codeFraction;
    
    /**
     * @var string
     *
     * @ORM\Column(name="val_min", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $valMin;
    
    /**
     * @var string
     *
     * @ORM\Column(name="val_max", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $valMax;
    
    public function getCodeMilieu() {
        return $this->codeMilieu;
    }

    public function getCodeParametre() {
        return $this->codeParametre;
    }

    public function getCodeFraction() {
        return $this->codeFraction;
    }

    public function getValMin() {
        return $this->valMin;
    }

    public function getValMax() {
        return $this->valMax;
    }

    public function setCodeMilieu($codeMilieu) {
        $this->codeMilieu = $codeMilieu;
    }

    public function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    public function setCodeFraction($codeFraction) {
        $this->codeFraction = $codeFraction;
    }

    public function setValMin($valMin) {
        $this->valMin = $valMin;
    }

    public function setValMax($valMax) {
        $this->valMax = $valMax;
    }



}
