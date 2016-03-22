<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreUnites
 *
 * @ORM\Table(name="pg_sandre_vals_possibles_params_env")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreUnitesPossiblesParamsEnvRepository")
 */
class PgSandreUnitesPossiblesParamsEnv {

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
     * @var string
     * @ORM\Column(name="valeur", type="decimal", precision=2, scale=1, nullable=false)
     * @ORM\Id
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=100, nullable=true)
     */
    private $libelle;

    /**
     * Constructor
     */
    public function __construct() {
        
    }

    function getCodeParametre() {
        return $this->codeParametre;
    }

    function getValeur() {
        return $this->valeur;
    }

    function getLibelle() {
        return $this->libelle;
    }

    function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;
    }

    function setValeur($valeur) {
        $this->valeur = $valeur;
    }

    function setLibelle($libelle) {
        $this->libelle = $libelle;
    }

}
