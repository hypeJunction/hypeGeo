<?php

// Create a new table that will hold geometry data for entities
$prefix = elgg_get_config('dbprefix');
$tables = get_db_tables();

if (!in_array("{$prefix}entity_geometry", $tables)) {
	set_time_limit(0);

	run_sql_script(__DIR__ . '/sql/create_table.sql');
	elgg_add_admin_notice("geo:create_table", "MySQL table for storing entity geometry with the name '{$prefix}entity_geometry' has been created");

	// Populate geometry table with know information about entities
	$batch = new ElggBatch('elgg_get_entities_from_metadata', array(
		'metadata_name_value_pairs' => array(
			array('name' => 'geo:lat', 'value' => null, 'operand' => 'NOT NULL'),
			array('name' => 'geo:lat', 'value' => '0', 'operand' => '!='),
			array('name' => 'geo:long', 'value' => null, 'operand' => 'NOT NULL'),
			array('name' => 'geo:long', 'value' => '0', 'operand' => '!='),
		),
		'order_by' => 'e.guid ASC',
		'limit' => 0
	));

	$i = $k = 0;
	foreach ($batch as $b) {
		if (elgg_instanceof($b)) {
			$lat = $b->getLatitude();
			$long = $b->getLongitude();
			if ($lat && $long) {
				$query = "INSERT INTO {$prefix}entity_geometry (entity_guid, geometry)
							VALUES ({$b->guid}, GeomFromText('POINT({$lat} {$long})'))
							ON DUPLICATE KEY UPDATE geometry=GeomFromText('POINT({$lat} {$long})')";

				if (insert_data($query)) {
					$i++;
				}
			} else {
				$k++;
			}
		}
	}

	elgg_add_admin_notice("geo:import", "'{$prefix}entity_geometry' has been populated with information about the location of $i entities; geographic coordinates for $k entities were incorrect");
}