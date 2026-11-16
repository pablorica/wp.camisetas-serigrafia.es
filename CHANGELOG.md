
# Change Log
All notable changes to this project will be documented in this file.
 
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).


## [1.1.10] - 2026-11-16

### Added
- `docs/PHPUnit.md`: how our plugins are tested (unit + integration suites), templates and pitfalls, to reuse in other plugins.


## [1.1.9] - 2026-11-16

### Added
- CPT Easy: PHPUnit tests (unit + integration) for the order prices. See `docs/CPTEasy.md`.


## [1.1.8] - 2026-11-16

### Changed
- Theme: "Pay for order" page styled like the "4. Pago" checkout step (`assets/css/order-pay.css`):
  centred column, radio + method name, no gateway logos, full-width dark button.


## [1.1.7] - 2026-11-16

### Fixed
- Theme: the "Pay for order" page (CPT Easy orders, payment links from the admin) showed no payment methods,
  because Cartify's multi-step checkout hides `#payment` and that page has no steps. The child theme removes
  `has-multistep-navigation` from the body there.


## [1.1.6] - 2026-11-16

### Changed
- CPT Easy orders accept every enabled payment method (cash on delivery and cheque were hidden).


## [1.1.5] - 2026-11-16

### Added
- CPT Easy works with the "WC – APG NIF/CIF/NIE Field" plugin (shared NIF field). See `docs/CPTEasy.md`.


## [1.1.4] - 2026-11-16

### Added
- CPT Easy form: billing and shipping pre-filled for logged-in customers.


## [1.1.3] - 2026-11-16

### Added
- CPT Easy landing as ACF blocks (`cpt-easy/hero`, `precios`, `opiniones`, `pedido`, `categorias`, `faq`) and `wp cpt-easy landing <page-id>`. See `docs/CPTEasy.md`.


## [1.1.2] - 2026-11-16

### Added
- `[cpt_easy_precios calidad="…"]`: landing price rows (1 and 2 caras) rendered from the variations.
- `[cpt_easy_minimo]`: minimum quantity.
- Cash on delivery and cheque are hidden on the payment page of CPT Easy orders (`cpt_easy_excluded_gateways`).


## [1.1.1] - 2026-11-16

### Added
- `cpt-easy` plugin: CPT Easy quick-order product, `[cpt_easy_form]` form and `wp cpt-easy` commands. See `wordpress/wp-content/plugins/cpt-easy/README.md`.


## [1.1.0] - 2026-11-15


Local and production now run the same Docker configuration. Only `.env` differs per machine.
See `MIGRATION.md` for the production switch-over and the local database rebuild.

### Added
- Single `docker-compose.yml` shared by local and production, driven by `.env`:
  - `PROJECT_NAME` names every container, volume and network (`camisetasparatodos_mysql`, `camisetasparatodos_mysql_data`, …).
  - Profiles: `local` (Caddy), `node` (asset watcher, folder set with `NODE_WORKDIR`), `tools` (`wpcli`, `composer`, run on demand).
- Single `wp-config.php` for both environments, mounted read-only. All settings come from `.env`
  (`APP_ENV` → `WP_ENVIRONMENT_TYPE`, `WORDPRESS_CACHE` → `WP_CACHE`, `WORDPRESS_DEBUG`, salts, ACF licence).
- `env.local.example` and `env.production.example`.
- MySQL healthcheck: WordPress, phpMyAdmin and WP-CLI wait until the database is ready.
- CPU limits in the compose file (WordPress 3.0, MySQL 1.5), previously set by hand on the server.
- `docker/wpcli/Dockerfile` (PHP 8.3, with `mariadb-connector-c` for `wp db` commands against MySQL 8).
- `MIGRATION.md`: runbook for the production switch-over and the local database refresh.
- Theme: `assets/css/order-items.css`, loaded on My Account and the order-received page (versioned with `filemtime`).

