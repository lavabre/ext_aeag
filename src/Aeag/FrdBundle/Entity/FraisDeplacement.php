<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FraisDeplacement
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="FraisDeplacement",indexes={@ORM\Index(name="user_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="Aeag\FrdBundle\Repository\FraisDeplacementRepository")
 * @ORM\HasLifecycleCallbacks()
 *
 */
class FraisDeplacement {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\SequenceGenerator(sequenceName="fraisDeplacement_seq", initialValue=1, allocationSize=1)
     * ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(name="user_id",type="integer"))
     * */
    protected $user;

    /**
     * @ORM\Column(name="valider", type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"O", "N"}, message="Cette valeur doit être l'un des choix proposés")
     */
    private $valider;

    /**
     * @ORM\Column(name="exporter", type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"O", "N"}, message="la valeur exporter est erronée")
     */
    private $exporter;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\FrdBundle\Entity\Phase" )
     */
    private $phase;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $datePhase;

    /**

      /**
     * @ORM\Column(type="string", length=150)
     * @Assert\NotBlank(message="L'objet est obligatoire")
     */
    private $objet;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="La date de départ est obligatoire")
     * @Assert\Type(type="datetime", message="La date de départ est incorrecte")
     */
    protected $dateDepart;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\Type(type="string",  message="L'heure de départ est incorrecte")
     * @Assert\NotBlank(message="L'heure de départ est obligatoire")
     */
    private $heureDepart;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="La date de retour est obligatoire")
     * @Assert\Type(type="datetime", message="La date de retour est incorrecte")
     */
    protected $dateRetour;

    /**
     * @ORM\Column(type="string", length=5)
     * @Assert\Type(type="string",  message="L'heure de retour est incorrecte")
     * @Assert\NotBlank(message="L'heure de retour est obligatoire")
     */
    private $heureRetour;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\FrdBundle\Entity\TypeMission" )
     */
    private $typeMission;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\FrdBundle\Entity\Finalite" )
     * @Assert\NotBlank(message="La finalite est obligatoire")
     */
    private $finalite;

    /**
     * @ORM\ManyToOne(targetEntity="Aeag\FrdBundle\Entity\SousTheme" )
     */
    private $sousTheme;

    /**
     * @ORM\Column(type="string", length=150)
     * @Assert\Type(type="string",  message="L'itinéraire est incorrect")
     * @Assert\NotBlank(message="L'itinéraire est obligatoire")
     */
    private $itineraire;

    /**
     * @ORM\Column(name="dept",type="string")
     * @Assert\NotBlank(message="Le département est obligatoire")
     */
    private $departement;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de kilometres efectuée par le véhicule est incorrect")
     */
    private $KmVoiture;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de kilometres efectuée par la moto est incorrect")
     */
    private $KmMoto;

    /**
     *
     * @ORM\Column(name="aeroport", type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"O", "N"}, message="Cette valeur doit être l'un des choix proposés")
     */
    private $aeroport;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     */
    private $AdmiMidiSem;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     */
    private $AdmiMidiWeek;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $AdmiSoir;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas de midi en semaine au restaurant est incorrect")
     */
    private $AutreMidiSem;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas de midi en week-end au restaurant est incorrect")
     */
    private $AutreMidiWeek;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas du soir au restaurant est incorrect")
     */
    private $AutreSoir;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas de midi en semaine offerts ou sans frais  est incorrect")
     */
    private $OffertMidiSem;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas de midi en week-end offerts ou sans frais  est incorrect")
     */
    private $OffertMidiWeek;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de repas du soir offerts ou sans frais  est incorrect")
     */
    private $OffertSoir;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de nuitées justifiés est incorrect")
     */
    private $ProvenceJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de nuitées non justifiés est incorrect")
     */
    private $ProvenceNonJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ParisJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ParisNonJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de nuitées offertes ou sans frais est incorrect")
     */
    private $offertNuit;

    /**
     * @ORM\Column(name="adminNuit", type="string", length=1, nullable=true)
     * @Assert\Choice(choices = {"O", "N"}, message="L'hébergement administratif doit coché ou décoché")
     */
    private $adminNuit;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le parking est incorrect")
     */
    private $parkJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le parking est incorrect")
     */
    private $parkNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix du parking est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix du parking ne peut avoir plus de 2 décimales")
     */
    private $parkTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le péage est incorrect")
     */
    private $peageJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le péage est incorrect")
     */
    private $peageNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix du péage est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix du péage ne peut avoir plus de 2 décimales")
     */
    private $peageTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le transport en commun est incorrect")
     */
    private $busMetroJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le transport en commun est incorrect")
     */
    private $busMetroNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix du transport en commun est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix du transport en commun ne peut avoir plus de 2 décimales")
     */
    private $busMetroTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour l'Olyval est incorrect")
     */
    private $orlyvalJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour l'Orlyval est incorrect")
     */
    private $orlyvalNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix de l'Olyval est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix de l'Olyval ne peut avoir plus de 2 décimales")
     */
    private $orlyvalTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le train est incorrect")
     */
    private $trainJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le train est incorrect")
     */
    private $trainNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix du train est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix du train ne peut avoir plus de 2 décimales")
     */
    private $trainTotal;

    /**
     * @ORM\Column(type="integer" , length=1, nullable=true)
     * @Assert\Type(type="integer", message="La classe du train est incorrect")
     */
    private $trainClasse;

    /**
     * @ORM\Column(name="trainCouchette", type="string", length=1, nullable=true)
     * @Assert\Type(type="string")
     * @Assert\Choice(choices = {"O", "N"}, message="La couchette du train doit cochée ou décochée")
     */
    private $trainCouchette;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour l'avion (et/ou bateau) est incorrect")
     */
    private $avionJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour l'avion (et/ou bateau) est incorrect")
     */
    private $avionNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix de l'avion (et/ou bateau) est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix de l'avion (et/ou bateau) ne peut avoir plus de 2 décimales")
     */
    private $avionTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le véhicule de location est incorrect")
     */
    private $reservationJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le véhicule de location est incorrect")
     */
    private $reservationNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix de la location du véhicule est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix de la location du véhicule ne peut avoir plus de 2 décimales")
     */
    private $reservationTotal;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets joints pour le taxi est incorrect")
     */
    private $taxiJustif;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="integer", message="Le nombre de tickets non joints pour le taxi est incorrect")
     */
    private $taxiNonJustif;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Type(type="numeric", message="Le prix du taxi est incorrect")
     * @Assert\Regex(pattern="/^[0-9]+(\.[0-9]{1,2})?$/", message="le prix du taxi ne peut avoir plus de 2 décimales")
     */
    private $taxiTotal;

    /**
     * @ORM\Column(type="integer", length=4, nullable=true)
     */
    private $exercice;

    /**
     * @ORM\Column(type="integer", length=6, nullable=true)
     */
    private $numMandat;

    /**
     * @ORM\Column(type="integer", length=6, nullable=true)
     */
    private $numBordereau;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $datePaiement;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $montRemtb;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $dateCourrier;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     *
     */
    public function __construct() {
        $this->setCreated(new \DateTime());
        $this->setUpdated(new \DateTime());
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdatedValue() {
        $this->setUpdated(new \DateTime());
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUser() {
        return $this->user;
    }

    public function setUser($user) {
        $this->user = $user;
    }

    public function getValider() {
        return $this->valider;
    }

    public function setValider($valider) {
        $this->valider = $valider;
    }

    public function getExporter() {
        return $this->exporter;
    }

    public function setExporter($exporter) {
        $this->exporter = $exporter;
    }

    public function getPhase() {
        return $this->phase;
    }

    public function setPhase($phase) {
        $this->phase = $phase;
    }

    public function getDatePhase() {
        return $this->datePhase;
    }

    public function setDatePhase($datePhase) {
        $this->datePhase = $datePhase;
    }

    public function getObjet() {
        return $this->objet;
    }

    public function setObjet($objet) {
        $this->objet = $objet;
    }

    public function getDateDepart() {
        return $this->dateDepart;
    }

    public function setDateDepart($dateDepart) {
        $this->dateDepart = $dateDepart;
    }

    public function getHeureDepart() {
        return $this->heureDepart;
    }

    public function setHeureDepart($heureDepart) {
        $this->heureDepart = $heureDepart;
    }

    public function getDateRetour() {
        return $this->dateRetour;
    }

    public function setDateRetour($dateRetour) {
        $this->dateRetour = $dateRetour;
    }

    public function getHeureRetour() {
        return $this->heureRetour;
    }

    public function setHeureRetour($heureRetour) {
        $this->heureRetour = $heureRetour;
    }

    public function getTypeMission() {
        return $this->typeMission;
    }

    public function setTypeMission($typeMission) {
        $this->typeMission = $typeMission;
    }

    public function getFinalite() {
        return $this->finalite;
    }

    public function setFinalite($finalite) {
        $this->finalite = $finalite;
    }

    public function getSousTheme() {
        return $this->sousTheme;
    }

    public function setSousTheme($sousTheme) {
        $this->sousTheme = $sousTheme;
    }

    public function getItineraire() {
        return $this->itineraire;
    }

    public function setItineraire($itineraire) {
        $this->itineraire = $itineraire;
    }

    public function getDepartement() {
        return $this->departement;
    }

    public function setDepartement($departement) {
        $this->departement = $departement;
    }

    public function getKmVoiture() {
        return $this->KmVoiture;
    }

    public function setKmVoiture($KmVoiture) {
        $this->KmVoiture = $KmVoiture;
    }

    public function getKmMoto() {
        return $this->KmMoto;
    }

    public function setKmMoto($KmMoto) {
        $this->KmMoto = $KmMoto;
    }

    public function getAeroport() {
        return $this->aeroport;
    }

    public function setAeroport($aeroport) {
        $this->aeroport = $aeroport;
    }

    public function getAdmiMidiSem() {
        return $this->AdmiMidiSem;
    }

    public function setAdmiMidiSem($AdmiMidiSem) {
        $this->AdmiMidiSem = $AdmiMidiSem;
    }

    public function getAdmiMidiWeek() {
        return $this->AdmiMidiWeek;
    }

    public function setAdmiMidiWeek($AdmiMidiWeek) {
        $this->AdmiMidiWeek = $AdmiMidiWeek;
    }

    public function getAdmiSoir() {
        return $this->AdmiSoir;
    }

    public function setAdmiSoir($AdmiSoir) {
        $this->AdmiSoir = $AdmiSoir;
    }

    public function getAutreMidiSem() {
        return $this->AutreMidiSem;
    }

    public function setAutreMidiSem($AutreMidiSem) {
        $this->AutreMidiSem = $AutreMidiSem;
    }

    public function getAutreMidiWeek() {
        return $this->AutreMidiWeek;
    }

    public function setAutreMidiWeek($AutreMidiWeek) {
        $this->AutreMidiWeek = $AutreMidiWeek;
    }

    public function getAutreSoir() {
        return $this->AutreSoir;
    }

    public function setAutreSoir($AutreSoir) {
        $this->AutreSoir = $AutreSoir;
    }

    public function getOffertMidiSem() {
        return $this->OffertMidiSem;
    }

    public function setOffertMidiSem($OffertMidiSem) {
        $this->OffertMidiSem = $OffertMidiSem;
    }

    public function getOffertMidiWeek() {
        return $this->OffertMidiWeek;
    }

    public function setOffertMidiWeek($OffertMidiWeek) {
        $this->OffertMidiWeek = $OffertMidiWeek;
    }

    public function getOffertSoir() {
        return $this->OffertSoir;
    }

    public function setOffertSoir($OffertSoir) {
        $this->OffertSoir = $OffertSoir;
    }

    public function getProvenceJustif() {
        return $this->ProvenceJustif;
    }

    public function setProvenceJustif($ProvenceJustif) {
        $this->ProvenceJustif = $ProvenceJustif;
    }

    public function getProvenceNonJustif() {
        return $this->ProvenceNonJustif;
    }

    public function setProvenceNonJustif($ProvenceNonJustif) {
        $this->ProvenceNonJustif = $ProvenceNonJustif;
    }

    public function getParisJustif() {
        return $this->ParisJustif;
    }

    public function setParisJustif($ParisJustif) {
        $this->ParisJustif = $ParisJustif;
    }

    public function getParisNonJustif() {
        return $this->ParisNonJustif;
    }

    public function setParisNonJustif($ParisNonJustif) {
        $this->ParisNonJustif = $ParisNonJustif;
    }

    public function getOffertNuit() {
        return $this->offertNuit;
    }

    public function setOffertNuit($offertNuit) {
        $this->offertNuit = $offertNuit;
    }

    public function getAdminNuit() {
        return $this->adminNuit;
    }

    public function setAdminNuit($adminNuit) {
        $this->adminNuit = $adminNuit;
    }

    public function getParkJustif() {
        return $this->parkJustif;
    }

    public function setParkJustif($parkJustif) {
        $this->parkJustif = $parkJustif;
    }

    public function getParkNonJustif() {
        return $this->parkNonJustif;
    }

    public function setParkNonJustif($parkNonJustif) {
        $this->parkNonJustif = $parkNonJustif;
    }

    public function getParkTotal() {
        return $this->parkTotal;
    }

    public function setParkTotal($parkTotal) {
        $this->parkTotal = $parkTotal;
    }

    public function getPeageJustif() {
        return $this->peageJustif;
    }

    public function setPeageJustif($peageJustif) {
        $this->peageJustif = $peageJustif;
    }

    public function getPeageNonJustif() {
        return $this->peageNonJustif;
    }

    public function setPeageNonJustif($peageNonJustif) {
        $this->peageNonJustif = $peageNonJustif;
    }

    public function getPeageTotal() {
        return $this->peageTotal;
    }

    public function setPeageTotal($peageTotal) {
        $this->peageTotal = $peageTotal;
    }

    public function getBusMetroJustif() {
        return $this->busMetroJustif;
    }

    public function setBusMetroJustif($busMetroJustif) {
        $this->busMetroJustif = $busMetroJustif;
    }

    public function getBusMetroNonJustif() {
        return $this->busMetroNonJustif;
    }

    public function setBusMetroNonJustif($busMetroNonJustif) {
        $this->busMetroNonJustif = $busMetroNonJustif;
    }

    public function getBusMetroTotal() {
        return $this->busMetroTotal;
    }

    public function setBusMetroTotal($busMetroTotal) {
        $this->busMetroTotal = $busMetroTotal;
    }

    public function getOrlyvalJustif() {
        return $this->orlyvalJustif;
    }

    public function setOrlyvalJustif($orlyvalJustif) {
        $this->orlyvalJustif = $orlyvalJustif;
    }

    public function getOrlyvalNonJustif() {
        return $this->orlyvalNonJustif;
    }

    public function setOrlyvalNonJustif($orlyvalNonJustif) {
        $this->orlyvalNonJustif = $orlyvalNonJustif;
    }

    public function getOrlyvalTotal() {
        return $this->orlyvalTotal;
    }

    public function setOrlyvalTotal($orlyvalTotal) {
        $this->orlyvalTotal = $orlyvalTotal;
    }

    public function getTrainJustif() {
        return $this->trainJustif;
    }

    public function setTrainJustif($trainJustif) {
        $this->trainJustif = $trainJustif;
    }

    public function getTrainNonJustif() {
        return $this->trainNonJustif;
    }

    public function setTrainNonJustif($trainNonJustif) {
        $this->trainNonJustif = $trainNonJustif;
    }

    public function getTrainTotal() {
        return $this->trainTotal;
    }

    public function setTrainTotal($trainTotal) {
        $this->trainTotal = $trainTotal;
    }

    public function getTrainClasse() {
        return $this->trainClasse;
    }

    public function setTrainClasse($trainClasse) {
        $this->trainClasse = $trainClasse;
    }

    public function getTrainCouchette() {
        return $this->trainCouchette;
    }

    public function setTrainCouchette($trainCouchette) {
        $this->trainCouchette = $trainCouchette;
    }

    public function getAvionJustif() {
        return $this->avionJustif;
    }

    public function setAvionJustif($avionJustif) {
        $this->avionJustif = $avionJustif;
    }

    public function getAvionNonJustif() {
        return $this->avionNonJustif;
    }

    public function setAvionNonJustif($avionNonJustif) {
        $this->avionNonJustif = $avionNonJustif;
    }

    public function getAvionTotal() {
        return $this->avionTotal;
    }

    public function setAvionTotal($avionTotal) {
        $this->avionTotal = $avionTotal;
    }

    public function getReservationJustif() {
        return $this->reservationJustif;
    }

    public function setReservationJustif($reservationJustif) {
        $this->reservationJustif = $reservationJustif;
    }

    public function getReservationNonJustif() {
        return $this->reservationNonJustif;
    }

    public function setReservationNonJustif($reservationNonJustif) {
        $this->reservationNonJustif = $reservationNonJustif;
    }

    public function getReservationTotal() {
        return $this->reservationTotal;
    }

    public function setReservationTotal($reservationTotal) {
        $this->reservationTotal = $reservationTotal;
    }

    public function getTaxiJustif() {
        return $this->taxiJustif;
    }

    public function setTaxiJustif($taxiJustif) {
        $this->taxiJustif = $taxiJustif;
    }

    public function getTaxiNonJustif() {
        return $this->taxiNonJustif;
    }

    public function setTaxiNonJustif($taxiNonJustif) {
        $this->taxiNonJustif = $taxiNonJustif;
    }

    public function getTaxiTotal() {
        return $this->taxiTotal;
    }

    public function setTaxiTotal($taxiTotal) {
        $this->taxiTotal = $taxiTotal;
    }

    public function getExercice() {
        return $this->exercice;
    }

    public function setExercice($exercice) {
        $this->exercice = $exercice;
    }

    public function getNumMandat() {
        return $this->numMandat;
    }

    public function setNumMandat($numMandat) {
        $this->numMandat = $numMandat;
    }

    public function getNumBordereau() {
        return $this->numBordereau;
    }

    public function setNumBordereau($numBordereau) {
        $this->numBordereau = $numBordereau;
    }

    public function getDatePaiement() {
        return $this->datePaiement;
    }

    public function setDatePaiement($datePaiement) {
        $this->datePaiement = $datePaiement;
    }

    public function getMontRemtb() {
        return $this->montRemtb;
    }

    public function setMontRemtb($montRemtb) {
        $this->montRemtb = $montRemtb;
    }

    function getDateCourrier() {
        return $this->dateCourrier;
    }

    function setDateCourrier($dateCourrier) {
        $this->dateCourrier = $dateCourrier;
    }

    public function getCreated() {
        return $this->created;
    }

    public function setCreated($created) {
        $this->created = $created;
    }

    public function getUpdated() {
        return $this->updated;
    }

    public function setUpdated($updated) {
        $this->updated = $updated;
    }

}
