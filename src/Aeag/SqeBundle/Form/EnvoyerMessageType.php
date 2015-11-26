<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnvoyerMessageType extends AbstractType {

   
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('copie', 'email', array('label' => 'Cc', 'required' => false))
                ->add('sujet', 'text', array('label' => 'Sujet', 'required' => true))
                ->add('message', 'textarea', array('label' => 'Message', 'required' => true))

        ;
    }

    public function getName() {
        return '';
    }

}
