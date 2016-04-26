<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PgCmdSuiviPrelMajType extends AbstractType {

    public function __construct($user,$pgCmdSuiviPrelActuel) {
        $this->user = $user;
        $this->pgCmdSuiviPrelActuel = $pgCmdSuiviPrelActuel;
      }
   
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->user;
        $pgCmdSuiviPrelActuel  = $this->pgCmdSuiviPrelActuel;
        if ($user->hasRole('ROLE_ADMINSQE')) {
             if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' and $pgCmdSuiviPrelActuel->getValidation() == 'R'){
                   $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => true,))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', ),
                                'required'  => true,
                            ))
                         ->add('validation', 'choice', array(
                                'choices'   => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté'),
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
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                'required'  => true,
                            ))
                         ->add('validation', 'choice', array(
                                'choices'   => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté'),
                                'required'  => false,
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                  ;
             }
    }else{
        if ($pgCmdSuiviPrelActuel->getStatutPrel() == 'P' and $pgCmdSuiviPrelActuel->getValidation() == 'R'){
                 $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => true,))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel'),
                                'required'  => true,
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                  ;
        }else{
                 $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => true,))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                'required'  => true,
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                  ;
        }
    }
        
    }

    public function getName() {
        return '';
    }

}
