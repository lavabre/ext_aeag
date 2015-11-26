<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnvoyerMessageType extends AbstractType {

     public function __construct($email = null) {
        $this->email = $email;
       }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $email = $this->email;
        $builder
                 ->add('destinataire', 'choice', array(
                        'choices'   => array($email[0] => $email[0], $email[1] => $email[1], $email[2] => $email[2]),
                        'multiple'  => true,
                        'expanded' => false,
                        'empty_value' => 'Choisissez une adresse mail',
                    ))
                ->add('copie', 'email', array('label' => 'Cc', 'required' => false))
                ->add('sujet', 'text', array('label' => 'Sujet', 'required' => true))
                ->add('message', 'textarea', array('label' => 'Message', 'required' => true))

        ;
    }

    public function getName() {
        return '';
    }

}
