<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DemandeThemeType extends AbstractType {

    public function __construct($idTheme) {
        $this->id = $idTheme;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $idTheme = $this->id; //le parametre à passer à la fonction	
                    
        $builder
                ->add('nom')
                ->add('prenom')
                ->add('organisme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Organisme',
                    'property' => 'organisme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'empty_value' => '',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er)
                                    {
                                    $qb = $er->createQueryBuilder('d');
                                    return $qb->orderBy('d.ordre', 'ASC');
                                    },
                ))
                ->add('email')
               /*->add('theme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Theme',
                    'property' => 'theme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'read_only' => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($idTheme) {
                                       $qb = $er->createQueryBuilder('t')
                                                ->where('t.id = :id')
                                                ->setParameter('id', $idTheme);
                                        return $qb->orderBy('t.theme', 'ASC');
                    }
                ))
                ->add('sousTheme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\SousTheme',
                    'property' => 'soustheme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($idTheme) {
                                       $qb = $er->createQueryBuilder('st')
                                                ->where('st.theme = :id')
                                                ->setParameter('id', $idTheme);
                                        return $qb->orderBy('st.sousTheme', 'ASC');
                    }
                ))*/
               ->add('objet')
               ->add('corps', 'textarea', array('trim' => true))
                                            
               ->add('dept', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Departement',
                    'multiple' => false,
                    'required' => true,
                    'label' => 'Département',
                    'empty_value' => '',
                    'property' => 'DeptLibelle',
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er)
                                    {
                                    $qb = $er->createQueryBuilder('d');
                                    return $qb->orderBy('d.dept', 'ASC');
                                    },
                  
                ))  


        ;
    }

    public function getName() {
        return 'aeag_diebundle_demandethemetype';
    }

    

}
