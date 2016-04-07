<?php

namespace Aeag\EdlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Aeag\EdlBundle\Repository\AdminDepartementRepository;

class MasseEauRechercheForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {      
       
        $builder
            ->add('codecle', 'text', array('label' => 'code-cle', 
                                           'required' => false    
                ))
            ->add('massecle', 'text', array('label' => 'masse-clé',
                                            'required' => false,
                ))
//                ->add('deptcle', 'text', array('label' => 'deptcle',
//                                            'required' => false,
//                ))
            ->add('deptcle', 'entity', array(
		'class' => 'Aeag\EdlBundle\Entity\AdminDepartement',
                                      'multiple' => false,
                                     'required' => true,
                                     'label' => 'Département',
                                     'empty_value' => '',
                                     'choice_label' => 'DeptLibelle',
                                     'query_builder' => function(AdminDepartementRepository $er)
                                                            {
                                                             return $er->createQueryBuilder('d')
                                                              ->orderBy('d.inseeDepartement', 'ASC');
                                                            },
		))
                                    
             ->add('typecle', 'choice', array(
		'choices' => array('CW' => 'Cotière',
                                   'TW' => 'Transition',
                                   'LW' => 'lacs',
                                   'RW' => 'Rivière',
                                   'GW' => 'Souterraine'),
		'expanded' => false,
		'multiple' => false,
                'required' => false,
              ))
                                    
             ->add('territoirecle', 'checkbox', array( 'label'     => 'Mon territoire ?',
                                                       'required'  => false,
                ));
                       
    }
    
    public function getName()
    {        
        return 'masseeaurecherche';
    }
}
