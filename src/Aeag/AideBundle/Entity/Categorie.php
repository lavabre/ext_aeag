<?php

namespace Aeag\AideBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="categorie")
 * @ORM\Entity(repositoryClass="Aeag\AideBundle\Repository\CategorieRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Categorie {

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=25)
     * 
     * @Assert\NotBlank()
     */
    private $cate;

    public function getCate() {
        return $this->cate;
    }

    public function setCate($cate) {
        $this->cate = $cate;
    }

}
