<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepUtilisateur
 *
 * @ORM\Table(name="dep_utilisateur", indexes={@ORM\Index(name="idx_956fa98550eae44", columns={"id_utilisateur"})})
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\DepUtilisateurRepository")
 */
class DepUtilisateur
{
    /**
     * @var string
     *
     * @ORM\Column(name="insee_departement", type="string", length=2, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $inseeDepartement;

    /**
     * @var \Utilisateur
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Utilisateur")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="id_utilisateur", referencedColumnName="id")
     * })
     */
    private $utilisateur;



    /**
     * Set inseeDepartement
     *
     * @param string $inseeDepartement
     *
     * @return depUtilisateur
     */
    public function setInseeDepartement($inseeDepartement)
    {
        $this->inseeDepartement = $inseeDepartement;

        return $this;
    }

    /**
     * Get inseeDepartement
     *
     * @return string
     */
    public function getInseeDepartement()
    {
        return $this->inseeDepartement;
    }

    /**
     * Set utilisateur
     *
     * @param \Aeag\EdlBundle\Entity\Utilisateur $utilisateur
     *
     * @return depUtilisateur
     */
    public function setUtilisateur(\Aeag\EdlBundle\Entity\Utilisateur $utilisateur)
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    /**
     * Get utilisateur
     *
     * @return \Aeag\EdlBundle\Entity\Utilisateur
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
