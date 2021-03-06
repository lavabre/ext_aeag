<?php

namespace Aeag\SqeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Response;

class PgCmdSuiviPrelMajType extends AbstractType {

    public function __construct($user, $pgCmdSuiviPrelActuel) {
        $this->user = $user;
        $this->pgCmdSuiviPrelActuel = $pgCmdSuiviPrelActuel;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $user = $this->user;
        $pgCmdSuiviPrelActuel = $this->pgCmdSuiviPrelActuel;

        if ($pgCmdSuiviPrelActuel) {
            if ($user->hasRole('ROLE_ADMINSQE')) {
                switch ($pgCmdSuiviPrelActuel->getStatutPrel()) {
                    case 'P' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                            'data' => $pgCmdSuiviPrelActuel->getValidation(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel',),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'F' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'N' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'R' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'D' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'DF' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'DO' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('validation', 'choice', array(
                                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté', 'F' => 'Abandonné'),
                                            'required' => false,
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                }
            } else {
                switch ($pgCmdSuiviPrelActuel->getStatutPrel()) {
                    case 'P' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel',),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'F' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'N' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'R' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'D' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'DF' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                    case 'DO' :
                        switch ($pgCmdSuiviPrelActuel->getValidation()) {
                            case 'E' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'R' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                            case 'A' :
                                $builder
                                        ->add('datePrel', 'date', array('widget' => 'single_text',
                                            'format' => 'dd/MM/yyyy HH:mm',
                                            'data' => $pgCmdSuiviPrelActuel->getDatePrel(),
                                            'required' => true,
                                        ))
                                        ->add('statutPrel', 'choice', array(
                                            'choices' => array('D' => 'Déposé'),
                                            'required' => true,
                                            'data' => $pgCmdSuiviPrelActuel->getStatutPrel(),
                                        ))
                                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => true))
                                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                                ;
                                break;
                        }
                        break;
                }
            }
        } else {
            if ($user->hasRole('ROLE_ADMINSQE')) {
                $builder
                        ->add('datePrel', 'date', array('widget' => 'single_text',
                            'format' => 'dd/MM/yyyy HH:mm',
                            'required' => true,))
                        ->add('statutPrel', 'choice', array(
                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté', 'D' => 'Déposé'),
                            'required' => true,
                        ))
                        ->add('validation', 'choice', array(
                            'choices' => array('E' => 'En attente', 'R' => 'Refusé', 'A' => 'Accepté'),
                            'required' => false,
                        ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                ;
            } else {
                $builder
                        ->add('datePrel', 'date', array('widget' => 'single_text',
                            'format' => 'dd/MM/yyyy HH:mm',
                            'required' => true,))
                        ->add('statutPrel', 'choice', array(
                            'choices' => array('P' => 'Prévisionnel', 'F' => 'Effectué', 'N' => 'Non effectué', 'R' => 'Reporté', 'D' => 'Déposé'),
                            'required' => true,
                        ))
                        ->add('commentaire', 'textarea', array('label' => 'Commentaire', 'required' => false))
                        ->add('commentaireAvis', 'textarea', array('label' => 'CommentaireAvis', 'required' => false))
                ;
            }
        }
    }

    public function getName() {
        return '';
    }

}
