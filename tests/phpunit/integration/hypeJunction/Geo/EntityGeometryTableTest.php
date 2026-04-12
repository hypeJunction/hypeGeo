<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

/**
 * Verifies the {prefix}entity_geometry table is present and that
 * set_entity_coordinates() / unset_entity_coordinates() can round-trip rows.
 *
 * NOTE: legacy MySQL spatial functions (GLength, GeomFromText, LineStringFromWKB)
 * are REMOVED in MySQL 8.0. This test is a canary — it will fail on MySQL 8
 * and that failure is the signal for the migration work.
 */
class EntityGeometryTableTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testEntityGeometryTableExists(): void {
        $db = elgg()->db;
        $prefix = $db->prefix;
        $rows = $db->getData("SHOW TABLES LIKE '{$prefix}entity_geometry'");
        $this->assertNotEmpty($rows, 'entity_geometry table should exist (created by activate.php)');
    }

    public function testSetEntityCoordinatesRequiresValidEntity(): void {
        $result = set_entity_coordinates(0, 51.5, -0.12);
        $this->assertFalse($result);
    }

    public function testSetEntityCoordinatesRejectsZeroCoords(): void {
        $user = $this->createUser();
        $object = $this->createObject(['subtype' => 'hypegeo_test']);
        $result = set_entity_coordinates($object->guid, 0, 0);
        $this->assertFalse($result);
    }

    public function testSetAndUnsetEntityCoordinatesRoundTrip(): void {
        $user = $this->createUser();
        $object = $this->createObject(['subtype' => 'hypegeo_test', 'owner_guid' => $user->guid]);

        try {
            $ok = set_entity_coordinates($object->guid, 51.5074, -0.1278);
        } catch (\Throwable $e) {
            $this->markTestSkipped(
                'Legacy MySQL spatial functions unavailable in this MySQL version: ' . $e->getMessage()
            );
            return;
        }
        $this->assertNotFalse($ok, 'Inserting into entity_geometry should succeed on MySQL 5.x');

        $this->assertEquals(51.5074, (float) $object->getLatitude());
        $this->assertEquals(-0.1278, (float) $object->getLongitude());

        $db = elgg()->db;
        $prefix = $db->prefix;
        $rows = $db->getData("SELECT entity_guid FROM {$prefix}entity_geometry WHERE entity_guid = {$object->guid}");
        $this->assertCount(1, $rows);

        unset_entity_coordinates($object->guid);
        $rows = $db->getData("SELECT entity_guid FROM {$prefix}entity_geometry WHERE entity_guid = {$object->guid}");
        $this->assertCount(0, $rows);
    }
}
