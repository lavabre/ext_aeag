<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajCompteType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('username', 'text', array('label' => 'Login', 'required' => false, 'read_only' => true))
                ->add('email', 'email', array('label' => 'Email', 'required' => true))
                ->add('email1', 'email', array('label' => 'Email 2', 'required' => false))
                ->add('email2', 'email', array('label' => 'Email 3', 'required' => false))
                ->add('passwordEnClair', 'text', array('label' => 'Mot de passe', 'required' => true, 'read_only' => true))
                ->add('tel', 'text', array('label' => 'N° téléphone', 'required' => false))

        ;
    }

    public function getName() {
        return 'MajCompte';
    }

}
