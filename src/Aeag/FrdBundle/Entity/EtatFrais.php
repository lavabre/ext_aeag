<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EtatFrais
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="EtatFrais")
 * @ORM\Entity(repositoryClass="Aeag\FrdBundle\Repository\EtatFraisRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class EtatFrais {

    /**
     * @ORM\Id
     * @ORM\Column(name ="id", type="integer")
       */
    private $id;

    /**
     * @ORM\Column(name="annee",type="integer"))
     * */
    protected $annee;

    /**
     * @ORM\Column(name="num",type="integer"))
     * */
    protected $num;

    /**
     * @ORM\Column(name="phase", type="string", length=4)
     */
    private $phase;

    /**
     * @ORM\Column(name="version",type="integer", nullable=true))
     * */
    protected $version;

    /**
     * @ORM\Column(name="cor_id",type="integer"))
     * */
    protected $corId;

    /**
     * @ORM\Column(name="dombanq_id",type="integer", nullable=true))
     * */
    protected $dombanqId;

    /**
     * @ORM\Column(name="service", type="string", length=100, nullable=true)
     */
    private $service;

    /**
     * @ORM\Column(name="fonction", type="string", length=60)
     */
    private $fonction;

    /**
     * @ORM\Column(name="type_contrat", type="string", length=40, nullable=true)
     */
    private $typeContrat;

    /**
     * @ORM\Column(name="indice_categ",type="integer", nullable=true))
     * */
    protected $indiceCateg;

    /**
     * @ORM\Column(name="resid_famil", type="string", length=50)
     */
    private $residFamil;

    /**
     * @ORM\Column(name="resid_admin", type="string", length=50)
     */
    private $residAdmin;

    /**
     * @ORM\Column(name="majoration", type="string", length=50)
     */
    private $majoration;

    /**
     * @ORM\Column(name="reduc_sncf",type="integer", nullable=true))
     * */
    protected $reducSncf;

    /**
     * @ORM\Column(name="cv_vp",type="integer", nullable=true))
     * */
    protected $cvVp;

    /**
     * @ORM\Column(name="km_an",type="integer", nullable=true))
     * */
    protected $kmAn;

    /**
     * @ORM\Column(name="km_etfr",type="integer", nullable=true))
     * */
    protected $kmEtfr;

    /**
     * @ORM\Column(name="obs_gen", type="string", length=1000, nullable=true)
     */
    private $obsGen;

    /**
     * @ORM\Column(name="obs_sup", type="string", length=1000, nullable=true)
     */
    private $obsSup;

    /**
     * @ORM\Column(name="mnt_remb",type="integer", nullable=true))
     * */
    protected $mntRemb;

    /**
     * @ORM\Column(name="mnt_regul",type="integer", nullable=true))
     * */
    protected $mntRegul;

    /**
     * @ORM\Column(name="mnt_a_regul",type="integer", nullable=true))
     * */
    protected $mntARegul;

    /**
     * @ORM\Column(name="regul_visee", type="string", length=1, nullable=true)
     */
    private $regulVisee;

    /**
     * @ORM\Column(name="regul_etfr_id",type="integer", nullable=true))
     * */
    protected $regulEtfrId;

    /**
     * @ORM\Column(name="tr_a_deduire",type="integer", nullable=true))
     * */
    protected $trAdeduire;

    /**
     * @ORM\Column(name="tr_date_arret", type="datetime")
     */
    protected $trDateArret;

    /**
     * @ORM\Column(name="type_etat_frais", type="string", length=3)
     */
    private $typeEtatFrais;

    function getId() {
        return $this->id;
    }

    function getAnnee() {
        return $this->annee;
    }

    function getNum() {
        return $this->num;
    }

    function getPhase() {
        return $this->phase;
    }

    function getVersion() {
        return $this->version;
    }

    function getCorId() {
        return $this->corId;
    }

    function getDombanqId() {
        return $this->dombanqId;
    }

    function getService() {
        return $this->service;
    }

    function getFonction() {
        return $this->fonction;
    }

    function getTypeContrat() {
        return $this->typeContrat;
    }

    function getIndiceCateg() {
        return $this->indiceCateg;
    }

    function getResidFamil() {
        return $this->residFamil;
    }

    function getResidAdmin() {
        return $this->residAdmin;
    }

    function getMajoration() {
        return $this->majoration;
    }

    function getReducSncf() {
        return $this->reducSncf;
    }

    function getCvVp() {
        return $this->cvVp;
    }

    function getKmAn() {
        return $this->kmAn;
    }

    function getKmEtfr() {
        return $this->kmEtfr;
    }

    function getObsGen() {
        return $this->obsGen;
    }

    function getObsSup() {
        return $this->obsSup;
    }

    function getMntRemb() {
        return $this->mntRemb;
    }

    function getMntRegul() {
        return $this->mntRegul;
    }

    function getMntARegul() {
        return $this->mntARegul;
    }

    function getRegulVisee() {
        return $this->regulVisee;
    }

    function getRegulEtfrId() {
        return $this->regulEtfrId;
    }

    function getTrAdeduire() {
        return $this->trAdeduire;
    }

    function getTrDateArret() {
        return $this->trDateArret;
    }

    function getTypeEtatFrais() {
        return $this->typeEtatFrais;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setAnnee($annee) {
        $this->annee = $annee;
    }

    function setNum($num) {
        $this->num = $num;
    }

    function setPhase($phase) {
        $this->phase = $phase;
    }

    function setVersion($version) {
        $this->version = $version;
    }

    function setCorId($corId) {
        $this->corId = $corId;
    }

    function setDombanqId($dombanqId) {
        $this->dombanqId = $dombanqId;
    }

    function setService($service) {
        $this->service = $service;
    }

    function setFonction($fonction) {
        $this->fonction = $fonction;
    }

    function setTypeContrat($typeContrat) {
        $this->typeContrat = $typeContrat;
    }

    function setIndiceCateg($indiceCateg) {
        $this->indiceCateg = $indiceCateg;
    }

    function setResidFamil($residFamil) {
        $this->residFamil = $residFamil;
    }

    function setResidAdmin($residAdmin) {
        $this->residAdmin = $residAdmin;
    }

    function setMajoration($majoration) {
        $this->majoration = $majoration;
    }

    function setReducSncf($reducSncf) {
        $this->reducSncf = $reducSncf;
    }

    function setCvVp($cvVp) {
        $this->cvVp = $cvVp;
    }

    function setKmAn($kmAn) {
        $this->kmAn = $kmAn;
    }

    function setKmEtfr($kmEtfr) {
        $this->kmEtfr = $kmEtfr;
    }

    function setObsGen($obsGen) {
        $this->obsGen = $obsGen;
    }

    function setObsSup($obsSup) {
        $this->obsSup = $obsSup;
    }

    function setMntRemb($mntRemb) {
        $this->mntRemb = $mntRemb;
    }

    function setMntRegul($mntRegul) {
        $this->mntRegul = $mntRegul;
    }

    function setMntARegul($mntARegul) {
        $this->mntARegul = $mntARegul;
    }

    function setRegulVisee($regulVisee) {
        $this->regulVisee = $regulVisee;
    }

    function setRegulEtfrId($regulEtfrId) {
        $this->regulEtfrId = $regulEtfrId;
    }

    function setTrAdeduire($trAdeduire) {
        $this->trAdeduire = $trAdeduire;
    }

    function setTrDateArret($trDateArret) {
        $this->trDateArret = $trDateArret;
    }

    function setTypeEtatFrais($typeEtatFrais) {
        $this->typeEtatFrais = $typeEtatFrais;
    }

}
