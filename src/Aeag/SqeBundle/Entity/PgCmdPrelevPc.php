<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdPrelevPc
 *
 * @ORM\Table(name="pg_cmd_prelev_pc", indexes={@ORM\Index(name="IDX_D27F068D8E1F6AA", columns={"prelev_id"}), @ORM\Index(name="IDX_D27F068985D8034", columns={"zone_verticale"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdPrelevPcRepository")
 */
class PgCmdPrelevPc
{
    /**
     * @var string
     *
     * @ORM\Column(name="num_ordre", type="decimal", precision=4, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $numOrdre;

    /**
     * @var string
     *
     * @ORM\Column(name="conformite", type="string", length=1, nullable=true)
     */
    private $conformite;

    /**
     * @var string
     *
     * @ORM\Column(name="accreditation", type="string", length=1, nullable=true)
     */
    private $accreditation;

    /**
     * @var string
     *
     * @ORM\Column(name="agrement", type="string", length=1, nullable=true)
     */
    private $agrement;

    /**
     * @var string
     *
     * @ORM\Column(name="reserves", type="string", length=1, nullable=true)
     */
    private $reserves;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=2000, nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="x_prel", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $xPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="y_prel", type="decimal", precision=12, scale=5, nullable=true)
     */
    private $yPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="localisation", type="string", length=80, nullable=true)
     */
    private $localisation;

    /**
     * @var string
     *
     * @ORM\Column(name="profondeur", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $profondeur;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ech_cmd", type="string", length=100, nullable=true)
     */
    private $refEchCmd;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ech_prel", type="string", length=100, nullable=true)
     */
    private $refEchPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ech_labo", type="string", length=100, nullable=true)
     */
    private $refEchLabo;
    
    /**
     * @var string
     *
     * @ORM\Column(name="completude_ech", type="string", length=1, nullable=true)
     */
    private $completudeEch;
    
    /**
     * @var string
     *
     * @ORM\Column(name="acceptabilite_ech", type="string", length=1, nullable=true)
     */
    private $acceptabiliteEch;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_recep_ech", type="datetime", nullable=true)
     */
    private $dateRecepEch;

    /**
     * @var \PgCmdPrelev
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelev")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="id")
     * })
     */
    private $prelev;

    /**
     * @var \PgSandreZoneVerticaleProspectee
     *
     * @ORM\ManyToOne(targetEntity="PgSandreZoneVerticaleProspectee")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="zone_verticale", referencedColumnName="code_zone")
     * })
     */
    private $zoneVerticale;



    /**
     * Set numOrdre
     *
     * @param string $numOrdre
     *
     * @return PgCmdPrelevPc
     */
    public function setNumOrdre($numOrdre)
    {
        $this->numOrdre = $numOrdre;

        return $this;
    }

    /**
     * Get numOrdre
     *
     * @return string
     */
    public function getNumOrdre()
    {
        return $this->numOrdre;
    }

    /**
     * Set conformite
     *
     * @param string $conformite
     *
     * @return PgCmdPrelevPc
     */
    public function setConformite($conformite)
    {
        $this->conformite = $conformite;

        return $this;
    }

    /**
     * Get conformite
     *
     * @return string
     */
    public function getConformite()
    {
        return $this->conformite;
    }

    /**
     * Set accreditation
     *
     * @param string $accreditation
     *
     * @return PgCmdPrelevPc
     */
    public function setAccreditation($accreditation)
    {
        $this->accreditation = $accreditation;

        return $this;
    }

    /**
     * Get accreditation
     *
     * @return string
     */
    public function getAccreditation()
    {
        return $this->accreditation;
    }

    /**
     * Set agrement
     *
     * @param string $agrement
     *
     * @return PgCmdPrelevPc
     */
    public function setAgrement($agrement)
    {
        $this->agrement = $agrement;

        return $this;
    }

    /**
     * Get agrement
     *
     * @return string
     */
    public function getAgrement()
    {
        return $this->agrement;
    }

    /**
     * Set reserves
     *
     * @param string $reserves
     *
     * @return PgCmdPrelevPc
     */
    public function setReserves($reserves)
    {
        $this->reserves = $reserves;

        return $this;
    }

    /**
     * Get reserves
     *
     * @return string
     */
    public function getReserves()
    {
        return $this->reserves;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return PgCmdPrelevPc
     */
    public function setCommentaire($commentaire)
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire()
    {
        return $this->commentaire;
    }

    /**
     * Set xPrel
     *
     * @param string $xPrel
     *
     * @return PgCmdPrelevPc
     */
    public function setXPrel($xPrel)
    {
        $this->xPrel = $xPrel;

        return $this;
    }

    /**
     * Get xPrel
     *
     * @return string
     */
    public function getXPrel()
    {
        return $this->xPrel;
    }

    /**
     * Set yPrel
     *
     * @param string $yPrel
     *
     * @return PgCmdPrelevPc
     */
    public function setYPrel($yPrel)
    {
        $this->yPrel = $yPrel;

        return $this;
    }

    /**
     * Get yPrel
     *
     * @return string
     */
    public function getYPrel()
    {
        return $this->yPrel;
    }

    /**
     * Set localisation
     *
     * @param string $localisation
     *
     * @return PgCmdPrelevPc
     */
    public function setLocalisation($localisation)
    {
        $this->localisation = $localisation;

        return $this;
    }

    /**
     * Get localisation
     *
     * @return string
     */
    public function getLocalisation()
    {
        return $this->localisation;
    }

    /**
     * Set profondeur
     *
     * @param string $profondeur
     *
     * @return PgCmdPrelevPc
     */
    public function setProfondeur($profondeur)
    {
        $this->profondeur = $profondeur;

        return $this;
    }

    /**
     * Get profondeur
     *
     * @return string
     */
    public function getProfondeur()
    {
        return $this->profondeur;
    }

    /**
     * Set refEchCmd
     *
     * @param string $refEchCmd
     *
     * @return PgCmdPrelevPc
     */
    public function setRefEchCmd($refEchCmd)
    {
        $this->refEchCmd = $refEchCmd;

        return $this;
    }

    /**
     * Get refEchCmd
     *
     * @return string
     */
    public function getRefEchCmd()
    {
        return $this->refEchCmd;
    }

    /**
     * Set refEchPrel
     *
     * @param string $refEchPrel
     *
     * @return PgCmdPrelevPc
     */
    public function setRefEchPrel($refEchPrel)
    {
        $this->refEchPrel = $refEchPrel;

        return $this;
    }

    /**
     * Get refEchPrel
     *
     * @return string
     */
    public function getRefEchPrel()
    {
        return $this->refEchPrel;
    }

    /**
     * Set refEchLabo
     *
     * @param string $refEchLabo
     *
     * @return PgCmdPrelevPc
     */
    public function setRefEchLabo($refEchLabo)
    {
        $this->refEchLabo = $refEchLabo;

        return $this;
    }

    /**
     * Get refEchLabo
     *
     * @return string
     */
    public function getRefEchLabo()
    {
        return $this->refEchLabo;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelev $prelev
     *
     * @return PgCmdPrelevPc
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelev $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelev
     */
    public function getPrelev()
    {
        return $this->prelev;
    }

    /**
     * Set zoneVerticale
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $zoneVerticale
     *
     * @return PgCmdPrelevPc
     */
    public function setZoneVerticale(\Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee $zoneVerticale = null)
    {
        $this->zoneVerticale = $zoneVerticale;

        return $this;
    }

    /**
     * Get zoneVerticale
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreZoneVerticaleProspectee
     */
    public function getZoneVerticale()
    {
        return $this->zoneVerticale;
    }
    
    public function getCompletudeEch() {
        return $this->completudeEch;
    }

    public function getAcceptabiliteEch() {
        return $this->acceptabiliteEch;
    }

    public function getDateRecepEch() {
        return $this->dateRecepEch;
    }

    public function setCompletudeEch($completudeEch) {
        $this->completudeEch = $completudeEch;
    }

    public function setAcceptabiliteEch($acceptabiliteEch) {
        $this->acceptabiliteEch = $acceptabiliteEch;
    }

    public function setDateRecepEch(\DateTime $dateRecepEch) {
        $this->dateRecepEch = $dateRecepEch;
    }
}
