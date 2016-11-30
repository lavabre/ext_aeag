<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreAppellationTaxon
 *
 * @ORM\Table(name="pg_sandre_appellation_taxon")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreAppellationTaxonRepository")
 */
class PgSandreAppellationTaxon {

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
     * @ORM\Column(name="nom_appel_taxon", type="string", length=255, nullable=false)
     */
    private $nomAppelTaxon;

    /**
     * @var \PgSandreSupports
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     * })
     */
    private $codeSupport;

    function getCodeAppelTaxon() {
        return $this->codeAppelTaxon;
    }

    function getNomAppelTaxon() {
        return $this->nomAppelTaxon;
    }

    function getCodeSupport() {
        return $this->codeSupport;
    }

    function setCodeAppelTaxon($codeAppelTaxon) {
        $this->codeAppelTaxon = $codeAppelTaxon;
    }

    function setNomAppelTaxon($nomAppelTaxon) {
        $this->nomAppelTaxon = $nomAppelTaxon;
    }

    function setCodeSupport(\PgSandreSupports $codeSupport) {
        $this->codeSupport = $codeSupport;
    }

}
