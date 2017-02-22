<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreCodesAlternAppelTaxon
 *
 * @ORM\Table(name="pg_sandre_codes_altern_appel_taxon")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreCodesAlternAppelTaxonRepository")
 */
class PgSandreCodesAlternAppelTaxon {

    /**
     * @var string
     *
     * @ORM\Column(name="code_appel_taxon", type="string", length=6, nullable=false)
     * @ORM\Id
     */
    private $codeAppelTaxon;

    /**
     * @var string
     *
     * @ORM\Column(name="code_altern", type="string", length=20, nullable=false)
     */
    private $codeAltern;

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(name="origine_code_altern", type="string", length=50, nullable=false)
     */
    private $origineCodeAltern;

    function getCodeAppelTaxon() {
        return $this->codeAppelTaxon;
    }

    function getCodeAltern() {
        return $this->codeAltern;
    }

    function getOrigineCodeAltern() {
        return $this->origineCodeAltern;
    }

    function setCodeAppelTaxon($codeAppelTaxon) {
        $this->codeAppelTaxon = $codeAppelTaxon;
    }

    function setCodeAltern($codeAltern) {
        $this->codeAltern = $codeAltern;
    }

    function setOrigineCodeAltern($origineCodeAltern) {
        $this->origineCodeAltern = $origineCodeAltern;
    }

}
