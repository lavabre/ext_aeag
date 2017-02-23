<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDiatoListe
 *
 * @ORM\Table(name="pg_cmd_diato_liste")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdDiatoListeRepository")
 */
class PgCmdDiatoListe {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_diato_liste_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_sandre", type="string", length=5, nullable=false)
     */
    private $codeSandre;

    /**
     * @var string
     *
     * @ORM\Column(name="taxon", type="string", length=50, nullable=true)
     */
    private $taxon;

    /**
     * @var string
     *
     * @ORM\Column(name="denombrement", type="decimal", precision=20, scale=10, nullable=true)
     */
    private $denombrement;

    /**
     * @var \PgCmdPrelevHbDiato
     *
     *
     *
     * @ORM\OneToOne(targetEntity="PgCmdPrelevHbDiato")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id")
     * })
     */
    private $prelev;

    function getId() {
        return $this->id;
    }

    function getCodeSandre() {
        return $this->codeSandre;
    }

    function getTaxon() {
        return $this->taxon;
    }

    function getDenombrement() {
        return $this->denombrement;
    }

    function getPrelev() {
        return $this->prelev;
    }

//    function setId($id) {
//        $this->id = $id;
//    }

    function setCodeSandre($codeSandre) {
        $this->codeSandre = $codeSandre;
    }

    function setTaxon($taxon) {
        $this->taxon = $taxon;
    }

    function setDenombrement($denombrement) {
        $this->denombrement = $denombrement;
    }

    function setPrelev($prelev) {
        $this->prelev = $prelev;
    }

}
