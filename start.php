<?php

/**
 * hypeGeo
 *
 * @package hypeJunction
 * @subpackage Geo
 *
 * @author Ismayil Khayredinov <ismayil.khayredinov@gmail.com>
 */

namespace hypeJunction\Geo;

const PLUGIN_ID = 'hypeGeo';
const SEARCH_RADIUS = 1000000;

// Autoload dependencies
require __DIR__ . '/vendors/autoload.php';

define('HYPEGEO_METRIC_SYSTEM', 'SI');

elgg_register_class('hypeJunction\\Geo\\ElggGeocoder', __DIR__ . '/classes/hypeJunction/Geo/ElggGeocoder.php');
elgg_register_class('hypeJunction\\Geo\\ElggIPResolver', __DIR__ . '/classes/hypeJunction/Geo/ElggIPResolver.php');
elgg_register_class('hypeJunction\\Geo\\Countries', __DIR__ . '/classes/hypeJunction/Geo/Countries.php');

require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';

elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\\init');

function init() {

	elgg_extend_view('css/elgg', 'css/framework/geo/css');

	/**
	 * Geocoding
	 */
	elgg_register_plugin_hook_handler('geocode', 'location', __NAMESPACE__ . '\\geocode_location');
	elgg_register_event_handler('all', 'metadata', __NAMESPACE__ . '\\geocode_location_metadata');

	/**
	 * Add location based search
	 */
	elgg_register_plugin_hook_handler('search_types', 'get_types', __NAMESPACE__ . '\\search_custom_types');
	elgg_register_plugin_hook_handler('search', 'proximity', __NAMESPACE__ . '\\search_by_proximity_hook');

}
