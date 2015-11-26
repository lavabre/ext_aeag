<?php

namespace Aeag\DecBundle\Form\Referentiel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajNafType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code', 'text', array('label' => 'Code','required' => true))
            ->add('libelle', 'text', array('label' => 'Libelle','required' => true))
            ->add('aidable', 'choice', array('choices'   => array('O' => 'Oui', 'N' => 'Non'),
                                               'required' => true,
                                               'empty_value' => false))
            ;
       
    }

    public function getName()
    {
        return 'majNaf';
    }
}
