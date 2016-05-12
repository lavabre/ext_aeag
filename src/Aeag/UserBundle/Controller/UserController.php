<?php

namespace Aeag\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Aeag\UserBundle\Entity\User;
use Aeag\UserBundle\Entity\UserEdl;
use Aeag\AeagBundle\Entity\Correspondant;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\UserBundle\Form\UserType;
use Aeag\UserBundle\Form\UsersUpdateType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Aeag\AeagBundle\Controller\AeagController;
use Aeag\EdlBundle\Entity\Utilisateur;
use Aeag\EdlBundle\Entity\AdminDepartement;
use Aeag\EdlBundle\Entity\DepUtilisateur;

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
        } elseif ($security->isGranted('ROLE_EDL')) {
            $entities = $repoUsers->getUsersByRole('ROLE_EDL');
            $role = 'ROLE_EDL';
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
        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $role = 'ROLE_AEAG';

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

        if ($security->isGranted('ROLE_ADMINEDL')) {
            $role = 'ROLE_EDL';
        };

        $entity = new User();
        $entity->setEnabled(true);
        $form = $this->createForm(new UserType(), $entity);

        return $this->render('AeagUserBundle:User:new.html.twig', array(
                    'entity' => $entity,
                    'role' => $role,
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
        $emEdl = $this->getDoctrine()->getManager('edl');
        $repoUser = $em->getRepository('AeagUserBundle:User');
        $user = $this->getUser();

        $role = 'ROLE_AEAG';

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

        if ($security->isGranted('ROLE_ADMINEDL')) {
            $role = 'ROLE_EDL';
        };

        $entity = new User();
        $form = $this->createForm(new UserType(), $entity);

        $form->handleRequest($request);

        $message = null;

        if ($role != 'ROLE_EDL') {
            if (is_null($entity->getCorrespondant())) {
                $message = 'La référence aeag est obligatoire ';
            }
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

        $newUser = $repoUser->getUserByUsername($entity->getUsername());
        if ($newUser) {
            $message = 'Le login est déjà utilisé ';
        }


        if ($message) {
            return $this->render('AeagUserBundle:User:new.html.twig', array(
                        'entity' => $entity,
                        'role' => $role,
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

            if ($entity->getEnabled() == '0') {
                $entity->setEnabled(FALSE);
            } else {
                $entity->setEnabled(TRUE);
            }
            if ($entity->hasRole('ROLE_COMMENTATEUREDL') or $entity->hasRole('ROLE_SUPERVISEUREDL') or $entity->hasRole('ROLE_ADMINEDL')) {
                $entity->addRole('ROLE_EDL');
                $entity->removeRole('ROLE_AEAG');
            } else {
                if (!$entity->hasRole('ROLE_AEAG')) {
                    $entity->addRole('ROLE_AEAG');
                }
            }
            $em->persist($entity);
            $em->flush();
            if ($user->hasRole('ROLE_EDL')) {
                $utilisateur = new Utilisateur();
                $utilisateur->setUsername($entity->getUsername());
                $utilisateur->setUsernameCanonical($entity->getUsernameCanonical());
                $utilisateur->setEmail($entity->getEmail());
                $utilisateur->setEmailCanonical($entity->getEmailCanonical());
                $utilisateur->setEnabled($entity->getEnabled());
                $utilisateur->setAlgorithm($entity->getSalt());
                $utilisateur->setSalt($entity->getSalt());
                $utilisateur->setPassword($entity->getPassword());
                $utilisateur->setPasswordEnClair($entity->getPassword());
                $utilisateur->setLocked(false);
                $utilisateur->setExpired(false);
                $utilisateur->setCredentialsExpired(false);
                $utilisateur->setExtId($entity->getId());
                $utilisateur->removeRole('ROLE_AEAG');
                if ($entity->hasRole('ROLE_COMMENTATEUREDL')) {
                    $utilisateur->addRole('ROLE_COMMENTATEUR');
                } elseif ($entity->hasRole('ROLE_SUPERVISEUREDL')) {
                    $utilisateur->addRole('ROLE_SUPERVISEUR');
                } elseif ($entity->hasRole('ROLE_ADMINEDL')) {
                    $utilisateur->addRole('ROLE_ADMIN');
                }

//                $roles = $utilisateur->getRoles();
//                for ($i = 0; $i < count($roles); $i++) {
//                    print_r('role ' . $i . ' :  ' . $roles[$i]);
//                }
                //return new Response('  ici : ');


                $emEdl->persist($utilisateur);
                $depts = $entity->getDepts();
                foreach ($depts as $dept) {
                    $depUtilisateur = new DepUtilisateur();
                    $depUtilisateur->setInseeDepartement($dept->getInseeDepartement());
                    $depUtilisateur->setUtilisateur($utilisateur);
                    $emEdl->persist($depUtilisateur);
                }
                $emEdl->flush();
            }

            $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
            $notification = new Notification();
            $notification->setRecepteur($entity->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage('Votre compte a été crée.');
            $em->persist($notification);
            $em->flush();


            if ($security->isGranted('ROLE_ADMIN')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_AEAG')));
            };
            if ($security->isGranted('ROLE_ADMINDEC')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_ODEC')));
            };
            if ($security->isGranted('ROLE_ADMINFRD')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_FRD')));
            };

            if ($security->isGranted('ROLE_ADMINSQE')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_SQE')));
            };
            if ($security->isGranted('ROLE_ADMINSTOCK')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_STOCK')));
            };
            if ($security->isGranted('ROLE_ADMINEDL')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_EDL')));
            };
        }

        $maj = 'ko';

        return $this->render('AeagUserBundle:User:new.html.twig', array(
                    'entity' => $entity,
                    'role' => $role,
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

        $session = $this->get('session');
        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $emEdl = $this->getDoctrine()->getManager('edl');
        $user = $this->getUser();
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoDeptUtilisateur = $emEdl->getRepository('AeagEdlBundle:DepUtilisateur');

        $entity = $em->getRepository('AeagUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        $role = 'ROLE_AEAG';

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

        if ($security->isGranted('ROLE_ADMINEDL')) {
            $role = 'ROLE_EDL';
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($entity->getId());
            $deptUtilisateurs = $repoDeptUtilisateur->getDepartementByUtilisateur($utilisateur);
        } else {
            $utilisateur = null;
            $deptUtilisateurs = null;
        }

        $message = null;
        $editForm = $this->createForm(new UsersUpdateType($entity), $entity);
        $maj = 'ko';
        return $this->render('AeagUserBundle:User:edit.html.twig', array(
                    'entity' => $entity,
                    'role' => $role,
                    'utilisateur' => $utilisateur,
                    'depUtilisateurs' => $deptUtilisateurs,
                    'message' => $message,
                    'form' => $editForm->createView(),
                    'maj' => $maj
        ));
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction($id, Request $request) {

        $session = $this->get('session');
        $security = $this->get('security.authorization_checker');
        $factory = $this->get('security.encoder_factory');
        $em = $this->getDoctrine()->getManager();
        $emEdl = $this->getDoctrine()->getManager('edl');
        $user = $this->getUser();
        $repoUtilisateur = $emEdl->getRepository('AeagEdlBundle:Utilisateur');
        $repoDeptUtilisateur = $emEdl->getRepository('AeagEdlBundle:DepUtilisateur');
        $repoUsers = $em->getRepository('AeagUserBundle:User');

        $entity = $em->getRepository('AeagUserBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Impossible de retrouver l\' entity.' . $id);
        }

        $message = null;

        $role = 'ROLE_AEAG';

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

        if ($security->isGranted('ROLE_ADMINEDL')) {
            $role = 'ROLE_EDL';
            $utilisateur = $repoUtilisateur->getUtilisateurByExtid($entity->getId());
            $deptUtilisateurs = $repoDeptUtilisateur->getDepartementByUtilisateur($utilisateur);
        } else {
            $utilisateur = null;
            $deptUtilisateurs = null;
        }



        $maj = 'ko';
        $form = $this->createForm(new UsersUpdateType($entity), $entity);

        $form->handleRequest($request);

        //$message = null;

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
                        'utilisateur' => $utilisateur,
                        'depUtilisateurs' => $deptUtilisateurs,
                        'role' => $role,
                        'message' => $message,
                        'form' => $form->createView()
            ));
        }


        if ($form->isValid()) {
//            \Symfony\Component\VarDumper\VarDumper::dump($entity->getRoles());
//            \Symfony\Component\VarDumper\VarDumper::dump($entity->getDepts());
//             return new Response('username : ' . $entity->getUsername() );
            $encoder = $factory->getEncoder($entity);
            $entity->setSalt('');
            $password = $encoder->encodePassword($entity->getPassword(), $entity->getSalt());
            $entity->setpassword($password);
            $entity->setPlainPassword($entity->getPassword());

            if ($entity->getEnabled() == '0') {
                $entity->setEnabled(FALSE);
            } else {
                $entity->setEnabled(TRUE);
            }

            $entityHold = clone($entity);

            if ($entity->hasRole('ROLE_ADMINEDL')) {
                $entity->removeRole('ROLE_COMMENTATEUREDL');
                $entity->removeRole('ROLE_SUPERVISEUREDL');
            }
            if ($entity->hasRole('ROLE_SUPERVISEUREDL')) {
                $entity->removeRole('ROLE_COMMENTATEUREDL');
                $entity->removeRole('ROLE_ADMINEDL');
            }
            if ($entity->hasRole('ROLE_COMMENTATEUREDL')) {
                $entity->removeRole('ROLE_ADMINEDL');
                $entity->removeRole('ROLE_SUPERVISEUREDL');
            }
            if ($entity->hasRole('ROLE_COMMENTATEUREDL') or $entity->hasRole('ROLE_SUPERVISEUREDL') or $entity->hasRole('ROLE_ADMINEDL')) {
                $entity->addRole('ROLE_EDL');
                $entity->removeRole('ROLE_AEAG');
            } else {
                if (!$entity->hasRole('ROLE_AEAG')) {
                    $entity->addRole('ROLE_AEAG');
                }
            }
            $em->persist($entity);
            $em->flush();
            if ($entity->hasRole('ROLE_EDL')) {
                $utilisateur->setEmail($entity->getEmail());
                $utilisateur->setEmailCanonical($entity->getEmailCanonical());
                $utilisateur->removeRole('ROLE_ADMIN');
                $utilisateur->removeRole('ROLE_SUPERVISEUR');
                $utilisateur->removeRole('ROLE_COMMENTATEUR');
                if ($entity->hasRole('ROLE_COMMENTATEUREDL')) {
                    $utilisateur->addRole('ROLE_COMMENTATEUR');
                } elseif ($entity->hasRole('ROLE_SUPERVISEUREDL')) {
                    $utilisateur->addRole('ROLE_SUPERVISEUR');
                } elseif ($entity->hasRole('ROLE_ADMINEDL')) {
                    $utilisateur->addRole('ROLE_ADMIN');
                }

                $emEdl->persist($utilisateur);
                $depUtilisateurs = $repoDeptUtilisateur->getDepartementByUtilisateur($utilisateur);
                foreach ($depUtilisateurs as $depUtilisateur) {
                    $emEdl->remove($depUtilisateur);
                }
                $emEdl->flush();
                $depts = $entity->getDepts();
                foreach ($depts as $dept) {
                    $depUtilisateur = new DepUtilisateur();
                    $depUtilisateur->setInseeDepartement($dept->getInseeDepartement());
                    $depUtilisateur->setUtilisateur($utilisateur);
                    $emEdl->persist($depUtilisateur);
                }
                $emEdl->flush();
            }

            $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
            $notification = new Notification();
            $notification->setRecepteur($entity->getId());
            $notification->setEmetteur($user->getId());
            $notification->setNouveau(true);
            $notification->setIteration(2);
            $notification->setMessage('Votre compte a été mis à jour.');
            $em->persist($notification);
            $em->flush();


            if ($security->isGranted('ROLE_ADMIN')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_AEAG')));
            };
            if ($security->isGranted('ROLE_ADMINDEC')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_ODEC')));
            };
            if ($security->isGranted('ROLE_ADMINFRD')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_FRD')));
            };

            if ($security->isGranted('ROLE_ADMINSQE')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_SQE')));
            };
            if ($security->isGranted('ROLE_ADMINSTOCK')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_STOCK')));
            };
            if ($security->isGranted('ROLE_ADMINEDL')) {
                return $this->redirect($this->generateUrl('AeagUserBundle_User', array('role' => 'ROLE_EDL')));
            };
        }



        return $this->render('AeagUserBundle:User:edit.html.twig', array(
                    'entity' => $entity,
                    'utilisateur' => $utilisateur,
                    'depUtilisateurs' => $deptUtilisateurs,
                    'role' => $role,
                    'message' => $message,
                    'form' => $form->createView(),
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
