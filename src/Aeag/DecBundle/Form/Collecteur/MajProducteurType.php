<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajProducteurType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('ouvId', 'text', array('label' => 'Ouvrage id', 'required' => false, 'read_only' => true))
                ->add('numero', 'text', array('label' => 'Numéro', 'required' => false, 'read_only' => true))
                ->add('libelle', 'text', array('label' => 'Libelle', 'required' => true))
                ->add('siret', 'text', array('label' => 'Siret', 'required' => true))
                ->add('adresse', 'text', array('label' => 'Adresse', 'required' => false))
                ->add('cp', 'text', array('label' => 'Cp', 'required' => true))
                /*/*->add('Commune', 'entity', array(
                    'class' => 'Aeag\DecBundle\Entity\Commune',
                    'multiple' => false,
                    'required' => true,
                    'label' => 'Commune',
                    'empty_value' => '',
                    'choice_label' => 'CommuneLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er)
                                    {
                                    $qb = $er->createQueryBuilder('d');
                                    return $qb->orderBy('d.commune', 'ASC');
                                    },
                  
                ))*/        
                ;
    }

    public function getName() {
        return 'MajProducteur';
    }

}
