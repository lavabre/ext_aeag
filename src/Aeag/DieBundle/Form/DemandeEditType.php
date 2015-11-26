<?php
namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class DemandeEditType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
		->add('nom')
		->add('prenom')
		->add('organisme')
		->add('email')
		->add('theme')
                ->add('objet')
		->add('corps')
		

		;
	}

	public function getName()
	{
		return 'aeag_diebundle_demandetype';
	}
	
	public function getDefaultOptions(array $options)
	{
		return array(
	'data_class' => 'Aeag\DieBundle\Entity\Demande',
	);
	}
	
}
