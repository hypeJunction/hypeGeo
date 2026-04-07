<?php

$db = elgg()->db;

$table_exists = false;
try {
	$result = $db->getData("SHOW TABLES LIKE '{$db->prefix}entity_geometry'");
	$table_exists = !empty($result);
} catch (\Throwable $e) {
}

if (!$table_exists) {
	set_time_limit(0);
	$sql = file_get_contents(__DIR__ . '/sql/create_table.sql');
	$sql = str_replace('prefix_', $db->prefix, $sql);
	// Strip comments
	$sql = preg_replace('/^--.*$/m', '', $sql);
	$sql = trim($sql);
	if ($sql) {
		// Remove trailing semicolon for execute compatibility
		$sql = rtrim($sql, ';');
		$db->getData($sql);
	}
	elgg_add_admin_notice("geo:create_table", "MySQL table for storing entity geometry with the name '{$db->prefix}entity_geometry' has been created");
}