### Changed
- PHP 8.3 everywhere (local was 8.5). Composer image now uses PHP 8.3 too, so dependencies resolve for production's PHP.
- MySQL pinned to `8.0.44` (the version production runs); phpMyAdmin pinned to `5.2`.
- Database service renamed `db` → `mysql`; host is `mysql:3306` on both machines.
- Production WordPress files folder renamed `wp_data` → `wordpress` (inside `/srv/docker/wordpress`, as locally).
- WordPress and phpMyAdmin ports bound to `127.0.0.1` only.
- PHP and WordPress logs moved to `/var/log/wordpress` (host `./logs`), outside the web root.
- Single-file mounts (`wp-config.php`, `uploads.ini`, `Caddyfile`) use the long bind syntax: Docker now refuses to start if the file is missing, instead of creating an empty folder.
- `WP_DEBUG` is off unless `WORDPRESS_DEBUG=true`.
- Theme: order line-item details (size, colour, print) are shown as an aligned label/value list, like the admin order view.

### Fixed
- Theme: fatal error `Call to undefined function get_current_screen()` in `inc/sadoje-setup.php` on production (the guarded version is now deployed).
- Theme: fatal `ArgumentCountError` in `wp_kses()` on the order-received page when the order has a customer note.
  Override: `woocommerce/order/order-details.php` (uses `wp_kses_post()`).
- Theme: order item details were printed as raw HTML (`<p>XL</p>`, `<a href=…>`) in My Account and on the order-received page.
  Override: `woocommerce/order/order-details-item.php`.

### Removed
- `mysql-lowmem.cnf` (it was never actually applied locally; both machines now run MySQL defaults).
- `mysql_native_password` server flag (the default `caching_sha2_password` is used, as in production).
- phpMyAdmin auto-login.
- Per-service container-name variables, `MYSQL_HOST`, `WORDPRESS_THEMENAME` and the Sage-only `VITE_PATH`.
- `WORDPRESS_CONFIG_EXTRA` / `eval()` hook from the old image-generated `wp-config.php`.

### Security
- `DISALLOW_FILE_EDIT`: no theme or plugin code editor in wp-admin.
- `no-new-privileges` on all services.
- Logs and the git repository are no longer inside the web root.
- New WordPress salts and MySQL passwords in production as part of the migration (values live only in `.env`, which is not committed).



## [1.0.3] - 2026-02-18

### Fixed

- Fatal error due `get_current_screen()` fucntion



## [1.0.2] - 2025-11-25

### Updated

