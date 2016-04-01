<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgUnitesPossiblesParam
 *
 * @ORM\Table(name="pg_prog_unites_possibles_param")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgUnitesPossiblesParamRepository")
 */
class PgProgUnitesPossiblesParam {
    
    /**
     * @var \PgSandreParametres
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="PgSandreParametres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     * })
     */
    private $codeParametre;
    
    /**
     * @var \PgSandreUnites
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgSandreUnites")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_unite", referencedColumnName="code_unite")
     * })
     */
    private $codeUnite;
    
    /**
     * @var string
     *
     * @ORM\Column(name="nature_fraction", type="string", length=1, nullable=true)
     */
    private $natureFraction;
    
    /**
     * @var string
     *
     * @ORM\Column(name="unite_defaut", type="string", length=1, nullable=true)
     */
    private $uniteDefaut;
    
    /**
     * @var decimal
     *
     * @ORM\Column(name="val_max", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $valMax;
    
    public function getCodeParametre() {
        return $this->codeParametre;
    }

    public function getCodeUnite() {
        return $this->codeUnite;
    }

    public function getNatureFraction() {
        return $this->natureFraction;
    }

    public function getUniteDefaut() {
        return $this->uniteDefaut;
    }

    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    public function setCodeUnite(\Aeag\SqeBundle\Entity\PgSandreUnites $codeUnite) {
        $this->codeUnite = $codeUnite;
    }

    public function setNatureFraction($natureFraction) {
        $this->natureFraction = $natureFraction;
    }

    public function setUniteDefaut($uniteDefaut) {
        $this->uniteDefaut = $uniteDefaut;
    }
    
    public function getValMax() {
        return $this->valMax;
    }

    public function setValMax($valMax) {
        $this->valMax = $valMax;
    }
}
