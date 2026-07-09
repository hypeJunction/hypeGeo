<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

/**
 * Runtime regression guards for the hypeGeo 7.x migration fixes that require a
 * booted Elgg (DB + translator + entities).
 *
 *   72da4f0 — elgg_geocode_location() removed with NO core replacement; the
 *             geocoding-dependent write/search paths must degrade cleanly
 *             (coords stay null) rather than fatal.
 *   2ce90d7 — add_translation() removed in 5.x; Countries::registerTranslations()
 *             now feeds elgg()->translator->addTranslation('en', ...).
 */
class MigrationBehaviorTest extends IntegrationTestCase {

	public function up() {}

	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	private function makeEvent(string $name, string $type, mixed $value = null, array $params = []): \Elgg\Event {
		$event = $this->getMockBuilder(\Elgg\Event::class)
			->disableOriginalConstructor()
			->getMock();
		$event->method('getName')->willReturn($name);
		$event->method('getType')->willReturn($type);
		$event->method('getValue')->willReturn($value);
		$event->method('getParam')->willReturnCallback(fn($key, $default = null) => $params[$key] ?? $default);
		$event->method('getObject')->willReturn($params['__object'] ?? null);
		return $event;
	}

	/**
	 * 72da4f0: with geocoding disabled (elgg_geocode_location removed -> coords
	 * null), search_by_proximity_hook must return the incoming value verbatim —
	 * NOT run the spatial query, NOT fatal.
	 */
	public function testSearchByProximityReturnsValueUnchangedWhenGeocodingDisabled(): void {
		$incoming = ['entities' => ['sentinel'], 'count' => 7];
		$event = $this->makeEvent('search', 'proximity', $incoming, ['query' => 'London']);

		$result = search_by_proximity_hook($event);

		$this->assertSame($incoming, $result);
	}

	/**
	 * 72da4f0: on a create/update of `location` metadata the 7.x handler leaves
	 * coordinates null (geocoding removed) and therefore UNSETS any previously
	 * stored coordinates rather than setting new ones.
	 */
	public function testLocationMetadataUpdateUnsetsStoredCoordinates(): void {
		$user = $this->createUser();
		$object = $this->createObject(['subtype' => 'hypegeo_test', 'owner_guid' => $user->guid]);

		try {
			$ok = set_entity_coordinates($object->guid, 51.5074, -0.1278);
		} catch (\Throwable $e) {
			$this->markTestSkipped('Spatial functions unavailable in this MySQL version: ' . $e->getMessage());
			return;
		}
		$this->assertNotFalse($ok, 'precondition: coordinates stored');

		$db = elgg()->db;
		$conn = $db->getConnection('read');
		$countRows = static function () use ($conn, $db, $object): int {
			return count($conn->executeQuery(
				"SELECT entity_guid FROM {$db->prefix}entity_geometry WHERE entity_guid = ?",
				[$object->guid]
			)->fetchAllAssociative());
		};
		$this->assertSame(1, $countRows(), 'precondition: geometry row present');

		try {
			$metadata = new \ElggMetadata((object) [
				'name'        => 'location',
				'value'       => 'London, UK',
				'entity_guid' => $object->guid,
			]);
		} catch (\Throwable $e) {
			$this->markTestSkipped('Unable to construct ElggMetadata fixture: ' . $e->getMessage());
			return;
		}

		$event = $this->makeEvent('update', 'metadata', null, ['__object' => $metadata]);
		geocode_location_metadata($event);

		$this->assertSame(0, $countRows(), 'coordinates must be unset when geocoding yields null on 7.x');
	}

	/**
	 * 2ce90d7: registerTranslations() must resolve country labels via the
	 * translator service (add_translation() is removed). If it still called the
	 * removed function the handler would fatal before returning.
	 */
	public function testCountryTranslationsRegisteredViaTranslatorService(): void {
		$us = Countries::getCountryByCode('US');
		$this->assertNotEmpty($us, 'US must resolve in the country list');

		$this->assertSame(
			'United States',
			elgg_echo('country:US', [], 'en'),
			'registerTranslations() must feed elgg()->translator->addTranslation(\'en\', ...)'
		);
	}
}
