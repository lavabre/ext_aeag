<?php

namespace Aeag\FrdBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class SousThemeFinaliteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function __construct($idFinalite) {
        $this->idFinalite = $idFinalite;
     }
    
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $idFinalite = $this->idFinalite; //le parametre à passer à la fonction
        $builder
          
            ->add('sousTheme', 'entity', array(
                    'class' => 'Aeag\FrdBundle\Entity\SousTheme',
                    'choice_label' => 'libelle',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => false,
                    'read_only' => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er)  use ($idFinalite){
                        $qb = $er->createQueryBuilder('t')
                                   ->where('t.finalite = :id')
                                   ->setParameter('id', $idFinalite);
                        return $qb->orderBy('t.libelle', 'ASC');
                    }
                ))
            
        ;
    }
    
   

    /**
     * @return string
     */
    public function getName()
    {
        return 'aeag_frdbundle_sousThemeFinalite';
    }
}
