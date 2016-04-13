<?php

namespace Aeag\EdlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\CallbackValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\RegexValidator;

class LoginType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder
		->add('login')
		->add('passwd', 'password')
                ->add('messageError')       
		
		;

	}

	public function getName()
	{
		return 'aeag_etatdeslieusbundle_logintype';
	}

	public function getDefaultOptions(array $options)
	{
		return array(
	'data_class' => 'Aeag\EdlBundle\Entity\Utilisateur',
	);
	}

}
