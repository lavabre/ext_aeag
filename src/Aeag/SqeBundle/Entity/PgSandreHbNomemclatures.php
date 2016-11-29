<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgSandreHbNomemclatures
 *
 * @ORM\Table(name="pg_sandre_hb_nomenclatures")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgSandreHbNomemclaturesRepository")
 */
class PgSandreHbNomemclatures {

    /**
     * @var string
     *
     * @ORM\Column(name="code_nomenclature", type="string", length=10, nullable=false)
     * @ORM\Id
     */
    private $codeNomemclature;

    /**
     * @var string
     *
     * @ORM\Column(name="lib_nomenclature", type="string", length=100, nullable=false)
     */
    private $libNomemclature;

    /**
     * @var string
     *
     * @ORM\Column(name="code_element", type="string", length=10, nullable=true)
     * @ORM\Id
     */
    private $codeElement;

    /**
     * @var string
     *
     * @ORM\Column(name="lib_element", type="string", length=100, nullable=false)
     */
    private $libElement;

    /**
     * @var \PgSandreSupports
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     * })
     */
    private $codeSupport;

    function getCodeNomemclature() {
        return $this->codeNomemclature;
    }

    function getLibNomemclature() {
        return $this->libNomemclature;
    }

    function getCodeElement() {
        return $this->codeElement;
    }

    function getLibElement() {
        return $this->libElement;
    }

    function getCodeSupport() {
        return $this->codeSupport;
    }

    function setCodeNomemclature($codeNomemclature) {
        $this->codeNomemclature = $codeNomemclature;
    }

    function setLibNomemclature($libNomemclature) {
        $this->libNomemclature = $libNomemclature;
    }

    function setCodeElement($codeElement) {
        $this->codeElement = $codeElement;
    }

    function setLibElement($libElement) {
        $this->libElement = $libElement;
    }

    function setCodeSupport ($codeSupport) {
        $this->codeSupport = $codeSupport;
    }

}
