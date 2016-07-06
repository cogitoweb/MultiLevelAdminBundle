<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cogitoweb\MultiLevelAdminBundle\Admin;

use Knp\Menu\ItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\BreadcrumbsBuilderInterface;

/**
 * Description of BreadcrumbsBuilder
 *
 * @author Daniele Artico <daniele.artico@cogitoweb.it>
 */
final class BreadcrumbsBuilder implements BreadcrumbsBuilderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getBreadcrumbs(AdminInterface $admin, $action)
	{
		$breadcrumbs = array();
		if ($admin->isChild()) {
			return $this->getBreadcrumbs($admin->getParent(), $action);
		}

		$menu = $this->buildBreadcrumbs($admin, $action);

		do {
			$breadcrumbs[] = $menu;
		} while ($menu = $menu->getParent());

		$breadcrumbs = array_reverse($breadcrumbs);
		array_shift($breadcrumbs);

		return $breadcrumbs;
	}

	/**
	 * {@inheritdoc}
	 * NEXT_MAJOR : make this method private.
	 */
	public function buildBreadcrumbs(AdminInterface $admin, $action, ItemInterface $menu = null)
	{
		if (!$menu) {
			$menu = $admin->getMenuFactory()->createItem('root');

			$menu = $this->createMenuItem(
				$admin,
				$menu,
				'dashboard',
				'SonataAdminBundle',
				array('uri' => $admin->getRouteGenerator()->generate(
					'sonata_admin_dashboard'
				))
			);
		}

		// Cogitoweb: first element is created in the next foreach loop
//		$menu = $this->createMenuItem(
//			$admin,
//			$menu,
//			sprintf('%s_list', $admin->getClassnameLabel()),
//			null,
//			array(
//				'uri' => $admin->hasRoute('list') && $admin->isGranted('LIST') ?
//				$admin->generateUrl('list') :
//				null,
//			)
//		);

		$childAdmin = $admin->getCurrentChildAdmin();

		if ($childAdmin) {
			// Cogitoweb: set request if it is unset
			if (!$admin->hasRequest()) {
				$admin->setRequest($childAdmin->getRequestFromParents());
			}

			$id = $admin->getRequest()->get($admin->getIdParameter());

			// Cogitoweb: first element is created in the next foreach loop
//			$menu = $menu->addChild(
//				$admin->toString($admin->getSubject()),
//				array(
//					'uri' => $admin->hasRoute('edit') && $admin->isGranted('EDIT') ?
//					$admin->generateUrl('edit', array('id' => $id)) :
//					null,
//				)
//			);

			// Cogitoweb: add child Admins
			foreach ($childAdmin->getHierachy() as $child) {
				$menu = $menu->addChild(
					$child->trans($child->getLabelTranslatorStrategy()->getLabel(sprintf('%s_list', $child->getClassnameLabel()), 'breadcrumb', 'link')),
					array(
						'uri' => $child->hasRoute('list') && $child->isGranted('LIST') ?
						$child->generateUrl('list') :
						null
					)
				);

				if ($child->hasChildren()) {
					$id = $admin->getRequest()->get($child->getIdParameter());
					$menu = $menu->addChild(
						$child->toString($child->getSubject()),
						array(
							'uri' => $child->hasRoute('edit') && $child->isGranted('VIEW') ?
							$child->generateUrl('edit', array($child->getIdParameter() => $id)) :
							null
						)
					);
				}
			}

			return $this->buildBreadcrumbs($childAdmin, $action, $menu);
		}

		if ('list' === $action && $admin->isChild()) {
			$menu->setUri(false);
		} elseif ('create' !== $action && $admin->hasSubject()) {
			$menu = $menu->addChild($admin->toString($admin->getSubject()));
		} else {
			$menu = $this->createMenuItem(
				$admin,
				$menu,
				sprintf('%s_%s', $admin->getClassnameLabel(), $action)
			);
		}

		return $menu;
	}

	/**
	 * Creates a new menu item from a simple name. The name is normalized and
	 * translated with the specified translation domain.
	 *
	 * @param AdminInterface $admin             used for translation
	 * @param ItemInterface  $menu              will be modified and returned
	 * @param string         $name              the source of the final label
	 * @param string         $translationDomain for label translation
	 * @param array          $options           menu item options
	 *
	 * @return ItemInterface
	 */
	private function createMenuItem(
		AdminInterface $admin,
		ItemInterface $menu,
		$name,
		$translationDomain = null,
		$options = array()
	) {
		return $menu->addChild(
			$admin->trans(
				$admin->getLabelTranslatorStrategy()->getLabel(
					$name,
					'breadcrumb',
					'link'
				),
				array(),
				$translationDomain
			),
			$options
		);
	}
}