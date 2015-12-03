<?php

namespace Aeag\AgentBundle\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class CriteresEtat {

    private $nomPrenom;

    function getNomPrenom() {
        return $this->nomPrenom;
    }

    function setNomPrenom($nomPrenom) {
        $this->nomPrenom = $nomPrenom;
    }

}
