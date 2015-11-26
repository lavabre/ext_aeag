<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('theme')
        ;
    }

    public function getName()
    {
        return 'aeag_diebundle_themetype';
    }
}
