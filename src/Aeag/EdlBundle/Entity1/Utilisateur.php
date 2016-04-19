<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Aeag\EdlBundle\Entity\Utilisateur
 * @ORM\Table(name="utilisateur")
 * @ORM\Entity
 */
class Utilisateur {

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\generatedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $usernameCanonical;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $emailCanonical;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * The salt to use for hashing
     *
     * @var string
     */
    protected $salt;

    /**
     * Encrypted password. Must be persisted.
     *
     * @var string
     */
    protected $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @var string
     */
    protected $plainPassword;

    /**
     * @var \DateTime
     */
    protected $lastLogin;

    /**
     * Random string sent to the user email address in order to verify it
     *
     * @var string
     */
    protected $confirmationToken;

    /**
     * @var \DateTime
     */
    protected $passwordRequestedAt;

   

    /**
     * @var boolean
     */
    protected $locked;

    /**
     * @var boolean
     */
    protected $expired;

    /**
     * @var \DateTime
     */
    protected $expiresAt;

    /**
     * @var array
     */
    protected $roles;

    /**
     * @var boolean
     */
    protected $credentialsExpired;

    /**
     * @var \DateTime
     */
    protected $credentialsExpireAt;

    /**
     * @ORM\Column(type="string", length=30, name="passwordEnClair", nullable=false)
     *
     * @var string $passwordEnClair
     */
    protected $passwordEnClair;

    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\DepUtilisateur", mappedBy="utilisateur")
     */
    protected $dept;

    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\EtatMeProposed", mappedBy="utilisateur")
     */
    protected $etatMeProposed;

    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\PressionMeProposed", mappedBy="utilisateur")
     */
    protected $pressionMeProposed;

    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\ImpactMeProposed", mappedBy="utilisateur")
     */
    protected $impactMeProposed;

    /**
     * @ORM\OneToMany(targetEntity="Aeag\EdlBundle\Entity\RisqueMeProposed", mappedBy="utilisateur")
     */
    protected $risqueMeProposed;
  
