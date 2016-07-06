<?php

namespace Cogitoweb\MultiLevelAdminBundle;

use Cogitoweb\MultiLevelAdminBundle\DependencyInjection\Compiler\CogitowebMultiLevelAdminPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CogitowebMultiLevelAdminBundle extends Bundle
{
	/**
	 * {@inheritdoc}
	 */
	public function build(ContainerBuilder $container)
	{
		parent::build($container);

		// Register custom Pass to override some SonataAdmin settings
		$cogitowebMultiLevelAdminPass = new CogitowebMultiLevelAdminPass();
		$container->addCompilerPass($cogitowebMultiLevelAdminPass);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParent()
	{
		return 'SonataAdminBundle';
	}
}
