<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgTmpValidEdilabo
 *
 * @ORM\Table(name="pg_tmp_valid_edilabo")
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgTmpValidEdilaboRepository")
 * 
 */
class PgTmpValidEdilabo {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_tmp_valid_edilabo_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="demande_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $demandeId;

    /**
     * @var string
     *
     * @ORM\Column(name="fichier_rps_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $fichierRpsId;

    /**
     * @var string
     *
     * @ORM\Column(name="code_demande", type="string", length=100, nullable=true)
     */
    private $codeDemande;

    /**
     * @var string
     *
     * @ORM\Column(name="code_prelevement", type="string", length=100, nullable=true)
     */
    private $codePrelevement;

    /**
     * @var string
     *
     * @ORM\Column(name="code_station", type="string", length=50, nullable=true)
     */
    private $codeStation;

    /**
     * @var string
     *
     * @ORM\Column(name="date_prel", type="string", length=50, nullable=true)
     */
    private $datePrel;

    /**
     * @var string
     *
     * @ORM\Column(name="heure_prel", type="string", length=50, nullable=true)
     */
    private $heurePrel;

    /**
     * @var string
     *
     * @ORM\Column(name="code_support", type="string", length=2, nullable=true)
     */
    private $codeSupport;

    /**
     * @var string
     *
     * @ORM\Column(name="zone_vert", type="string", length=2, nullable=true)
     */
    private $zoneVert;

    /**
     * @var string
     *
     * @ORM\Column(name="prof", type="string", length=10, nullable=true)
     */
    private $prof;

    /**
     * @var string
     *
     * @ORM\Column(name="preleveur", type="string", length=14, nullable=true)
     */
    private $preleveur;

    /**
     * @var string
     *
     * @ORM\Column(name="in_situ", type="string", length=1, nullable=true)
     */
    private $inSitu;

    /**
     * @var string
     *
     * @ORM\Column(name="date_m", type="string", length=50, nullable=true)
     */
    private $dateM;

    /**
     * @var string
     *
     * @ORM\Column(name="heure_m", type="string", length=50, nullable=true)
     */
    private $heureM;

    /**
     * @var string
     *
     * @ORM\Column(name="code_parametre", type="string", length=5, nullable=true)
     */
    private $codeParametre;

    /**
     * @var string
     *
     * @ORM\Column(name="res_m", type="string", length=20, nullable=true)
     */
    private $resM;

    /**
     * @var string
     *
     * @ORM\Column(name="code_rq_m", type="string", length=2, nullable=true)
     */
    private $codeRqM;

    /**
     * @var string
     *
     * @ORM\Column(name="lq_m", type="string", length=20, nullable=true)
     */
    private $lqM;

    /**
     * @var string
     *
     * @ORM\Column(name="code_fraction", type="string", length=3, nullable=true)
     */
    private $codeFraction;

    /**
     * @var string
     *
     * @ORM\Column(name="code_unite", type="string", length=5, nullable=true)
     */
    private $codeUnite;

