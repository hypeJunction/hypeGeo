<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

class ViewsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testLocationInputViewRenders(): void {
        if (!elgg_view_exists('input/geo/location')) {
            $this->markTestSkipped('input/geo/location view not registered — plugin may not be active in test DB');
            return;
        }
        $output = elgg_view('input/geo/location', ['name' => 'location']);
        $this->assertIsString($output);
    }

    public function testCountryInputViewRenders(): void {
        if (!elgg_view_exists('input/geo/country')) {
            $this->markTestSkipped('input/geo/country view not registered');
            return;
        }
        $output = elgg_view('input/geo/country', ['name' => 'country']);
        $this->assertIsString($output);
        $this->assertNotEmpty($output);
    }

    public function testCountryOutputViewRenders(): void {
        if (!elgg_view_exists('output/geo/country')) {
            $this->markTestSkipped('output/geo/country view not registered');
            return;
        }
        $output = elgg_view('output/geo/country', ['value' => 'US']);
        $this->assertIsString($output);
    }

    public function testLocationOutputViewRenders(): void {
        if (!elgg_view_exists('output/geo/location')) {
            $this->markTestSkipped('output/geo/location view not registered');
            return;
        }
        $output = elgg_view('output/geo/location', ['value' => 'London, UK']);
        $this->assertIsString($output);
    }

    public function testPostalAddressFormRenders(): void {
        if (!elgg_view_exists('forms/geo/postal_address')) {
            $this->markTestSkipped('forms/geo/postal_address view not registered');
            return;
        }
        $output = elgg_view('forms/geo/postal_address', [
            'prefix' => 'address',
            'value'  => [
                'street_address'   => '10 Downing St',
                'extended_address' => '',
                'locality'         => 'London',
                'region'           => '',
                'postal_code'      => 'SW1A 2AA',
                'country'          => 'GB',
            ],
        ]);
        $this->assertIsString($output);
        $this->assertStringContainsString('address[', $output);
    }

    public function testSettingsViewRenders(): void {
        if (!elgg_view_exists('plugins/hypeGeo/settings')) {
            $this->markTestSkipped('plugins/hypeGeo/settings view not registered');
            return;
        }
        $plugin = elgg_get_plugin_from_id('hypeGeo');
        if (!$plugin) {
            $this->markTestSkipped('hypeGeo plugin entity not in test DB');
            return;
        }
        $output = elgg_view('plugins/hypeGeo/settings', ['entity' => $plugin]);
        $this->assertIsString($output);
    }
}
