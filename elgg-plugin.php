<?php

// Git-tracked procedural libraries must be loaded before the handlers
// referenced below are resolved. composer autoload.files is insufficient
// for git-tracked customs, so require them here at the top of elgg-plugin.php.
require_once __DIR__ . '/lib/functions.php';
require_once __DIR__ . '/lib/hooks.php';

return [
	'plugin' => [
		'name' => 'hypeGeo',
		'version' => '6.0.0',
	],

	'bootstrap' => \hypeJunction\Geo\Bootstrap::class,

	'events' => [
		'geocode' => [
			'location' => [
				'hypeJunction\Geo\geocode_location' => [],
			],
		],
		'search_types' => [
			'get_types' => [
				'hypeJunction\Geo\search_custom_types' => [],
			],
		],
		'search' => [
			'proximity' => [
				'hypeJunction\Geo\search_by_proximity_hook' => [],
			],
		],
		'all' => [
			'metadata' => [
				'hypeJunction\Geo\geocode_location_metadata' => [],
			],
		],
	],

	'view_extensions' => [
		'elgg.css' => [
			'css/framework/geo/css' => [],
		],
	],

	'upgrades' => [
		\hypeJunction\Geo\Upgrades\CreateEntityGeometryTable::class,
	],
];
