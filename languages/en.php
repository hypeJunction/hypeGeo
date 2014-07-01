<?php

namespace hypeJunction\Geo;

$en = array(
	/**
	 * PLUGIN SETTINGS
	 */
	'geo:settings:proximity_search' => 'Enable proximity search in Elgg search results',
	'geo:providers:none' => 'Please set up geocoding providers in hypeGeo plugin settings',
	'geo:settings:enable' => 'Enable',
	'geo:settings:api_key' => 'API Key',
	'geo:settings:locale' => 'Locale (optional)',
	'geo:settings:region' => 'Region (optional)',
	'geo:settings:ssl' => 'Use SSL',
	'geo:settings:url' => 'URL',
	'geo:settings:toponym' => 'Toponym',
	'geo:settings:gmb:client_id' => 'Client ID',
	'geo:settings:gmb:private_key' => 'Private Key',

	/**
	 * SEARCH
	 */
	'geo:search:proximity' => '%2$s km',
	'search_types:proximity' => 'Location',
	
	/**
	 * POSTAL ADDRESS FORM
	 */
	'geo:postal_address:street_address' => 'Street address',
	'geo:postal_address:extended_address' => 'Street address 2',
	'geo:postal_address:locality' => 'City',
	'geo:postal_address:postal_code' => 'Postal code',
	'geo:postal_address:region' => 'State',
	'geo:postal_address:country' => 'Country',
	

);

add_translation('en', $en);
