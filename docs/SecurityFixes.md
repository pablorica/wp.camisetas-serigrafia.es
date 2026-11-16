# Security fixes — sadoje and cg-woo-grid REST API

**Status**: open · found 2026-11-16 (deploy of v1.2.0, WP-CLI notices about `permission_callback`) ·
**Priority**: 1 is urgent, the rest soon.

Both custom plugins register REST routes **without `permission_callback`**, so anyone on the internet can call
them without logging in. Two of them are dangerous.

| # | Priority | Route (POST unless noted) | Problem |
|---|---|---|---|
| 1 | 🔴 Urgent | `/wp-json/customisations-for-cg-woo-grid/v1/encode` (sadoje) and `/wp-json/cg-woo-grid/v1/encode` (cg-woo-grid) | `maybe_unserialize()` on the request: **PHP Object Injection** |
| 2 | 🔴 Urgent | `/wp-json/customisations-for-cg-woo-grid/v1/upload-image` (sadoje) | **Anonymous file upload** to `uploads/` + attachments in the database |
| 3 | 🟠 Soon | `…/serialize` (both), `…/testing` (both, GET) | Public; `testing` is leftover test code |
| 4 | 🟡 Clean-up | `/cg-woo-grid/v1/totalbox/…` (GET), `/updatetotalbox`, `/attmatrix/…` (GET) | Meant to be public (product page), but should say so and validate their input |

The routes also answer at `/?rest_route=/…`, so any Nginx rule must cover both forms.

