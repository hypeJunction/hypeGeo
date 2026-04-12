<?php

namespace hypeJunction\Geo;

use Elgg\UnitTestCase;

/**
 * Tests for get_distance() which uses the treffynnon/navigator library via
 * composer. If vendors/autoload.php was not installed (bead 3t4t), these
 * tests are skipped rather than fataling.
 */
class GetDistanceTest extends UnitTestCase {

    public function up() {}
    public function down() {}

    protected function skipIfNavigatorMissing(): void {
        if (!class_exists(\Treffynnon\Navigator\LatLong::class)) {
            $this->markTestSkipped('treffynnon/navigator not installed (see bead 3t4t)');
        }
    }

    public function testDistanceBetweenIdenticalPointsIsZero(): void {
        $this->skipIfNavigatorMissing();
        $d = get_distance(51.5, -0.12, 51.5, -0.12);
        $this->assertEqualsWithDelta(0.0, (float) $d, 0.001);
    }

    public function testDistanceBetweenLondonAndParisRoughly344km(): void {
        $this->skipIfNavigatorMissing();
        // London ~51.5074,-0.1278  Paris ~48.8566,2.3522
        $metres = (float) get_distance(51.5074, -0.1278, 48.8566, 2.3522);
        // Expect ~344 km great-circle, allow +/- 20 km slack for library rounding.
        $this->assertGreaterThan(320000, $metres);
        $this->assertLessThan(370000, $metres);
    }
}