- [New Font Namaku](https://app.asana.com/1/355454733208534/project/1211704400399240/task/1211703380586135)

- [New Home Content](https://app.asana.com/1/355454733208534/project/1211704400399240/task/1211703380586135)



## [1.0.1] - 2025-07-07


### Fixed

- [Fallo en presupuestador](https://app.asana.com/1/355454733208534/project/1203117341882428/task/1210729685536229)


## [1.0.0] - 2025-06-27

Go Live

## [0.6.17] - 2025-06-20

### Updates

 - Add the new column 'Color Code' in colors attribute taxonomy list
 - Make the 'Color Code' column sortable
 - Filter dropdown for color codes

## [0.6.16] - 2025-06-03

### Updates

- Single Product Tabs
- Registration Form
- Checkout & Cart styles


## [0.6.15] - 2025-05-30

### Updates

- [Add order item meta for customizations](https://app.asana.com/1/355454733208534/project/1203117341882428/task/1210409351761295)




## [0.6.14] - 2025-05-26

### Updates

- [Estampador](https://app.asana.com/1/355454733208534/project/1203117341882428/task/1210143831408687) El estampador tendrá ahora SOLO dos campos: Ubicación & Tamaño


- [Números y Bordes](https://app.asana.com/1/355454733208534/project/1203117341882428/task/1210143831408697)


## [0.6.13] - 2025-04-17

### Updates

 - Aplicación de IVA corregido


## [0.6.12] - 2025-03-07

### Add

 - [Plugin para importar código de colores](https://app.asana.com/1/355454733208534/project/1203117341882428/task/1209606747447919) Custom custom plugin to import RGB colors from a CSV file


## [0.6.11] - 2025-03-07

### Updates

 - Colors filter: Override custom Yith colors with Product Attribute colors


## [0.6.10] - 2025-02-28

### Updates

 - Plugin code updates


## [0.6.9] - 2025-02-27

### Updates

 - Theme translations
 -  Ensure the Text Domain is Loaded Correctly


## [0.6.8] - 2025-02-27

### Updates

 - Ocultar descripciones en el menu de categorías

## [0.6.7] - 2025-02-13

### Updates

 - [Editor de productos forzado a usar Gutenberg](https://app.asana.com/0/1203117341882428/1209397188060092) 


## [0.6.6] - 2025-02-03

### Updates

 - [Nueva plantilla](https://app.asana.com/0/1203117341882428/1209387493727969) 
    - Gutenberg style for editor page


## [0.6.5] - 2025-02-03

### Updates

 - [Tabla de precios: Margen beneficio por producto y categoría](https://app.asana.com/0/1203117341882428/1209050961520871) 
    - Implement open ranges in profit calculation




## [0.6.4] - 2025-01-31

### Updates

 - [Tallas Adultos/Niños](https://app.asana.com/0/1203117341882428/1209050961520853) 
    - Precio principal igual al mínimo de la tabla de precios


## [0.6.3] - 2025-01-31

### Updates

 - [Tabla de precios: Margen beneficio por producto y categoría](https://app.asana.com/0/1203117341882428/1209050961520871) 
     - Update harcoded sizes
     - Implement dynamic products/categories accordion name & show price table only in existing variants
     - Implement multiple custom price tables by category and product
     - Implement custom price table by category and product
     - Implement benefit tables by category and product


## [0.6.2] - 2025-01-30

### Updates

 - Nueva plantilla
    - Ha sido requerida en [Página de producto](https://app.asana.com/0/1203117341882428/1208913047697317) 


## [0.6.1] - 2025-01-30

### Updates

 - Hero Home CSS added to custom plugin
    - Load CSS files based on the page
    - [Página de producto](https://app.asana.com/0/1203117341882428/1208913047697317) Product thumbnails smaller


## [0.6.0] - 2025-01-29

### Updates

 - New plugin to manage the template


## [0.5.8] - 2025-01-21

### Updates

#### Codigo Woo Grid plugin

 - [Read More Updates](https://github.com/pablorica/wp.camisetas-serigrafia.es/tree/main/wp-content/plugins/cg-woo-grid#readme)
 - [New Changelog File](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/plugins/cg-woo-grid/CHANGELOG.md)
 - [0.12.11](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/plugins/cg-woo-grid/CHANGELOG.md) Fixed: PHP warning errors


#### Codigo Woo Grid add-on

  - [Read More Updates](https://github.com/pablorica/wp.camisetas-serigrafia.es/tree/main/wp-content/mu-plugins/sadoje#readme)
  - [New Changelog File](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/mu-plugins/sadoje/CHANGELOG.md)
  - [0.14.3](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/mu-plugins/sadoje/CHANGELOG.md) 
    - Implement new price table adults / children design
    - Add new values to the child sizes array



## [0.5.7] - 2024-12-23

 - Presupuestador: Update package.json

 - [Presupuestador: Activar/Desactivar campos](https://app.asana.com/0/1203117341882428/1209039244783521) 
  - Opcion de desactivar campos del presupuestador en el CMS
    - Cuando un campo se desactiva:
        - No aparecerá en el frontend.
        - Todos los valores asociados a tipos de estampación serán eliminados del backend.

## [0.5.6] - 2024-12-09

### Fix

 - [Plugin Malla: Numeros negativos](https://app.asana.com/0/1203117341882428/1208880819951180)
    -  Se ha arreglado el detalle del mensaje duplicado
    -  además también se arreglo el mensaje que mostraba un stock negativo


## [0.5.5] - 2024-12-05

### Update

 - [Numeros negativos](https://app.asana.com/0/1203117341882428/1208880819951180)
    -  Hide negative product quantities and show out-of-stocks instead


## [0.5.4] - 2024-12-04

### Update

 - Flush cache in JS and CSS files in plugin and add-on

### Fix

- Fix compilaton files


## [0.5.3] - 2024-12-04

### Update

 - [Plugin: Malla bloqueada](https://app.asana.com/0/1203117341882428/1208880819951183)
    -  Input disabled only when product stock is less than 0


## [0.5.2] - 2024-12-04

### Update

 - [Add-on: Tabla de precios](https://app.asana.com/0/1203117341882428/1203756195707413)
    - Fix: param type array to object


## [0.5.1] - 2024-11-13

### Update

 - Readme with instructions to download assets from staging server

## [0.5.0] - 2024-11-13

### Starting again


