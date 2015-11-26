<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgLotParamPprog
 *
 * @ORM\Table(name="pg_prog_lot_param_pprog", indexes={@ORM\Index(name="IDX_6F8C668E4763011C", columns={"pprog_id"}), @ORM\Index(name="IDX_6F8C668E82E879", columns={"code_parametre"}), @ORM\Index(name="IDX_6F8C668EBE3DB2B7", columns={"prestataire_id"}), @ORM\Index(name="IDX_6F8C668E7ED10327", columns={"rsx_id"}), @ORM\Index(name="IDX_6F8C668E9A9106A4", columns={"code_methode"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgLotParamPprogRepository")
 */
class PgProgLotParamPprog
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_lot_param_pprog_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     * @var \PgProgLotPeriodeProg
     *
     * @ORM\ManyToOne(targetEntity="PgProgLotPeriodeProg")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pprog_id", referencedColumnName="id")
     * })
     */
    private $pprog;

    /**
     * @var \PgSandreParametres
     *
     * @ORM\ManyToOne(targetEntity="PgSandreParametres")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_parametre", referencedColumnName="code_parametre")
     * })
     */
    private $codeParametre;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="prestataire_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire;

    /**
     * @var \PgRefReseauMesure
     *
     * @ORM\ManyToOne(targetEntity="PgRefReseauMesure")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rsx_id", referencedColumnName="groupement_id")
     * })
     */
    private $rsx;

    /**
     * @var \PgSandreMethodes
     *
     * @ORM\ManyToOne(targetEntity="PgSandreMethodes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="code_methode", referencedColumnName="code_methode")
     * })
     */
    private $codeMethode;



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
     * Set codeFraction
     *
     * @param string $codeFraction
     * @return PgProgLotParamPprog
     */
    public function setCodeFraction($codeFraction)
    {
        $this->codeFraction = $codeFraction;

        return $this;
    }

    /**
     * Get codeFraction
     *
     * @return string 
     */
    public function getCodeFraction()
    {
        return $this->codeFraction;
    }

    /**
     * Set codeUnite
     *
     * @param string $codeUnite
     * @return PgProgLotParamPprog
     */
    public function setCodeUnite($codeUnite)
    {
        $this->codeUnite = $codeUnite;

        return $this;
    }

    /**
     * Get codeUnite
     *
     * @return string 
     */
    public function getCodeUnite()
    {
        return $this->codeUnite;
    }

    /**
     * Set pprog
     *
     * @param \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog
     * @return PgProgLotParamPprog
     */
    public function setPprog(\Aeag\SqeBundle\Entity\PgProgLotPeriodeProg $pprog = null)
    {
        $this->pprog = $pprog;

        return $this;
    }

    /**
     * Get pprog
     *
     * @return \Aeag\SqeBundle\Entity\PgProgLotPeriodeProg 
     */
    public function getPprog()
    {
        return $this->pprog;
    }

    /**
     * Set codeParametre
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre
     * @return PgProgLotParamPprog
     */
    public function setCodeParametre(\Aeag\SqeBundle\Entity\PgSandreParametres $codeParametre = null)
    {
        $this->codeParametre = $codeParametre;

        return $this;
    }

    /**
     * Get codeParametre
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreParametres 
     */
    public function getCodeParametre()
    {
        return $this->codeParametre;
    }

    /**
     * Set prestataire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire
     * @return PgProgLotParamPprog
     */
    public function setPrestataire(\Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire = null)
    {
        $this->prestataire = $prestataire;

        return $this;
    }

    /**
     * Get prestataire
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPrestataire()
    {
        return $this->prestataire;
    }

    /**
     * Set rsx
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx
     * @return PgProgLotParamPprog
     */
    public function setRsx(\Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx = null)
    {
        $this->rsx = $rsx;

        return $this;
    }

    /**
     * Get rsx
     *
     * @return \Aeag\SqeBundle\Entity\PgRefReseauMesure 
     */
    public function getRsx()
    {
        return $this->rsx;
    }

    /**
     * Set codeMethode
     *
     * @param \Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode
     * @return PgProgLotParamPprog
     */
    public function setCodeMethode(\Aeag\SqeBundle\Entity\PgSandreMethodes $codeMethode = null)
    {
        $this->codeMethode = $codeMethode;

        return $this;
    }

    /**
     * Get codeMethode
     *
     * @return \Aeag\SqeBundle\Entity\PgSandreMethodes 
     */
    public function getCodeMethode()
    {
        return $this->codeMethode;
    }
}
