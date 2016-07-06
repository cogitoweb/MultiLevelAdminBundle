<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cogitoweb\MultiLevelAdminBundle\Menu\Provider;

use Knp\Menu\FactoryInterface;
use Cogitoweb\MultiLevelAdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Menu\Provider\GroupMenuProvider as SonataGroupMenuProvider;

/**
 * Description of GroupMenuProvider
 *
 * @author Daniele Artico <daniele.artico@cogitoweb.it>
 */
class GroupMenuProvider extends SonataGroupMenuProvider
{
	/**
	 * {@inheritdoc}
	 */
	private $menuFactory;

	/**
	 * {@inheritdoc}
	 */
	private $pool;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(FactoryInterface $menuFactory, Pool $pool)
	{
		$this->menuFactory = $menuFactory;
		$this->pool        = $pool;
	}

	/**
	 * Retrieves the menu based on the group options.
	 *
	 * @param string $name
	 * @param array  $options
	 *
	 * @return \Knp\Menu\ItemInterface
	 *
	 * @throws \InvalidArgumentException if the menu does not exists
	 */
	public function get($name, array $options = array())
	{
		$group = $options['group'];

		$menuItem = $this->menuFactory->createItem(
			$options['name'],
			array(
				'label' => $group['label'],
			)
		);

		if (empty($group['on_top'])) {
			foreach ($group['items'] as $item) {
				if (isset($item['admin']) && !empty($item['admin'])) {
					$admin = $this->pool->getInstance($item['admin']);

					/**
					 * Cogitoweb: use showIn method.
					 * See {@see AbstractAdmin::showIn()} for further informations.
					 */
					if (!$admin->showIn(AbstractAdmin::CONTEXT_MENU)) {
						continue;
					}

					// skip menu item if no `list` url is available or user doesn't have the LIST access rights
					if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
						continue;
					}

					$label = $admin->getLabel();
					$options = $admin->generateMenuUrl('list', array(), $item['route_absolute']);
					$options['extras'] = array(
						'translation_domain' => $admin->getTranslationDomain(),
						'admin' => $admin,
					);
				} else {
					$label = $item['label'];
					$options = array(
						'route' => $item['route'],
						'routeParameters' => $item['route_params'],
						'routeAbsolute' => $item['route_absolute'],
						'extras' => array(
							'translation_domain' => $group['label_catalogue'],
						),
					);
				}

				$menuItem->addChild($label, $options);
			}

			if (false === $menuItem->hasChildren()) {
				$menuItem->setDisplay(false);
			}
		} else {
			foreach ($group['items'] as $item) {
				if (isset($item['admin']) && !empty($item['admin'])) {
					$admin = $this->pool->getInstance($item['admin']);

					/**
					 * Cogitoweb: use showIn method.
					 * See {@see AbstractAdmin::showIn()} for further informations.
					 */
					if (!$admin->showIn(AbstractAdmin::CONTEXT_MENU)) {
						continue;
					}

					// skip menu item if no `list` url is available or user doesn't have the LIST access rights
					if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
						continue;
					}

					$options = $admin->generateUrl('list');
					$menuItem->setExtra('route', $admin->getBaseRouteName().'_list');
					$menuItem->setExtra('on_top', $group['on_top']);
					$menuItem->setUri($options);
				} else {
					$menuItem->setUri($item['route']);
				}
			}
		}

		return $menuItem;
	}
}
