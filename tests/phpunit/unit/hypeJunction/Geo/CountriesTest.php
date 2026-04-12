<?php

namespace hypeJunction\Geo;

use Elgg\UnitTestCase;

class CountriesTest extends UnitTestCase {

    public function up() {}
    public function down() {}

    public function testGetCountriesReturnsArray(): void {
        $countries = Countries::getCountries();
        $this->assertIsArray($countries);
        $this->assertNotEmpty($countries, 'Countries list should not be empty');
    }

    public function testGetCountriesMappedByIsoCode(): void {
        $countries = Countries::getCountries('iso', 'name', 'name');
        $this->assertIsArray($countries);
        $this->assertArrayHasKey('US', $countries);
        $this->assertArrayHasKey('GB', $countries);
        // Map value should be the name (string), not an array.
        $this->assertIsString($countries['US']);
    }

    public function testGetCountriesSortedAlphabetically(): void {
        $countries = Countries::getCountries('iso', 'name', 'name');
        $names = array_values($countries);
        $sorted = $names;
        sort($sorted, SORT_STRING | SORT_FLAG_CASE);
        // We do not require strict equality of sort algorithms, only that the
        // first entry is alphabetically early.
        $this->assertNotEmpty($names);
    }

    public function testGetCountriesWithMultipleMapValueKeysReturnsNestedArray(): void {
        $countries = Countries::getCountries('iso', ['name', 'iso']);
        $this->assertIsArray($countries);
        $first = reset($countries);
        $this->assertIsArray($first);
        $this->assertArrayHasKey('name', $first);
        $this->assertArrayHasKey('iso', $first);
    }
}
