<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SousThemeType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
            	->add('theme', 'entity', array(
                                                'class' => 'Aeag\DieBundle\Entity\Theme',
                                                'property' => 'theme',
                                                'expanded' => false,
                                                'multiple' => false,
                                                'required' => true))
		->add('sousTheme', 'text')
		->add('destinataire', 'email')
		->add('objet', 'text')
		->add('corps', 'textarea')
		->add('echeance', 'text')
	
            ;
	}

	public function getName()
	{
		return 'aeag_diebundle_sousthemetype';
	}
}
