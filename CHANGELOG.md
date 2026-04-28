# Changelog

## 5.0.0 — Elgg 5.x compatibility

### Breaking
- Requires Elgg `^5.0` and PHP `>=8.2`.
- Hook handler functions in `lib/hooks.php` now receive `\Elgg\Event $event`
  instead of the old four-argument `($hook, $type, $return, $params)` signature.
- MySQL 8.0 minimum. Legacy spatial functions (`GeomFromText`, `GLength`,
  `LineStringFromWKB`) replaced with `ST_GeomFromText` / `ST_Distance`.
- `entity_geometry` table switched from `ENGINE=MyISAM` to `ENGINE=InnoDB`
  with explicit `SRID 0` column. Existing installs need to re-run the upgrade.
- `Elgg\Upgrade\AsynchronousUpgrade` changed to abstract class in Elgg 5.x;
  `CreateEntityGeometryTable` now extends it instead of implementing it.
- Raw-string `getData()` / `insertData()` / `deleteData()` API removed in
  Elgg 5.x. All low-level SQL now uses `getConnection()->executeQuery/Statement`.

### Fixed
- `Countries::registerTranslations()`: `add_translation()` removed in Elgg 5.x;
  now calls `elgg()->translator->addTranslation()` directly.
- `views/default/forms/geo/postal_address.php`: `$label_attrs` is now initialized
  to `''` before the conditional to avoid PHP 8.2 undefined-variable warning.

## 4.0.0 — Elgg 4.x compatibility

### Breaking
- Requires Elgg `^4.0` and PHP `>=7.4`.
- Plugin id is now lowercase `hypegeo`. When installed via composer or
  symlinked into `mod/`, the directory name MUST be `hypegeo` to match
  the `composer.json` `name` field. Downstream projects using
  case-sensitive symlinks need to update them.
- `manifest.xml`, `start.php`, and `activate.php` were removed. All
  registrations now live in `elgg-plugin.php` and the
  `hypeJunction\Geo\Bootstrap` class.
- Dropped the `treffynnon/navigator` dependency.

### Added
- `hypeJunction\Geo\Upgrades\CreateEntityGeometryTable` — an
  `Elgg\Upgrade\AsynchronousUpgrade` that idempotently creates the
  `entity_geometry` MySQL table. Replaces the legacy `activate.php`
  table bootstrap and works on both fresh installs and sites upgrading
  from hypeGeo 3.x.
- `hypeJunction\Geo\Bootstrap` — declarative plugin bootstrap that loads
  the procedural `lib/` files containing hook/event handlers.
- `ARCHITECTURE.md` documenting the 4.x plugin layout.

### Fixed
- `composer.json` schema: removed invalid `repository` scalar key and
  non-standard `vendor-dir: vendors`, added `config.allow-plugins` block.
  The previous schema blocked `composer audit`.
- `ElggEntity::getLocation()` (removed in 4.0) → `$entity->location`
  property access in `lib/functions.php`, `lib/hooks.php`, and
  `views/default/output/geo/location.php`.
