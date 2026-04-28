<?php

namespace hypeJunction\Geo\Upgrades;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * Creates the `entity_geometry` table used by hypeGeo for spatial queries.
 *
 * Idempotent — uses CREATE TABLE IF NOT EXISTS. Safe to run repeatedly on
 * fresh installs and on sites upgrading from hypeGeo 3.x where the table
 * was created via the legacy activate.php hook.
 */
class CreateEntityGeometryTable extends AsynchronousUpgrade
{
	public function getVersion(): int
	{
		return 2026041200;
	}

	public function needsIncrementOffset(): bool
	{
		return false;
	}

	public function shouldBeSkipped(): bool
	{
		return $this->tableExists();
	}

	public function countItems(): int
	{
		return $this->tableExists() ? 0 : 1;
	}

	public function run(Result $result, $offset): Result
	{
		$db = elgg()->db;
		$prefix = $db->prefix;

		try {
			$sql = "CREATE TABLE IF NOT EXISTS `{$prefix}entity_geometry` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`entity_guid` bigint(20) unsigned NOT NULL,
				`geometry` GEOMETRY NOT NULL SRID 0,
				PRIMARY KEY (`id`),
				UNIQUE KEY `entity_guid` (`entity_guid`),
				SPATIAL KEY `geometry_idx` (`geometry`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

			$db->getConnection('write')->executeStatement($sql);
			$result->addSuccesses(1);
		} catch (\Throwable $e) {
			$result->addError($e->getMessage());
			$result->addFailures(1);
		}

		return $result;
	}

	private function tableExists(): bool
	{
		try {
			$db = elgg()->db;
			$tableName = $db->prefix . 'entity_geometry';
			$rows = $db->getConnection('read')
				->executeQuery("SHOW TABLES LIKE ?", [$tableName])
				->fetchAllAssociative();
			return !empty($rows);
		} catch (\Throwable $e) {
			return false;
		}
	}
}
