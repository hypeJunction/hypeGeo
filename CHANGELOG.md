# Changelog

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
