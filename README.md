# Camisetas Serigrafía (Wordpress)


[![version](https://img.shields.io/badge/version-0.6.6-blue.svg)](https://semver.org)


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


# Codigo Wordpress Site for Camisetas Serigrafia
A clean slate Wordpress theme template with Bootstrap(5), Sass, and a Webpack config with Babel.
Based in [understrap.com](https://understrap.com)
And in customised version https://github.com/pablorica/wordpress_codigo_theme

Website: [understrap.com](https://understrap.com)

Parent Theme Project: [github.com/understrap/understrap](https://github.com/understrap/understrap)

Premium Child Themes: [understrap.com/child-themes/](https://understrap.com/child-themes/)

And in customised version [Pablo Rica Understrap Theme](https://github.com/pablorica/understrap-academy)

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


## Installation

Understrap uses [npm](https://www.npmjs.com/) as manager for dependency packages like Bootstrap and Underscores. And it uses tools like [rollup.js](https://www.rollupjs.org/) and [postCSS](https://postcss.org) as taskrunners to compile .scss code into .css, minify .js code, etc. The following chapter describes the usage and workflow.

### [Preparations: Install node.js](https://docs.understrap.com/#/understrap-child/npm?id=preparations-install-nodejs)

At first you need node.js installed on your computer globally. If you already done this before skip this section. If not: You have to install node.js (comes along with npm) before you can proceed. To install node.js visit the node.js website for the latest installer for your OS. Download and install it like any other program, too.

We also recommend using [NVM - Node Version Manager](https://github.com/nvm-sh/nvm) to keep closer control over your version of node.js and switch between versions.

### [Installing dependencies](https://docs.understrap.com/#/understrap-child/npm?id=installing-dependencies)

-   Make sure you have installed Node.js and Browser-Sync* (* optional, if you wanna use it) on your computer globally
-   Then open your terminal and browse to the location of your Understrap copy
-   Run: `npm install`

### [Running](https://docs.understrap.com/#/understrap-child/npm?id=running)

To work and compile your Sass files on the fly start:

```
npm run watch
```

Or, to run with Browser-Sync:

First change the browser-sync options to reflect your environment in the file `/src/build/browser-sync.config.js` in the beginning of the file:

```
module.exports = {
    "proxy": "localhost/", // Change here
    "notify": false,
    "files": ["./css/*.min.css", "./js/*.min.js", "./**/*.php"]
};
```

Replace `localhost/theme_test/` with the URL to your local WordPress test environment. For example if you run MAMP and your WordPress installation is placed in a htdocs subfolder called `/understrap` you have to add `localhost:8888/understrap`. Its the same URL you have to type in to see your local WordPress installation. Then run:

```
npm run watch-bs
```

## Development

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