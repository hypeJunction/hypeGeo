<?php

namespace hypeJunction\Geo;

const PLUGIN_ID = 'hypeGeo';
const SEARCH_RADIUS = 1000000;
if (file_exists(__DIR__ . '/vendors/autoload.php')) {
    require __DIR__ . '/vendors/autoload.php';
}
define('HYPEGEO_METRIC_SYSTEM', 'SI');
require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';
elgg_register_event_handler('init', 'system', __NAMESPACE__ . '\init');
function init()
{
    elgg_extend_view('css/elgg', 'css/framework/geo/css');
    elgg_register_plugin_hook_handler('geocode', 'location', __NAMESPACE__ . '\geocode_location');
    elgg_register_event_handler('all', 'metadata', __NAMESPACE__ . '\geocode_location_metadata');
    elgg_register_plugin_hook_handler('search_types', 'get_types', __NAMESPACE__ . '\search_custom_types');
    elgg_register_plugin_hook_handler('search', 'proximity', __NAMESPACE__ . '\search_by_proximity_hook');
}
