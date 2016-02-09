<?php

namespace Aeag\AideBundle\Form\Criteres;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of CriteresRequestType
 *
 * @author lavabre
 */
class CriteresRequestType extends AbstractType {

    public function buildform(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('ligne', 'entity', array(
                    'class' => 'Aeag\\AideBundle\\Entity\\Ligne',
                    'multiple' => false,
                    'required' => false,
                    'label' => "",
                    'choice_label' => 'LigneLibelle',
                ))
                ->add('cate', 'entity', array(
                    'class' => 'Aeag\\AideBundle\\Entity\\Categorie',
                    'multiple' => true,
                    'required' => false,
                    'choice_label' => 'Cate',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->orderBy('c.cate', 'desc');
                    }
                ))
//                ->add('DebutAnnee', 'entity', array(
//                    'class' => 'Aeag\\AideBundle\\Entity\\Annee',
//                    'multiple' => false,
//                    'required' => true,
//                    'label' => "",
//                    'choice_label' => 'annee',
//                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
//                        return $er->createQueryBuilder('a')
//                                ->orderBy('a.annee', 'desc');
//                    }
//                ))
//               
               ->add('dateDebut', 'datetime', array('widget' => 'single_text',
                                              'format' => 'dd/MM/yyyy',
                                              'required' => false,
                                              'read_only' => true))
//                ->add('FinAnnee', 'entity', array(
//                    'class' => 'Aeag\\AideBundle\\Entity\\Annee',
//                    'multiple' => false,
//                    'required' => true,
//                    'label' => "",
//                    'choice_label' => 'annee',
//                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
//                        return $er->createQueryBuilder('a')
//                                ->orderBy('a.annee', 'desc');
//                    }
//                ))
                 ->add('dateFin', 'datetime', array('widget' => 'single_text',
                                              'format' => 'dd/MM/yyyy',
                                              'required' => false,
                                              'read_only' => true))
                ->add('regionAdmin', 'entity', array(
                    'class' => 'Aeag\\AeagBundle\\Entity\\Region',
                    'multiple' => false,
                    'required' => false,
                    'label' => "",
                    'choice_label' => 'Libelle',
                  
                )) 
                ->add('departement', 'entity', array(
                    'class' => 'Aeag\\AeagBundle\\Entity\\Departement',
                    'multiple' => false,
                    'required' => false,
                    'label' => "",
                    'choice_label' => 'DeptLibelle',
                  
                ))  
                        
                 ->add('regionHydro', 'entity', array(
                    'class' => 'Aeag\\AideBundle\\Entity\\RegionHydro',
                    'multiple' => false,
                    'required' => false,
                    'label' => "",
                    'choice_label' => 'Libelle',
                  
                ))         
                        
                ->getForm();
    }

    public function getName() {
        return 'criteres';
    }

}

