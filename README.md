# Camisetas Para Todos


[![version](https://img.shields.io/badge/version-1.1.1-blue.svg)](https://semver.org)


## Live Site

[https://camisetasparatodos.es/](https://camisetasparatodos.es)

  
## Staging Site

[https://camisetas-serigrafia.codigo.co.uk/](https://camisetas-serigrafia.codigo.co.uk/)

## TODO

[Security Fixes](docs/SecurityFixes.md)



# Codigo Wordpress Template for Camisetas Serigrafia
A clean slate Wordpress child theme template of [Cartify](http://demo.agnidesigns.com/cartify) for Camisetas Serigrafia.

Parent Theme: [Cartify](http://demo.agnidesigns.com/cartify)

## Basic Features

-   Combines Underscore’s PHP/JS files and Bootstrap’s HTML/CSS/JS.
-   Comes with Bootstrap v5 Sass source files and additional .scss files. Nicely sorted and ready to add your own variables and customize the Bootstrap variables.
-   Uses sass and postCSS to handle compiling all of the styles into one style sheet. The theme also includes rollup.js to handle javascript compilation and minification.
-   Uses a single minified CSS file for all the basic stuff.
-   [Font Awesome](http://fortawesome.github.io/Font-Awesome/) integration (v4.7.0)
-   Jetpack ready
-   WooCommerce support
-   Contact Form 7 support
-   Translation ready


## Theme Translation

### Create .pot files with WP-CLI

Navigate to the cartify folder and execute the following command (be sure WP-CLI is installed):

    cd wp-content/themes/cartify
    wp i18n make-pot . languages/cartify.pot


 [make-pot documentation](https://developer.wordpress.org/cli/commands/i18n/make-pot/) 

You can then edit this file with Poedit or any other handy translation tool.
Use the pot file to generate the .po and .mo files of each language.

Save them on the languages folder of the child theme, and name them using the <plugin-name>-<language> structure:

- themes/sadoje/languages/es_ES.po
- themes/sadoje/languages/es_ES.mo


### Place the Translation Files in the Correct Directory

Since Cartify (the parent theme) does not have translations, WordPress may not automatically detect translations stored in the child theme. Try placing your translation files in the recommended location:


 - wp-content/languages/themes/cartify-es_ES.po
 - wp-content/languages/themes/cartify-es_ES.mo

WordPress prefers to load translation files from `wp-content/languages/themes/` instead of `wp-content/themes/sadoje/languages/`. This ensures that translations are used even after theme updates.


--

# Custom Plugins Development

### Codigo Woo Grid plugin

Custom plugin that adds a matrix of Woocommerce Products variations

[Read More](docs/CodigoWooGrid.md)


### Codigo Woo Grid add-on

This is an extension of the previous plugins, with specific customizations for this project. This must-use plugin updates prices in the WooCommerce product variations matrix

[Read More](https://github.com/pablorica/wp.camisetas-serigrafia.es/tree/main/wp-content/mu-plugins/sadoje#readme)

### CPT Easy

Quick-order system: one variable product (Calidad × Impresión) with quantity price tiers, an order form that goes straight to payment, and the landing page as ACF blocks.

[Read More](docs/CPTEasy.md) · [Tests](docs/PHPUnit.md)


# Deployment

The custom theme and plugins are built and zipped from a git tag with `bin/release.sh`, and installed in production with `bin/deploy.sh` (backup, swap, health check, rollback).

[Read More](docs/Deployment.md)


## Basic Features

Codigo Woo Grid allows your users to add multiple variations of a product to their cart from single form.


## Required Plugins
* [Carousel Slider Block for Gutenberg](https://en-gb.wordpress.org/plugins/carousel-block/)
* [Page Builder Gutenberg Blocks – CoBlocks](https://en-gb.wordpress.org/plugins/coblocks/)
* [Advanced Custom Fields](https://www.advancedcustomfields.com/pro/)


### Debug and Development Plugins
* [FakerPress](https://wordpress.org/plugins/fakerpress/)
* [Custom Post Type UI](https://en-gb.wordpress.org/plugins/custom-post-type-ui/)
* [WP Migrate DB Pro](https://deliciousbrains.com/wp-migrate-db-pro/)
* [Query Monitor](https://en-gb.wordpress.org/plugins/query-monitor/)

### Recommended Plugins
* [Yoast Duplicate Post](https://en-gb.wordpress.org/plugins/duplicate-post/)
* [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/)


## Acknowledgements

* [HTML 5 Blank](https://github.com/toddmotto/html5blank)



## Copyright and License

Copyright 2024 Camisetas Serigrafía Theme released under the [MIT](https://github.com/pablorica/hmo/blob/main/LICENSE) license.

## Versioning

We use [SemVer](https://semver.org/) for versioning. For the versions available, [list of tags can be found in this page](https://github.com/pablorica/wordpress_codigo_theme/tags).

### Changelog

[CHANGELOG.md](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/main/CHANGELOG.md)