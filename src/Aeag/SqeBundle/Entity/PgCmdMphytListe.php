<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdMphytListe
 *
 * @ORM\Table(name="pg_cmd_mphyt_liste", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_cmd_mphyt_liste", columns={"prelev_id", "type_ur", "code_sandre"})}, indexes={@ORM\Index(name="IDX_B5F41E10D8E1F6AAD7EF649F", columns={"prelev_id", "type_ur"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdMphytListeRepository")
 */
class PgCmdMphytListe
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_cmd_mphyt_liste_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     * @var \PgCmdMphytUr
     *
     * @ORM\ManyToOne(targetEntity="PgCmdMphytUr")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id"),
     *   @ORM\JoinColumn(name="type_ur", referencedColumnName="type_ur")
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
     * Set codeSandre
     *
     * @param string $codeSandre
     *
     * @return PgCmdMphytListe
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
     * @return PgCmdMphytListe
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
     * @return PgCmdMphytListe
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
     * @param \Aeag\SqeBundle\Entity\PgCmdMphytUr $prelev
     *
     * @return PgCmdMphytListe
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdMphytUr $prelev = null)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdMphytUr
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
