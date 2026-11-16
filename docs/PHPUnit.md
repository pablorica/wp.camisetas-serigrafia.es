# PHPUnit tests for our plugins

How the plugins in this repo are tested, using **CPT Easy** as the working example
(`wordpress/wp-content/plugins/cpt-easy/`), and how to add the same setup to another plugin.

- [The idea: two suites](#the-idea-two-suites)
- [Files](#files)
- [Running the tests](#running-the-tests)
- [Unit tests](#unit-tests)
- [Integration tests](#integration-tests)
- [Writing good tests](#writing-good-tests)
- [Adding tests to another plugin](#adding-tests-to-another-plugin)
- [Templates](#templates)
- [Pitfalls we hit](#pitfalls-we-hit)
- [Deploy](#deploy)

---

## The idea: two suites

| | Unit | Integration |
|---|---|---|
| What | Pure logic: calculations, parsing, validation | The plugin working inside the real site |
| WordPress | Not loaded | The local site, with WooCommerce and **all** active plugins and the theme |
| Database | None | The local database, inside a transaction that is **rolled back** |
| Speed | Milliseconds | Seconds |
| Runs in | `composer` container (PHP only) | `wpcli` container (has the database and the `.env`) |
| Config | `phpunit.xml.dist` | `phpunit-integration.xml.dist` |

Why the integration suite uses the real local site instead of the official WordPress test suite
(`wp scaffold plugin-tests` + a separate test database): it needs no extra infrastructure, and it
tests what will actually run in production — other plugins' hooks included (e.g. CG Woo Grid / Sadoje
price filters, Cartify, ACF). The price is that it depends on the local data (products, tax rates),
so those tests read their expectations from the site (e.g. the product tiers) instead of hard-coding them.

## Files

```
wp-content/plugins/<plugin>/
├── composer.json                 # require-dev phpunit/phpunit + "test" scripts
├── phpunit.xml.dist              # unit suite
├── phpunit-integration.xml.dist  # integration suite
├── tests/
│   ├── bootstrap-unit.php        # loads only the classes under test (no WordPress)
│   ├── bootstrap-integration.php # loads wp-load.php, refuses production
│   ├── Unit/
│   │   └── PricingTest.php
│   └── Integration/
│       └── OrderPricingTest.php
└── vendor/                       # composer install (git-ignored, never deployed)
```

In `.gitignore` (root), next to the other plugins:

```gitignore
wordpress/wp-content/plugins/<plugin>/vendor
wordpress/wp-content/plugins/<plugin>/composer.lock
wordpress/wp-content/plugins/<plugin>/.phpunit.cache
wordpress/wp-content/plugins/<plugin>/.phpunit.result.cache
```

## Running the tests

```bash
P=/var/www/html/wp-content/plugins/cpt-easy

# once, and after composer.json changes
docker compose run --rm -w $P composer install

# unit
docker compose run --rm -w $P composer test

# integration (wpcli container: database + .env)
docker compose run --rm -w $P --entrypoint php wpcli vendor/bin/phpunit -c phpunit-integration.xml.dist

# one test only
docker compose run --rm -w $P composer test -- --filter test_totals_with_vat
```

Why two containers: `composer` has PHP 8.3 (same as production) but no database variables; `wpcli`
has the database, `wp-config.php`, `APP_ENV` and the 1024M `memory_limit` from `uploads.ini`.

## Unit tests

**What goes here**: anything that can be a pure function — input in, output out, no WordPress calls.
In CPT Easy: `CPT_Easy_Pricing::price_at()`, `totals()`, `parse()`, `normalise()`, `default_tiers()`.

**The bootstrap** defines `ABSPATH` (our files start with `defined( 'ABSPATH' ) || exit;`) and
requires only the class files under test:

```php
define( 'ABSPATH', __DIR__ . '/' );
require dirname( __DIR__ ) . '/includes/class-cpt-easy-pricing.php';
```

If the class uses a couple of WordPress functions in methods you don't test, that's fine — PHP only
fails when a missing function is actually called. If a tested method needs one (`apply_filters`,
`wc_format_decimal`…), either move the logic into a pure function, or add a tiny stub in the bootstrap:

```php
if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) { return $value; }
}
```

**Make code testable by extracting pure functions.** Before the tests, CPT Easy computed prices inside
methods that read post meta. We split them:

```php
// Reads WordPress → not unit-testable
public static function unit_price( int $variation_id, int $qty ): ?float {
	return self::price_at( self::get_tiers( $variation_id ), $qty );
}

// Pure → unit-tested
public static function price_at( array $tiers, int $qty ): ?float { … }
public static function totals( float $unit, int $qty, float $vat_rate ): array { … }
```

Everything else (orders, the landing, the form) calls the pure functions, so testing them tests the maths
everywhere.

## Integration tests

**The bootstrap** loads the WordPress the plugin lives in and stops if it isn't safe:

```php
$wp_load = getenv( 'WP_LOAD' ) ?: dirname( __DIR__, 4 ) . '/wp-load.php'; // plugins/<plugin>/tests → root
if ( ( getenv( 'APP_ENV' ) ?: 'production' ) === 'production' ) { exit( 1 ); } // never against production
define( 'WP_USE_THEMES', false );
require $wp_load;
```

**Every test runs in a transaction** that is rolled back in `tearDown()`, so the database is left as it was:

```php
protected function setUp(): void {
	global $wpdb;
	$wpdb->query( 'START TRANSACTION' );
	add_filter( 'pre_wp_mail', '__return_true' ); // no emails
}

protected function tearDown(): void {
	global $wpdb;
	$wpdb->query( 'ROLLBACK' );
	remove_filter( 'pre_wp_mail', '__return_true' );
}
```

Notes on the transaction:

- Works with InnoDB tables (WordPress, WooCommerce and HPOS tables are InnoDB).
- `AUTO_INCREMENT` counters still move forward (order IDs skip numbers). Harmless.
- If a test dies halfway, MySQL rolls back when the connection closes.
- Anything that causes an implicit commit (`CREATE TABLE`, `ALTER TABLE`, plugin activation that creates
  tables) breaks the isolation. Don't activate plugins or run installers inside a test.
- Files written to disk (uploads) are **not** rolled back: pass URLs instead of real uploads, or delete
  the files in `tearDown()`.

**Read expectations from the site, not from memory.** The integration test compares the order with the
product tiers it reads (`CPT_Easy_Pricing::get_tiers()`), so it keeps passing when Sergio changes a price.
The unit test is the one that pins the landing prices (`default_tiers()`).

**Skip, don't fail, when the site isn't set up**:

```php
if ( ! CPT_Easy_Pricing::get_product() ) {
	$this->markTestSkipped( 'No CPT Easy product (run: wp cpt-easy install).' );
}
```

**Use guests unless the test is about accounts**: `wp_set_current_user( 0 )`, so nothing is written to a
real customer.

## Writing good tests

- **Test the boundaries**, not random values: for tiers starting at 10/25/50/100 test 9, 10, 24, 25, 49,
  50, 99, 100, and a big number.
- **Loop over combinations** with a message per case, so a failure says which one:
  ```php
  foreach ( $cases as $qty => $min ) {
  	$this->assertSame( $prices[ $min ], CPT_Easy_Pricing::price_at( $tiers, $qty ), "$quality / $print × $qty" );
  }
  ```
  (We use loops instead of `@dataProvider` / `#[DataProvider]` so the same tests run on PHPUnit 9, 10 and 11.)
- **Money**: compare exact values with `assertSame()` after rounding to cents in the code under test;
  for values read back from WooCommerce (strings) use `assertEqualsWithDelta( $expected, (float) $value, 0.001 )`.
- **Write down real examples** that someone checked by hand (e.g. *297,50 € → IVA 62,48 € → 359,98 €*, the
  80 × Premium 2 caras order = 769,56 €). They document the rules better than any comment.
- **Invariants over many inputs** catch rounding bugs: CPT Easy checks *net + VAT = total* for every
  quantity from 10 to 1000 of every list (≈12,000 assertions, still milliseconds).
- **One behaviour per test method**, named as a sentence: `test_no_price_below_the_minimum()`.
- **When a test finds a bug, keep the test.** The integration suite found that `CPT_Easy_Order::create()`
  returned an order without the line meta in memory; the test now guards it.

## Adding tests to another plugin

1. Copy `composer.json`, `phpunit.xml.dist`, `phpunit-integration.xml.dist` and `tests/` from CPT Easy
   (or the [templates](#templates) below) into the plugin.
2. Change the `name` in `composer.json` and the `require` lines in `tests/bootstrap-unit.php`.
3. In `tests/bootstrap-integration.php`, change the "is the plugin active?" check (`class_exists(…)`).
4. Add the `vendor/`, `composer.lock` and `.phpunit*` lines to the root `.gitignore`.
5. `composer install`, then write the first unit test for the most critical calculation.
6. Extract pure functions where the logic is mixed with WordPress calls.
7. Add an integration test for the main flow (e.g. "add to cart with these options → cart total").
8. Document the commands in the plugin's page in `docs/` and add the `rsync` excludes.

## Templates

### `composer.json`

```json
{
	"name": "codigo/<plugin>",
	"type": "wordpress-plugin",
	"license": "GPL-2.0-or-later",
	"require": {
		"php": ">=8.1"
	},
	"require-dev": {
		"phpunit/phpunit": "^11.5"
	},
	"scripts": {
		"test": "phpunit",
		"test:integration": "phpunit -c phpunit-integration.xml.dist"
	},
	"config": {
		"sort-packages": true
	}
}
```

### `phpunit.xml.dist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap-unit.php" colors="true" failOnWarning="true">
	<testsuites>
		<testsuite name="unit">
			<directory>tests/Unit</directory>
		</testsuite>
	</testsuites>
</phpunit>
```

### `phpunit-integration.xml.dist`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="tests/bootstrap-integration.php" colors="true" failOnWarning="true">
	<testsuites>
		<testsuite name="integration">
			<directory>tests/Integration</directory>
		</testsuite>
	</testsuites>
</phpunit>
```

### `tests/bootstrap-unit.php`

```php
<?php
define( 'ABSPATH', __DIR__ . '/' );

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require $autoload;
}

require dirname( __DIR__ ) . '/includes/class-<plugin>-<thing>.php';
```

### `tests/bootstrap-integration.php`

```php
<?php
$wp_load = getenv( 'WP_LOAD' ) ?: dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "wp-load.php not found at {$wp_load}\n" );
	exit( 1 );
}

$autoload = dirname( __DIR__ ) . '/vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require $autoload;
}

if ( ( getenv( 'APP_ENV' ) ?: 'production' ) === 'production' && ! getenv( 'ALLOW_PRODUCTION_TESTS' ) ) {
	fwrite( STDERR, "Refusing to run the integration tests with APP_ENV=production.\n" );
	exit( 1 );
}

define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';
require $wp_load;

if ( ! class_exists( 'WooCommerce' ) || ! class_exists( '<Main_Plugin_Class>' ) ) {
	fwrite( STDERR, "WooCommerce and <plugin> must be active.\n" );
	exit( 1 );
}
```

### `tests/Unit/ExampleTest.php`

```php
<?php
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase {

	public function test_price_changes_at_the_tier_boundary(): void {
		$tiers = array(
			array( 'min' => 10, 'price' => 8.50 ),
			array( 'min' => 25, 'price' => 6.95 ),
		);
		$this->assertNull( My_Pricing::price_at( $tiers, 9 ) );
		$this->assertSame( 8.50, My_Pricing::price_at( $tiers, 24 ) );
		$this->assertSame( 6.95, My_Pricing::price_at( $tiers, 25 ) );
	}
}
```

### `tests/Integration/ExampleTest.php`

```php
<?php
use PHPUnit\Framework\TestCase;

final class ExampleTest extends TestCase {

	protected function setUp(): void {
		global $wpdb;
		$wpdb->query( 'START TRANSACTION' );
		wp_set_current_user( 0 );
		add_filter( 'pre_wp_mail', '__return_true' );
	}

	protected function tearDown(): void {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
		remove_filter( 'pre_wp_mail', '__return_true' );
	}

	public function test_an_order_charges_the_product_price(): void {
		$product = wc_get_product( wc_get_product_id_by_sku( 'MY-SKU' ) );
		if ( ! $product ) {
			$this->markTestSkipped( 'Product MY-SKU not found.' );
		}

		$order = wc_create_order();
		$order->add_product( $product, 3 );
		$order->calculate_totals();

		$this->assertEqualsWithDelta( 3 * (float) $product->get_price(), (float) $order->get_subtotal(), 0.001 );
	}
}
```

## Pitfalls we hit

| Problem | Cause | Fix |
|---|---|---|
| `wp-load.php not found` when the plugin folder is a symlink | `__DIR__` resolves the symlink, so `dirname( __DIR__, 4 )` isn't the WordPress root | `WP_LOAD=/path/to/wp-load.php` (the bootstrap reads it). Not needed in Docker. |
| Line meta (colour, sizes…) missing on the order object | `$order->get_item( $id )` loads a **separate copy** from the database; meta added to it isn't on `$order` | Use the order's own object: `$order->get_items()[ $id ]` |
| WP-CLI / PHPUnit runs out of memory | CLI PHP defaults to 128M; the site with all plugins needs more | `wpcli` mounts `uploads.ini` (1024M) |
| Data providers work on one PHPUnit version and not another | PHPUnit 9 reads `@dataProvider`, 11 prefers `#[DataProvider]`, 12 only attributes | Loop inside the test with a message per case |
| `Class "XyzTest" not found` (PHPUnit 10+) | The file name must match the class name | `PricingTest.php` → `final class PricingTest` |
| Totals off by one cent between the form and the order | JS float maths vs PHP `round()` | Round the VAT to cents from the net: `round( net × rate / 100, 2 )` in PHP, `Math.round( net × rate ) / 100` in JS; unit-test `totals()` with real examples |

## Deploy

`vendor/`, `tests/`, `composer.json`, `composer.lock` and `phpunit*.xml.dist` are development files.
Don't copy them to production — PHPUnit doesn't belong on a public web server:

```bash
rsync … \
  --exclude 'wp-content/plugins/*/vendor' \
  --exclude 'wp-content/plugins/*/tests' \
  --exclude 'wp-content/plugins/*/phpunit*.xml.dist' \
  …
```

If a plugin needs runtime Composer packages in production, install them there with
`composer install --no-dev` instead of copying the local `vendor/`.
