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
                ->add('organisme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Organisme',
                    'choice_label' => 'organisme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'empty_value' => '',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        $qb = $er->createQueryBuilder('d');
                        return $qb->orderBy('d.ordre', 'ASC');
                    },
                ))
                ->add('email')
                ->add('theme', 'entity', array(
                    'label' => 'Thème    ',
                    'class' => 'Aeag\DieBundle\Entity\Theme',
                    'choice_label' => 'theme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'empty_value' => '',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        $qb = $er->createQueryBuilder('d');
                        return $qb->orderBy('d.ordre', 'ASC');
                    },
                ))
                /* ->add('sousTheme', 'entity', array(
                  'class' => 'Aeag\DieBundle\Entity\SousTheme',
                  'property' => 'soustheme',
                  'expanded' => false,
                  'multiple' => false,
                  'required' => false
                  )) */
                ->add('objet')
                ->add('corps', 'textarea', array('trim' => true))
                ->add('dept', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Departement',
                    'multiple' => false,
                    'required' => true,
                    'label' => 'Département',
                    'empty_value' => '',
                    'choice_label' => 'DeptLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        $qb = $er->createQueryBuilder('d');
                        return $qb->orderBy('d.dept', 'ASC');
                    },
                ))


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
