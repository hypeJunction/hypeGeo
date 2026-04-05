<?php

// Create a new table that will hold geometry data for entities
$prefix = elgg_get_config('dbprefix');

// get_db_tables() removed in 3.x — use direct SQL check
$table_exists = false;
try {
    $result = get_data("SHOW TABLES LIKE '{$prefix}entity_geometry'");
    $table_exists = !empty($result);
} catch (\Throwable $e) {
    // Table check failed — assume it doesn't exist
}

if (!$table_exists) {
    set_time_limit(0);
    run_sql_script(__DIR__ . '/sql/create_table.sql');
    elgg_add_admin_notice("geo:create_table", "MySQL table for storing entity geometry with the name '{$prefix}entity_geometry' has been created");
}
