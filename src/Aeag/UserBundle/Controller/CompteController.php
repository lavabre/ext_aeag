<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aeag\UserBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Aeag\AeagBundle\Entity\Notification;
use Aeag\AeagBundle\Controller\AeagController;
use Aeag\UserBundle\Form\UserUpdateType;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller managing the user profile
 *
 * @author Christophe Coevoet <stof@notk.org>
 */
class CompteController extends Controller {

    /**
     * Show the user
     */
    public function showAction() {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('AeagUserBundle:Profile:show.html.twig', array(
                    'user' => $user
        ));
    }

    /**
     * Edit the user
     */
    public function edit1Action(Request $request) {
        $em = $this->container->get('doctrine')->getManager();
        $session = $this->container->get('session');
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
                $userManager = $this->container->get('fos_user.user_manager');

                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

                $userManager->updateUser($user);

                $repoNotifications = $em->getRepository('AeagAeagBundle:Notification');
                $notification = new Notification();
                $notification->setRecepteur($user->getId());
                $notification->setEmetteur($user->getId());
                $notification->setNouveau(true);
                $notification->setIteration(2);
                $notification->setMessage('Votre compte a été mis à jour.');
                $em->persist($notification);
                $em->flush();
                $notifications = $repoNotifications->getNotificationByRecepteur($user);
                $session->set('Notifications', $notifications);

                // message aux administrateurs du site
                $repoUsers = $em->getRepository('AeagUserBundle:User');
                $admins = $repoUsers->getUsersByRole('ROLE_ADMINDEC');
                foreach ($admins as $admin) {
                    $notification = new Notification();
                    $notification->setRecepteur($admin->getId());
                    $notification->setEmetteur($user->getId());
                    $notification->setNouveau(true);
                    $notification->setIteration(2);
                    $notification->setMessage('Mon compte a été modifié.');
                    $em->persist($notification);
                    $em->flush();
                }

                if (is_object($user)) {
                    $mes = AeagController::notificationAction($user, $em, $session);
                    $mes1 = AeagController::messageAction($user, $em, $session);
                } else {
                    return $this->redirect($this->generateUrl('fos_user_security_login'));
                }


                if (null === $response = $event->getResponse()) {
                    $url = $this->container->get('router')->generate('fos_user_profile_show');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }
        }

        return $this->container->get('templating')->renderResponse(
                        'FOSUserBundle:Profile:edit.html.twig', array('form' => $form->createView())
        );
    }

    public function editAction(Request $request) {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $emSqe = $this->get('doctrine')->getManager('sqe');
        $repoPgProgWebusers = $emSqe->getRepository('AeagSqeBundle:PgProgWebusers');


        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
//        $formFactory = $this->get('fos_user.profile.form.factory');
//
//        $form = $formFactory->createForm();
//        $form->setData($user);

        $form = $this->createForm(new UserUpdateType(), $user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');

            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            
            $pgProgWebuser = $repoPgProgWebusers->getPgProgWebusersByExtid($user->getId());
            if ($pgProgWebuser) {
                $pgProgWebuser->setMail($user->getEmail());
                $pgProgWebuser->setPwd($user->getPassword());
                $emSqe->persist($pgProgWebuser);
                $emSqe->flush();
            }

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::PROFILE_EDIT_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        //return new Response ('ici');

        return $this->render('AeagUserBundle:Profile:edit.html.twig', array(
                    'form' => $form->createView()
        ));
    }

}
