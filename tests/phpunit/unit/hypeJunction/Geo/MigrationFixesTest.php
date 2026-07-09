<?php

namespace hypeJunction\Geo;

use Elgg\UnitTestCase;
use hypeJunction\Geo\Upgrades\CreateEntityGeometryTable;

/**
 * Static / pure-logic regression guards for the hypeGeo 2.x -> 7.x migration
 * fixes. These do NOT touch the database — they pin the shape of the migrated
 * code so a bulk remediation pass cannot silently regress it.
 *
 * One test per relevant migrationFix ref:
 *   ef99140  — AsynchronousUpgrade became abstract -> `extends` (not `implements`)
 *   471749e  — 5.x hooks converted to single \Elgg\Event + ST_ spatial functions
 *   bdd1edd  — require_once lib/functions.php + lib/hooks.php at TOP of elgg-plugin.php
 *   72da4f0  — elgg_format_attributes() -> html_formatter->formatAttributes()
 *   1a978fe  — $label_attrs initialised to '' (PHP 8.2 undefined-var warning)
 */
class MigrationFixesTest extends UnitTestCase {

	public function up() {}

	public function down() {}

	/**
	 * ef99140: the upgrade must EXTEND the (now abstract) AsynchronousUpgrade
	 * base class — `implements Batch` fatals on 6.x+.
	 */
	public function testUpgradeExtendsAsynchronousUpgradeWithCorrectShape(): void {
		$this->assertTrue(
			is_subclass_of(CreateEntityGeometryTable::class, \Elgg\Upgrade\AsynchronousUpgrade::class),
			'CreateEntityGeometryTable must extend \\Elgg\\Upgrade\\AsynchronousUpgrade (abstract since 6.x)'
		);

		$upgrade = new CreateEntityGeometryTable();
		$this->assertSame(2026041200, $upgrade->getVersion());
		$this->assertFalse($upgrade->needsIncrementOffset());
	}

	/**
	 * 471749e: the 5.x spatial-function conversion must emit MySQL-8 functions
	 * ST_Distance() + ST_GeomFromText(). Guards against the latent DOUBLE-prefix
	 * regression `ST_ST_GeomFromText` (invalid SQL, fatals at query time) left by
	 * the sed that rewrote the legacy GeomFromText/GLength calls.
	 */
	public function testProximityClausesEmitValidSpatialSql(): void {
		$select = add_order_by_proximity_clauses([], 51.5, -0.12)['selects'][0];
		$this->assertStringContainsString('ST_Distance(', $select);
		$this->assertStringNotContainsString(
			'ST_ST_',
			$select,
			'double ST_ prefix (ST_ST_GeomFromText) is invalid MySQL 8 SQL and fatals at query time'
		);

		$where = add_distance_constraint_clauses([], 51.5, -0.12, 10000)['wheres'][0];
		$this->assertStringContainsString('ST_Distance(', $where);
		$this->assertStringNotContainsString('ST_ST_', $where);
	}

	/**
	 * 471749e / 7194167: every registered handler was converted from the legacy
	 * 4-arg ($hook,$type,$return,$params) signature to a single \Elgg\Event.
	 */
	public function testEventHandlersUseSingleEventSignature(): void {
		$handlers = [
			'hypeJunction\\Geo\\geocode_location',
			'hypeJunction\\Geo\\geocode_location_metadata',
			'hypeJunction\\Geo\\search_custom_types',
			'hypeJunction\\Geo\\search_by_proximity_hook',
		];

		foreach ($handlers as $handler) {
			$this->assertTrue(function_exists($handler), "{$handler} must be loaded");
			$ref = new \ReflectionFunction($handler);
			$this->assertCount(1, $ref->getParameters(), "{$handler} must take a single event param");

			$type = $ref->getParameters()[0]->getType();
			$this->assertNotNull($type, "{$handler} param must be type-hinted");
			$this->assertSame(
				'Elgg\\Event',
				$type->getName(),
				"{$handler} must type-hint \\Elgg\\Event (5.x single-event signature)"
			);
		}
	}

	/**
	 * bdd1edd: the procedural handler libs must be require_once'd at the TOP of
	 * elgg-plugin.php (composer autoload.files is too late for git-tracked
	 * customs), and the returned manifest must wire all four handlers to the
	 * correct event/type. This is the registration contract for the plugin.
	 */
	public function testManifestWiresProceduralHandlersLoadedAtBoot(): void {
		$config = require dirname(__DIR__, 5) . '/elgg-plugin.php';

		$expected = [
			['geocode', 'location', 'hypeJunction\\Geo\\geocode_location'],
			['search_types', 'get_types', 'hypeJunction\\Geo\\search_custom_types'],
			['search', 'proximity', 'hypeJunction\\Geo\\search_by_proximity_hook'],
			['all', 'metadata', 'hypeJunction\\Geo\\geocode_location_metadata'],
		];

		foreach ($expected as [$event, $type, $handler]) {
			$this->assertArrayHasKey(
				$handler,
				$config['events'][$event][$type] ?? [],
				"{$handler} must be registered on {$event}/{$type}"
			);
			$this->assertTrue(
				function_exists($handler),
				"{$handler} must be callable at boot (require_once at top of elgg-plugin.php)"
			);
		}

		$this->assertContains(CreateEntityGeometryTable::class, $config['upgrades']);
	}

	/**
	 * 72da4f0 + 1a978fe: the postal_address form must use the html_formatter
	 * service (elgg_format_attributes() was removed in 4.x) and must initialise
	 * $label_attrs to '' to avoid a PHP 8.2 undefined-variable warning.
	 */
	public function testPostalAddressFormAvoidsRemovedAttributeFormatter(): void {
		$src = file_get_contents(dirname(__DIR__, 5) . '/views/default/forms/geo/postal_address.php');

		$this->assertStringContainsString('html_formatter->formatAttributes', $src);
		$this->assertDoesNotMatchRegularExpression(
			'/(?<![\w>$:\\\\])elgg_format_attributes\s*\(/',
			$src,
			'elgg_format_attributes() was removed in Elgg 4.x — use html_formatter->formatAttributes()'
		);
		$this->assertStringContainsString("\$label_attrs = ''", $src);
	}
}