- [1. Object injection in /encode](#1-object-injection-in-encode)
- [2. Anonymous upload in /upload-image](#2-anonymous-upload-in-upload-image)
- [3. /serialize and /testing](#3-serialize-and-testing)
- [4. Public product-page routes](#4-public-product-page-routes)
- [Immediate mitigation: mu-plugin](#immediate-mitigation-mu-plugin)
- [Check whether it was used](#check-whether-it-was-used)
- [Checklist](#checklist)

---

## 1. Object injection in /encode

**Where**

| Plugin | Route registered | Vulnerable code |
|---|---|---|
| sadoje | `admin/customisations-for-cg-woo-grid-admin-rest-api.php` ~l. 247 | `jsonencode_serialized_string()` ~l. 40: `maybe_unserialize( $parameters['serialized'] )` |
| cg-woo-grid | `admin/class-cg-woo-grid-admin-rest-api.php` ~l. 140 | `jsonencode_serialized_string()` ~l. 74: same |

**Why it's dangerous**: `unserialize()` on data from a visitor lets them create objects of any class loaded in
WordPress (WooCommerce, ~20 plugins…). Their magic methods (`__wakeup`, `__destruct`…) run, and with the right
chain of classes ("POP chain") that can end in deleting files, SQL, or running code. It's a classic way in.

**Confirmed** on a test WordPress with both plugins: a serialized object posted to `/encode` was created and its
`__wakeup()` ran. Production runs the same code.

**Who uses it** — it can't just be closed, it's used by the shop:

| Caller | File | Data |
|---|---|---|
| Product page (public) | `sadoje/src/js/templates/Budgeteer.vue` (`buildBugeteer`) | `data-serialized` / `data-wording` printed by `sadoje/public/display/customisations-for-cg-woo-grid-display.php` |
| Admin (sadoje settings) | `sadoje/admin/src/js/vue-customisations-for-cg-woo-grid-admin.js` (several calls) | Settings read from options |
| Admin (CG Woo Grid) | `cg-woo-grid/admin/src/js/vueAdmin.js` | Settings |

The page prints PHP-serialized options into HTML and then asks the server to turn them into JSON. The server
already has the data, so the round trip isn't needed.

**Fix**

1. **Product page**: print JSON instead of serialized strings, and drop the `/encode` call.
   ```php
   // sadoje/public/display/customisations-for-cg-woo-grid-display.php
   data-rows="<?php echo esc_attr( wp_json_encode( maybe_unserialize( $serialized_option ) ) ); ?>"
   ```
   ```js
   // Budgeteer.vue
   this.rows = JSON.parse(element.parentElement.dataset.rows || '[]');
   ```
2. **Admin**: same idea (`wp_localize_script` / `wp_add_inline_script` with the decoded settings), or keep the route
   for admins only:
   ```php
   'permission_callback' => static fn() => current_user_can( 'manage_woocommerce' ),
   ```
   and send the REST nonce from the admin JS (without it, WordPress treats the request as logged out → 401):
   ```php
   wp_add_inline_script( $handle, 'window.SADOJE_REST_NONCE = ' . wp_json_encode( wp_create_nonce( 'wp_rest' ) ) . ';', 'before' );
   ```
   ```js
   axios.defaults.headers.common['X-WP-Nonce'] = window.SADOJE_REST_NONCE;
   ```
3. **Wherever** a stored serialized value still has to be decoded, never create objects:
   ```php
   $value = is_serialized( $s ) ? unserialize( trim( $s ), array( 'allowed_classes' => false ) ) : $s;
   ```

## 2. Anonymous upload in /upload-image

**Where**: sadoje, route ~l. 229; `cdg_process_image()` ~l. 175 → `cdg_upload_image()` ~l. 89.

**Problem**: anyone can POST a file. The only check is `str_contains( $files['file']['type'], 'image' )`, and that
type is **sent by the client**. `wp_upload_bits()` blocks PHP, but any other allowed type goes into
`uploads/YYYY/MM/` and creates an attachment: disk filling, spam/malware PDFs hosted on the client's domain, junk
in the media library. No JavaScript in the repo calls this route.

**Fix**: delete the route and both methods. If it's ever needed: `permission_callback` with `upload_files`,
nonce, and `wp_handle_upload()` with an explicit `mimes` list and the real-type check
(`wp_check_filetype_and_ext()`), like the CPT Easy form.

## 3. /serialize and /testing

- `…/serialize` (both plugins) — `maybe_serialize()` of the input. Not dangerous by itself, but public for no
  reason; only the admin JS uses it → `permission_callback` `manage_woocommerce` + nonce (as in 1.2).
- `…/testing` (both plugins, GET, returns "Howdy!!") — delete the route and `at_rest_testing_endpoint()`.

## 4. Public product-page routes

`cg-woo-grid/public/class-cg-woo-grid-public-rest-api.php`: `totalbox/product_id=…` (~l. 584), `updatetotalbox`
(~l. 599), `attmatrix/…` (~l. 616). The product page needs them for visitors, so they stay public, but:

- say it explicitly — `'permission_callback' => '__return_true'` (silences the notice, documents the intent);
- validate the input with `args` (`product_id` → `absint`, only published products; `taxonomy_x/y` only
  `pa_*` taxonomies that exist; `quantities` → array of non-negative integers with a size limit), so a visitor
  can't make them do heavy work or read products they shouldn't.

## Immediate mitigation: mu-plugin

Until the plugins are fixed, this must-use plugin closes 1 and 2 **without breaking the product page**: `/encode`
gives the same answer for normal data (arrays, strings), but objects arrive inert (`__PHP_Incomplete_Class`),
and `upload-image` / `testing` answer 404. Tested on a test WordPress with the real plugins.

`wordpress/wp-content/mu-plugins/cpt-rest-hotfix.php`:

```php
<?php
/**
 * Plugin Name: CPT · REST security hotfix (temporary)
 * Description: Safe /encode (no PHP objects), and no public upload-image / testing routes in sadoje and cg-woo-grid. Delete when both plugins are fixed (docs/SecurityFixes.md).
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'rest_pre_dispatch',
	static function ( $result, $server, $request ) {
		if ( null !== $result ) {
			return $result;
		}
		$route = untrailingslashit( $request->get_route() );
		if ( ! preg_match( '#^/(cg-woo-grid|customisations-for-cg-woo-grid)/v1/(encode|upload-image|testing)$#', $route, $m ) ) {
			return $result;
		}

		if ( 'encode' === $m[2] ) {
			// Same answer as the plugins' maybe_unserialize(), but objects can't be created.
			$s = $request->get_param( 'serialized' );
			if ( ! is_string( $s ) || '' === $s ) {
				return new WP_Error( 'jsonencode_invalid', 'No results', array( 'status' => 404 ) );
			}
			$value = is_serialized( $s ) ? @unserialize( trim( $s ), array( 'allowed_classes' => false ) ) : $s; // phpcs:ignore
			return rest_ensure_response( $value );
		}

		// upload-image and testing are not used by the site.
		return new WP_Error( 'rest_no_route', 'No route was found matching the URL and request method.', array( 'status' => 404 ) );
	},
	10,
	3
);
```

`rest_pre_dispatch` runs for both URL forms (`/wp-json/…` and `?rest_route=…`), before the plugin callbacks.

**Install**

1. Add it to git: the root `.gitignore` ignores `mu-plugins/*`, so whitelist it
   (`!wordpress/wp-content/mu-plugins/cpt-rest-hotfix.php`).
2. Copy it to production — mu-plugins load on their own, no activation:
   ```bash
   scp wordpress/wp-content/mu-plugins/cpt-rest-hotfix.php DigitalOcean10DesignRoot:/srv/docker/wordpress/wordpress/wp-content/mu-plugins/
   ssh DigitalOcean10DesignRoot 'chown www-data:www-data /srv/docker/wordpress/wordpress/wp-content/mu-plugins/cpt-rest-hotfix.php && chmod 644 /srv/docker/wordpress/wordpress/wp-content/mu-plugins/cpt-rest-hotfix.php'
   ```
   (Or add it to `bin/components.conf` as a new type; today the scripts only handle themes and plugins.)
3. Check:
   ```bash
   # 404 = closed
   curl -s -o /dev/null -w '%{http_code}\n' https://camisetasparatodos.es/wp-json/cg-woo-grid/v1/testing
   curl -s -o /dev/null -w '%{http_code}\n' -X POST https://camisetasparatodos.es/wp-json/customisations-for-cg-woo-grid/v1/upload-image
   # normal data still works: [{"a":1}]
   curl -s -X POST -H 'Content-Type: application/json' \
     -d '{"serialized":"a:1:{i:0;a:1:{s:1:\"a\";i:1;}}"}' \
     https://camisetasparatodos.es/wp-json/customisations-for-cg-woo-grid/v1/encode
   ```
4. Check a product page with the budgeteer (prices table, totals) still works.
5. **Delete the mu-plugin** when sections 1–3 are fixed in the plugins.

## Check whether it was used

These routes have been public at least since the site moved to these plugins, which includes the July 2026
intrusion. Worth checking:

```bash
# On the server: Nginx access logs (current and rotated)
zgrep -hE '"POST /(wp-json/|\?rest_route=/)(cg-woo-grid|customisations-for-cg-woo-grid)/v1/(encode|upload-image)' /var/log/nginx/access.log* \
  | awk '{print $1, $4, $7, $9}' | sort | uniq -c | sort -rn | head -50

# The product page calls /encode legitimately: look for IPs/user-agents that don't browse the shop,
# bodies you can't see in the log, or bursts around 2026-07-26 02:40.

# Files uploaded through upload-image
find /srv/docker/wordpress/wordpress/wp-content/uploads -newermt 2026-01-01 -type f ! -name '*.jpg' ! -name '*.jpeg' ! -name '*.png' ! -name '*.webp' ! -name '*.gif' ! -path '*/cpt-easy/*' | head -50
ls -la /srv/docker/wordpress/wordpress/wp-content/uploads/cg_uploads 2>/dev/null
```

```sql
-- Attachments created without an author (anonymous uploads)
SELECT ID, post_date, post_title, post_mime_type, guid
FROM wp_posts WHERE post_type = 'attachment' AND post_author = 0 ORDER BY post_date DESC LIMIT 50;
```

## Checklist

- [ ] Mitigation: `cpt-rest-hotfix.php` in git and in production; the curl checks pass; product page OK.
- [ ] Logs and uploads reviewed (section above); findings added to the incident notes.
- [ ] sadoje: product page reads JSON (no `/encode`); admin sends `X-WP-Nonce`; `/encode` and `/serialize` admin-only
      with `allowed_classes => false`; `upload-image` and `testing` deleted.
- [ ] cg-woo-grid: same for `/encode`, `/serialize`, `testing`; public routes with `__return_true` + `args` validation.
- [ ] No `permission_callback` notices left: `docker compose run --rm wpcli eval 'echo "ok";'` (with `WORDPRESS_DEBUG=true` locally).
- [ ] PHPUnit: an integration test that POSTs a serialized object to `/encode` and asserts no object is created,
      and that anonymous calls to admin routes get 401/403 (see `docs/PHPUnit.md`).
- [ ] Release + deploy of `plugin-sadoje` and `plugin-cg-woo-grid` (`bin/deploy.sh vX.Y.Z plugin-sadoje cg-woo-grid`),
      then delete the mu-plugin.
