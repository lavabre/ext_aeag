<?php

namespace Aeag\EdlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PressionDerniereProposition
 *
 * @ORM\Table(name="pression_derniere_proposition")
 * @ORM\Entity(repositoryClass="Aeag\EdlBundle\Repository\PressionDernierePropositionRepository")
 */
class PressionDerniereProposition {

    /**
     * @var string
     *
     * @ORM\Column(name="eu_cd", type="string", length=24, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $euCd;

    /**
     * @var string $cdPression
     *
     * @ORM\Column(name="cd_pression", type="string", length=16, nullable=false)
     * @ORM\Id
     */
    private $cdPression;

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
        if ($this->cdPression == 'RW_HYM_CONT' ||
                $this->cdPression == 'RW_HYM_HYD' ||
                $this->cdPression == 'RW_HYM_MOR') {
            if ($this->valeur) {
                switch ($this->valeur) {
                    case '1' : return 'Minime';
                    case '2' : return 'Modérée';
                    case '3' : return 'Elevée';
                    case 'U' : return 'Inconnu';
                }
            } else {
                return 'non renseigné';
            }
        } else {
            if ($this->valeur) {
                switch ($this->valeur) {
                    case '1' : return 'Pas de pression';
                    case '2' : return 'Pression non significative';
                    case '3' : return 'Pression significative';
                    case 'U' : return 'Inconnu';
                }
            } else {
                return 'non renseigné';
            }
        }
    }

    function getEuCd() {
        return $this->euCd;
    }

    function getCdPression() {
        return $this->cdPression;
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
