<?php

namespace Aeag\DecBundle\Form\Parametres;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewParametreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           ->add('code', 'text', array('required' => true))
           ->add('libelle', 'textarea', array('required' => false, 'trim' => true))
        
        ;
       
    }

    public function getName()
    {
        return 'newparametres';
    }
}
