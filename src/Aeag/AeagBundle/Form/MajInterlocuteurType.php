<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajInterlocuteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
             ->add('nom', 'text', array('label' => 'Nom','required' => true))
            ->add('prenom', 'text', array('label' => 'Prénom','required' => false))
            ->add('fonction', 'text', array('label' => 'Fonction','required' => true))
            ->add('email', 'email', array('label' => 'Email','required' => true))
            ->add('tel', 'text', array('label' => 'form.tel',
                                           'translation_domain' => 'FOSUserBundle',
                                           'required' => false,
                                           'path' => '^(?:0|\(?\+33\)?\s?|0033\s?)[1-79](?:[\.\-\s]?\d\d){4}$'))
            ;
       
    }

    public function getName()
    {
        return 'majInterlocuteur';
    }
}
