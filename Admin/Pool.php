<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Cogitoweb\MultiLevelAdminBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\Pool as SonataPool;

/**
 * Description of Pool
 *
 * @author Daniele Artico <daniele.artico@cogitoweb.it>
 */
class Pool extends SonataPool
{
	/**
	 * {@inheritdoc}
	 */
	public function getAdminByClass($class)
	{
		if (!$this->hasAdminByClass($class)) {
			return;
		}

		if (!is_array($this->adminClasses[$class])) {
			throw new \RuntimeException('Invalid format for the Pool::adminClass property');
		}

		// Cogitoweb: allow a class to be associated to several Admins
//		if (count($this->adminClasses[$class]) > 1) {
//			throw new \RuntimeException(sprintf('Unable to found a valid admin for the class: %s, get too many admin registered: %s', $class, implode(',', $this->adminClasses[$class])));
//		}

		return $this->getInstance($this->adminClasses[$class][0]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAdminByAdminCode($adminCode)
	{
		$codes = explode('|', $adminCode);
		/*
		 * Cogitoweb: at this point of execution,
		 * Admins are not aware of their children yet.
		 */
//		$admin = false;
//		foreach ($codes as $code) {
//			if ($admin == false) {
//				$admin = $this->getInstance($code);
//			} elseif ($admin->hasChild($code)) {
//				$admin = $admin->getChild($code);
//			}
//		}
		$code  = end($codes);
		$admin = $this->getInstance($code);

		return $admin;
	}
}
