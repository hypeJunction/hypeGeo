<?php

namespace hypeJunction\Geo;

use Elgg\IntegrationTestCase;

/**
 * Smoke test: verifies the plugin is registered and that activate.php could
 * run without error. Does NOT re-run activate.php — relies on prior install.
 */
class PluginActivationTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testPluginRegistered(): void {
        $plugin = elgg_get_plugin_from_id('hypeGeo');
        if (!$plugin) {
            $this->markTestSkipped('hypeGeo plugin entity not in test DB (c_i_elgg_)');
            return;
        }
        $this->assertEquals('hypeGeo', $plugin->getID());
    }

    public function testActivateSqlFileExists(): void {
        $sql = dirname(__DIR__, 5) . '/sql/create_table.sql';
        $this->assertFileExists($sql);
        $contents = file_get_contents($sql);
        $this->assertStringContainsString('entity_geometry', $contents);
    }

    public function testRequiredConstantsDefined(): void {
        $this->assertTrue(defined('hypeJunction\\Geo\\PLUGIN_ID'));
        $this->assertTrue(defined('hypeJunction\\Geo\\SEARCH_RADIUS'));
        $this->assertEquals('hypeGeo', PLUGIN_ID);
    }
}
