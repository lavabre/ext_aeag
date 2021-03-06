<?php

/**
 * Description de Theme
 *
 * @author lavabre
 */

namespace Aeag\DieBundle\Entity;

use Doctrine\ORM\Id as ID;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ORM\Table(name="theme")
 * @ORM\Entity(repositoryClass="Aeag\DieBundle\Repository\ThemeRepository")
 */
class Theme {

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="theme_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @ORM\Column(name="theme", type="string", length=150)
     * @Assert\NotBlank(message = "Le thème est obligatoire")
     * @assert\LessThan(150)
     */
    private $theme;
    private $themeLibelle;

    /**
     * @ORM\Column(name="ordre", type="integer", length=2, nullable=true)
     */
    private $ordre;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getTheme() {
        return $this->theme;
    }

    public function setTheme($theme) {
        $this->theme = $theme;
    }

    public function getThemeLibelle() {
        return $this->id . " : " . $this->theme;
    }

    function getOrdre() {
        return $this->ordre;
    }

    function setOrdre($ordre) {
        $this->ordre = $ordre;
    }

}
