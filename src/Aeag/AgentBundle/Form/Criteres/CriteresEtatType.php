<?php

namespace Aeag\AgentBundle\Form\Criteres;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of CriteresRequestType
 *
 * @author lavabre
 */
class CriteresEtatType extends AbstractType {

    public function buildform(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('nomPrenom', 'text', array('label' => 'nom', 'required' => false));
    
    }

    public function getName() {
        return 'criteres';
    }

}

