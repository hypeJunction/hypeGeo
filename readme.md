Elgg Geo Tools
================

Various tools intended to simplify location-aware development

[My phone bill is due!](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=P7QA9CFMENBKA)


## Dependencies

* If downloading from github, install composer dependencies by running ```composer install```


## MySQL Spatial Extentions

* The plugin optimizes the storage of geographical coordinates by adding a
geometry-enabled ```prefix_entity_geometry``` table, with a unique entity_guid key.
Whenever ```location``` metadata is updated, the metadata value is geocoded, and
corresponding coordinates are stored in the geometry-enabled table. This allows
to reduce the overhead, necessary to sort entities by distance to a given
geographical point, or to select entities within certain bounds

* When you first activate the plugin, the script will iterate through all
entities that have ```geo:lat``` and ```geo:long``` metadata attached to them,
and populate the geometry table

* Default Elgg workflow for storing and updating metadata is left unchanged,
so it is safe to disable the plugin at any time without loosing any data


## Filtering

The plugins provides API for getting entities by proximity to a certain geographical point,
as well as convenience functions to adding necessary clauses to your ```elgg_get_entities_*```
options array;

For a sample implementation see, the search hooks.


## Location-based search

The plugin implements location-based search type, where entities within a given radius
are shown in the search results and ordered by proximity to the search query
if it's a geocodeable location


## Geocoding

* Geocoding is performed via multiple providers using PHP Geocoder library by William Durand
https://github.com/geocoder-php/Geocoder

* Configuration for individual providers is available in the plugin settings

* Geocoded addresses/locations will be cached in ```prefix_geocode_cache```


## Geographic Calculations

* Geographic calculations are  performed using Navigator class by Simon Holywell
http://simonholywell.com/projects/navigator/



## Views

The plugin adds several views, which make it easier to standardize location-aware UI:

### Form Views

* ```forms/geo/postal_address``` - standard postal address form (submits an array with a configurable name)
The action will receive an array with the following keys:
```street_address```,
```extended_address```,
```locality```,
```region```,
```postal_code```,
```country_code```


### Input Views

* ```input/geo/location``` - location input with an autocomplete (from existing location metadata)
elgg_tokeninput input is required for autocomplete https://github.com/hypeJunction/elgg_tokeninput

* ```input/geo/country``` - country selector (passes ISO codes as option values)

### Output Views

* ```output/geo/location```
* ```output/geo/country```


## Screenshots

![alt text](https://raw.github.com/hypeJunction/hypeGeo/master/screenshots/search.png "Search Results")
![alt text](https://raw.github.com/hypeJunction/hypeGeo/master/screenshots/form.png "Form")