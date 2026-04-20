<?php
/**
 * PHPUnit bootstrap for hypeGeo plugin tests (Elgg 3.x).
 * Plugin must be installed at {elgg_root}/mod/hypeGeo/
 */

// tests/ -> mod/hypeGeo/ -> mod/ -> elgg_root/
$elggRoot = dirname(dirname(dirname(__DIR__)));

require_once $elggRoot . '/vendor/autoload.php';

// Load Elgg test classes (UnitTestCase, IntegrationTestCase, etc.)
$testClassesDir = $elggRoot . '/vendor/elgg/elgg/engine/tests/classes';
spl_autoload_register(function ($class) use ($testClassesDir) {
    $file = $testClassesDir . '/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load plugin composer autoloader if present.
// NOTE: bead 3t4t — composer install frequently fails on this plugin's legacy
// willdurand/geocoder + treffynnon/navigator deps, so we must NOT assume
// vendors/autoload.php exists. Register a PSR-0 fallback for the plugin's own
// classes instead.
$pluginRoot = dirname(__DIR__);
if (file_exists($pluginRoot . '/vendors/autoload.php')) {
    require_once $pluginRoot . '/vendors/autoload.php';
}

spl_autoload_register(function ($class) use ($pluginRoot) {
    $prefix = 'hypeJunction\\Geo\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = $pluginRoot . '/classes/hypeJunction/Geo/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Declare the constants the plugin's procedural code references, in case
// start.php never ran (plugin not active in the c_i_elgg_ test DB).
if (!defined('hypeJunction\\Geo\\PLUGIN_ID')) {
    define('hypeJunction\\Geo\\PLUGIN_ID', 'hypegeo');
}
if (!defined('hypeJunction\\Geo\\SEARCH_RADIUS')) {
    define('hypeJunction\\Geo\\SEARCH_RADIUS', 1000000);
}
if (!defined('HYPEGEO_METRIC_SYSTEM')) {
    define('HYPEGEO_METRIC_SYSTEM', 'SI');
}

// Load procedural libs. These reference functions from the Countries + Geocoder
// classes (via the PSR-0 loader above) and Elgg core (loaded below), so they
// must be loaded AFTER autoloaders are registered but BEFORE loadCore() is OK
// because they only declare functions.
$libFunctions = $pluginRoot . '/lib/functions.php';
$libHooks     = $pluginRoot . '/lib/hooks.php';
if (file_exists($libFunctions) && !function_exists('hypeJunction\\Geo\\get_distance')) {
    require_once $libFunctions;
}
if (file_exists($libHooks) && !function_exists('hypeJunction\\Geo\\geocode_location')) {
    require_once $libHooks;
}

\Elgg\Application::loadCore();
