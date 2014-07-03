<?php

namespace hypeJunction\Geo;

use Exception;
use Geocoder\Formatter\Formatter;
use Geocoder\Geocoder;
use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\ChainProvider;
use Geocoder\Provider\GoogleMapsBusinessProvider;
use Geocoder\Provider\GoogleMapsProvider;
use Geocoder\Provider\NominatimProvider;
use Geocoder\Provider\YandexProvider;

class ElggGeocoder {

	private static $adapter;
	private static $providers;
	private static $geocoder;

	/**
	 * Constructs a geocoder and builds providers from plugin settings
	 */
	function __construct() {

		if (!isset(self::$adapter)) {
			self::$adapter = new CurlHttpAdapter();
		}

		if (!isset(self::$providers)) {
			self::$providers = array_filter(array(
				$this->buildGoogleMapsProvider(),
				$this->buildNominatimProvider(),
				$this->buildYandexProvider(),
				$this->buildGoogleMapsBusinessProvider(),
			));
		}

		if (!count(self::$providers)) {
			elgg_add_admin_notice('geo:providers', elgg_echo('geo:providers:none'));
		} else if (!isset(self::$geocoder)) {
			$geocoder = new Geocoder();
			$chain = new ChainProvider(self::$providers);
			$geocoder->registerProvider($chain);
			self::$geocoder = $geocoder;
		}
	}

	/**
	 * Geocode an address string
	 *
	 * @param string $address Address or location
	 * @param boolean $filter Filter the result for elgg_geocode_location() or output all data
	 * @return mixed
	 */
	public static function geocodeAddress($address = '', $filter = true) {
		if (!$address) {
			return false;
		}

		if (is_array($address)) {
			$address = implode(', ', $address);
		}

		if (!isset(self::$geocoder)) {
			new ElggGeocoder();
		}

		$geocoder = self::$geocoder;
		if (!$geocoder) {
			return false;
		}

		try {
			$data = $geocoder->geocode($address);
		} catch (Exception $e) {
			elgg_log("ElggGeocoder::geocodeAddress failed with the following message: " . $e->getMessage(), 'WARNING');
		}

		if ($data) {
			return ($filter) ? array(
				'lat' => $data->getLatitude(),
				'long' => $data->getLongitude(),
					) : $data;
		} else {
			return false;
		}
	}

	/**
	 * Reverse lookup of coordinates
	 *
	 * @param float $latitude Latitude
	 * @param float $longitude Longitude
	 * @param mixed $format Format of the output string, or false to output result object
	 * @return mixed
	 */
	public static function reverseCoordinates($latitude, $longitude, $format = "%S %n, %z %L, %C") {
		if (!is_float($latitude) || !is_float($longitude)) {
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
			$data = $geocoder->reverse($latitude, $longitude);
		} catch (Exception $e) {
			elgg_log("ElggGeocoder::reverseCooridnates failed with the following message: " . $e->getMessage(), 'WARNING');
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

		$data = $geocoder->geocode($ip);

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

	private function buildGoogleMapsProvider() {

		$provider = 'GoogleMapsProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$apiKey = elgg_get_plugin_setting("$provider:api_key", PLUGIN_ID);
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);
		$region = elgg_get_plugin_setting("$provider:region", PLUGIN_ID);
		if (!$apiKey) {
			$useSsl = elgg_get_plugin_setting("$provider:ssl", PLUGIN_ID);
		} else {
			$useSsl = true;
		}
		return new GoogleMapsProvider(
				$adapter, $locale, $region, $useSsl, $apiKey
		);
	}

	private function buildNominatimProvider() {

		$provider = 'NominatimProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);
		$url = elgg_get_plugin_setting("$provider:url", PLUGIN_ID);
		return new NominatimProvider(
				$adapter, $url, $locale
		);
	}

	private function buildYandexProvider() {

		$provider = 'YandexProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);
		$toponym = elgg_get_plugin_setting("$provider:toponym", PLUGIN_ID);
		return new YandexProvider($adapter, $locale, $toponym);
	}

	private function buildGoogleMapsBusinessProvider() {

		$provider = 'GoogleMapsBusinessProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);
		$region = elgg_get_plugin_setting("$provider:region", PLUGIN_ID);
		$useSsl = elgg_get_plugin_setting("$provider:ssl", PLUGIN_ID);
		$client_id = elgg_get_plugin_setting("$provider:client_id", PLUGIN_ID);
		$private_key = elgg_get_plugin_setting("$provider:private_key", PLUGIN_ID);

		return new GoogleMapsBusinessProvider($adapter, $client_id, $private_key, $locale, $region, $useSsl);
	}

	private function buildFreeGeoIpProvider() {
		$provider = 'FreeGeoIpProvider';
		if (!elgg_get_plugin_setting($provider, PLUGIN_ID)) {
			return null;
		}

		$adapter = self::$adapter;
		$locale = elgg_get_plugin_setting("$provider:locale", PLUGIN_ID);

		return new GoogleMapsBusinessProvider($adapter, $locale);
	}

}
