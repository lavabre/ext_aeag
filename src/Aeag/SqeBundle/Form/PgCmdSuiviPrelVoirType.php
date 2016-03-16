<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PgCmdSuiviPrelVoirType extends AbstractType {

     public function __construct($user) {
        $this->user = $user;
      }
   
    public function buildForm(FormBuilderInterface $builder, array $options) {
         $user = $this->user;
        if ($user->hasRole('ROLE_ADMINSQE')) {
                $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => false,
                                                      'read_only' => true))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'V' => 'Validé', 'R' => 'Réfusé'),
                                'required'  =>false,
                            'read_only' => true
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' =>false, 'read_only' => true))
                    ;
        }else{
              $builder
                        ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                                      'format' => 'dd/MM/yyyy',
                                                      'required' => false,
                                                      'read_only' => true))
                        ->add('statutPrel', 'choice', array(
                                'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                'required'  =>false,
                            'read_only' => true
                            ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' =>false, 'read_only' => true))
                    ;
        }
    }

    public function getName() {
        return '';
    }

}
