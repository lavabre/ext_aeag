<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class OrganismeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('organisme')
            ->add('ordre')
        ;
    }

    public function getName()
    {
        return 'aeag_diebundle_organismetype';
    }
}