    public function __construct() {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
        $this->enabled = false;
        $this->locked = false;
        $this->expired = false;
        $this->roles = array();
        $this->credentialsExpired = false;

        $this->dept = new ArrayCollection();
        $this->etatMeProposed = new ArrayCollection();
        $this->pressionMeProposed = new ArrayCollection();
        $this->impactMeProposed = new ArrayCollection();
        $this->risqueMeProposed = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Add dept
     *
     * @param Aeag\EdlBundle\Entity\DepUtilisateur $dept
     */
    public function addDepUtilisateur(\Aeag\EdlBundle\Entity\DepUtilisateur $dept) {
        $this->dept[] = $dept;
    }

    /**
     * Get dept
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getDept() {
        return $this->dept;
    }

    /**
     * Add etatMeProposed
     *
     * @param Aeag\EdlBundle\Entity\EtatMeProposed $etatMe
     */
    public function addEtatMeProposed(\Aeag\EdlBundle\Entity\EtatMeProposed $etatMe) {
        $this->etatMeProposed[] = $etatMe;
    }

    /**
     * Get etatMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getEtatMeProposed() {
        return $this->etatMeproposed;
    }

    /**
     * Add pressionMeProposed
     *
     * @param Aeag\pressiondeslieuxBundle\Entity\PressionMeProposed $pressionMe
     */
    public function addPressionMeProposed(\Aeag\EdlBundle\Entity\PressionMeProposed $pressionMe) {
        $this->pressionMeProposed[] = $pressionMe;
    }

    /**
     * Get pressionMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getPressionMeProposed() {
        return $this->pressionMeproposed;
    }

    /**
     * Add impactMeProposed
     *
     * @param Aeag\EdlBundle\Entity\ImpactMeProposed $impactMe
     */
    public function addImpactMeProposed(\Aeag\EdlBundle\Entity\ImpactMeProposed $impactMe) {
        $this->impactMeProposed[] = $impactMe;
    }

    /**
     * Get impactMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getImpactMeProposed() {
        return $this->impactMeproposed;
    }

    /**
     * Add risqueMeProposed
     *
     * @param Aeag\EdlBundle\Entity\RisqueMeProposed $risqueMe
     */
    public function addRisqueMeProposed(\Aeag\EdlBundle\Entity\RisqueMeProposed $risqueMe) {
        $this->risqueMeProposed[] = $risqueMe;
    }

    /**
     * Get risqueMeProposed
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getRisqueMeProposed() {
        return $this->risqueMeproposed;
    }

    public function getPasswordEnClair() {
        return $this->passwordEnClair;
    }

    public function setPasswordEnClair($passwordEnClair) {
        $this->passwordEnClair = $passwordEnClair;
    }

    

    public function addRole($role) {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * Serializes the user.
     *
     * The serialized data have to contain the fields used during check for
     * changes and the id.
     *
     * @return string
     */
    public function serialize() {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->expiresAt,
            $this->credentialsExpireAt,
            $this->email,
            $this->emailCanonical,
        ));
    }

    /**
     * Unserializes the user.
     *
     * @param string $serialized
     */
    public function unserialize($serialized) {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
                $this->password,
                $this->salt,
                $this->usernameCanonical,
                $this->username,
                $this->expired,
                $this->locked,
                $this->credentialsExpired,
                $this->enabled,
                $this->id,
                $this->expiresAt,
                $this->credentialsExpireAt,
                $this->email,
                $this->emailCanonical
                ) = $data;
    }

    /**
     * Removes sensitive data from the user.
     */
    public function eraseCredentials() {
        $this->plainPassword = null;
    }

   

    public function getUsername() {
        return $this->username;
    }

    public function getUsernameCanonical() {
        return $this->usernameCanonical;
    }

    public function getSalt() {
        return $this->salt;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getEmailCanonical() {
        return $this->emailCanonical;
    }

    /**
     * Gets the encrypted password.
     *
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    public function getPlainPassword() {
        return $this->plainPassword;
    }

    /**
     * Gets the last login time.
     *
     * @return \DateTime
     */
    public function getLastLogin() {
        return $this->lastLogin;
    }

    public function getConfirmationToken() {
        return $this->confirmationToken;
    }

    /**
     * Returns the user roles
     *
     * @return array The roles
     */
    public function getRoles() {
        $roles = $this->roles;

       

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the SecurityContext, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $securityContext->isGranted('ROLE_AEAG');
     *
     * @param string $role
     *
     * @return boolean
     */
    public function hasRole($role) {
        return in_array(strtoupper($role), $this->getRoles(), true);
    }

    public function isAccountNonExpired() {
        if (true === $this->expired) {
            return false;
        }

        if (null !== $this->expiresAt && $this->expiresAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isAccountNonLocked() {
        return !$this->locked;
    }

    public function isCredentialsNonExpired() {
        if (true === $this->credentialsExpired) {
            return false;
        }

        if (null !== $this->credentialsExpireAt && $this->credentialsExpireAt->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function isCredentialsExpired() {
        return !$this->isCredentialsNonExpired();
    }

    public function isEnabled() {
        return $this->enabled;
    }

    public function isExpired() {
        return !$this->isAccountNonExpired();
    }

    public function isLocked() {
        return !$this->isAccountNonLocked();
    }

    public function isSuperAdmin() {
        return $this->hasRole(static::ROLE_SUPER_ADMIN);
    }

    public function removeRole($role) {
        if (false !== $key = array_search(strtoupper($role), $this->roles, true)) {
            unset($this->roles[$key]);
            $this->roles = array_values($this->roles);
        }

        return $this;
    }

    public function setUsername($username) {
        $this->username = $username;

        return $this;
    }

    public function setUsernameCanonical($usernameCanonical) {
        $this->usernameCanonical = $usernameCanonical;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setCredentialsExpireAt(\DateTime $date = null) {
        $this->credentialsExpireAt = $date;

        return $this;
    }

    /**
     * @param boolean $boolean
     *
     * @return User
     */
    public function setCredentialsExpired($boolean) {
        $this->credentialsExpired = $boolean;

        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;

        return $this;
    }

    public function setEmailCanonical($emailCanonical) {
        $this->emailCanonical = $emailCanonical;

        return $this;
    }

    public function setEnabled($boolean) {
        $this->enabled = (Boolean) $boolean;

        return $this;
    }

    /**
     * Sets this user to expired.
     *
     * @param Boolean $boolean
     *
     * @return User
     */
    public function setExpired($boolean) {
        $this->expired = (Boolean) $boolean;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return User
     */
    public function setExpiresAt(\DateTime $date = null) {
        $this->expiresAt = $date;

        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;

        return $this;
    }

    public function setSuperAdmin($boolean) {
        if (true === $boolean) {
            $this->addRole(static::ROLE_SUPER_ADMIN);
        } else {
            $this->removeRole(static::ROLE_SUPER_ADMIN);
        }

        return $this;
    }

    public function setPlainPassword($password) {
        $this->plainPassword = $password;

        return $this;
    }

    public function setLastLogin(\DateTime $time = null) {
        $this->lastLogin = $time;

        return $this;
    }

    public function setLocked($boolean) {
        $this->locked = $boolean;

        return $this;
    }

    public function setConfirmationToken($confirmationToken) {
        $this->confirmationToken = $confirmationToken;

        return $this;
    }

    public function setPasswordRequestedAt(\DateTime $date = null) {
        $this->passwordRequestedAt = $date;

        return $this;
    }

    /**
     * Gets the timestamp that the user requested a password reset.
     *
     * @return null|\DateTime
     */
    public function getPasswordRequestedAt() {
        return $this->passwordRequestedAt;
    }

    public function isPasswordRequestNonExpired($ttl) {
        return $this->getPasswordRequestedAt() instanceof \DateTime &&
                $this->getPasswordRequestedAt()->getTimestamp() + $ttl > time();
    }

    public function setRoles(array $roles) {
        $this->roles = array();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

}
