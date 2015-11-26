<?php

namespace Aeag\FrdBundle\Form\Referentiel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajPhaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('code', 'text', array('required' => false, 'read_only' => true))
           ->add('libelle', 'textarea', array('required' => false, 'trim' => true))
        
        ;
       
    }

    public function getName()
    {
        return 'aeag_frdbundle_phasetype';
    }
}
