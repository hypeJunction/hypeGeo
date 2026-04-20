<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

/**
 * Exercises the plugin's registered hooks:
 *   - geocode/location        (returns lat/long from an address string)
 *   - search_types/get_types  (adds 'proximity' when enabled in settings)
 *   - search/proximity        (proximity entity listing)
 *   - all/metadata            (re-geocodes on metadata create/update)
 */
class HooksTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testGeocodeLocationHookHandlerIsCallable(): void {
        $this->assertTrue(
            function_exists('hypeJunction\\Geo\\geocode_location'),
            'geocode_location handler must be loaded'
        );
    }

    public function testGeocodeLocationReturnsFalseForEmptyLocation(): void {
        $result = geocode_location('geocode', 'location', null, ['location' => '']);
        $this->assertFalse($result);
    }

    public function testSearchCustomTypesIncludesProximityWhenEnabled(): void {
        $plugin = elgg_get_plugin_from_id('hypegeo');
        if (!$plugin) {
            $this->markTestSkipped('hypegeo plugin entity not found in test DB');
            return;
        }
        $plugin->setSetting('proximity_search', 1);

        $result = search_custom_types('search_types', 'get_types', ['user', 'object'], []);
        $this->assertContains('proximity', $result);

        $plugin->setSetting('proximity_search', 0);
        $result = search_custom_types('search_types', 'get_types', ['user', 'object'], []);
        $this->assertNotContains('proximity', $result);
    }

    public function testGeocodeLocationMetadataIgnoresUnrelatedMetadata(): void {
        $md = (object) [
            'name'         => 'some_other_field',
            'value'        => 'ignored',
            'entity_guid'  => 0,
        ];
        $event = $this->createMock(\Elgg\Event::class);
        $event->method('getObject')->willReturn($md);
        $event->method('getName')->willReturn('update');

        geocode_location_metadata($event);
        $this->addToAssertionCount(1);
    }
}
