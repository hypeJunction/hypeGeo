<?php
/**
 * Output a postal address
 *
 * @uses $vars['value'] An array in a format accepted by forms/geo/postal_address
 *
 */

namespace hypeJunction\Geo;

$address = elgg_extract('value', $vars);
?>

<div class="geo-vcard">
	<div class="geo-postal-address">
		<div class="geo-street-address"><?php echo elgg_extract('street_address', $address) ?></div>
		<div class="geo-extended-address"><?php echo elgg_extract('extended_address', $address) ?></div>
		<span class="geo-locality"><?php echo elgg_extract('locality', $address) ?></span>,
		<abbr class="geo-region"><?php echo elgg_extract('region', $address) ?></abbr>
		<span class="geo-postal-code"><?php echo elgg_extract('postal_code', $address) ?></span>
		<div class="geo-country-name"><?php echo elgg_view('output/geo/country', array('value' => elgg_extract('country_code', $address))) ?></div>
	</div>
</div>