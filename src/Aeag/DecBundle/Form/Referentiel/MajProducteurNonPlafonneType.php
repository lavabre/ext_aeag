<?php

namespace Aeag\DecBundle\Form\Referentiel;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajProducteurNonPlafonneType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siret', 'text', array('label' => 'Siret','required' => true))
            ->add('libelle', 'text', array('label' => 'Libelle','required' => true))
            ->add('aidable', 'choice', array('choices'   => array('O' => 'Oui', 'N' => 'Non'),
                                               'required' => true,
                                               'empty_value' => false))
            ;
       
    }

    public function getName()
    {
        return 'majProducteurNonPlafonne';
    }
}
