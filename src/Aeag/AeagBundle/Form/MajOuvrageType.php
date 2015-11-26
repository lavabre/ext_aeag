<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajOuvrageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
               ->add('ouvId', 'text', array('label' => 'Ouvrage id', 'required' => false, 'read_only' => true))
                ->add('numero', 'text', array('label' => 'Numéro', 'required' => false, 'read_only' => true))
                ->add('libelle', 'text', array('label' => 'Libelle', 'required' => true))
                ->add('siret', 'text', array('label' => 'Siret', 'required' => true))
                ->add('dec', 'choice', array('choices'   => array('O' => 'Oui', 'N' => 'Non'),
                                               'required' => true,
                                               'empty_value' => false));
               
       
    }

    public function getName()
    {
        return 'majOuvrage';
    }
}
