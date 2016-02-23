<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdInvertListe
 *
 * @ORM\Table(name="pg_cmd_invert_liste", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_cmd_invert_liste", columns={"prelev_id", "prelem", "phase", "code_sandre"})}, indexes={@ORM\Index(name="IDX_56245650D8E1F6AA", columns={"prelev_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdInvertListeRepository")
 */
class PgCmdInvertListe
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_invert_liste_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="phase", type="string", length=4, nullable=true)
     */
    private $phase;

    /**
     * @var string
     *
     * @ORM\Column(name="prelem", type="string", length=3, nullable=true)
     */
    private $prelem;

    /**
     * @var string
     *
     * @ORM\Column(name="code_sandre", type="string", length=5, nullable=true)
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
     * @var \PgCmdPrelevHbInvert
     *
     * @ORM\ManyToOne(targetEntity="PgCmdPrelevHbInvert")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id")
     * })
     */
    private $prelev;



    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set phase
     *
     * @param string $phase
     *
     * @return PgCmdInvertListe
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;

        return $this;
    }

    /**
     * Get phase
     *
     * @return string
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * Set prelem
     *
     * @param string $prelem
     *
     * @return PgCmdInvertListe
     */
    public function setPrelem($prelem)
    {
        $this->prelem = $prelem;

        return $this;
    }

    /**
     * Get prelem
     *
     * @return string
     */
    public function getPrelem()
    {
        return $this->prelem;
    }

    /**
     * Set codeSandre
     *
     * @param string $codeSandre
     *
     * @return PgCmdInvertListe
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
     * @return PgCmdInvertListe
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
     * @return PgCmdInvertListe
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
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert $prelev
     *
     * @return PgCmdInvertListe
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert $prelev = null)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelevHbInvert
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
