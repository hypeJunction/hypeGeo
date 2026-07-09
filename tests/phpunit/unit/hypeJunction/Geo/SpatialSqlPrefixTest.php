<?php

declare(strict_types=1);

namespace hypeJunction\Geo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Call-site-complete guard for the MySQL-8 spatial-constructor rewrite in
 * lib/functions.php (hypeGeo 2.x -> 7.x migration, ref 471749e).
 *
 * The sed that rewrote the legacy GeomFromText()/GLength() calls to their
 * MySQL-8 ST_-prefixed names double-applied the prefix on the two proximity
 * read-path builders, emitting the invalid identifier `ST_ST_GeomFromText`:
 *
 *   - add_order_by_proximity_clauses()  (selects[] — get_entities_by_proximity)
 *   - add_distance_constraint_clauses() (wheres[]  — radius search)
 *
 * Either one fatals at query time ("FUNCTION ST_ST_GeomFromText does not
 * exist"), so proximity search is dead until fixed. The write path
 * set_entity_coordinates() was rewritten correctly (single `ST_GeomFromText`).
 *
 * DistanceHelpersTest / MigrationFixesTest cover the two read-path *builders*
 * by executing them; this static guard adds two things they lack:
 *   1. It also pins the WRITE path (set_entity_coordinates INSERT + ON DUPLICATE
 *      KEY UPDATE) so an over-eager bulk "fix" cannot double-prefix that one.
 *   2. It runs without booting Elgg / opening a DB, so plain CI catches the
 *      double-prefix even where the \Elgg\UnitTestCase suite can't run.
 *
 * MySQL 8.0 exposes ST_GeomFromText() and ST_Distance(); there is no
 * ST_ST_GeomFromText(). A double prefix is ALWAYS a bug.
 */
final class SpatialSqlPrefixTest extends TestCase {

	private static function pluginRoot(): string {
		// tests/phpunit/unit/hypeJunction/Geo -> plugin root
		return \dirname(__DIR__, 5);
	}

	private static function functionsSource(): string {
		$file = self::pluginRoot() . '/lib/functions.php';
		self::assertFileExists($file, 'lib/functions.php must exist');

		return (string) file_get_contents($file);
	}

	/**
	 * No GeomFromText call anywhere in lib/functions.php may carry the doubled
	 * `ST_ST_` prefix. Reports the exact 1-indexed line of every offender so a
	 * bulk remediation pass gets an actionable failure.
	 */
	public function testNoDoublePrefixedSpatialConstructor(): void {
		$src = self::functionsSource();

		$offenders = [];
		foreach (explode("\n", $src) as $i => $line) {
			// Match the constructor with an optional extra ST_ prefix so we can
			// distinguish the doubled form from the correct single form.
			if (preg_match_all('/ST_(ST_)?GeomFromText\s*\(/', $line, $m, PREG_SET_ORDER)) {
				foreach ($m as $match) {
					if (($match[1] ?? '') !== '') {
						$offenders[] = sprintf(
							'lib/functions.php:%d — %s (no such MySQL 8 function; fatals at query time)',
							$i + 1,
							rtrim($match[0], " \t(")
						);
					}
				}
			}
		}

		$this->assertSame(
			[],
			$offenders,
			"Double-prefixed spatial constructor(s) — proximity search fatals until fixed:\n"
				. implode("\n", $offenders)
		);
	}

	/**
	 * Every spatial-point constructor in the file must be the canonical
	 * single-prefix `ST_GeomFromText`. There are four call sites after the
	 * migration: proximity select, distance where, and the INSERT +
	 * ON DUPLICATE KEY UPDATE of set_entity_coordinates(). All four must match.
	 */
	public function testAllSpatialConstructorsUseCanonicalName(): void {
		$src = self::functionsSource();

		$total = preg_match_all('/ST_(?:ST_)?GeomFromText\s*\(/', $src);
		$good = preg_match_all('/(?<!ST_)ST_GeomFromText\s*\(/', $src);

		$this->assertGreaterThanOrEqual(
			4,
			$total,
			'expected the four migrated ST_GeomFromText call sites in lib/functions.php'
		);
		$this->assertSame(
			$total,
			$good,
			'every ST_GeomFromText call site must use the single canonical prefix (no ST_ST_)'
		);
	}

	/**
	 * The two proximity read-path clauses must both use the MySQL-8 distance
	 * function ST_Distance() (legacy GLength() was removed in MySQL 8).
	 */
	public function testProximityClausesUseStDistance(): void {
		$src = self::functionsSource();

		$this->assertSame(
			2,
			preg_match_all('/ST_Distance\s*\(/', $src),
			'both proximity clauses (selects[] + wheres[]) must call ST_Distance()'
		);
		$this->assertDoesNotMatchRegularExpression(
			'/(?<![\w_])GLength\s*\(/',
			$src,
			'GLength() was removed in MySQL 8 — use ST_Distance()'
		);
	}
}
