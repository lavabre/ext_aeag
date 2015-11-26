<?php

namespace Aeag\DieBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Aeag\DieBundle\Entity\Theme;
use Aeag\DieBundle\Form\ThemeType;

/**
 * Theme controller.
 *
 */
class ThemeController extends Controller
{
	/**
	 * Lists all Theme entities.
	 *
	 */
	public function indexAction()
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entities = $em->getRepository('AeagDieBundle:Theme')->findAll();

		return $this->render('AeagDieBundle:Theme:index.html.twig', array(
            'entities' => $entities
            ));
	}

	/**
	 * Finds and displays a Theme entity.
	 *
	 */
	public function showAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Theme entity.');
		}

		$deleteForm = $this->createDeleteForm($id);

		return $this->render('AeagDieBundle:Theme:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

            ));
	}

	/**
	 * Displays a form to create a new Theme entity.
	 *
	 */
	public function newAction()
	{
		$entity = new Theme();
		$form   = $this->createForm(new ThemeType(), $entity);

		return $this->render('AeagDieBundle:Theme:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            ));
	}

	/**
	 * Creates a new Theme entity.
	 *
	 */
	public function createAction()
	{
		$entity  = new Theme();
		$request = $this->getRequest();
		$form    = $this->createForm(new ThemeType(), $entity);
		$form->bindRequest($request);

		if ($form->isValid()) {
			$em = $this->getDoctrine()->getEntityManager('die');
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('theme'));

		}

		return $this->render('AeagDieBundle:Theme:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
            ));
	}

	/**
	 * Displays a form to edit an existing Theme entity.
	 *
	 */
	public function editAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Theme entity.');
		}

		$editForm = $this->createForm(new ThemeType(), $entity);
		$deleteForm = $this->createDeleteForm($id);

		return $this->render('AeagDieBundle:Theme:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
	}

	/**
	 * Edits an existing Theme entity.
	 *
	 */
	public function updateAction($id)
	{
		$em = $this->getDoctrine()->getEntityManager('die');

		$entity = $em->getRepository('AeagDieBundle:Theme')->find($id);

		if (!$entity) {
			throw $this->createNotFoundException('Unable to find Theme entity.');
		}

		$editForm   = $this->createForm(new ThemeType(), $entity);
		$deleteForm = $this->createDeleteForm($id);

		$request = $this->getRequest();

		$editForm->bindRequest($request);

		if ($editForm->isValid()) {
			$em->persist($entity);
			$em->flush();

			return $this->redirect($this->generateUrl('theme'));
		}

		return $this->render('AeagDieBundle:Theme:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            ));
	}

	/**
	 * Deletes a Theme entity.
	 *
	 */
	public function deleteAction($id)
	{
			
		$em = $this->getDoctrine()->getEntityManager('die');

		$qb = $em->createQueryBuilder();
		$qb->select('count(a)')
		->from('AeagDieBundle:SousTheme', 'a')
		->where("a.theme = :id_theme")
		->setParameter('id_theme',  $id );
		$nb_sousthemes = $qb->getQuery()->getSingleScalarResult();
		
		if ($nb_sousthemes == 0)
		{
			$entity = $em->getRepository('AeagDieBundle:Theme')->find($id);
	
			if (!$entity) {
				throw $this->createNotFoundException('Unable to find Theme entity.');
			}
	
			$em->remove($entity);
			$em->flush();
		
		}
		 
		return $this->redirect($this->generateUrl('theme'));
	}

	private function createDeleteForm($id)
	{
		return $this->createFormBuilder(array('id' => $id))
		->add('id', 'hidden')
		->getForm()
		;
	}
}
