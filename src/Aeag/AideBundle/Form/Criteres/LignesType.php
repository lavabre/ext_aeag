<?php

namespace Aeag\AideBundle\Form\Criteres;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 * Description of Lignestype
 *
 * @author lavabre
 */
class Lignestype extends AbstractType {

    public function buildForm(FormBuilder $builder, array $options) {
        $builder
                ->add('ligne')
                ->add('libelle'); // Notez ici que l'on n'a pas précisé le type de champ : c'est parce que
        // le composant Form sait le reconnaître… depuis nos annotations Doctrine !
    }

    public function getName() {
        return 'aeag_aidebundle_lignestype';  // N'oubliez pas de changer le nom du formulaire.
    }

    public function getDefaultOptions(array $options) {
        return array(
            'data_class' => 'Aeag\AideBundle\Entity\Lignes', // Ni de modifier la classe ici.
        );
    }

}