    /**
     * @var string
     *
     * @ORM\Column(name="labo", type="string", length=14, nullable=true)
     */
    private $labo;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="string", length=20000, nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="num_ordre", type="string", length=4, nullable=true)
     */
    private $numOrdre;

    /**
     * @var string
     *
     * @ORM\Column(name="meth_prel", type="string", length=5, nullable=true)
     */
    private $methPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="conform_prel", type="string", length=1, nullable=true)
     */
    private $conformPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="accred_prel", type="string", length=1, nullable=true)
     */
    private $accredPrel;
    
    /**
     * @var string
     *
     * @ORM\Column(name="agre_prel", type="string", length=1, nullable=true)
     */
    private $agrePrel;

    /**
     * @var string
     *
     * @ORM\Column(name="reserv_prel", type="string", length=1, nullable=true)
     */
    private $reservPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="x_prel", type="string", length=20, nullable=true)
     */
    private $xPrel;

    /**
     * @var string
     *
     * @ORM\Column(name="y_prel", type="string", length=20, nullable=true)
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
     * @ORM\Column(name="meth_ana", type="string", length=5, nullable=true)
     */
    private $methAna;

    /**
     * @var string
     *
     * @ORM\Column(name="ref_ana_labo", type="string", length=100, nullable=true)
     */
    private $refAnaLabo;

    /**
     * @var string
     *
     * @ORM\Column(name="accred_ana", type="string", length=1, nullable=true)
     */
    private $accredAna;

    /**
     * @var string
     *
     * @ORM\Column(name="confirm_ana", type="string", length=1, nullable=true)
     */
    private $confirmAna;

    /**
     * @var string
     *
     * @ORM\Column(name="reserv_ana", type="string", length=1, nullable=true)
     */
    private $reservAna;

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
     * @ORM\Column(name="complet_ech", type="string", length=1, nullable=true)
     */
    private $completEch;

    /**
     * @var string
     *
     * @ORM\Column(name="accept_ech", type="string", length=1, nullable=true)
     */
    private $acceptEch;

    /**
     * @var string
     *
     * @ORM\Column(name="date_recep_ech", type="string", length=50, nullable=true)
     */
    private $dateRecepEch;

    /**
     * @var string
     *
     * @ORM\Column(name="code_statut", type="string", length=1, nullable=true)
     */
    private $codeStatut;

    /**
     * @var string
     *
     * @ORM\Column(name="param_prog_id", type="decimal", precision=38, scale=0, nullable=true)
     */
    private $paramProgId;
    
    /**
     * @var string
     *
     * @ORM\Column(name="comment_prel", type="string", length=20000, nullable=true)
     */
    private $commentPrel;

    /**
     * Get id
     *
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set demandeId
     *
     * @param string $demandeId
     *
     * @return PgTmpValidEdilabo
     */
    public function setDemandeId($demandeId) {
        $this->demandeId = $demandeId;

        return $this;
    }

    /**
     * Get demandeId
     *
     * @return string
     */
    public function getDemandeId() {
        return $this->demandeId;
    }

    /**
     * Set fichierRpsId
     *
     * @param string $fichierRpsId
     *
     * @return PgTmpValidEdilabo
     */
    public function setFichierRpsId($fichierRpsId) {
        $this->fichierRpsId = $fichierRpsId;

        return $this;
    }

    /**
     * Get fichierRpsId
     *
     * @return string
     */
    public function getFichierRpsId() {
        return $this->fichierRpsId;
    }

    /**
     * Set codeDemande
     *
     * @param string $codeDemande
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeDemande($codeDemande) {
        $this->codeDemande = $codeDemande;

        return $this;
    }

    /**
     * Get codeDemande
     *
     * @return string
     */
    public function getCodeDemande() {
        return $this->codeDemande;
    }

    /**
     * Set codePrelevement
     *
     * @param string $codePrelevement
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodePrelevement($codePrelevement) {
        $this->codePrelevement = $codePrelevement;

        return $this;
    }

    /**
     * Get codePrelevement
     *
     * @return string
     */
    public function getCodePrelevement() {
        return $this->codePrelevement;
    }

    /**
     * Set codeStation
     *
     * @param string $codeStation
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeStation($codeStation) {
        $this->codeStation = $codeStation;

        return $this;
    }

    /**
     * Get codeStation
     *
     * @return string
     */
    public function getCodeStation() {
        return $this->codeStation;
    }

    /**
     * Set datePrel
     *
     * @param string $datePrel
     *
     * @return PgTmpValidEdilabo
     */
    public function setDatePrel($datePrel) {
        $this->datePrel = $datePrel;

        return $this;
    }

    /**
     * Get datePrel
     *
     * @return string
     */
    public function getDatePrel() {
        return $this->datePrel;
    }

    /**
     * Set heurePrel
     *
     * @param string $heurePrel
     *
     * @return PgTmpValidEdilabo
     */
    public function setHeurePrel($heurePrel) {
        $this->heurePrel = $heurePrel;

        return $this;
    }

    /**
     * Get heurePrel
     *
     * @return string
     */
    public function getHeurePrel() {
        return $this->heurePrel;
    }

    /**
     * Set codeSupport
     *
     * @param string $codeSupport
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeSupport($codeSupport) {
        $this->codeSupport = $codeSupport;

        return $this;
    }

    /**
     * Get codeSupport
     *
     * @return string
     */
    public function getCodeSupport() {
        return $this->codeSupport;
    }

    /**
     * Set zoneVert
     *
     * @param string $zoneVert
     *
     * @return PgTmpValidEdilabo
     */
    public function setZoneVert($zoneVert) {
        $this->zoneVert = $zoneVert;

        return $this;
    }

    /**
     * Get zoneVert
     *
     * @return string
     */
    public function getZoneVert() {
        return $this->zoneVert;
    }

    /**
     * Set prof
     *
     * @param string $prof
     *
     * @return PgTmpValidEdilabo
     */
    public function setProf($prof) {
        $this->prof = $prof;

        return $this;
    }

    /**
     * Get prof
     *
     * @return string
     */
    public function getProf() {
        return $this->prof;
    }

    /**
     * Set preleveur
     *
     * @param string $preleveur
     *
     * @return PgTmpValidEdilabo
     */
    public function setPreleveur($preleveur) {
        $this->preleveur = $preleveur;

        return $this;
    }

    /**
     * Get preleveur
     *
     * @return string
     */
    public function getPreleveur() {
        return $this->preleveur;
    }

    /**
     * Set inSitu
     *
     * @param string $inSitu
     *
     * @return PgTmpValidEdilabo
     */
    public function setInSitu($inSitu) {
        $this->inSitu = $inSitu;

        return $this;
    }

    /**
     * Get inSitu
     *
     * @return string
     */
    public function getInSitu() {
        return $this->inSitu;
    }

    /**
     * Set dateM
     *
     * @param string $dateM
     *
     * @return PgTmpValidEdilabo
     */
    public function setDateM($dateM) {
        $this->dateM = $dateM;

        return $this;
    }

    /**
     * Get dateM
     *
     * @return string
     */
    public function getDateM() {
        return $this->dateM;
    }

    /**
     * Set heureM
     *
     * @param string $heureM
     *
     * @return PgTmpValidEdilabo
     */
    public function setHeureM($heureM) {
        $this->heureM = $heureM;

        return $this;
    }

    /**
     * Get heureM
     *
     * @return string
     */
    public function getHeureM() {
        return $this->heureM;
    }

    /**
     * Set codeParametre
     *
     * @param string $codeParametre
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeParametre($codeParametre) {
        $this->codeParametre = $codeParametre;

        return $this;
    }

    /**
     * Get codeParametre
     *
     * @return string
     */
    public function getCodeParametre() {
        return $this->codeParametre;
    }

    /**
     * Set resM
     *
     * @param string $resM
     *
     * @return PgTmpValidEdilabo
     */
    public function setResM($resM) {
        $this->resM = $resM;

        return $this;
    }

    /**
     * Get resM
     *
     * @return string
     */
    public function getResM() {
        return $this->resM;
    }

    /**
     * Set codeRqM
     *
     * @param string $codeRqM
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeRqM($codeRqM) {
        $this->codeRqM = $codeRqM;

        return $this;
    }

    /**
     * Get codeRqM
     *
     * @return string
     */
    public function getCodeRqM() {
        return $this->codeRqM;
    }

    /**
     * Set lqM
     *
     * @param string $lqM
     *
     * @return PgTmpValidEdilabo
     */
    public function setLqM($lqM) {
        $this->lqM = $lqM;

        return $this;
    }

    /**
     * Get lqM
     *
     * @return string
     */
    public function getLqM() {
        return $this->lqM;
    }

    /**
     * Set codeFraction
     *
     * @param string $codeFraction
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeFraction($codeFraction) {
        $this->codeFraction = $codeFraction;

        return $this;
    }

    /**
     * Get codeFraction
     *
     * @return string
     */
    public function getCodeFraction() {
        return $this->codeFraction;
    }

    /**
     * Set codeUnite
     *
     * @param string $codeUnite
     *
     * @return PgTmpValidEdilabo
     */
    public function setCodeUnite($codeUnite) {
        $this->codeUnite = $codeUnite;

        return $this;
    }

    /**
     * Get codeUnite
     *
     * @return string
     */
    public function getCodeUnite() {
        return $this->codeUnite;
    }

    /**
     * Set labo
     *
     * @param string $labo
     *
     * @return PgTmpValidEdilabo
     */
    public function setLabo($labo) {
        $this->labo = $labo;

        return $this;
    }

    /**
     * Get labo
     *
     * @return string
     */
    public function getLabo() {
        return $this->labo;
    }

    /**
     * Set commentaire
     *
     * @param string $commentaire
     *
     * @return PgTmpValidEdilabo
     */
    public function setCommentaire($commentaire) {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * Get commentaire
     *
     * @return string
     */
    public function getCommentaire() {
        return $this->commentaire;
    }

    public function getNumOrdre() {
        return $this->numOrdre;
    }

    public function getMethPrel() {
        return $this->methPrel;
    }

    public function getConformPrel() {
        return $this->conformPrel;
    }

    public function getAccredPrel() {
        return $this->accredPrel;
    }

    public function getReservPrel() {
        return $this->reservPrel;
    }

    public function getXPrel() {
        return $this->xPrel;
    }

    public function getYPrel() {
        return $this->yPrel;
    }

    public function getMethAna() {
        return $this->methAna;
    }

    public function getRefAnaLabo() {
        return $this->refAnaLabo;
    }

    public function getAccredAna() {
        return $this->accredAna;
    }

    public function getConfirmAna() {
        return $this->confirmAna;
    }

    public function getReservAna() {
        return $this->reservAna;
    }

    public function getRefEchCmd() {
        return $this->refEchCmd;
    }

    public function getRefEchPrel() {
        return $this->refEchPrel;
    }

    public function getRefEchLabo() {
        return $this->refEchLabo;
    }

    public function getCompletEch() {
        return $this->completEch;
    }

    public function getAcceptEch() {
        return $this->acceptEch;
    }

    public function getDateRecepEch() {
        return $this->dateRecepEch;
    }

    public function getCodeStatut() {
        return $this->codeStatut;
    }

    public function getParamProgId() {
        return $this->paramProgId;
    }

    public function setNumOrdre($numOrdre) {
        $this->numOrdre = $numOrdre;
    }

    public function setMethPrel($methPrel) {
        $this->methPrel = $methPrel;
    }

    public function setConformPrel($conformPrel) {
        $this->conformPrel = $conformPrel;
    }

    public function setAccredPrel($accredPrel) {
        $this->accredPrel = $accredPrel;
    }

    public function setReservPrel($reservPrel) {
        $this->reservPrel = $reservPrel;
    }

    public function setXPrel($xPrel) {
        $this->xPrel = $xPrel;
    }

    public function setYPrel($yPrel) {
        $this->yPrel = $yPrel;
    }

    public function setMethAna($methAna) {
        $this->methAna = $methAna;
    }

    public function setRefAnaLabo($refAnaLabo) {
        $this->refAnaLabo = $refAnaLabo;
    }

    public function setAccredAna($accredAna) {
        $this->accredAna = $accredAna;
    }

    public function setConfirmAna($confirmAna) {
        $this->confirmAna = $confirmAna;
    }

    public function setReservAna($reservAna) {
        $this->reservAna = $reservAna;
    }

    public function setRefEchCmd($refEchCmd) {
        $this->refEchCmd = $refEchCmd;
    }

    public function setRefEchPrel($refEchPrel) {
        $this->refEchPrel = $refEchPrel;
    }

    public function setRefEchLabo($refEchLabo) {
        $this->refEchLabo = $refEchLabo;
    }

    public function setCompletEch($completEch) {
        $this->completEch = $completEch;
    }

    public function setAcceptEch($acceptEch) {
        $this->acceptEch = $acceptEch;
    }

    public function setDateRecepEch($dateRecepEch) {
        $this->dateRecepEch = $dateRecepEch;
    }

    public function setCodeStatut($codeStatut) {
        $this->codeStatut = $codeStatut;
    }

    public function setParamProgId($paramProgId) {
        $this->paramProgId = $paramProgId;
    }
    
    public function getAgrePrel() {
        return $this->agrePrel;
    }

    public function getLocalisation() {
        return $this->localisation;
    }

    public function setAgrePrel($agrePrel) {
        $this->agrePrel = $agrePrel;
    }

    public function setLocalisation($localisation) {
        $this->localisation = $localisation;
    }
    
    public function getCommentPrel() {
        return $this->commentPrel;
    }

    public function setCommentPrel($commentPrel) {
        $this->commentPrel = $commentPrel;
    }

}
