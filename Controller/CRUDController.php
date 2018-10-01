<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cogitoweb\MultiLevelAdminBundle\Controller;

use FOS\RestBundle\Controller\Annotations        as FOSRest;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description of CRUDController
 *
 * @author Daniele Artico <daniele.artico@cogitoweb.it>
 */
class CRUDController extends SonataCRUDController
{
	/**
	 * 
	 * @const
	 */
	const RESTFUL_ADMIN_API_PREFIX = '/api/admin';

	/**
	 * {@inheritdoc}
	 *
	 * @FOSRest\Get()
	 * @FOSRest\View(serializerGroups={"Default", "AdminList"})
	 */
	public function listAction()
	{
		if ($this->isRequestApiCall()) {
			$this->admin->checkAccess('list');

			// Cogitoweb: RESTful API calls do not have persistent filters
			$this->admin->setPersistFilters(false);

			return $this->admin->getDatagrid()->getResults();
		}

		return parent::listAction();
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Cogitoweb: disable batch delete action in RESTful Admin API
	 * 
	 * @FOSRest\NoRoute()
	 */
	public function batchActionDelete(ProxyQueryInterface $query)
	{
		return parent::batchActionDelete($query);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Delete()
	 * @FOSRest\View(serializerGroups={"Default", "AdminDelete"})
	 */
	public function deleteAction($id)
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();
			$object  = $this->admin->getObject($id);

			if (!$object) {
				throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
			}

			$this->admin->checkAccess('delete', $object);

			try {
				$this->admin->delete($object);

				return ['result' => 'ok'];
			} catch (ModelManagerException $e) {
				return ['result' => 'error'];
			}
		}

		return parent::deleteAction($id);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Patch()
	 * @FOSRest\View(serializerGroups={"Default", "AdminEdit"})
	 */
	public function editAction($id = null)
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();
			$object  = $this->admin->getObject($id);

			if (!$object) {
				throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
			}

			$this->admin->checkAccess('edit', $object);

			$this->admin->setSubject($object);

			/** @var $form Form */
			$form = $this->admin->getForm();
			$form->setData($object);

			// Cogitoweb: normalize API call data before handling
			$data = $this->normalizeApiCallData($request);
			$form->handleRequest($data);

			$isFormValid = $form->isValid();

			if ($isFormValid) {
				$this->admin->update($object);

				if ($request->query->get('reload')) {
					$this->getDoctrine()->getManager()->refresh($object);
				}

				return $object;
			} else {
				return array(
					'result'  => 'error',
					'message' => 'validation error',
					'details' => $this->getFormErrors($form)
				);
			}
		}

		return parent::editAction($id);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Cogitoweb: disable batch action in RESTful Admin API
	 * 
	 * @FOSRest\NoRoute()
	 */
	public function batchAction()
	{
		return parent::batchAction();
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Post()
	 * @FOSRest\View(serializerGroups={"Default", "AdminCreate"})
	 */
	public function createAction()
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();

			$this->admin->checkAccess('create');

			$object = $this->admin->getNewInstance();

			$this->admin->setSubject($object);

			/** @var $form Form */
			$form = $this->admin->getForm();
			$form->setData($object);

			// Cogitoweb: normalize API call data before handling
//			$data = $this->normalizeApiCallData($request);
//			$form->handleRequest($data);
			$form->handleRequest($request);

			$isFormValid = $form->isValid();

			if ($isFormValid) {
				$this->admin->create($object);

				if ($request->query->get('reload')) {
					$this->getDoctrine()->getManager()->refresh($object);
				}

				return $object;
			} else {
				return array(
					'result'  => 'error',
					'message' => 'validation error',
					'details' => $this->getFormErrors($form)
				);
			}
		}

		return parent::createAction();
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Get()
	 * @FOSRest\View(serializerGroups={"Default", "AdminShow"})
	 */
	public function showAction($id = null)
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();
			$object  = $this->admin->getObject($id);

			if (!$object) {
				throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
			}

			$this->admin->checkAccess('show', $object);

