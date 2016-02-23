<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgRefReseauMesure
 *
 * @ORM\Table(name="pg_ref_reseau_mesure")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgRefReseauMesureRepository")
 */
class PgRefReseauMesure {

    /**
     * @var string
     *
     * @ORM\Column(name="groupement_id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_ref_reseau_mesure_groupement_id_seq", allocationSize=1, initialValue=1)
     */
    private $groupementId;

    /**
     * @var string
     *
     * @ORM\Column(name="code_aeag_rsx", type="string", length=12, nullable=false)
     */
    private $codeAeagRsx;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_rsx", type="string", length=255, nullable=false)
     */
    private $nomRsx;

    /**
     * @var string
     *
     * @ORM\Column(name="code_sandre", type="string", length=10, nullable=true)
     */
    private $codeSandre;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgWebusers", mappedBy="rsx")
     */
    private $webuser;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgRefStationMesure", mappedBy="groupement")
     */
    private $ouvFonc;

    /**
     * @var string
     *
     * @ORM\Column(name="categorie_milieu", type="string", length=50, nullable=true)
     */
    private $categorieMilieu;

    /**
     * Constructor
     */
    public function __construct() {
        $this->webuser = new \Doctrine\Common\Collections\ArrayCollection();
        $this->ouvFonc = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get groupementId
     *
     * @return string 
     */
    public function getGroupementId() {
        return $this->groupementId;
    }

    /**
     * Set codeAeagRsx
     *
     * @param string $codeAeagRsx
     * @return PgRefReseauMesure
     */
    public function setCodeAeagRsx($codeAeagRsx) {
        $this->codeAeagRsx = $codeAeagRsx;

        return $this;
    }

    /**
     * Get codeAeagRsx
     *
     * @return string 
     */
    public function getCodeAeagRsx() {
        return $this->codeAeagRsx;
    }

    /**
     * Set nomRsx
     *
     * @param string $nomRsx
     * @return PgRefReseauMesure
     */
    public function setNomRsx($nomRsx) {
        $this->nomRsx = $nomRsx;

        return $this;
    }

    /**
     * Get nomRsx
     *
     * @return string 
     */
    public function getNomRsx() {
        return $this->nomRsx;
    }

    /**
     * Set codeSandre
     *
     * @param string $codeSandre
     * @return PgRefReseauMesure
     */
    public function setCodeSandre($codeSandre) {
        $this->codeSandre = $codeSandre;

        return $this;
    }

    /**
     * Get codeSandre
     *
     * @return string 
     */
    public function getCodeSandre() {
        return $this->codeSandre;
    }

    function getCategorieMilieu() {
        return $this->categorieMilieu;
    }

    function setCategorieMilieu($categorieMilieu) {
        $this->categorieMilieu = $categorieMilieu;
    }

    /**
     * Add webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     * @return PgRefReseauMesure
     */
    public function addWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser) {
        $this->webuser[] = $webuser;

        return $this;
    }

    /**
     * Remove webuser
     *
     * @param \Aeag\SqeBundle\Entity\PgProgWebusers $webuser
     */
    public function removeWebuser(\Aeag\SqeBundle\Entity\PgProgWebusers $webuser) {
        $this->webuser->removeElement($webuser);
    }

    /**
     * Get webuser
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getWebuser() {
        return $this->webuser;
    }

    /**
     * Add ouvFonc
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc
     * @return PgRefReseauMesure
     */
    public function addOuvFonc(\Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc) {
        $this->ouvFonc[] = $ouvFonc;

        return $this;
    }

    /**
     * Remove ouvFonc
     *
     * @param \Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc
     */
    public function removeOuvFonc(\Aeag\SqeBundle\Entity\PgRefStationMesure $ouvFonc) {
        $this->ouvFonc->removeElement($ouvFonc);
    }

    /**
     * Get ouvFonc
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOuvFonc() {
        return $this->ouvFonc;
    }

}
