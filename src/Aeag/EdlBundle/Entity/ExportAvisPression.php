<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * Aeag\EdlBundle\Entity\ExportAvisPression
 *
 * @ORM\Table(name="export_avis_pression")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\ExportAvisPressionRepository")
 */
class ExportAvisPression {

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
     * @var string $cdPression
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=true)
     */
    private $cdPression;

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
     * @ORM\Column(name="p_sdage2016", type="string", length=1, nullable=true)
     */
    private $pSdage2016;

    /**
     * @var string $pSdage2016Lib
     *
     * @ORM\Column(name="p_sdage2016_lib", type="text", nullable=true)
     */
    private $pSdage2016Lib;

    /**
     * @var string $ePropose
     *
     * @ORM\Column(name="p_propose", type="string", length=1, nullable=true)
     */
    private $pPropose;

    /**
     * @var string $pProposeLib
     *
     * @ORM\Column(name="p_propose_lib", type="text", nullable=true)
     */
    private $pProposeLib;
    
     /**
     * @var string $pRetenu
     *
     * @ORM\Column(name="e_retenu", type="string", length=255, nullable=true)
     */
    private $pRetenu;

    /**
     * @var string $pretenuLib
     *
     * @ORM\Column(name="e_retenu_lib", type="text", nullable=true)
     */
    private $pRetenuLib;

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

    function getCdPression() {
        return $this->cdPression;
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

    function getPSdage2016() {
        return $this->pSdage2016;
    }

    function getPSdage2016Lib() {
        return $this->pSdage2016Lib;
    }

    function getPPropose() {
        return $this->pPropose;
    }

    function getPProposeLib() {
        return $this->pProposeLib;
    }
    
    function getPRetenu() {
        return $this->pRetenu;
    }

    function getPRetenuLib() {
        return $this->pRetenuLib;
    }

    
    function getCommentaire() {
        return $this->commentaire;
    }

}
