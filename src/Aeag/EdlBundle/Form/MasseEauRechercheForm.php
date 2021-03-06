<?php

namespace Aeag\EdlBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MasseEauRechercheForm extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
                ->add('codecle', 'text', array('label' => 'code-cle',
                    'required' => false
                ))
                ->add('massecle', 'text', array('label' => 'masse-clé',
                    'required' => false,
                ))
                ->add('deptcle', 'entity', array(
                    'class' => 'Aeag\EdlBundle\Entity\AdminDepartement',
                    'choice_label' => 'nomDepartement',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'empty_value' => '',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        $qb = $er->createQueryBuilder('d');
                        return $qb->orderBy('d.inseeDepartement', 'ASC');
                    },
                ))
                ->add('typecle', 'choice', array(
                    'choices' => array('CW' => 'Cotière',
                        'TW' => 'Transition',
                        'LW' => 'Lac',
                        'RW' => 'Rivière',
                        'GW' => 'Souterraine'),
                    'expanded' => false,
                    'multiple' => false,
                    'choice_translation_domain' => false,
                    'required' => false,
                ))
                ->add('territoirecle', 'checkbox', array('label' => 'Mon territoire ?',
                    'required' => false,
                    'translation_domain' => false,
        ));
    }

    public function getName() {
        return 'masseeaurecherche';
    }

}
