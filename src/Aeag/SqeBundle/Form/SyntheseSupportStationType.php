<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

class SyntheseSupportStationType extends AbstractType {
    
      public function __construct($pgCmdSuiviPrel) {
        $this->pgCmdSuiviPrel = $pgCmdSuiviPrel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $pgCmdSuiviPrel = $this->pgCmdSuiviPrel;
        $builder
                ->add('validation', 'choice', array(
                    'choices' => array('F' => 'Favorable', 'D' => 'Défavorable'),
                    'required' => true,
                    'data' => $pgCmdSuiviPrel->getValidation(),
                ))
                 ->add('commentaireActuel', 'textarea', array(
                    'label' => 'Commentaire',
                    'data' => $pgCmdSuiviPrel->getCommentaire(),
                    'required' => false,
                     'disabled' => true))
                ->add('commentaire', 'textarea', array(
                    'label' => 'Commentaire',
                    'required' => true))
        ;
    }

    public function getName() {
        return '';
    }

}
