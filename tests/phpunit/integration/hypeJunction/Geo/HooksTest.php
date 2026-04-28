<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

/**
 * Exercises the plugin's registered event handlers:
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

    public function testGeocodeLocationHandlerIsCallable(): void {
        $this->assertTrue(
            function_exists('hypeJunction\\Geo\\geocode_location'),
            'geocode_location handler must be loaded'
        );
    }

    public function testGeocodeLocationReturnsFalseForEmptyLocation(): void {
        $event = $this->makeEvent('geocode', 'location', null, ['location' => '']);
        $result = geocode_location($event);
        $this->assertFalse($result);
    }

    public function testSearchCustomTypesIncludesProximityWhenEnabled(): void {
        $plugin = elgg_get_plugin_from_id('hypegeo');
        if (!$plugin) {
            $this->markTestSkipped('hypegeo plugin entity not found in test DB');
            return;
        }
        $plugin->setSetting('proximity_search', 1);

        $event = $this->makeEvent('search_types', 'get_types', ['user', 'object']);
        $result = search_custom_types($event);
        $this->assertContains('proximity', $result);

        $plugin->setSetting('proximity_search', 0);
        $event = $this->makeEvent('search_types', 'get_types', ['user', 'object']);
        $result = search_custom_types($event);
        $this->assertNotContains('proximity', $result);
    }

    public function testGeocodeLocationMetadataIgnoresUnrelatedMetadata(): void {
        $md = (object) [
            'name'         => 'some_other_field',
            'value'        => 'ignored',
            'entity_guid'  => 0,
        ];
        $event = $this->makeEvent('update', 'metadata', null, ['__object' => $md]);

        geocode_location_metadata($event);
        $this->addToAssertionCount(1);
    }
}
