<?php

namespace Aeag\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('correspondant', 'integer', array('label' => 'Référence aeag','required' => true))
            ->add('username', 'text', array('label' => 'Login','required' => true))
            ->add('prenom', 'text', array('label' => 'Prénom','required' => false))
            ->add('password', 'text', array('label' => 'password','required' => true))
            ->add('email', 'email', array('label' => 'Email','required' => true))
            ->add('email1', 'email', array('label' => 'Email1','required' => false))
            ->add('email2', 'email', array('label' => 'Email2','required' => false))
            ->add('tel', 'text', array('label' => 'N° télphone','required' => false))
            ->add('tel1', 'text', array('label' => 'N° télphone','required' => false))
            ->add('enabled','choice', array('choices' => array( '1' => 'Oui', '2' => 'Non'),
                                            'empty_data'  => array('1'),
                                            'required' => true,
                                            'expanded' => true,
                ))
           ->add('roles', 'choice', array(
                'choices'   => array('ROLE_ADMIN' => 'Administrateur ',
                                     'ROLE_ADMINDEC' => 'Administrateur Déchet',
                                     'ROLE_ADMINFRD' => 'Administrateur Frd',
                                     'ROLE_ADMINSQE' => 'Administrateur Sqe',
                                     'ROLE_ADMINEDL' => 'Administrateur Edl',
                                     'ROLE_PROGSQE' => 'Programmeur Sqe',
                                     'ROLE_PRESTASQE' => 'Prestataire Sqe',
                                     'ROLE_ODEC' => 'Collecteur  Déchet',
                                    'ROLE_FRD' => 'Membre Frd ',
                                    'ROLE_SQE' => 'Consultant Sqe ',
                                    'ROLE_COMMENTATEUREDL' => 'Commentateur Edl',
                                    'ROLE_SUPERVISEUREDL' => 'Superviseur Edl',),
               'empty_value' => 'Choisissez un rôle',
                'required'  => true,
                'multiple' => true,
              ))
                
             ->add('depts', 'entity', array(
                    'class' => 'Aeag\EdlBundle\Entity\AdminDepartement',
                    'property' => 'nomDepartement',
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                    'empty_value' => '     ',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        $qb = $er->createQueryBuilder('d');
                        return $qb->orderBy('d.inseeDepartement', 'ASC');
                    },
                ))
          
        ;
       
    }

    public function getName()
    {
        return 'aeag_userbundle_usertype';
    }
}
