<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgRefSitePrelevement
 *
 * @ORM\Table(name="pg_ref_site_prelevement", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_ref_site_prelevement", columns={"ouv_fonc_id", "code_site"})}, indexes={@ORM\Index(name="IDX_1A907A96B8A3AF39", columns={"code_support"}), @ORM\Index(name="IDX_1A907A9630218F48", columns={"ouv_fonc_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgRefSitePrelevementRepository")
 */
class PgRefSitePrelevement
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_ref_site_prelevement_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code_site", type="string", length=3, nullable=false)
     */
    private $codeSite;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_site", type="string", length=60, nullable=false)
     */
    private $nomSite;

    /**
     * @var \PgSandreSupports
     *
     * @ORM\ManyToOne(targetEntity="PgSandreSupports")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_support", referencedColumnName="code_support")
     * })
     */
    private $codeSupport;

    /**
     * @var \PgRefStationMesure
     *
     * @ORM\ManyToOne(targetEntity="PgRefStationMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ouv_fonc_id", referencedColumnName="ouv_fonc_id")
     * })
     */
    private $ouvFonc;



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
     * Set codeSite
     *
     * @param string $codeSite
     * @return PgRefSitePrelevement
     */
    public function setCodeSite($codeSite)
    {
        $this->codeSite = $codeSite;

        return $this;
    }

    /**
     * Get codeSite
     *
     * @return string 
     */
    public function getCodeSite()
    {
        return $this->codeSite;
    }

    /**
     * Set nomSite
     *
     * @param string $nomSite
     * @return PgRefSitePrelevement
     */
    public function setNomSite($nomSite)
    {
        $this->nomSite = $nomSite;

        return $this;
    }

    /**
     * Get nomSite
     *
     * @return string 
     */
    public function getNomSite()
    {
        return $this->nomSite;
    }

    /**
     * Set codeSupport
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport
     * @return PgRefSitePrelevement
     */
    public function setCodeSupport(\Aeag\SqeBundle\Entity\PgSandreSupports $codeSupport = null)
    {
        $this->codeSupport = $codeSupport;

        return $this;
    }

    /**
     * Get codeSupport
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreSupports 
     */
    public function getCodeSupport()
    {
        return $this->codeSupport;
    }

    /**
     * Set ouvFonc
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc
     * @return PgRefSitePrelevement
     */
    public function setOuvFonc(\Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc = null)
    {
        $this->ouvFonc = $ouvFonc;

        return $this;
    }

    /**
     * Get ouvFonc
     *
     * @return \Aeag\SqeBundle\Entity\PgRefStationMesure 
     */
    public function getOuvFonc()
    {
        return $this->ouvFonc;
    }
}
