<?php

namespace hypeJunction\Geo;

use Geocoder\Formatter\Formatter;
use Treffynnon\Navigator\Coordinate;
use Treffynnon\Navigator\Distance;
use Treffynnon\Navigator\Distance\Calculator\GreatCircle;
use Treffynnon\Navigator\LatLong;

/**
 * Get a list of entities using $getter function ordered by proximity to $lat $long
 *
 * @param array $options	An array of constraints for the $getter
 * @param type $lat			Latitude
 * @param type $long		Longitude
 * @param type $getter		Getter function to use
 * @return array|boolean
 */
function get_entities_by_proximity($options = array(), $lat = null, $long = null, $getter = 'elgg_get_entities') {

	if (is_null($lat) || is_null($long)) {
		$geopositioning = get_geopositioning();
		$lat = $geopositioning['lat'];
		$long = $geopositioning['long'];
	}

	$lat = (float) $lat;
	$long = (float) $long;

	$options = add_order_by_proximity_clauses($options, $lat, $long);

	if (!is_callable($getter)) {
		return false;
	}

	return $getter($options);
}

/**
 * Add parameters to the getter options array to order entities by distance to given coordinates
 *
 * @param array $options Current getter options
 * @param float $lat
 * @param float $long
 */
function add_order_by_proximity_clauses($options = array(), $lat = 0, $long = 0) {

	if (!is_array($options)) {
		$options = array();
	}

	$lat = sanitize_string((float) $lat);
	$long = sanitize_string((float) $long);

	$dbprefix = elgg_get_config('dbprefix');

	$options['selects'][] = "(GLength(LineStringFromWKB(LineString(eg.geometry,GeomFromText('POINT({$lat} {$long})'))))) as proximity";
	$options['joins'][] = "JOIN {$dbprefix}entity_geometry eg ON e.guid = eg.entity_guid";
	$options['order_by'] = "proximity ASC, e.time_updated DESC";

	return $options;
}

/**
 * Add a constraint for limiting the proximity to a certain distance in metres
 *
 * Examples of $radius and $ratio combinations:
 * 500 meters: $radius = 500, $ratio = 1
 * 20 kilometers: $radius = 20, $ratio = 0.001
 * 30 miles: $radius = 30, $ratio = 0.000621371
 *
 * @param array $options	An array of getter options
 * @param float $lat		Latitude of the center
 * @param float $long		Longitude of the center
 * @param int $radius		Distance from the center
 * @param float $ratio		Ratio used to convert meters to the unit of the radius
 *
 * @return array
 */
function add_distance_constraint_clauses($options = array(), $lat = 0, $long = 0, $radius = 50000, $ratio = 1) {

	if (!is_array($options)) {
		$options = array();
	}

	$lat = sanitize_string((float) $lat);
	$long = sanitize_string((float) $long);
	$radius = sanitize_string((int) $radius);
	$ratio = sanitize_string((float) $ratio);

	$dbprefix = elgg_get_config('dbprefix');

	// 1825 is the number of meters in a nautical mile
	$options['wheres'][] = "((GLength(LineStringFromWKB(LineString(eg.geometry,GeomFromText('POINT({$lat} {$long})')))))*60*1825*{$ratio} <= {$radius})";
	$options['joins'][] = "JOIN {$dbprefix}entity_geometry eg ON e.guid = eg.entity_guid";
	return $options;
}

/**
 * Calculate distance between two points
 *
 * @param float $lat1
 * @param float $long1
 * @param float $lat2
 * @param float $long2
 * @return float	Distance in meters
 */
function get_distance($lat1, $long1, $lat2, $long2, $unit = 'metres') {
	$point1 = new LatLong(new Coordinate($lat1), new Coordinate($long1));
	$point2 = new LatLong(new Coordinate($lat2), new Coordinate($long2));
	$distance = new Distance($point1, $point2);
	return $distance->get(new GreatCircle());
}

