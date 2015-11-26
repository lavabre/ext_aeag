<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EnvoyerMessageType extends AbstractType {
    
    public function __construct($emails = null) {
        $this->emails = $emails;
       }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $emails = $this->emails;
        $mailListe = array();
        foreach ($emails as $email){
            $mailListe[$email] = $email;
        }
        
        $builder
                ->add('destinataire', 'choice', array(
                        'choices'   => $mailListe,
                        'multiple'  => true,
                        'expanded' => false,
                        'empty_value' => 'Choisissez une adresse mail',
                    ))
                ->add('sujet', 'text', array('label' => 'Sujet', 'required' => false))
                ->add('message', 'textarea', array('label' => 'Message', 'required' => true))

        ;
    }

    public function getName() {
        return '';
    }

}
