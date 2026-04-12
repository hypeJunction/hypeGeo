<?php

return [
	'plugin' => [
		'name' => 'hypeGeo',
		'version' => '4.0.0',
	],

	'bootstrap' => \hypeJunction\Geo\Bootstrap::class,

	'hooks' => [
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
	],

	'events' => [
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
