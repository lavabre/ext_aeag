<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnvoyerMessageInterlocuteurType extends AbstractType {
    
  
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('destinataire', 'email', array('label' => 'Email','required' => true))
                ->add('sujet', 'text', array('label' => 'Sujet', 'required' => false))
                ->add('message', 'textarea', array('label' => 'Message', 'required' => true))

        ;
    }

    public function getName() {
        return '';
    }

}
