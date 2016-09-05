<?php

namespace Aeag\FrdBundle\Form\FraisDeplacement;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


class FraisDeplacementType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('valider', 'text', array('required' => false)) 
            ->add('objet', 'text', array('required' => true))
            ->add('dateDepart', 'datetime', array('widget' => 'single_text',
                                              'format' => 'dd/MM/yyyy',
                                              'required' => true,
                                              'read_only' => true))
            ->add('heureDepart', 'text', array('required' => true, 'read_only' => true))
            ->add('dateRetour', 'datetime', array('widget' => 'single_text',
                                              'format' => 'dd/MM/yyyy',
                                              'required' => true,
                                              'read_only' => true))
            ->add('heureRetour', 'text', array('required' => true, 'read_only' => true))
            ->add('itineraire', 'text', array('required' => true))
            ->add('KmVoiture', 'integer', array('required' => false,'invalid_message' => 'Le nombre de kilometres efectuée par le véhicule est incorrect'))
            ->add('KmMoto', 'integer', array('required' => false,'invalid_message' => 'Le nombre de kilometres efectuée par la moto est incorrect'))
            ->add('AutreMidiSem', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas de midi en semaine au restaurant est incorrect'))
            ->add('AutreMidiWeek', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas de midi en week-end au restaurant est incorrect'))
            ->add('AutreSoir', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas du soir au restaurant est incorrect'))
            ->add('OffertMidiSem', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas de midi en semaine offerts ou sans frais  est incorrect'))
            ->add('OffertMidiWeek', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas de midi en week-end offerts ou sans frais  est incorrect'))
            ->add('OffertSoir', 'integer', array('required' => false,'invalid_message' => 'Le nombre de repas du soir offerts ou sans frais  est incorrect'))
            ->add('ProvenceJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de nuitées justifiés est incorrect'))
            ->add('offertNuit', 'integer', array('required' => false,'invalid_message' => 'Le nombre de nuitées offetes ou sans frais est incorrect'))
            ->add('adminNuit', 'choice', array('choices'   => array('O' => 'Oui', 'N' => 'Non'),
                                               'required' => false,
                                               'empty_value' => false))
            ->add('parkJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le parking est incorrect'))
            ->add('parkNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le parking est incorrect'))
            ->add('parkTotal',  'number', array('required' => false,'invalid_message' => 'Le prix du parking est incorrect'))
            ->add('peageJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le péage est incorrect'))
            ->add('peageNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le péage est incorrect'))
            ->add('peageTotal',  'number', array('required' => false,'invalid_message' => 'Le prix du péage est incorrect'))
            ->add('busMetroJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le transport en commun est incorrect'))
            ->add('busMetroNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le transport en commun est incorrect'))
            ->add('busMetroTotal',  'number', array('required' => false,'invalid_message' => 'Le prix du transport en commun est incorrect'))
            ->add('orlyvalJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour l\'Olyval est incorrect'))
            ->add('orlyvalNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour l\'Orlyval est incorrect'))
            ->add('orlyvalTotal',  'number', array('required' => false,'invalid_message' => 'Le prix de l\'Olyval est incorrect'))
            ->add('trainJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le train est incorrect'))
            ->add('trainNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le train est incorrect'))
            ->add('trainTotal',  'number', array('required' => false,'invalid_message' => 'Le prix du train est incorrect'))
            ->add('trainClasse',  'choice', array('choices'   => array('1' => '1ere', '2' => '2eme'),
                                                   'required' => false,
                                                   'empty_value' => false,
                                                    'data'  => '2'))
            ->add('trainCouchette', 'choice', array('choices'   => array('O' => 'Oui', 'N' => 'Non'),
                                                    'required' => false,
                                                    'empty_value' => false))
            ->add('avionJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour l\'avion (et/ou bateau) est incorrect'))
            ->add('avionNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour l\'avion (et/ou bateau) est incorrect'))
            ->add('avionTotal',  'number', array('required' => false,'invalid_message' => 'Le prix de l\'avion (et/ou bateau) est incorrect'))
            ->add('reservationJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le véhicule de location est incorrect'))
            ->add('reservationNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le véhicule de location est incorrect'))
            ->add('reservationTotal',  'number', array('required' => false,'invalid_message' => 'Le prix de la location du véhicule est incorrect'))
            ->add('taxiJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets joints pour le taxi est incorrect'))
            ->add('taxiNonJustif', 'integer', array('required' => false,'invalid_message' => 'Le nombre de tickets non joints pour le taxi est incorrect'))
            ->add('taxiTotal',  'number', array('required' => false,'invalid_message' => 'Le prix du taxi est incorrect'))
            ->add('finalite', 'entity', array(
                    'class' => 'Aeag\FrdBundle\Entity\Finalite',
                    'choice_label' => 'libelle',
                    'expanded' => false,
                    'multiple' => false,
                    'required' => true,
                    'read_only' => false,
                    'query_builder' => function(\Doctrine\ORM\EntityRepository $er){
                        $qb = $er->createQueryBuilder('t');
                        return $qb->orderBy('t.libelle', 'ASC');
                    }
                ))
            ->add('sousTheme', 'text')
            ->add('departement', 'text', array('required' => true))
        ;
    }
    
   

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }
}