/**
 * Set entity coordinates
 * Updates geo:lat and geo:long metadata and sets the geometry value
 * @param integer $entity_guid
 * @param float $lat
 * @param float $long
 * @return boolean
 */
function set_entity_coordinates($entity_guid = 0, $lat = 0, $long = 0) {

	$lat = (float) $lat;
	$long = (float) $long;

	if (!$lat || !$long) {
		return false;
	}

	$entity = get_entity($entity_guid);

	if (!elgg_instanceof($entity)) {
		return false;
	}

	$entity->setLatLong($lat, $long);

	$dbprefix = elgg_get_config('dbprefix');
	$query = "INSERT DELAYED INTO {$dbprefix}entity_geometry (entity_guid, geometry)
							VALUES ({$entity->guid}, GeomFromText('POINT({$lat} {$long})'))
							ON DUPLICATE KEY UPDATE geometry=GeomFromText('POINT({$lat} {$long})')";
	return insert_data($query);
}

/**
 * Unsets entity coordinates
 *
 * @param integer $entity_guid
 * @return boolean
 */
function unset_entity_coordinates($entity_guid = 0, $lat = 0, $long = 0) {

	$entity = get_entity($entity_guid);

	if (!elgg_instanceof($entity)) {
		return false;
	}

	elgg_delete_metadata(array(
		'guids' => $entity->guid,
		'metadata_names' => array('geo:lat', 'geo:long'),
		'limit' => 0
	));

	$dbprefix = elgg_get_config('dbprefix');
	$query = "DELETE LOW_PRIORITY FROM {$dbprefix}entity_geometry WHERE entity_guid = $entity->guid";
	return delete_data($query);
}

/**
 * Callback function for token input search
 *
 * @param string $term
 * @param array $options
 * @return array
 */
function search_locations($term, $options = array()) {

	$term = sanitize_string($term);

	$q = str_replace(array('_', '%'), array('\_', '\%'), $term);

	$options['metadata_names'] = array('location', 'temp_location');
	$options['group_by'] = "v.string";
	$options['wheres'] = array("v.string LIKE '%$q%'");

	return elgg_get_metadata($options);
}

/**
 * Get coordinates and location name of the current session
 * @return array
 */
function get_geopositioning() {

	// We have a session location set previously or by another plugin
	if (isset($_SESSION['geopositioning'])) {
		return $_SESSION['geopositioning'];
	}

	// Get an address from IP
	$data = ElggIPResolver::resolveIP($_SERVER['REMOTE_ADDR'], false);

	if ($data) {
		$formatter = new Formatter($data);
		return array(
			'location' => $formatter->format("%S %n, %z %L, %C"),
			'latitude' => $data->getLatitude(),
			'longitude' => $data->getLongitude()
		);
	} else if (elgg_is_logged_in()) {
		$user = elgg_get_logged_in_user_entity();
		return array(
			'location' => $user->getLocation(),
			'latitude' => $user->getLatitude(),
			'longitude' => $user->getLongitude()
		);
	} else {
		return array(
			'location' => '',
			'latitude' => 0,
			'longitude' => 0
		);
	}
}

/**
 * Set session geopositioning
 * Cache geocode along the way
 *
 * @param string $location
 * @param float $latitude
 * @param float $longitude
 * @return void
 */
function set_geopositioning($location = '', $latitude = 0, $longitude = 0) {

	$location = sanitize_string($location);
	$lat = (float) $latitude;
	$long = (float) $longitude;

	if (!$lat && !$long) {
		$latlong = elgg_geocode_location($location);
		if ($latlong) {
			$latitude = elgg_extract('lat', $latlong);
			$longitude = elgg_extract('long', $latlong);
		}
	}

	$_SESSION['geopositioning'] = array(
		'location' => $location,
		'latitude' => (float) $latitude,
		'longitude' => (float) $longitude
	);
}
