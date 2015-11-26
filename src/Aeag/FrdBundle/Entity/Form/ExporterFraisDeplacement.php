<?php

/**
 * Description of NewFraisDeplacement
 *
 * @author lavabre
 */

namespace Aeag\FrdBundle\Entity\Form;

use Symfony\Component\Validator\Constraints as Assert;

class EporterFraisDeplacement {

    /**
     * Assert\Type( type="string)
     * @Assert\Choice(choices = {"O", "N"}, message="Répondre 'oui' ou 'Non'")
     */
    private $exporter;
    
    public function getExporter() {
        return $this->exporter;
    }

    public function setExporter($exporter) {
        $this->exporter = $exporter;
    }


            
   
}

