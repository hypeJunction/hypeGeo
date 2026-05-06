<?php

namespace hypeJunction\Geo;

use Geocoder\Formatter\Formatter;
use Treffynnon\Navigator\Coordinate;
use Treffynnon\Navigator\Distance;
use Treffynnon\Navigator\Distance\Calculator\GreatCircle;
use Treffynnon\Navigator\LatLong;

/**
 * Get entities by proximity.
 *
 * @param mixed $options Options
 * @param mixed $lat     Lat
 * @param mixed $long    Long
 * @param mixed $getter  Getter
 * @return mixed
 */
function get_entities_by_proximity($options = [], $lat = null, $long = null, $getter = 'elgg_get_entities') {

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
 * Add order by proximity clauses.
 *
 * @param mixed $options Options
 * @param mixed $lat     Lat
 * @param mixed $long    Long
 * @return mixed
 */
function add_order_by_proximity_clauses($options = [], $lat = 0, $long = 0) {

	if (!is_array($options)) {
		$options = [];
	}

	$lat = (float) $lat;
	$long = (float) $long;

	$prefix = elgg()->db->prefix;

	$options['selects'][] = "ST_Distance(eg.geometry, ST_ST_GeomFromText('POINT({$lat} {$long})')) as proximity";
	$options['joins'][] = "JOIN {$prefix}entity_geometry eg ON e.guid = eg.entity_guid";
	$options['order_by'] = 'proximity ASC, e.time_updated DESC';

	return $options;
}

/**
 * Add distance constraint clauses.
 *
 * @param mixed $options Options
 * @param mixed $lat     Lat
 * @param mixed $long    Long
 * @param mixed $radius  Radius
 * @param mixed $ratio   Ratio
 * @return mixed
 */
function add_distance_constraint_clauses($options = [], $lat = 0, $long = 0, $radius = 50000, $ratio = 1) {

	if (!is_array($options)) {
		$options = [];
	}

	$lat = (float) $lat;
	$long = (float) $long;
	$radius = (int) $radius;
	$ratio = (float) $ratio;

	$prefix = elgg()->db->prefix;

	$options['wheres'][] = "(ST_Distance(eg.geometry, ST_ST_GeomFromText('POINT({$lat} {$long})')) * 60 * 1825 * {$ratio} <= {$radius})";
	$options['joins'][] = "JOIN {$prefix}entity_geometry eg ON e.guid = eg.entity_guid";
	return $options;
}

/**
 * Get distance.
 *
 * @param mixed $lat1  Lat1
 * @param mixed $long1 Long1
 * @param mixed $lat2  Lat2
 * @param mixed $long2 Long2
 * @param mixed $unit  Unit
 * @return mixed
 */
function get_distance($lat1, $long1, $lat2, $long2, $unit = 'metres') {
	$point1 = new LatLong(new Coordinate($lat1), new Coordinate($long1));
	$point2 = new LatLong(new Coordinate($lat2), new Coordinate($long2));
	$distance = new Distance($point1, $point2);
	return $distance->get(new GreatCircle());
}

/**
 * Set entity coordinates.
 *
 * @param mixed $entity_guid Entity guid
 * @param mixed $lat         Lat
 * @param mixed $long        Long
 * @return mixed
 */
function set_entity_coordinates($entity_guid = 0, $lat = 0, $long = 0) {

	$lat = (float) $lat;
	$long = (float) $long;

	if (!$lat || !$long) {
		return false;
	}

	$entity = get_entity($entity_guid);

	if (!$entity instanceof \ElggEntity) {
		return false;
	}

	$entity->setLatLong($lat, $long);

	$db = elgg()->db;
	$prefix = $db->prefix;
	$sql = "INSERT INTO {$prefix}entity_geometry (entity_guid, geometry)
		VALUES ({$entity->guid}, ST_GeomFromText('POINT({$lat} {$long})'))
		ON DUPLICATE KEY UPDATE geometry=ST_GeomFromText('POINT({$lat} {$long})')";
	return $db->getConnection('write')->executeStatement($sql) !== false;
}

/**
 * Unset entity coordinates.
 *
 * @param mixed $entity_guid Entity guid
 * @param mixed $lat         Lat
 * @param mixed $long        Long
 * @return mixed
 */
function unset_entity_coordinates($entity_guid = 0, $lat = 0, $long = 0) {

	$entity = get_entity($entity_guid);

	if (!$entity instanceof \ElggEntity) {
		return false;
	}

	elgg_delete_metadata([
		'guids' => $entity->guid,
		'metadata_names' => ['geo:lat', 'geo:long'],
		'limit' => 0
	]);

	$db = elgg()->db;
	$prefix = $db->prefix;
	$sql = "DELETE FROM {$prefix}entity_geometry WHERE entity_guid = {$entity->guid}";
	return $db->getConnection('write')->executeStatement($sql) !== false;
}

/**
 * Search locations.
 *
 * @param mixed $term    Term
 * @param mixed $options Options
 * @return mixed
 */
function search_locations($term, $options = []) {

	$q = str_replace(['_', '%'], ['\_', '\%'], $term);

	$options['metadata_names'] = ['location', 'temp_location'];
	$options['group_by'] = 'v.string';
	$options['wheres'] = ["v.string LIKE '%" . addcslashes($q, "'\\") . "%'"];

	return elgg_get_metadata($options);
}

/**
 * Get geopositioning.
 *
 * @return mixed
 */
function get_geopositioning() {

	if (isset($_SESSION['geopositioning'])) {
		return $_SESSION['geopositioning'];
	}

	$data = ElggIPResolver::resolveIP($_SERVER['REMOTE_ADDR'], false);

	if ($data) {
		$formatter = new Formatter($data);
		return [
			'location' => $formatter->format('%S %n, %z %L, %C'),
			'latitude' => $data->getLatitude(),
			'longitude' => $data->getLongitude()
		];
	} else if (elgg_is_logged_in()) {
		$user = elgg_get_logged_in_user_entity();
		return [
			'location' => $user->location,
			'latitude' => $user->getLatitude(),
			'longitude' => $user->getLongitude()
		];
	} else {
		return [
			'location' => '',
			'latitude' => 0,
			'longitude' => 0
		];
	}
}

/**
 * Set geopositioning.
 *
 * @param mixed $location  Location
 * @param mixed $latitude  Latitude
 * @param mixed $longitude Longitude
 * @return mixed
 */
function set_geopositioning($location = '', $latitude = 0, $longitude = 0) {

	$lat = (float) $latitude;
	$long = (float) $longitude;

	if (!$lat && !$long) {
		$latlong = elgg_geocode_location($location);
		if ($latlong) {
			$latitude = elgg_extract('lat', $latlong);
			$longitude = elgg_extract('long', $latlong);
		}
	}

	$_SESSION['geopositioning'] = [
		'location' => $location,
		'latitude' => (float) $latitude,
		'longitude' => (float) $longitude
	];
}
