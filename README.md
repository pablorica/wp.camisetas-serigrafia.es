# Camisetas Serigrafía (Wordpress)


[![version](https://img.shields.io/badge/version-0.6.8-blue.svg)](https://semver.org)


## Live Site

[https://camisetas-serigrafia.es](https://camisetas-serigrafia.es)

  
## Staging Site

[https://camisetas-serigrafia.codigo.co.uk/](https://camisetas-serigrafia.codigo.co.uk/)

### Backup Current Site

[https://staging.camisetas-serigrafia.es/](https://staging.camisetas-serigrafia.es/)


### Staging Assets

[Database](https://camisetas-serigrafia.codigo.co.uk/last.camisetas_serigrafia.STAGING.sql.gz)

[Plugins](https://camisetas-serigrafia.codigo.co.uk/plugins.tar.gz)

[Parent Theme](https://camisetas-serigrafia.codigo.co.uk/storefront.tar.gz)

[Theme](https://camisetas-serigrafia.codigo.co.uk/galleria.tar.gz)


Regarding the images, since there are so many, it’s not worth downloading them as they will take up a lot of space, and the zip file is too large for the server. Instead, update the .htaccess file so the local version pulls the images from the server, allowing you to work more efficiently.

Note: In the .htaccess file, all images uploaded up to that month will be accessed from the server, so it will still be possible to upload images locally if needed
  

Here you have an example of htaccess:

     ## Load media from Production server, except the current month
    <IfModule  mod_rewrite.c>
    RewriteEngine on
    RewriteCond %{REQUEST_URI} !/2024/(02|03)/ [NC]
    RewriteRule ^wp-content/uploads/(.*) https://camisetas-serigrafia.codigo.co.uk/wp-content/uploads/$1 [R=301,L]
    </IfModule>

In case you are using NGINX, this configuration has the same effect:

    location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
	    expires max;
	    log_not_found off;
    
	    ## Load media from Production server
	    # Exceptions
	    location ~ /wp-content/uploads/smile_fonts/* {
		    # Do nothing, these are local fonts used by template
	    }
	    location ~ /wp-content/uploads/2024/(02|03)/* {
		    # Do nothing, these are local files from current month
	    }
	    rewrite ^/wp-content/uploads/(.*) https://camisetas-serigrafia.codigo.co.uk/wp-content/uploads/$1 redirect;
    
    }


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

- themes/cartify/languages/cartify-es_ES.po
- languages/cartify-es_ES.mo
- languages/cartify.pot

--

# Custom Plugins Development

### Codigo Woo Grid plugin

Custom plugin that adds a matrix of Woocommerce Products variations

[Read More](https://github.com/pablorica/wp.camisetas-serigrafia.es/tree/main/wp-content/plugins/cg-woo-grid#readme)

### Codigo Woo Grid add-on

This is an extension of the previous plugins, with specific customizations for this project. This must-use plugin updates prices in the WooCommerce product variations matrix

[Read More](https://github.com/pablorica/wp.camisetas-serigrafia.es/tree/main/wp-content/mu-plugins/sadoje#readme)

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