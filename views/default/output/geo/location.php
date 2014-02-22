<?php

/**
 * Outputs a location
 *
 * @uses $vars['value'] Location string to output
 * @uses $vars['entity'] Optionally pass an entity to set value to entity location
 */
namespace hypeJunction\Geo;

$value = elgg_extract('value', $vars, null);
$entity = elgg_extract('entity', $vars);

if (!$value && elgg_instanceof($entity)) {
	$value = $entity->getLocation();
}

if (!$value) {
	return;
}

if (elgg_is_active_plugin('search')) {
	echo elgg_view('output/url', array(
		'text' => $value,
		'href' => elgg_get_site_url() . "search?search_type=proximity&q=$value",
	));
} else {
	echo elgg_view('output/tag', array(
		'value' => $value
	));
}