<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdDiatoListe
 *
 * @ORM\Table(name="pg_cmd_diato_liste", indexes={@ORM\Index(name="IDX_477DE109D8E1F6AA", columns={"prelev_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdDiatoListeRepository")
 */
class PgCmdDiatoListe
{
    /**
     * @var string
     *
     * @ORM\Column(name="code_sandre", type="string", length=5, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelevHbDiato")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id")
     * })
     */
    private $prelev;



    /**
     * Set codeSandre
     *
     * @param string $codeSandre
     *
     * @return PgCmdDiatoListe
     */
    public function setCodeSandre($codeSandre)
    {
        $this->codeSandre = $codeSandre;

        return $this;
    }

    /**
     * Get codeSandre
     *
     * @return string
     */
    public function getCodeSandre()
    {
        return $this->codeSandre;
    }

    /**
     * Set taxon
     *
     * @param string $taxon
     *
     * @return PgCmdDiatoListe
     */
    public function setTaxon($taxon)
    {
        $this->taxon = $taxon;

        return $this;
    }

    /**
     * Get taxon
     *
     * @return string
     */
    public function getTaxon()
    {
        return $this->taxon;
    }

    /**
     * Set denombrement
     *
     * @param string $denombrement
     *
     * @return PgCmdDiatoListe
     */
    public function setDenombrement($denombrement)
    {
        $this->denombrement = $denombrement;

        return $this;
    }

    /**
     * Get denombrement
     *
     * @return string
     */
    public function getDenombrement()
    {
        return $this->denombrement;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelevHbDiato $prelev
     *
     * @return PgCmdDiatoListe
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelevHbDiato $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelevHbDiato
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
