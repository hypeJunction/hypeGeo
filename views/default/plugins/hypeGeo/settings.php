<?php

namespace hypeJunction\Geo;

$entity = elgg_extract('entity', $vars);

/**
 * Elgg Search
 */
echo '<div>';
echo '<label>' . elgg_echo('geo:settings:proximity_search') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[proximity_search]",
	'value' => $entity->proximity_search,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';


/**
 * GOOGLE MAPS PROVIDER
 */
$provider = 'GoogleMapsProvider';

echo '<h3>Google Maps</h3>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:enable') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[$provider]",
	'value' => $entity->$provider,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:api_key') . '</label>';
$opt = "$provider:api_key";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:locale') . '</label>';
$opt = "$provider:locale";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:region') . '</label>';
$opt = "$provider:region";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:ssl') . '</label>';
$opt = "$provider:region";
echo elgg_view('input/dropdown', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';


/**
 * NOMINATIM PROVIDER
 */
$provider = 'NominatimProvider';

echo '<h3>Nominatim/OSM</h3>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:enable') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[$provider]",
	'value' => $entity->$provider,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:locale') . '</label>';
$opt = "$provider:locale";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:url') . '</label>';
$opt = "$provider:url";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => (isset($entity->$opt)) ? $entity->$opt : 'http://nominatim.openstreetmap.org',
));
echo '</div>';


/**
 * YANDEX PROVIDER
 */
$provider = 'YandexProvider';

echo '<h3>Yandex</h3>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:enable') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[$provider]",
	'value' => $entity->$provider,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:locale') . '</label>';
$opt = "$provider:locale";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:toponym') . '</label>';
$opt = "$provider:toponym";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';


/**
 * GOOGLE MAPS BUSINESS PROVIDER
 */
$provider = 'GoogleMapsBusinessProvider';

echo '<h3>Google Maps Business</h3>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:enable') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[$provider]",
	'value' => $entity->$provider,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:gmb:client_id') . '</label>';
$opt = "$provider:client_id";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:gmb:private_key') . '</label>';
$opt = "$provider:private_key";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:locale') . '</label>';
$opt = "$provider:locale";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:region') . '</label>';
$opt = "$provider:region";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:ssl') . '</label>';
$opt = "$provider:region";
echo elgg_view('input/dropdown', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';


/**
 * FREE GEOIP PROVIDER
 */
$provider = 'FreeGeoIpProvider';

echo '<h3>FreeGeoIp.net</h3>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:enable') . '</label>';
echo elgg_view('input/dropdown', array(
	'name' => "params[$provider]",
	'value' => $entity->$provider,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	)
));
echo '</div>';

echo '<div>';
echo '<label>' . elgg_echo('geo:settings:locale') . '</label>';
$opt = "$provider:locale";
echo elgg_view('input/text', array(
	'name' => "params[$opt]",
	'value' => $entity->$opt,
));
echo '</div>';