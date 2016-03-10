<?php

namespace Aeag\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class UserUpdateType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
       $builder
                ->add('username', 'text', array('label' => 'Login', 'required' => false, 'read_only' => true))
                ->add('password', 'text', array('label' => 'password', 'required' => false, 'read_only' => true))
                ->add('email', 'email', array('label' => 'Email', 'required' => true))
                ->add('email1', 'email', array('label' => 'Email1', 'required' => false))
                ->add('email2', 'email', array('label' => 'Email2', 'required' => false))
                ->add('tel', 'text', array('label' => 'N° télphone', 'required' => false))
                ->add('tel1', 'text', array('label' => 'N° télphone', 'required' => false))
                ->add('enabled', 'choice', array('choices' => array('1' => 'Oui', '0' => ' Non'),
                    'required' => true,
                    'expanded' => true,
                ))
                ->add('roles', 'choice', array(
                    'choices' => array('ROLE_ADMIN' => 'Administrateur ',
                        'ROLE_ADMINDEC' => 'Administrateur Déchet',
                        'ROLE_ADMINFRD' => 'Administrateur Frd',
                        'ROLE_ADMINSQE' => 'Administrateur Sqe',
                       'ROLE_PROGSQE' => 'Programmeur Sqe',
                       'ROLE_PRESTASQE' => 'Prestataire Sqe',
                        'ROLE_ODEC' => 'Collecteur  Déchet',
                        'ROLE_FRD' => 'Membre Frd ',
                        'ROLE_SQE' => 'Consultant Sqe ',),
                    'required' => true,
                    'multiple' => true,
                ))
            ->add('current_password', 'password', array(
            'label' => 'form.current_password',
            'translation_domain' => 'FOSUserBundle',
            'mapped' => false,
            'constraints' => new UserPassword(),
        ));

        ;
    }

    public function getName() {
        return 'aeag_userbundle_userrtype';
    }

}
