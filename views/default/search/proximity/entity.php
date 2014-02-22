<?php

/**
 * Proximity view for an entity returned in a search
 *
 * Display largely controlled by a set of overrideable volatile data:
 *   - search_matched_title 
 *   - search_matched_location
 *   - search_proximity
 *
 * @uses $vars['entity'] Entity returned in a search
 */

namespace hypeJunction\Geo;

$entity = $vars['entity'];

if (substr_count($entity->getIconURL('small'), '_graphics/icons/default/small.png')) {
	$icon = elgg_view_entity_icon($entity->getOwnerEntity(), 'small');
} else {
	$icon = elgg_view_entity_icon($entity, 'small', array(
		'img_class' => 'no-style',
	));
}

$proximity = $entity->getVolatileData('search_proximity');

$title = elgg_view('output/url', array(
	'text' => $entity->getVolatileData('search_matched_title'),
	'href' => $entity->getURL()
		));

$location = elgg_view_icon('geo-location') . elgg_view('output/geo/location', array(
			'value' => $entity->getVolatileData('search_matched_location'),
		));

$body = elgg_view('object/elements/summary', array(
	'title' => $title,
	'subtitle' => $location,
		));

echo elgg_view_image_block($icon, $body, array(
	'image_alt' => $proximity,
	'class' => 'geo-search-item',
));
