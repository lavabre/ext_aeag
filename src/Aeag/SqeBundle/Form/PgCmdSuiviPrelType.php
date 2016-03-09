<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PgCmdSuiviPrelType extends AbstractType {

   
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('datePrel', 'datetime', array('widget' => 'single_text',
                                              'format' => 'dd/MM/yyyy',
                                              'required' => true,
                                              'read_only' => false))
                ->add('statutPrel', 'choice', array(
                        'choices'   => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'V' => 'Validé', 'R' => 'Réfusé'),
                        'required'  => true,
                    ))
                ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))

        ;
    }

    public function getName() {
        return '';
    }

}
