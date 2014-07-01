<?php

namespace hypeJunction\Geo;

/**
 * Check if the location has already been geocoded
 *
 * @param str $hook 'geocode'
 * @param str $type 'location'
 * @param false|array $return false or array($lat,$long)
 * @param mixed $params
 *
 * @return array|false Array of lat,long or false if geocoding fails
 */
function geocode_location($hook, $type, $return, $params) {

	$location = elgg_extract('location', $params, false);
	return ElggGeocoder::geocodeAddress($location);
}

/**
 * Geocode entity location and set latitude and longitude when 'location' metadata is created/updated
 *
 * @param type $event
 * @param type $type
 * @param type $metadata
 */
function geocode_location_metadata($event, $type, $metadata) {

	if ($metadata->name != 'location') {
		return true;
	}

	switch ($event) {

		case 'create' :
		case 'update' :
			$coordinates = elgg_geocode_location($metadata->value);
			if ($coordinates) {
				set_entity_coordinates($metadata->entity_guid, $coordinates['lat'], $coordinates['long']);
			} else {
				unset_entity_coordinates($metadata->entity_guid);
			}
			break;

		default :
			unset_entity_coordinates($metadata->entity_guid);
			break;
	}

	return true;
}

/**
 * Add search by proximity section
 * 
 * @param string $hook		Equals 'search_types'
 * @param string $type		Equals 'get_types'
 * @param array $return		A list of search types
 * @param array $params		Additional params
 * @return array
 */
function search_custom_types($hook, $type, $return, $params) {

	if (elgg_get_plugin_setting('proximity_search', PLUGIN_ID)) {
		$return[] = 'proximity';
	}
	return $return;
}

function search_by_proximity_hook($hook, $type, $return, $params) {

	$query = $params['query'];
	$coords = elgg_geocode_location($query);
	if (!$coords) {
		return $return;
	}

	$registered_entities = elgg_get_config('registered_entities');
	$options = array(
		'types' => array('object', 'user', 'group'),
		'subtypes' => array_merge($registered_entities['object'], $registered_entities['user'], $registered_entities['group']),
		'limit' => get_input('limit', 20),
		'offset' => get_input('proximity_offset', 0),
		'offset_key' => 'proximity_offset',
		'count' => true
	);

	$options = add_order_by_proximity_clauses($options, $coords['lat'], $coords['long']);
	$options = add_distance_constraint_clauses($options, $coords['lat'], $coords['long'], SEARCH_RADIUS);

	$count = elgg_get_entities($options);
	if ($count) {
		$options['count'] = false;
		$entities = elgg_get_entities($options);
	}

	if ($entities) {
		foreach ($entities as $entity) {

			$name = search_get_highlighted_relevant_substrings(isset($entity->name) ? $entity->name : $entity->title, $query);
			$entity->setVolatileData('search_matched_title', $name);

			$location = search_get_highlighted_relevant_substrings($entity->getLocation(), $query);
			$entity->setVolatileData('search_matched_location', $location);

			$distance = get_distance($entity->getLatitude(), $entity->getLongitude(), $coords['lat'], $coords['long']); // distance in metres
			$distance = round($distance / 1000, 2); // distance in km
			$distance_str = elgg_echo('geo:search:proximity', array($query, $distance));

			$entity->setVolatileData('search_proximity', $distance_str);
		}
	}

	return array(
		'entities' => $entities,
		'count' => $count,
	);
}
