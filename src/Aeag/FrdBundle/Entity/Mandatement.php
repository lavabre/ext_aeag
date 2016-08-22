<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mandatement
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Mandatement")
 * @ORM\Entity(repositoryClass="Aeag\FrdBundle\Repository\MandatementRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class Mandatement {

    /**
     * @ORM\Id
     * @ORM\Column(name="etfr_id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="fraisDeplacement_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $etfrId;

    /**
     * @ORM\Column(name="adrcor_id_benef",type="integer", nullable=true))
     * */
    protected $adrcorId;

    /**
     * @ORM\Column(name="num_ordre_ope_budg",type="integer", nullable=true))
     * */
    protected $numOrdreOpbudg;

    /**
     * @ORM\Column(name="exercice", type="integer", nullable=true)
     */
    private $exercice;

    /**
     * @ORM\Column(name="num_mandat", type="string", length=10, nullable=true)
     */
    private $numMandat;

    /**
     * @ORM\Column(name="num_bordereau", type="integer", nullable=true)
     */
    private $numBordereau;

    /**
     * @ORM\Column(name="etat_mandat", type="string", length=4, nullable=true)
     */
    private $etatMandat;

    /**
     * @ORM\Column(name="date_etat_frais", type="datetime", nullable=true)
     */
    protected $dateEtatFrais;

    /**
     * @ORM\Column(name="date_mandatement", type="datetime", nullable=true)
     */
    protected $dateMandatement;

    /**
     * @ORM\Column(name="date_paiement", type="datetime", nullable=true)
     */
    protected $datePaiement;

    /**
     * @ORM\Column(name="texte1_mandat", type="string", length=36, nullable=true)
     */
    private $texte1Mandat;

    /**
     * @ORM\Column(name="texte2_mandat", type="string", length=36, nullable=true)
     */
    private $texte2Mandat;

    /**
     * @ORM\Column(name="texte3_mandat", type="string", length=36, nullable=true)
     */
    private $texte3Mandat;

    /**
     * @ORM\Column(name="texte4_mandat", type="string", length=36, nullable=true)
     */
    private $texte4Mandat;

    /**
     * @ORM\Column(name="texte5_mandat", type="string", length=36, nullable=true)
     */
    private $texte5Mandat;

    /**
     * @ORM\Column(name="texte6_mandat", type="string", length=36, nullable=true)
     */
    private $texte6Mandat;

    function getEtfrId() {
        return $this->etfrId;
    }

    function getAdrcorId() {
        return $this->adrcorId;
    }

    function getNumOrdreOpbudg() {
        return $this->numOrdreOpbudg;
    }

    function getExercice() {
        return $this->exercice;
    }

    function getNumMandat() {
        return $this->numMandat;
    }

    function getNumBordereau() {
        return $this->numBordereau;
    }

    function getEtatMandat() {
        return $this->etatMandat;
    }

    function getDateEtatFrais() {
        return $this->dateEtatFrais;
    }

    function getDateMandatement() {
        return $this->dateMandatement;
    }

    function getDatePaiement() {
        return $this->datePaiement;
    }

    function getTexte1Mandat() {
        return $this->texte1Mandat;
    }

    function getTexte2Mandat() {
        return $this->texte2Mandat;
    }

    function getTexte3Mandat() {
        return $this->texte3Mandat;
    }

    function getTexte4Mandat() {
        return $this->texte4Mandat;
    }

    function getTexte5Mandat() {
        return $this->texte5Mandat;
    }

    function getTexte6Mandat() {
        return $this->texte6Mandat;
    }

    function setEtfrId($etfrId) {
        $this->etfrId = $etfrId;
    }

    function setAdrcorId($adrcorId) {
        $this->adrcorId = $adrcorId;
    }

    function setNumOrdreOpbudg($numOrdreOpbudg) {
        $this->numOrdreOpbudg = $numOrdreOpbudg;
    }

    function setExercice($exercice) {
        $this->exercice = $exercice;
    }

    function setNumMandat($numMandat) {
        $this->numMandat = $numMandat;
    }

    function setNumBordereau($numBordereau) {
        $this->numBordereau = $numBordereau;
    }

    function setEtatMandat($etatMandat) {
        $this->etatMandat = $etatMandat;
    }

    function setDateEtatFrais($dateEtatFrais) {
        $this->dateEtatFrais = $dateEtatFrais;
    }

    function setDateMandatement($dateMandatement) {
        $this->dateMandatement = $dateMandatement;
    }

    function setDatePaiement($datePaiement) {
        $this->datePaiement = $datePaiement;
    }

    function setTexte1Mandat($texte1Mandat) {
        $this->texte1Mandat = $texte1Mandat;
    }

    function setTexte2Mandat($texte2Mandat) {
        $this->texte2Mandat = $texte2Mandat;
    }

    function setTexte3Mandat($texte3Mandat) {
        $this->texte3Mandat = $texte3Mandat;
    }

    function setTexte4Mandat($texte4Mandat) {
        $this->texte4Mandat = $texte4Mandat;
    }

    function setTexte5Mandat($texte5Mandat) {
        $this->texte5Mandat = $texte5Mandat;
    }

    function setTexte6Mandat($texte6Mandat) {
        $this->texte6Mandat = $texte6Mandat;
    }

}
