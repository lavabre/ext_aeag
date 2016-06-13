<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtatDerniereProposition
 *
 * @ORM\Table(name="etat_derniere_proposition")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\EtatDernierePropositionRepository")
 */
class EtatDerniereProposition {

    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    /**
     * @var string $cdEtat
     *
     * @ORM\Column(name="cd_etat", type="string", length=16, nullable=false)
     * @ORM\Id
     */
    private $cdEtat;

    /**
     * @var string
     *
     * @ORM\Column(name="proposition_date", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $propositionDate;

    /**
     * @var string
     *
     * @ORM\Column(name="valeur", type="string", length=255, nullable=false)
     */
    private $valeur;

    /**
     * @var string
     *
     * @ORM\Column(name="commentaire", type="text", nullable=true)
     */
    private $commentaire;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, nullable=false)
     */
    private $username;
    
       public function getValueLib() {
        if ($this->valeur) {
            switch ($this->valeur) {
                case '1' : return 'Très bon état';
                case '2' : return 'Bon';
                case '3' : return 'Moyen';
                case '4' : return 'Médiocre';
                case '5' : return 'Mauvais';
                case 'U' : return 'Inconnu';
            }
        } else {
            return 'non renseigner';
        }
    }

    function getEuCd() {
        return $this->euCd;
    }

    function getCdEtat() {
        return $this->cdEtat;
    }

    function getPropositionDate() {
        return $this->propositionDate;
    }

    function getValeur() {
        return $this->valeur;
    }

    function getCommentaire() {
        return $this->commentaire;
    }

    function getUsername() {
        return $this->username;
    }

}
