<?php

namespace Aeag\DecBundle\Form\Parametres;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajParametreType extends AbstractType
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
        return 'majparametres';
    }
}
