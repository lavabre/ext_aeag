<?php

namespace Aeag\AeagBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class DocumentType extends AbstractType {
    
  
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
               ->add('file')
        ;
    }

    public function getName() {
        return '';
    }

}
