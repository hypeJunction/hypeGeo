<?php

$dbprefix = elgg_get_config('dbprefix');

$table_exists = false;
try {
	$result = get_data("SHOW TABLES LIKE '{$dbprefix}entity_geometry'");
	$table_exists = !empty($result);
} catch (\Throwable $e) {
}

if (!$table_exists) {
	set_time_limit(0);
	$sql = file_get_contents(__DIR__ . '/sql/create_table.sql');
	$sql = str_replace('prefix_', $dbprefix, $sql);
	// Strip comments
	$sql = preg_replace('/^--.*$/m', '', $sql);
	$sql = trim($sql);
	if ($sql) {
		// Remove trailing semicolon for execute_delayed_query compatibility
		$sql = rtrim($sql, ';');
		get_data($sql);
	}
	elgg_add_admin_notice("geo:create_table", "MySQL table for storing entity geometry with the name '{$dbprefix}entity_geometry' has been created");
}
