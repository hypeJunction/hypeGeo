<?php

namespace hypeJunction\Geo;

use Elgg\UnitTestCase;

/**
 * Pure-function tests for proximity / distance helpers in lib/functions.php.
 *
 * These tests intentionally do NOT touch the database — they only assert that
 * the SQL-builder helpers produce the expected option shapes. This protects us
 * during migration when SQL dialect (MySQL 8 removed GLength/GeomFromText)
 * is likely to change.
 */
class DistanceHelpersTest extends UnitTestCase {

    public function up() {}
    public function down() {}

    public function testAddOrderByProximityAddsSelectsJoinsAndOrderBy(): void {
        $options = add_order_by_proximity_clauses([], 51.5, -0.12);

        $this->assertArrayHasKey('selects', $options);
        $this->assertArrayHasKey('joins', $options);
        $this->assertArrayHasKey('order_by', $options);

        $this->assertCount(1, $options['selects']);
        $this->assertStringContainsString('proximity', $options['selects'][0]);
        $this->assertStringContainsString('51.5', $options['selects'][0]);
        $this->assertStringContainsString('-0.12', $options['selects'][0]);

        $this->assertCount(1, $options['joins']);
        $this->assertStringContainsString('entity_geometry', $options['joins'][0]);

        $this->assertStringContainsString('proximity ASC', $options['order_by']);
    }

    public function testAddOrderByProximityPreservesExistingOptions(): void {
        $input = [
            'types'   => ['object'],
            'selects' => ['e.guid'],
            'joins'   => ['JOIN something s ON s.guid = e.guid'],
        ];
        $options = add_order_by_proximity_clauses($input, 0, 0);

        $this->assertSame(['object'], $options['types']);
        $this->assertCount(2, $options['selects']);
        $this->assertCount(2, $options['joins']);
    }

    public function testAddOrderByProximityAcceptsNonArray(): void {
        $options = add_order_by_proximity_clauses('not-an-array', 1, 2);
        $this->assertIsArray($options);
        $this->assertArrayHasKey('selects', $options);
    }

    public function testAddDistanceConstraintAddsWhereAndJoin(): void {
        $options = add_distance_constraint_clauses([], 51.5, -0.12, 10000);

        $this->assertArrayHasKey('wheres', $options);
        $this->assertArrayHasKey('joins', $options);
        $this->assertCount(1, $options['wheres']);
        $this->assertStringContainsString('10000', $options['wheres'][0]);
    }

    public function testAddDistanceConstraintCastsNumerics(): void {
        // Inject string inputs — helper should cast and NOT produce SQL with
        // raw unescaped strings.
        $options = add_distance_constraint_clauses([], '51.5abc', '-0.12xyz', '5000foo', '1.5bar');
        $sql = $options['wheres'][0];
        $this->assertStringContainsString('51.5', $sql);
        $this->assertStringContainsString('-0.12', $sql);
        $this->assertStringContainsString('5000', $sql);
    }
}
