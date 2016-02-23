<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgCmdMphytUr
 *
 * @ORM\Table(name="pg_cmd_mphyt_ur", indexes={@ORM\Index(name="IDX_7F13F8A1D8E1F6AA", columns={"prelev_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgCmdMphytUrRepository")
 */
class PgCmdMphytUr
{
    /**
     * @var string
     *
     * @ORM\Column(name="type_ur", type="string", length=1, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typeUr;

    /**
     * @var string
     *
     * @ORM\Column(name="p_recouv_ur", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $pRecouvUr;

    /**
     * @var string
     *
     * @ORM\Column(name="longueur_ur", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $longueurUr;

    /**
     * @var string
     *
     * @ORM\Column(name="largeur_ur", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $largeurUr;

    /**
     * @var string
     *
     * @ORM\Column(name="p_surf_veget_ur", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $pSurfVegetUr;

    /**
     * @var string
     *
     * @ORM\Column(name="periphyton", type="string", length=20, nullable=true)
     */
    private $periphyton;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_ch_len", type="integer", nullable=true)
     */
    private $afmChLen;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_pl_len", type="integer", nullable=true)
     */
    private $afmPlLen;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_mouille", type="integer", nullable=true)
     */
    private $afmMouille;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_fos_dis", type="integer", nullable=true)
     */
    private $afmFosDis;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_ch_lot", type="integer", nullable=true)
     */
    private $afmChLot;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_radier", type="integer", nullable=true)
     */
    private $afmRadier;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_cascade", type="integer", nullable=true)
     */
    private $afmCascade;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_pl_courant", type="integer", nullable=true)
     */
    private $afmPlCourant;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_rapide", type="integer", nullable=true)
     */
    private $afmRapide;

    /**
     * @var integer
     *
     * @ORM\Column(name="afm_autre", type="integer", nullable=true)
     */
    private $afmAutre;

    /**
     * @var string
     *
     * @ORM\Column(name="afm_autre_txt", type="string", length=50, nullable=true)
     */
    private $afmAutreTxt;

    /**
     * @var integer
     *
     * @ORM\Column(name="afp_m1", type="integer", nullable=true)
     */
    private $afpM1;

    /**
     * @var integer
     *
     * @ORM\Column(name="afp_m2", type="bigint", nullable=true)
     */
    private $afpM2;

    /**
     * @var integer
     *
     * @ORM\Column(name="afp_m3", type="integer", nullable=true)
     */
    private $afpM3;

    /**
     * @var integer
     *
     * @ORM\Column(name="afp_m4", type="integer", nullable=true)
     */
    private $afpM4;

    /**
     * @var integer
     *
     * @ORM\Column(name="afp_m5", type="integer", nullable=true)
     */
    private $afpM5;

    /**
     * @var integer
     *
     * @ORM\Column(name="afv_m1", type="integer", nullable=true)
     */
    private $afvM1;

    /**
     * @var integer
     *
     * @ORM\Column(name="afv_m2", type="integer", nullable=true)
     */
    private $afvM2;

    /**
     * @var integer
     *
     * @ORM\Column(name="afv_m3", type="integer", nullable=true)
     */
    private $afvM3;

    /**
     * @var integer
     *
     * @ORM\Column(name="afv_m4", type="integer", nullable=true)
     */
    private $afvM4;

    /**
     * @var integer
     *
     * @ORM\Column(name="afv_m5", type="integer", nullable=true)
     */
    private $afvM5;

    /**
     * @var integer
     *
     * @ORM\Column(name="afe_m1", type="integer", nullable=true)
     */
    private $afeM1;

    /**
     * @var integer
     *
     * @ORM\Column(name="afe_m2", type="integer", nullable=true)
     */
    private $afeM2;

    /**
     * @var integer
     *
     * @ORM\Column(name="afe_m3", type="integer", nullable=true)
     */
    private $afeM3;

    /**
     * @var integer
     *
     * @ORM\Column(name="afe_m4", type="integer", nullable=true)
     */
    private $afeM4;

    /**
     * @var integer
     *
     * @ORM\Column(name="afe_m5", type="integer", nullable=true)
     */
    private $afeM5;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m1", type="integer", nullable=true)
     */
    private $afsM1;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m2", type="integer", nullable=true)
     */
    private $afsM2;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m3", type="integer", nullable=true)
     */
    private $afsM3;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m4", type="integer", nullable=true)
     */
    private $afsM4;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m5", type="integer", nullable=true)
     */
    private $afsM5;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m6", type="integer", nullable=true)
     */
    private $afsM6;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m7", type="integer", nullable=true)
     */
    private $afsM7;

    /**
     * @var integer
     *
     * @ORM\Column(name="afs_m8", type="integer", nullable=true)
     */
    private $afsM8;

    /**
     * @var \PgCmdPrelevHbMphyt
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="PgCmdPrelevHbMphyt")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prelev_id", referencedColumnName="prelev_id")
     * })
     */
    private $prelev;



    /**
     * Set typeUr
     *
     * @param string $typeUr
     *
     * @return PgCmdMphytUr
     */
    public function setTypeUr($typeUr)
    {
        $this->typeUr = $typeUr;

        return $this;
    }

    /**
     * Get typeUr
     *
     * @return string
     */
    public function getTypeUr()
    {
        return $this->typeUr;
    }

    /**
     * Set pRecouvUr
     *
     * @param string $pRecouvUr
     *
     * @return PgCmdMphytUr
     */
    public function setPRecouvUr($pRecouvUr)
    {
        $this->pRecouvUr = $pRecouvUr;

        return $this;
    }

    /**
     * Get pRecouvUr
     *
     * @return string
     */
    public function getPRecouvUr()
    {
        return $this->pRecouvUr;
    }

    /**
     * Set longueurUr
     *
     * @param string $longueurUr
     *
     * @return PgCmdMphytUr
     */
    public function setLongueurUr($longueurUr)
    {
        $this->longueurUr = $longueurUr;

        return $this;
    }

    /**
     * Get longueurUr
     *
     * @return string
     */
    public function getLongueurUr()
    {
        return $this->longueurUr;
    }

    /**
     * Set largeurUr
     *
     * @param string $largeurUr
     *
     * @return PgCmdMphytUr
     */
    public function setLargeurUr($largeurUr)
    {
        $this->largeurUr = $largeurUr;

        return $this;
    }

    /**
     * Get largeurUr
     *
     * @return string
     */
    public function getLargeurUr()
    {
        return $this->largeurUr;
    }

    /**
     * Set pSurfVegetUr
     *
     * @param string $pSurfVegetUr
     *
     * @return PgCmdMphytUr
     */
    public function setPSurfVegetUr($pSurfVegetUr)
    {
        $this->pSurfVegetUr = $pSurfVegetUr;

        return $this;
    }

    /**
     * Get pSurfVegetUr
     *
     * @return string
     */
    public function getPSurfVegetUr()
    {
        return $this->pSurfVegetUr;
    }

    /**
     * Set periphyton
     *
     * @param string $periphyton
     *
     * @return PgCmdMphytUr
     */
    public function setPeriphyton($periphyton)
    {
        $this->periphyton = $periphyton;

        return $this;
    }

    /**
     * Get periphyton
     *
     * @return string
     */
    public function getPeriphyton()
    {
        return $this->periphyton;
    }

    /**
     * Set afmChLen
     *
     * @param integer $afmChLen
     *
     * @return PgCmdMphytUr
     */
    public function setAfmChLen($afmChLen)
    {
        $this->afmChLen = $afmChLen;

        return $this;
    }

    /**
     * Get afmChLen
     *
     * @return integer
     */
    public function getAfmChLen()
    {
        return $this->afmChLen;
    }

    /**
     * Set afmPlLen
     *
     * @param integer $afmPlLen
     *
     * @return PgCmdMphytUr
     */
    public function setAfmPlLen($afmPlLen)
    {
        $this->afmPlLen = $afmPlLen;

        return $this;
    }

    /**
     * Get afmPlLen
     *
     * @return integer
     */
    public function getAfmPlLen()
    {
        return $this->afmPlLen;
    }

    /**
     * Set afmMouille
     *
     * @param integer $afmMouille
     *
     * @return PgCmdMphytUr
     */
    public function setAfmMouille($afmMouille)
    {
        $this->afmMouille = $afmMouille;

        return $this;
    }

    /**
     * Get afmMouille
     *
     * @return integer
     */
    public function getAfmMouille()
    {
        return $this->afmMouille;
    }

    /**
     * Set afmFosDis
     *
     * @param integer $afmFosDis
     *
     * @return PgCmdMphytUr
     */
    public function setAfmFosDis($afmFosDis)
    {
        $this->afmFosDis = $afmFosDis;

        return $this;
    }

    /**
     * Get afmFosDis
     *
     * @return integer
     */
    public function getAfmFosDis()
    {
        return $this->afmFosDis;
    }

    /**
     * Set afmChLot
     *
     * @param integer $afmChLot
     *
     * @return PgCmdMphytUr
     */
    public function setAfmChLot($afmChLot)
    {
        $this->afmChLot = $afmChLot;

        return $this;
    }

    /**
     * Get afmChLot
     *
     * @return integer
     */
    public function getAfmChLot()
    {
        return $this->afmChLot;
    }

    /**
     * Set afmRadier
     *
     * @param integer $afmRadier
     *
     * @return PgCmdMphytUr
     */
    public function setAfmRadier($afmRadier)
    {
        $this->afmRadier = $afmRadier;

        return $this;
    }

    /**
     * Get afmRadier
     *
     * @return integer
     */
    public function getAfmRadier()
    {
        return $this->afmRadier;
    }

    /**
     * Set afmCascade
     *
     * @param integer $afmCascade
     *
     * @return PgCmdMphytUr
     */
    public function setAfmCascade($afmCascade)
    {
        $this->afmCascade = $afmCascade;

        return $this;
    }

    /**
     * Get afmCascade
     *
     * @return integer
     */
    public function getAfmCascade()
    {
        return $this->afmCascade;
    }

    /**
     * Set afmPlCourant
     *
     * @param integer $afmPlCourant
     *
     * @return PgCmdMphytUr
     */
    public function setAfmPlCourant($afmPlCourant)
    {
        $this->afmPlCourant = $afmPlCourant;

        return $this;
    }

    /**
     * Get afmPlCourant
     *
     * @return integer
     */
    public function getAfmPlCourant()
    {
        return $this->afmPlCourant;
    }

    /**
     * Set afmRapide
     *
     * @param integer $afmRapide
     *
     * @return PgCmdMphytUr
     */
    public function setAfmRapide($afmRapide)
    {
        $this->afmRapide = $afmRapide;

        return $this;
    }

    /**
     * Get afmRapide
     *
     * @return integer
     */
    public function getAfmRapide()
    {
        return $this->afmRapide;
    }

    /**
     * Set afmAutre
     *
     * @param integer $afmAutre
     *
     * @return PgCmdMphytUr
     */
    public function setAfmAutre($afmAutre)
    {
        $this->afmAutre = $afmAutre;

        return $this;
    }

    /**
     * Get afmAutre
     *
     * @return integer
     */
    public function getAfmAutre()
    {
        return $this->afmAutre;
    }

    /**
     * Set afmAutreTxt
     *
     * @param string $afmAutreTxt
     *
     * @return PgCmdMphytUr
     */
    public function setAfmAutreTxt($afmAutreTxt)
    {
        $this->afmAutreTxt = $afmAutreTxt;

        return $this;
    }

    /**
     * Get afmAutreTxt
     *
     * @return string
     */
    public function getAfmAutreTxt()
    {
        return $this->afmAutreTxt;
    }

    /**
     * Set afpM1
     *
     * @param integer $afpM1
     *
     * @return PgCmdMphytUr
     */
    public function setAfpM1($afpM1)
    {
        $this->afpM1 = $afpM1;

        return $this;
    }

    /**
     * Get afpM1
     *
     * @return integer
     */
    public function getAfpM1()
    {
        return $this->afpM1;
    }

    /**
     * Set afpM2
     *
     * @param integer $afpM2
     *
     * @return PgCmdMphytUr
     */
    public function setAfpM2($afpM2)
    {
        $this->afpM2 = $afpM2;

        return $this;
    }

    /**
     * Get afpM2
     *
     * @return integer
     */
    public function getAfpM2()
    {
        return $this->afpM2;
    }

    /**
     * Set afpM3
     *
     * @param integer $afpM3
     *
     * @return PgCmdMphytUr
     */
    public function setAfpM3($afpM3)
    {
        $this->afpM3 = $afpM3;

        return $this;
    }

    /**
     * Get afpM3
     *
     * @return integer
     */
    public function getAfpM3()
    {
        return $this->afpM3;
    }

    /**
     * Set afpM4
     *
     * @param integer $afpM4
     *
     * @return PgCmdMphytUr
     */
    public function setAfpM4($afpM4)
    {
        $this->afpM4 = $afpM4;

        return $this;
    }

    /**
     * Get afpM4
     *
     * @return integer
     */
    public function getAfpM4()
    {
        return $this->afpM4;
    }

    /**
     * Set afpM5
     *
     * @param integer $afpM5
     *
     * @return PgCmdMphytUr
     */
    public function setAfpM5($afpM5)
    {
        $this->afpM5 = $afpM5;

        return $this;
    }

    /**
     * Get afpM5
     *
     * @return integer
     */
    public function getAfpM5()
    {
        return $this->afpM5;
    }

    /**
     * Set afvM1
     *
     * @param integer $afvM1
     *
     * @return PgCmdMphytUr
     */
    public function setAfvM1($afvM1)
    {
        $this->afvM1 = $afvM1;

        return $this;
    }

    /**
     * Get afvM1
     *
     * @return integer
     */
    public function getAfvM1()
    {
        return $this->afvM1;
    }

    /**
     * Set afvM2
     *
     * @param integer $afvM2
     *
     * @return PgCmdMphytUr
     */
    public function setAfvM2($afvM2)
    {
        $this->afvM2 = $afvM2;

        return $this;
    }

    /**
     * Get afvM2
     *
     * @return integer
     */
    public function getAfvM2()
    {
        return $this->afvM2;
    }

    /**
     * Set afvM3
     *
     * @param integer $afvM3
     *
     * @return PgCmdMphytUr
     */
    public function setAfvM3($afvM3)
    {
        $this->afvM3 = $afvM3;

        return $this;
    }

    /**
     * Get afvM3
     *
     * @return integer
     */
    public function getAfvM3()
    {
        return $this->afvM3;
    }

    /**
     * Set afvM4
     *
     * @param integer $afvM4
     *
     * @return PgCmdMphytUr
     */
    public function setAfvM4($afvM4)
    {
        $this->afvM4 = $afvM4;

        return $this;
    }

    /**
     * Get afvM4
     *
     * @return integer
     */
    public function getAfvM4()
    {
        return $this->afvM4;
    }

    /**
     * Set afvM5
     *
     * @param integer $afvM5
     *
     * @return PgCmdMphytUr
     */
    public function setAfvM5($afvM5)
    {
        $this->afvM5 = $afvM5;

        return $this;
    }

    /**
     * Get afvM5
     *
     * @return integer
     */
    public function getAfvM5()
    {
        return $this->afvM5;
    }

    /**
     * Set afeM1
     *
     * @param integer $afeM1
     *
     * @return PgCmdMphytUr
     */
    public function setAfeM1($afeM1)
    {
        $this->afeM1 = $afeM1;

        return $this;
    }

    /**
     * Get afeM1
     *
     * @return integer
     */
    public function getAfeM1()
    {
        return $this->afeM1;
    }

    /**
     * Set afeM2
     *
     * @param integer $afeM2
     *
     * @return PgCmdMphytUr
     */
    public function setAfeM2($afeM2)
    {
        $this->afeM2 = $afeM2;

        return $this;
    }

    /**
     * Get afeM2
     *
     * @return integer
     */
    public function getAfeM2()
    {
        return $this->afeM2;
    }

    /**
     * Set afeM3
     *
     * @param integer $afeM3
     *
     * @return PgCmdMphytUr
     */
    public function setAfeM3($afeM3)
    {
        $this->afeM3 = $afeM3;

        return $this;
    }

    /**
     * Get afeM3
     *
     * @return integer
     */
    public function getAfeM3()
    {
        return $this->afeM3;
    }

    /**
     * Set afeM4
     *
     * @param integer $afeM4
     *
     * @return PgCmdMphytUr
     */
    public function setAfeM4($afeM4)
    {
        $this->afeM4 = $afeM4;

        return $this;
    }

    /**
     * Get afeM4
     *
     * @return integer
     */
    public function getAfeM4()
    {
        return $this->afeM4;
    }

    /**
     * Set afeM5
     *
     * @param integer $afeM5
     *
     * @return PgCmdMphytUr
     */
    public function setAfeM5($afeM5)
    {
        $this->afeM5 = $afeM5;

        return $this;
    }

    /**
     * Get afeM5
     *
     * @return integer
     */
    public function getAfeM5()
    {
        return $this->afeM5;
    }

    /**
     * Set afsM1
     *
     * @param integer $afsM1
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM1($afsM1)
    {
        $this->afsM1 = $afsM1;

        return $this;
    }

    /**
     * Get afsM1
     *
     * @return integer
     */
    public function getAfsM1()
    {
        return $this->afsM1;
    }

    /**
     * Set afsM2
     *
     * @param integer $afsM2
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM2($afsM2)
    {
        $this->afsM2 = $afsM2;

        return $this;
    }

    /**
     * Get afsM2
     *
     * @return integer
     */
    public function getAfsM2()
    {
        return $this->afsM2;
    }

    /**
     * Set afsM3
     *
     * @param integer $afsM3
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM3($afsM3)
    {
        $this->afsM3 = $afsM3;

        return $this;
    }

    /**
     * Get afsM3
     *
     * @return integer
     */
    public function getAfsM3()
    {
        return $this->afsM3;
    }

    /**
     * Set afsM4
     *
     * @param integer $afsM4
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM4($afsM4)
    {
        $this->afsM4 = $afsM4;

        return $this;
    }

    /**
     * Get afsM4
     *
     * @return integer
     */
    public function getAfsM4()
    {
        return $this->afsM4;
    }

    /**
     * Set afsM5
     *
     * @param integer $afsM5
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM5($afsM5)
    {
        $this->afsM5 = $afsM5;

        return $this;
    }

    /**
     * Get afsM5
     *
     * @return integer
     */
    public function getAfsM5()
    {
        return $this->afsM5;
    }

    /**
     * Set afsM6
     *
     * @param integer $afsM6
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM6($afsM6)
    {
        $this->afsM6 = $afsM6;

        return $this;
    }

    /**
     * Get afsM6
     *
     * @return integer
     */
    public function getAfsM6()
    {
        return $this->afsM6;
    }

    /**
     * Set afsM7
     *
     * @param integer $afsM7
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM7($afsM7)
    {
        $this->afsM7 = $afsM7;

        return $this;
    }

    /**
     * Get afsM7
     *
     * @return integer
     */
    public function getAfsM7()
    {
        return $this->afsM7;
    }

    /**
     * Set afsM8
     *
     * @param integer $afsM8
     *
     * @return PgCmdMphytUr
     */
    public function setAfsM8($afsM8)
    {
        $this->afsM8 = $afsM8;

        return $this;
    }

    /**
     * Get afsM8
     *
     * @return integer
     */
    public function getAfsM8()
    {
        return $this->afsM8;
    }

    /**
     * Set prelev
     *
     * @param \Aeag\SqeBundle\Entity\PgCmdPrelevHbMphyt $prelev
     *
     * @return PgCmdMphytUr
     */
    public function setPrelev(\Aeag\SqeBundle\Entity\PgCmdPrelevHbMphyt $prelev)
    {
        $this->prelev = $prelev;

        return $this;
    }

    /**
     * Get prelev
     *
     * @return \Aeag\SqeBundle\Entity\PgCmdPrelevHbMphyt
     */
    public function getPrelev()
    {
        return $this->prelev;
    }
}
