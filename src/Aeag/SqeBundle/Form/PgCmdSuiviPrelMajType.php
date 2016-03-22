<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PgCmdSuiviPrelMajType extends AbstractType {

    public function __construct($user) {
        $this->user = $user;
      }
   
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->user;
        if ($user->hasRole('ROLE_ADMINSQE')) {
                $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => true,))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'A' => 'Analyses effectuées', 'V' => 'Validé', 'R' => 'Réfusé'),
                                'required'  => false,
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                  ;
    }else{
                 $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => true,))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'A' => 'Analyses effectuées'),
                                'required'  => false,
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                  ;
    }
        
    }

    public function getName() {
        return '';
    }

}
