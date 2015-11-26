<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class HistoriqueType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateCreation')
            ->add('nom')
            ->add('prenom')
            ->add('organisme')
            ->add('email')
            ->add('theme')
            ->add('sousTheme')
            ->add('objet')
            ->add('corps')
            ->add('dateEcheance')
        ;
    }

    public function getName()
    {
        return 'aeag_diebundle_historiquetype';
    }
}
