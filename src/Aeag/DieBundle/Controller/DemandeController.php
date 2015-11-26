<?php

namespace Aeag\DieBundle\Controller;

use Aeag\DieBundle\Entity\SousTheme;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Aeag\DieBundle\Entity\Demande;
use Aeag\DieBundle\Form\DemandeType;
use Aeag\DieBundle\Form\DemandeEditType;
use Aeag\PagerBundle\Pager;
use Aeag\PagerBundle\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Demande controller.
 *
 */
class DemandeController extends Controller
{
	/**
	 * Lists all Demande entities.
	 *
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entities = $em->getRepository('AeagDieBundle:Demande')->findAll();
		
		$adapter = new ArrayAdapter($entities);
		
		$page = 1;
		
		$pager = new Pager($adapter, array('page' => $page, 'limit' => 10));
		
		$variables['pager'] = $pager;
		
		return $this->render('AeagDieBundle:Demande:listeDemandes.html.twig', $variables);

		/*return $this->render('AeagDieBundle:Demande:index.html.twig', array(
            'entities' => $entities
            ));
        */
	}
	
	
	/**
	 *  Liste des demandes (suite)
	 *
	 * @ Method("POST")
	 * @Route("/demande/{page}", defaults={"page"=1}, name="demande_listeDemandes")
	 *
	 * @Template()
	 *
	 */
	public function listeDemandesAction($page) {
	
		$request = $this->getRequest();
	
		$em = $this->getDoctrine()->getEntityManager('die');

		$entities = $em->getRepository('AeagDieBundle:Demande')->findAll();
	
		$adapter = new ArrayAdapter($entities);
	
		$pager = new Pager($adapter, array('page' => $page, 'limit' => 10));
	
		$variables['pager'] = $pager;
	
	
		return $this->render('AeagDieBundle:Demande:listeDemandes.html.twig', $variables);
	}

	/**
	 * Finds and displays a Demande entity.
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Demande entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->render('AeagDieBundle:Demande:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

            ));
	}

	/**
	 * Displays a form to create a new Demande entity.
	 *
	 */
	public function newAction()
	{
		$entity = new Demande();
		$form   = $this->createForm(new DemandeType(), $entity);

		return $this->render('AeagDieBundle:Demande:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            ));
	}

	/**
	 * Creates a new Demande entity.
	 *
	 */
	public function createAction()
	{
		$entity  = new Demande();
		$request = $this->getRequest();
		$form    = $this->createForm(new DemandeType(), $entity);
		$form->bindRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getEntityManager('die');
			
			$Organisme = $em->getRepository('AeagDieBundle:Organisme')->find($entity->getOrganisme()->getId());
			
			$Theme = $em->getRepository('AeagDieBundle:Theme')->find($entity->getTheme()->getId());
			
			$SousTheme = $em->getRepository('AeagDieBundle:SousTheme')->findOneBy(array('theme' => $Theme->getId()));

			$entity->setOrganisme($Organisme->getOrganisme());
			$entity->setTheme($Theme->getTheme());
			$entity->setSousTheme($SousTheme->getSousTheme());
			$entity->setDateCreation(new \Datetime());
			$date = new \Datetime();
			$date->add(new \DateInterval('P'.$SousTheme->getEcheance().'D'));
			$entity->setDateEcheance($date);
			
			
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('demande'));

		}

		return $this->render('AeagDieBundle:Demande:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            ));
	}

	/**
	 * Displays a form to edit an existing Demande entity.
	 *
	 */
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Demande entity.');
		}

		$editForm = $this->createForm(new DemandeEditType(), $entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
	}

	/**
	 * Edits an existing Demande entity.
	 *
	 */
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Demande entity.');
		}

		$editForm   = $this->createForm(new DemandeEditType(), $entity);
		$deleteForm = $this->createDeleteForm($id);

		$request = $this->getRequest();

		$editForm->bindRequest($request);

		if ($editForm->isValid()) {
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('demande'));
		}

		return $this->render('AeagDieBundle:Demande:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
	}

	/**
	 * Deletes a Demande entity.
	 *
	 */
	public function deleteAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');
		$entity = $em->getRepository('AeagDieBundle:Demande')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Demande entity.');
		}

		$em->remove($entity);
		$em->flush();


		return $this->redirect($this->generateUrl('demande'));
	}

	private function createDeleteForm($id)
	{
		return $this->createFormBuilder(array('id' => $id))
		->add('id', 'hidden')
		->getForm()
		;
	}


	/*
	 *  liste des sous-thèmes associés à un thème
	*/

	public function listesousthemeAction()
	{

		$list_sous_theme = array();
		$request = $this->container->get('request');

		if ($request->isXmlHttpRequest())
		{

			$id_theme = '';
			$id_theme = $request->get('id_theme');

			$em = $this->container->get('doctrine')->getEntityManager('die');


			if ($id_theme != '') {
				$qb = $em->createQueryBuilder();

				$qb->select('a')
				->from('AeagDieBundle:SousTheme', 'a')
				->where("a.theme = :id_theme")
				->orderBy('a.sousTheme', 'DESC')
				->setParameter('id_theme',  $id_theme );

				$query = $qb->getQuery();
				$sousthemes = $query->getResult();
			} else {
				$sousthemes = $em->getRepository('AeagDieBundle:SousTheme')->findAll();
			}

			return $this->container->get('templating')->renderResponse('AeagDieBundle:SousTheme:liste.html.twig', array(
			'sousthemes' => $sousthemes
			));

		}

	}

}
