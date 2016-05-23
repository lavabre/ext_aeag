<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * Aeag\EdlBundle\Entity\ExportAvisEtat
 *
 * @ORM\Table(name="export_avis_etat")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\ExportAvisEtatRepository")
 */
class ExportAvisEtat {

    /**
     * @var string $euCd
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     */
    private $euCd;

    /**
     * @var string
     *
     * @ORM\Column(name="type_me", type="string", length=2, nullable=true)
     */
    private $typeMe;

    /**
     * @var string
     *
     * @ORM\Column(name="nom_masse_eau", type="string", length=250, nullable=true)
     */
    private $nomMasseEau;

    /**
     * @var string $ct
     *
     * @ORM\Column(name="ct", type="string", length=40, nullable=true)
     */
    private $ct;

    /**
     * @var string $ctLib
     *
     * @ORM\Column(name="ct_lib", type="text",  nullable= true)

     */
    private $ctLib;

    /**
     * @var string $uhr
     *
     * @ORM\Column(name="uhr", type="text", nullable=true)
     */
    private $uhr;

    /**
     * @var string $uhrLib
     *
     * @ORM\Column(name="uhr_lib", type="text",  nullable= true)

     */
    private $uhrLib;

    /**
     * @var string $depts
     *
     * @ORM\Column(name="depts", type="text", nullable=true)
     */
    private $depts;

    /**
     * @var string $cdEtat
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=true)
     */
    private $cdEtat;

    /**
     * @var string
     *
     * @ORM\Column(name="proposition_date", type="string", length=255, nullable=true)
     */
    private $propositionDate;

    /**
     * @var string $gOrdre
     *
     * @ORM\Column(name="g_ordre", type="integer", length=9, nullable=true)
     */
    private $gOrdre;

    /**
     * @var string $typOrdre
     *
     * @ORM\Column(name="typ_pordre", type="integer", length=9, nullable=true)
     */
    private $typPordre;

    /**
     * @var string $groupe
     *
     * @ORM\Column(name="groupe", type="string", length=255, nullable=true)
     */
    private $groupe;

    /**
     * @var string $libelle
     *
     * @ORM\Column(name="libelle", type="string", length=255, nullable=true)
     */
    private $libelle;

    /**
     * @var string $eSdage2016
     *
     * @ORM\Column(name="e_sdage2016", type="string", length=15, nullable=true)
     */
    private $eSdage2016;

    /**
     * @var string $eSdage2016Lib
     *
     * @ORM\Column(name="e_sdage2016_lib", type="text", nullable=true)
     */
    private $eSdage2016Lib;

    /**
     * @var string $ePropose
     *
     * @ORM\Column(name="e_propose", type="string", length=255, nullable=true)
     */
    private $ePropose;

    /**
     * @var string $eProposeLib
     *
     * @ORM\Column(name="e_propose_lib", type="text", nullable=true)
     */
    private $eProposeLib;

    /**
     * @var string $commentaire
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    function getEuCd() {
        return $this->euCd;
    }

    function getTypeMe() {
        return $this->typeMe;
    }

    function getNomMasseEau() {
        return $this->nomMasseEau;
    }

    function getCt() {
        return $this->ct;
    }

    function getCtLib() {
        return $this->ctLib;
    }

    function getUhr() {
        return $this->uhr;
    }

    function getUhrLib() {
        return $this->uhrLib;
    }

    function getDepts() {
        return $this->depts;
    }

    function getCdEtat() {
        return $this->cdEtat;
    }

    function getPropositionDate() {
        return $this->propositionDate;
    }

    function getGOrdre() {
        return $this->gOrdre;
    }

    function getTypPordre() {
        return $this->typPordre;
    }

    function getGroupe() {
        return $this->groupe;
    }

    function getLibelle() {
        return $this->libelle;
    }

    function getESdage2016() {
        return $this->eSdage2016;
    }

    function getESdage2016Lib() {
        return $this->eSdage2016Lib;
    }

    function getEPropose() {
        return $this->ePropose;
    }

    function getEProposeLib() {
        return $this->eProposeLib;
    }

    function getCommentaire() {
        return $this->commentaire;
    }

}
