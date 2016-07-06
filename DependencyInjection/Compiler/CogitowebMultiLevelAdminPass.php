<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cogitoweb\MultiLevelAdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of OverrideServiceCompilerPass
 *
 * @author Daniele Artico <daniele.artico@cogitoweb.it>
 */
class CogitowebMultiLevelAdminPass implements CompilerPassInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function process(ContainerBuilder $container)
	{
		/**
		 * Override sonata.admin.breadcrumbs_builder.
		 * 
		 * See {@see \Cogitoweb\MultiLevelAdminBundle\Admin\BreadcrumbsBuilder::buildBreadcrumbs()} for further informations.
		 */
		$sonataBreadcrumbsBuilderDefinition = $container->getDefinition('sonata.admin.breadcrumbs_builder');
		$sonataBreadcrumbsBuilderDefinition->setClass('Cogitoweb\MultiLevelAdminBundle\Admin\BreadcrumbsBuilder');

		/**
		 * Override sonata.admin.menu.group_provider.
		 * 
		 * See {@see \Cogitoweb\MultiLevelAdminBundle\Admin\AbstractAdmin::showIn()} for further informations.
		 */
		$sonataGroupMenuProviderDefinition = $container->getDefinition('sonata.admin.menu.group_provider');
		$sonataGroupMenuProviderDefinition->setClass('Cogitoweb\MultiLevelAdminBundle\Menu\Provider\GroupMenuProvider');

		/**
		 * Override sonata.admin.pool.
		 * 
		 * See {@see \Cogitoweb\MultiLevelAdminBundle\Admin\Pool::getAdminByClass()} for further informations.
		 */
		$sonataAdminPoolDefinition = $container->getDefinition('sonata.admin.pool');
		$sonataAdminPoolDefinition->setClass('Cogitoweb\MultiLevelAdminBundle\Admin\Pool');

		/**
		 * Override sonata.admin.route.default_generator.
		 * 
		 * See {@see \Cogitoweb\MultiLevelAdminBundle\Route\DefaultRouteGenerator::loadCache()} for further informations.
		 */
		$sonataDefaultRouteGeneratorDefinition = $container->getDefinition('sonata.admin.route.default_generator');
		$sonataDefaultRouteGeneratorDefinition->setClass('Cogitoweb\MultiLevelAdminBundle\Route\DefaultRouteGenerator');
	}
}
