<?php
/**
 * A standard postal address form
 *
 * @uses $vars['prefix'] A string to prefix input names with, defaults to 'address'
 * @uses $vars['value'] An array of values to prepopulate the form with
 * @uses $vars['required'] Make required
 */

namespace hypeJunction\Geo;

$prefix = \elgg_extract('prefix', $vars, 'address');
$required = \elgg_extract('required', $vars, false);
$value = \elgg_extract('value', $vars);

$label_attrs = [];
if ($required) {
	$label_attrs = [
		'title' => \elgg_echo('geo:required'),
		'class' => 'geo-required',
	];
}

$street_address = \elgg_view('input/text', [
	'name' => "{$prefix}[street_address]",
	'value' => \elgg_extract('street_address', $value),
	'class' => 'geo-input-street-address',
	'placeholder' => \elgg_echo('geo:postal_address:street_address'),
	'required' => $required
]);

$extended_address = \elgg_view('input/text', [
	'name' => "{$prefix}[extended_address]",
	'value' => \elgg_extract('extended_address', $value),
	'class' => 'geo-input-extended-address',
	'placeholder' => \elgg_echo('geo:postal_address:extended_address')
]);

$locality = \elgg_view('input/text', [
	'name' => "{$prefix}[locality]",
	'value' => \elgg_extract('locality', $value),
	'class' => 'geo-input-locality',
	'placeholder' => \elgg_echo('geo:postal_address:locality'),
	'required' => $required
]);

$region = \elgg_view('input/text', [
	'name' => "{$prefix}[region]",
	'value' => \elgg_extract('region', $value),
	'class' => 'geo-input-region',
	'placeholder' => \elgg_echo('geo:postal_address:region'),
	'required' => $required
]);

$postal_code = \elgg_view('input/text', [
	'name' => "{$prefix}[postal_code]",
	'value' => \elgg_extract('postal_code', $value),
	'class' => 'geo-input-postal-code',
	'placeholder' => \elgg_echo('geo:postal_address:postal_code'),
	'required' => $required
]);

$country = \elgg_view('input/geo/country', [
	'name' => "{$prefix}[country_code]",
	'value' => \elgg_extract('country_code', $value),
	'required' => $required
]);
?>

<fieldset class="geo-postal-address" data-postal-address>
	<div data-street-address>
		<?php echo \elgg_format_element('label', $label_attrs, \elgg_echo('geo:postal_address:street_address')) ?>
		<?php echo $street_address ?>
	</div>
	<div data-extended-address>
		<?php echo \elgg_format_element('label', [], \elgg_echo('geo:postal_address:extended_address')) ?>
		<?php echo $extended_address ?>
	</div>
	<div data-locality>
		<?php echo \elgg_format_element('label', $label_attrs, \elgg_echo('geo:postal_address:locality')) ?>
		<?php echo $locality ?>
	</div>
	<div data-region>
		<?php echo \elgg_format_element('label', $label_attrs, \elgg_echo('geo:postal_address:region')) ?>
		<?php echo $region ?>
	</div>
	<div data-postal-code>
		<?php echo \elgg_format_element('label', $label_attrs, \elgg_echo('geo:postal_address:postal_code')) ?>
		<?php echo $postal_code ?>
	</div>
	<div data-country>
		<?php echo \elgg_format_element('label', $label_attrs, \elgg_echo('geo:postal_address:country')) ?>
		<?php echo $country ?>
	</div>
</fieldset>
