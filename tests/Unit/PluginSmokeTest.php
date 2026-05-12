<?php

namespace hypeJunction\Geo\Tests\Unit;

use PHPUnit\Framework\TestCase;

class PluginSmokeTest extends TestCase {

    public function testPluginDirectoryExists(): void {
        $this->assertDirectoryExists(__DIR__ . '/../..');
    }

    public function testElggPluginFileExists(): void {
        $this->assertFileExists(__DIR__ . '/../../elgg-plugin.php');
    }
}
