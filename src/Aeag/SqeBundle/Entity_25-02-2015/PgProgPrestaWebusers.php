<?php

namespace Aeag\SqeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PgProgPrestaWebusers
 *
 * @ORM\Table(name="pg_prog_presta_webusers", uniqueConstraints={@ORM\UniqueConstraint(name="uk_cmd_presta_login", columns={"login"})}, indexes={@ORM\Index(name="IDX_1FCBACF0BA2F97F0", columns={"presta_id"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\SqeBundle\Repository\PgProgPrestaWebusersRepository")
 */
class PgProgPrestaWebusers
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="decimal", precision=38, scale=0, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="pg_prog_presta_webusers_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="role", type="string", length=3, nullable=false)
     */
    private $role;

    /**
     * @var \PgRefCorresPresta
     *
     * @ORM\ManyToOne(targetEntity="PgRefCorresPresta")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="presta_id", referencedColumnName="adr_cor_id")
     * })
     */
    private $presta;



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
     * Set nom
     *
     * @param string $nom
     * @return PgProgPrestaWebusers
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string 
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set login
     *
     * @param string $login
     * @return PgProgPrestaWebusers
     */
    public function setLogin($login)
    {
        $this->login = $login;

        return $this;
    }

    /**
     * Get login
     *
     * @return string 
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * Set pwd
     *
     * @param string $pwd
     * @return PgProgPrestaWebusers
     */
    public function setPwd($pwd)
    {
        $this->pwd = $pwd;

        return $this;
    }

    /**
     * Get pwd
     *
     * @return string 
     */
    public function getPwd()
    {
        return $this->pwd;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return PgProgPrestaWebusers
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set role
     *
     * @param string $role
     * @return PgProgPrestaWebusers
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string 
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set presta
     *
     * @param \Aeag\SqeBundle\Entity\PgRefCorresPresta $presta
     * @return PgProgPrestaWebusers
     */
    public function setPresta(\Aeag\SqeBundle\Entity\PgRefCorresPresta $presta = null)
    {
        $this->presta = $presta;

        return $this;
    }

    /**
     * Get presta
     *
     * @return \Aeag\SqeBundle\Entity\PgRefCorresPresta 
     */
    public function getPresta()
    {
        return $this->presta;
    }
}
