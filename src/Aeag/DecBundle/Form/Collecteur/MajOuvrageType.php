<?php

namespace Aeag\DecBundle\Form\Collecteur;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MajOuvrageType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('ouvId', 'integer', array('label' => 'Ouvrage id', 'required' => false, 'read_only' => true))
                ->add('numero', 'text', array('label' => 'Numéro', 'required' => false, 'read_only' => true))
                ->add('libelle', 'text', array('label' => 'Libelle', 'required' => false))
                ->add('siret', 'text', array('label' => 'Siret', 'required' => false))
                ->add('adresse', 'text', array('label' => 'Adresse', 'required' => false))
                ->add('cp', 'text', array('label' => 'Commune', 'required' => false))
                /*->add('Commune', 'entity', array(
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
                  
                )) */        
                ;
    }

    public function getName() {
        return 'MajOuvrage';
    }

}
