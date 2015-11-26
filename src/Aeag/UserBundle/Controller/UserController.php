<?php

namespace Aeag\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\UserBundle\Entity\User;
use Aeag\AeagBundle\Entity\Correspondant;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\UserBundle\Form\UserType;
use Aeag\UserBundle\Form\UserUpdateType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\AeagBundle\Controller\AeagController;

/**
 * User controller.
 *
 */
class UserController extends Controller {

    /**
     * Lists all User entities.
     *
     */
    public function indexAction($role = null) {

        $security = $this->get('security.authorization_checker');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $session->set('retourErreur', $this->generateUrl('AeagUserBundle_User', array('role' => $role)));
        $user = $this->getUser();
        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $session->set('menu', 'acteurs');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $repoCorrespondant = $em->getRepository('AeagAeagBundle:Correspondant');

        if ($security->isGranted('ROLE_ADMIN')) {
            $entities = $repoUsers->getUsersByRole('ROLE_AEAG');
            $role = 'ROLE_AEAG';
        } elseif ($security->isGranted('ROLE_ODEC')) {
            $entities = $repoUsers->getUsersByRole('ROLE_ODEC');
            $role = 'ROLE_ODEC';
        } elseif ($security->isGranted('ROLE_FRD')) {
            $entities = $repoUsers->getUsersByRole('ROLE_FRD');
            $role = 'ROLE_FRD';
        } elseif ($security->isGranted('ROLE_SQE')) {
            $entities = $repoUsers->getUsersByRole('ROLE_SQE');
            $role = 'ROLE_SQE';
        } else {
            $entities = $repoUsers->getUsersByRole('ROLE_AEAG');
            $role = 'ROLE_AEAG';
        }
        $session->set('retour', $this->generateUrl('AeagUserBundle_User', array('role' => $role)));
        $users = array();
        $i = 0;
        foreach ($entities as $entity) {
            if ($entity->getCorrespondant()) {
                $correspondant = $repoCorrespondant->getCorrespondantById($entity->getCorrespondant());
            } else {
                $correspondant = $repoCorrespondant->getCorrespondant($entity->getUsername());
            }
            $users[$i][0] = $entity;
            if ($correspondant) {
                $users[$i][1] = $correspondant;
            } else {
                $users[$i][1] = null;
            }
            $i++;
        }

        return $this->render('AeagUserBundle:User:index.html.twig', array(
                    'entities' => $users
        ));
    }

