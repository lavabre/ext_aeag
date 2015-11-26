<?php

namespace Aeag\DieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class DemandeSousThemeType extends AbstractType {

    public function __construct($idTheme, $idSousTheme) {
        $this->idTheme = $idTheme;
        //$this->idSousTheme = $idSousTheme;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $idTheme = $this->idTheme; //le parametre à passer à la fonction
        //$idSousTheme = $this->idSousTheme; //le parametre à passer à la fonction

        $builder
                ->add('nom')
                ->add('prenom')
                ->add('organisme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Organisme',
                    'property' => 'organisme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true
                ))
                ->add('email')
                ->add('theme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\Theme',
                    'property' => 'theme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'read_only' => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($idTheme) {
                                       $qb = $er->createQueryBuilder('t')
                                                ->where('t.id = :id')
                                                ->setParameter('id', $idTheme);
                                        return $qb->orderBy('t.theme', 'ASC');
                    }
                ))
                /*->add('sousTheme', 'entity', array(
                    'class' => 'Aeag\DieBundle\Entity\SousTheme',
                    'property' => 'soustheme',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'read_only' => true,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($idTheme,$idSousTheme) {
                                       $qb = $er->createQueryBuilder('st')
                                                ->where('st.id = :idSousTheme and st.theme = ' . $idTheme)
                                                ->setParameter('idSousTheme', $idSousTheme);
                                                return $qb->orderBy('st.sousTheme', 'ASC');
                    }
                ))*/
                ->add('objet')
                ->add('corps', 'textarea')


        ;
    }

    public function getName() {
        return 'aeag_diebundle_demandeSousThemetype';
    }

    public function getDefaultOptions(array $options) {
        return array(
            'data_class' => 'Aeag\DieBundle\Entity\Demande',
        );
    }

}
