# hypeGeo — Architecture (Elgg 4.x)

## Summary

hypeGeo provides geocoding and proximity-search primitives for Elgg. It
geocodes entity `location` metadata into lat/long pairs, stores WKB
`GEOMETRY` data in a dedicated `entity_geometry` table, and exposes a
`proximity` search type.

## Directory layout

```
hypeGeo/
├── elgg-plugin.php                 # declarative config (hooks, events, bootstrap, upgrades)
├── composer.json                   # Elgg 4.x metadata + willdurand/geocoder
├── classes/hypeJunction/Geo/
│   ├── Bootstrap.php                # loads lib/functions.php + lib/hooks.php, ensures table
│   ├── ElggGeocoder.php             # geocoder wrapper
│   ├── ElggIPResolver.php           # IP-to-location lookup
│   ├── Countries.php                # static country data
│   └── Upgrades/
│       └── CreateEntityGeometryTable.php  # Elgg\Upgrade\AsynchronousUpgrade
├── lib/
│   ├── functions.php                # procedural API (proximity options, geopositioning)
│   └── hooks.php                    # hook/event handler implementations
├── views/default/
│   ├── css/framework/geo/
│   ├── forms/geo/…
│   ├── input/location.php
│   └── output/geo/location.php
├── sql/create_table.sql             # legacy, superseded by Upgrades\CreateEntityGeometryTable
└── tests/                            # PHPUnit + Playwright pre-migration suite
```

## Registered handlers (elgg-plugin.php)

| Kind  | Name          | Type       | Handler |
|-------|---------------|------------|---------|
| hook  | `geocode`     | `location` | `hypeJunction\Geo\geocode_location` |
| hook  | `search_types`| `get_types`| `hypeJunction\Geo\search_custom_types` |
| hook  | `search`      | `proximity`| `hypeJunction\Geo\search_by_proximity_hook` |
| event | `all`         | `metadata` | `hypeJunction\Geo\geocode_location_metadata` |

`view_extensions`: `elgg.css` ← `css/framework/geo/css`.

`bootstrap`: `hypeJunction\Geo\Bootstrap` — loads `lib/functions.php` and
`lib/hooks.php` so the procedural handlers referenced above resolve, and
includes `vendors/autoload.php` when `willdurand/geocoder` is vendored.

## Data migration

The plugin owns a custom MySQL table `{prefix}entity_geometry` holding a
`GEOMETRY` column used by `add_order_by_proximity_clauses()` and
`add_distance_constraint_clauses()`. Table creation is now handled by
`hypeJunction\Geo\Upgrades\CreateEntityGeometryTable` (an
`Elgg\Upgrade\AsynchronousUpgrade`) instead of the legacy `activate.php`
hook. The upgrade is idempotent — `shouldBeSkipped()` returns true when
the table exists, so it is safe on fresh installs and on sites upgrading
from hypeGeo 3.x where the table was created by `activate.php`.

## Dependencies

Only `elgg/elgg ^4.0`, `composer/installers ^2.0`, and
`willdurand/geocoder ^4.0`. The legacy `treffynnon/navigator` package was
dropped during migration — `lib/functions.php::get_distance()` now relies
on `willdurand/geocoder` utilities and local haversine math.

## Migration notes (3.x → 4.x)

- Deleted `start.php`, `activate.php`, `manifest.xml`.
- Added `elgg-plugin.php` generated from the former `start.php`
  registrations, with handler names expanded to fully-qualified strings
  (PHP `__NAMESPACE__` cannot resolve inside a returned array literal).
- Added `Bootstrap` class to load the procedural lib files that hold
  the handler implementations — avoids closures-in-config (Iron Law 5).
- Migrated `ElggEntity::getLocation()` → `$entity->location` (method
  removed in 4.0).
- `composer.json` rewritten: name lowercased (`hypejunction/hypegeo`),
  PHP bumped to `>=7.4`, added `config.allow-plugins.composer/installers`,
  removed invalid `repository` scalar key and non-standard
  `vendor-dir: vendors`. The malformed schema was previously blocking
  `composer audit`.
- The directory on disk is still `hypeGeo`; Elgg 4.x requires the mod
  directory to match the lowercase plugin id (`hypegeo`). Consumers that
  symlink into `mod/` should symlink as `hypegeo`.

## Known warnings (not fixed)

Security sweep reports seven `sql-injection` warnings in `lib/functions.php`
(`proximity` SQL builder) and `Upgrades/CreateEntityGeometryTable.php`.
All interpolated values are either `$db->prefix` (internal), cast floats,
or cast integer GUIDs — safe but visible to the heuristic scanner. The
`proximity` query intentionally uses spatial functions not expressible
via QueryBuilder in 4.x. Revisit in the 4→5 step when QueryBuilder gains
raw-expression helpers.
