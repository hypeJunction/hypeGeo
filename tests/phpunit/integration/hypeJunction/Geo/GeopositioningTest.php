<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

class GeopositioningTest extends IntegrationTestCase {

    public function up() {}
    public function down() {
        unset($_SESSION['geopositioning']);
    }

    public function getPluginID(): string {
        return '';
    }

    public function testSetGeopositioningStoresInSession(): void {
        set_geopositioning('London, UK', 51.5074, -0.1278);
        $this->assertArrayHasKey('geopositioning', $_SESSION);
        $this->assertEquals('London, UK', $_SESSION['geopositioning']['location']);
        $this->assertEquals(51.5074, $_SESSION['geopositioning']['latitude']);
        $this->assertEquals(-0.1278, $_SESSION['geopositioning']['longitude']);
    }

    public function testGetGeopositioningReturnsSessionValue(): void {
        $_SESSION['geopositioning'] = [
            'location' => 'Paris, FR',
            'latitude' => 48.8566,
            'longitude' => 2.3522,
        ];
        $result = get_geopositioning();
        $this->assertEquals('Paris, FR', $result['location']);
        $this->assertEquals(48.8566, $result['latitude']);
        $this->assertEquals(2.3522, $result['longitude']);
    }

    public function testGetGeopositioningForAnonymousReturnsEmptyDefaults(): void {
        unset($_SESSION['geopositioning']);
        $remote = $_SERVER['REMOTE_ADDR'] ?? null;
        $_SERVER['REMOTE_ADDR'] = '';

        // Log out
        _elgg_services()->session_manager->removeLoggedInUser();

        $result = get_geopositioning();
        $this->assertIsArray($result);
        $this->assertArrayHasKey('location', $result);
        $this->assertArrayHasKey('latitude', $result);
        $this->assertArrayHasKey('longitude', $result);

        if ($remote !== null) {
            $_SERVER['REMOTE_ADDR'] = $remote;
        }
    }
}
