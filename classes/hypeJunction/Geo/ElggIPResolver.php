<?php

namespace hypeJunction\Geo;

use Exception;
use Geocoder\Formatter\Formatter;
use Geocoder\Geocoder;
use Geocoder\HttpAdapter\GuzzleHttpAdapter;
use Geocoder\Provider\FreeGeoIpProvider;

class ElggIPResolver {

	private static $adapter;
	private static $providers;
	private static $geocoder;

	/**
	 * Constructs a geocoder and builds providers from plugin settings
	 */
	function __construct() {

		if (!isset(self::$adapter)) {
			self::$adapter = new GuzzleHttpAdapter();
		}

		if (!isset(self::$providers)) {
			self::$providers = array_filter(array(
				$this->buildFreeGeoIpProvider(),
			));
		}

		if (!count(self::$providers)) {
			elgg_add_admin_notice('geo:providers', elgg_echo('geo:providers:none'));
		} else if (!isset(self::$geocoder)) {
			$geocoder = new Geocoder();
			$geocoder->registerProviders(self::$providers);
			self::$geocoder = $geocoder;
		}
	}

	/**
	 * Resolve an IP address to location
	 *
	 * @param string $ip IP Address
	 * @param mixed $format Format for an output string, or false to output result object
	 * @return mixed
	 */
	public static function resolveIP($ip = '', $format = "%S %n, %z %L, %C") {
		if (!$ip) {
			return false;
		}

		if (!isset(self::$geocoder)) {
			new ElggGeocoder();
		}

		$geocoder = self::$geocoder;
		if (!$geocoder) {
			return false;
		}

		try {
			$data = $geocoder->geocode($ip);
		} catch (Exception $e) {
			elgg_log("ElggIPResolver::resolveIP failed with the following message: " . $e->getMessage(), 'WARNING');
		}
		
		if ($data) {
			if ($format) {
				$formatter = new Formatter($data);
				return $formatter->format($format);
			} else {
				return $data;
			}
		} else {
			return false;
		}
	}

	private function buildFreeGeoIpProvider() {
		$provider = 'FreeGeoIpProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);

		return new FreeGeoIpProvider($adapter, $locale);
	}

}
