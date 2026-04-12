<?php

namespace hypeJunction\Geo;

use Elgg\DefaultPluginBootstrap;

/**
 * Bootstrap for hypeGeo plugin.
 *
 * Loads procedural function libraries at boot so that the handlers
 * referenced in elgg-plugin.php are available. Also ensures the
 * entity_geometry table exists on activation.
 */
class Bootstrap extends DefaultPluginBootstrap
{
	/**
	 * {@inheritdoc}
	 */
	public function load()
	{
		$root = $this->plugin->getPath();

		if (is_file($root . 'vendors/autoload.php')) {
			require_once $root . 'vendors/autoload.php';
		}

		if (!defined('HYPEGEO_METRIC_SYSTEM')) {
			define('HYPEGEO_METRIC_SYSTEM', 'SI');
		}

		require_once $root . 'lib/functions.php';
		require_once $root . 'lib/hooks.php';
	}

	/**
	 * {@inheritdoc}
	 */
	public function activate()
	{
		$upgrade = new Upgrades\CreateEntityGeometryTable();
		$upgrade->run(new \Elgg\Upgrade\Result(), 0);
	}
}
