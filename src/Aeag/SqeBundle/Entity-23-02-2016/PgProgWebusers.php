<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgWebusers
 *
 * @ORM\Table(name="pg_prog_webusers", uniqueConstraints={@ORM\UniqueConstraint(name="uk_pg_prog_usr_login", columns={"login"}), @ORM\UniqueConstraint(name="uk_pg_prog_usr_nom", columns={"nom"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgWebusersRepository")
 */
class PgProgWebusers {

    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_webusers_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=50, nullable=false)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=false)
     */
    private $login;

    /**
     * @var string
     *
     * @ORM\Column(name="pwd", type="string", length=50, nullable=false)
     */
    private $pwd;

    /**
     * @var string
     *
     * @ORM\Column(name="mail", type="string", length=255, nullable=false)
     */
    private $mail;

    /**
     * @var string
     *
     * @ORM\Column(name="type_user", type="string", length=5, nullable=false)
     */
    private $typeUser;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgMarche", inversedBy="webuser")
     * @ORM\JoinTable(name="pg_prog_marche_user",
     *   joinColumns={
     *     @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="marche_id", referencedColumnName="id")
     *   }
     * )
     */
    private $marche;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgRefReseauMesure", inversedBy="webuser")
     * @ORM\JoinTable(name="pg_prog_webuser_rsx",
     *   joinColumns={
     *     @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="rsx_id", referencedColumnName="groupement_id")
     *   }
     * )
     */
    private $rsx;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="PgProgZoneGeoRef", inversedBy="webuser")
     * @ORM\JoinTable(name="pg_prog_webuser_zgeoref",
     *   joinColumns={
     *     @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="zgeo_ref_id", referencedColumnName="id")
     *   }
     * )
     */
    private $zgeoRef;

    /**
     * @var string
     *
     * @ORM\Column(name="ext_id", type="integer", nullable=true)
     */
    private $extId;
    
    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $prestataire;

    /**
     * Constructor
     */
    public function __construct() {
        $this->marche = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rsx = new \Doctrine\Common\Collections\ArrayCollection();
        $this->zgeoRef = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set nom
     *
     * @param string $nom
     * @return PgProgWebusers
     */
    public function setNom($nom) {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom() {
        return $this->nom;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return PgProgWebusers
     */
    public function setLogin($login) {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * Set pwd
     *
     * @param string $pwd
     * @return PgProgWebusers
     */
    public function setPwd($pwd) {
        $this->pwd = $pwd;

        return $this;
    }

    /**
     * Get pwd
     *
     * @return string 
     */
    public function getPwd() {
        return $this->pwd;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return PgProgWebusers
     */
    public function setMail($mail) {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail() {
        return $this->mail;
    }

    /**
     * Set typeUser
     *
     * @param string $typeUser
     * @return PgProgWebusers
     */
    public function setTypeUser($typeUser) {
        $this->typeUser = $typeUser;

        return $this;
    }

    /**
     * Get typeUser
     *
     * @return string 
     */
    public function getTypeUser() {
        return $this->typeUser;
    }

    /**
     * Add marche
     *
     * @param \Aeag\SqeBundle\Entity\PgProgMarche $marche
     * @return PgProgWebusers
     */
    public function addMarche(\Aeag\SqeBundle\Entity\PgProgMarche $marche) {
        $this->marche[] = $marche;

        return $this;
    }

    /**
     * Remove marche
     *
     * @param \Aeag\SqeBundle\Entity\PgProgMarche $marche
     */
    public function removeMarche(\Aeag\SqeBundle\Entity\PgProgMarche $marche) {
        $this->marche->removeElement($marche);
    }

    /**
     * Get marche
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMarche() {
        return $this->marche;
    }

    /**
     * Add rsx
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx
     * @return PgProgWebusers
     */
    public function addRsx(\Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx) {
        $this->rsx[] = $rsx;

        return $this;
    }

    /**
     * Remove rsx
     *
     * @param \Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx
     */
    public function removeRsx(\Aeag\SqeBundle\Entity\PgRefReseauMesure $rsx) {
        $this->rsx->removeElement($rsx);
    }

    /**
     * Get rsx
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getRsx() {
        return $this->rsx;
    }

    /**
     * Add zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     * @return PgProgWebusers
     */
    public function addZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef) {
        $this->zgeoRef[] = $zgeoRef;

        return $this;
    }

    /**
     * Remove zgeoRef
     *
     * @param \Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef
     */
    public function removeZgeoRef(\Aeag\SqeBundle\Entity\PgProgZoneGeoRef $zgeoRef) {
        $this->zgeoRef->removeElement($zgeoRef);
    }

    /**
     * Get zgeoRef
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getZgeoRef() {
        return $this->zgeoRef;
    }

    function getExtId() {
        return $this->extId;
    }

    function setExtId($extId) {
        $this->extId = $extId;
    }
    
        /**
     * Set prestataire
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $prestataire
     * @return PgProgPrestaWebusers
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

}
