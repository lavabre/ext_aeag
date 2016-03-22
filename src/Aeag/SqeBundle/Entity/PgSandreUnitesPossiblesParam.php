<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreUnites
 *
 * @ORM\Table(name="pg_sandre_unites_possibles_param")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreUnitesPossiblesParamRepository")
 */
class PgSandreUnitesPossiblesParam {

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
     * @var \PgSandreUnites
     *
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
      *
     * @ORM\Column(name="unite_defaut", type="string", length=1, nullable=true)
     */
    private $uniteDefaut;

    /**
     * Constructor
     */
    public function __construct() {
        
    }

    function getCodeParametre() {
        return $this->codeParametre;
    }

    function getCodeUnite() {
        return $this->codeUnite;
    }

    function getNatureFraction() {
        return $this->natureFraction;
    }

    function getUniteDefaut() {
        return $this->uniteDefaut;
    }

    function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    function setCodeUnite($codeUnite) {
        $this->codeUnite = $codeUnite;
    }

    function setNatureFraction($natureFraction) {
        $this->natureFraction = $natureFraction;
    }

    function setUniteDefaut(\PgSandreUnites $uniteDefaut) {
        $this->uniteDefaut = $uniteDefaut;
    }

}