    /**
     * Finds and displays a User entity.
     *
     */
    public function showAction($id) {

        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $user = $this->getUser();
        if (is_object($user)) {
            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
        }

        $entity = $em->getRepository('AeagUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AeagUserBundle:User:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to create a new User entity.
     *
     */
    public function newAction() {
        $entity = new User();
        $entity->setEnabled(true);
        $form = $this->createForm(new UserType(), $entity);

        return $this->render('AeagUserBundle:User:new.html.twig', array(
                    'entity' => $entity,
                    'message' => null,
                    'form' => $form->createView()
        ));
    }

    /**
     * Creates a new User entity.
     *
     */
    public function createAction(Request $request) {
        $session = $this->get('session');
        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $entity = new User();
        $form = $this->createForm(new UserType(), $entity);
        $form->handleRequest($request);

        $message = null;

        if (is_null($entity->getCorrespondant())) {
            $message = 'La référence aeag est obligatoire ';
        }

        if ($entity->getPassword() == null) {
            if ($message) {
                $message = 'Le login et le mot de passe sont obligatoire';
            } else {
                $message = 'Le mot de passe est obligatoire';
            }
        }

        if (is_null($entity->getRoles())) {
            $message = 'Le role est obligatoire ';
        }

        if ($message) {
            return $this->render('AeagUserBundle:User:new.html.twig', array(
                        'entity' => $entity,
                        'message' => $message,
                        'form' => $form->createView()
            ));
        }


        if ($form->isValid()) {
            // return new Response('username : ' . $entity->getUsername() );
            $encoder = $factory->getEncoder($entity);
            $entity->setSalt('');
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setpassword($password);
            $entity->setPlainPassword($entity->getPassword());
            //return new Response ('enabled : ' . $entity->getEnabled());
            if ($entity->getEnabled() == '0') {
                $entity->setEnabled(FALSE);
            } else {
                $entity->setEnabled(TRUE);
            }
            if (!$entity->hasRole('ROLE_AEAG')) {
                $entity->addRole('ROLE_AEAG');
            }
            $em->persist($entity);
            $em->flush();

            $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
            $notification = new Notification();
            $notification->setRecepteur($entity->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage('Votre compte a été crée.');
            $em->persist($notification);
            $em->flush();
            $notifications = $repoNotifications->getNotificationByRecepteur($entity);
            $session->set('Notifications', $notifications);


            if ($security->isGranted('ROLE_ADMIN')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_AEAG')));
            };
            if ($security->isGranted('ROLE_ADMINDEC')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_ODEC')));
            };
            if ($security->isGranted('ROLE_ADMINFRD')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_FRD')));
            };
        }

        $maj = 'ko';

        return $this->render('AeagUserBundle:User:new.html.twig', array(
                    'entity' => $entity,
                    'message' => $message,
                    'form' => $form->createView(),
                    'maj' => $maj
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction($id) {
        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $role = 'ROLE-AEAG';

        if ($security->isGranted('ROLE_ADMIN')) {
            $role = 'ROLE_AEAG';
        };
        if ($security->isGranted('ROLE_ADMINDEC')) {
            $role = 'ROLE_ODEC';
        };
        if ($security->isGranted('ROLE_ADMINFRD')) {
            $role = 'ROLE_FRD';
        };
        if ($security->isGranted('ROLE_ADMINSQE')) {
            $role = 'ROLE_SQE';
        };
        if ($security->isGranted('ROLE_ADMINSTOCK')) {
            $role = 'ROLE_STOCK';
        };

        $entity = $em->getRepository('AeagUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        $editForm = $this->createForm(new UserUpdateType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        $message = null;
        $maj = 'ko';
        return $this->render('AeagUserBundle:User:edit.html.twig', array(
                    'entity' => $entity,
                    'role' => $role,
                    'message' => $message,
                    'form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'maj' => $maj
        ));
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction($id, Request $request) {

        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $session = $this->get('session');
        $repoUsers = $em->getRepository('AeagUserBundle:User');
        $role = 'ROLE-AEAG';

        if ($security->isGranted('ROLE_ADMIN')) {
            $role = 'ROLE_AEAG';
        };
        if ($security->isGranted('ROLE_ADMINDEC')) {
            $role = 'ROLE_ODEC';
        };
        if ($security->isGranted('ROLE_ADMINFRD')) {
            $role = 'ROLE_FRD';
        };

        $entity = $em->getRepository('AeagUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retrouver l\' entity.' . $id);
        }

        $maj = 'ko';
        $form = $this->createForm(new UserUpdateType(), $entity);
        $deleteForm = $this->createDeleteForm($id);


        $form->handleRequest($request);


        $message = null;

        if ($entity->getRoles() == null) {
            $message = 'Le role est obligatoire ';
        }
        if ($entity->getPassword() == null) {
            if ($message) {
                $message = 'Le login et le mot de passe sont obligatoire';
            } else {
                $message = 'Le mot de passe est obligatoire';
            }
        }

        if ($message) {
            return $this->render('AeagUserBundle:User:edit.html.twig', array(
                        'entity' => $entity,
                        'role' => $role,
                        'message' => $message,
                        'form' => $form->createView()
            ));
        }



        if ($form->isValid()) {
            $encoder = $factory->getEncoder($entity);
            $entity->setSalt('');
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setpassword($password);
            $entity->setPlainPassword($entity->getPassword());
            //return new Response ('enabled : ' . $entity->getEnabled());
            if ($entity->getEnabled() == '0') {
                $entity->setEnabled(FALSE);
            } else {
                $entity->setEnabled(TRUE);
            }
            if (!$entity->hasRole('ROLE_AEAG')) {
                $entity->addRole('ROLE_AEAG');
            }
            $em->persist($entity);
            $em->flush();

            $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
            $notification = new Notification();
            $notification->setRecepteur($entity->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage('Votre compte a été mis à jour.');
            $em->persist($notification);
            $em->flush();

            // message aux administrateurs du site
            $admins = $repoUsers->getUsersByRole('ROLE_ADMINDEC');
            foreach ($admins as $admin) {
                $notification = new Notification();
                $notification->setRecepteur($admin->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('le compte de ' . $entity->getUserName() . ' a été modifié.');
                $em->persist($notification);
                $em->flush();
            }

            $mes = AeagController::notificationAction($user, $em, $session);
            $mes1 = AeagController::messageAction($user, $em, $session);
            $maj = 'ok';
            $session->getFlashBag()->add('notice-success', "le compte de  " . $entity->getUserName() . " a été modifié !");
            return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => $role)));
        }


        return $this->render('AeagUserBundle:User:edit.html.twig', array(
                    'entity' => $entity,
                    'role' => $role,
                    'message' => $message,
                    'form' => $form->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'maj' => $maj
        ));
    }

    /**
     * Deletes a User entity.
     *
     */
    public function deleteAction($id, Request $request) {
        $form = $this->createDeleteForm($id);

        $form->bindRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getEntityManager();
            $entity = $em->getRepository('AeagUserBundle:User')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find User entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('AeagUserBundle_User'));
    }

    private function createDeleteForm($id) {
        return $this->createFormBuilder(array('id' => $id))
                        ->add('id', 'hidden')
                        ->getForm()
        ;
    }

}