			return $object;
		}

		return parent::showAction($id);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Get()
	 * @FOSRest\View(serializerGroups={"Default", "AdminHistory"})
	 */
	public function historyAction($id = null)
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();
			$object  = $this->admin->getObject($id);

			if (!$object) {
				throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
			}

			$this->admin->checkAccess('history', $object);

			$manager = $this->get('sonata.admin.audit.manager');

			if (!$manager->hasReader($this->admin->getClass())) {
				throw $this->createNotFoundException(
					sprintf(
						'unable to find the audit reader for class : %s',
						$this->admin->getClass()
					)
				);
			}

			$reader = $manager->getReader($this->admin->getClass());

			$revisions = $reader->findRevisions($this->admin->getClass(), $id);

			return $revisions;
		}

		return parent::historyAction($id);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @FOSRest\Get()
	 * @FOSRest\View(serializerGroups={"Default", "AdminHistoryViewRevision"})
	 */
	public function historyViewRevisionAction($id = null, $revision = null)
	{
		if ($this->isRequestApiCall()) {
			$request = $this->getRequest();
			$object  = $this->admin->getObject($id);

			if (!$object) {
				throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
			}

			$this->admin->checkAccess('historyViewRevision', $object);

			$manager = $this->get('sonata.admin.audit.manager');

			if (!$manager->hasReader($this->admin->getClass())) {
				throw $this->createNotFoundException(
					sprintf(
						'unable to find the audit reader for class : %s',
						$this->admin->getClass()
					)
				);
			}

			$reader = $manager->getReader($this->admin->getClass());

			// retrieve the revisioned object
			$object = $reader->find($this->admin->getClass(), $id, $revision);

			if (!$object) {
				throw $this->createNotFoundException(
					sprintf(
						'unable to find the targeted object `%s` from the revision `%s` with classname : `%s`',
						$id,
						$revision,
						$this->admin->getClass()
					)
				);
			}

			return $object;
		}

		return parent::historyViewRevisionAction($id, $revision);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Cogitoweb: disable history comparison action in RESTful Admin API
	 * 
	 * @FOSRest\NoRoute()
	 */
	public function historyCompareRevisionsAction($id = null, $base_revision = null, $compare_revision = null)
	{
		return parent::historyCompareRevisionsAction($id, $base_revision, $compare_revision);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Cogitoweb: disable export action in RESTful Admin API
	 * 
	 * @FOSRest\NoRoute()
	 */
	public function exportAction(Request $request)
	{
		return parent::exportAction($request);
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Cogitoweb: disable ACL action in RESTful Admin API
	 * 
	 * @FOSRest\NoRoute()
	 */
	public function aclAction($id = null)
	{
		return parent::aclAction($id);
	}

	/**
	 * Is request an API call?
	 * 
	 * @return boolean
	 */
	protected function isRequestApiCall()
	{
		$request = $this->getRequest();
		$url     = $request->getRequestUri();

		return (boolean) strpos($url, self::RESTFUL_ADMIN_API_PREFIX);
	}

	/**
	 * Normalize API call data
	 * 
	 * @param  Request $request
	 * 
	 * @return array
	 */
	protected function normalizeApiCallData(Request $request)
	{
		// Cogitoweb: append unique ID to request, if it does not have any
		if (!$request->query->has('uniqid')) {
			$request->query->set('uniqid', uniqid());
		}

		return $request->request->all();
	}

	/**
	 * Get form errors
	 * 
	 * @param  Form $form
	 * 
	 * @return array
	 */
	protected function getFormErrors(Form $form)
	{
		$errors = array();

		// Cogitoweb: parse main form errors
		foreach ($form->getErrors() as $error) {
			$errors[] = $error->getMessage();
		}

		// Cogitoweb: parse child forms errors
		foreach ($form->all() as $child) {
			if (!$child->isValid()) {
				$childName   = $child->getName();
				$childErrors = $this->getFormErrors($child);

				$errors[$childName] = $childErrors;
			}
		}

		return $errors;
	}
}
