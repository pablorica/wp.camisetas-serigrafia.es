# Codigo Woo Grid plugin
Adds a matrix of Woocommerce Products variations

Website: [codigo.co.uk](https://codigo.co.uk)

[![version](https://img.shields.io/badge/version-0.12.13-blue.svg)](https://semver.org)


## Basic Features

Codigo Woo Grid allows your users to add multiple variations of a product to their cart from single form.



## Development

The plugin uses [npm](https://www.npmjs.com/) as manager for dependency packages. And it uses tools like [rollup.js](https://www.rollupjs.org/) and [sass](https://sass-lang.com/) as taskrunners to compile .scss code into .css, minify .js code, etc. The following chapter describes the usage and workflow.

### Preparations: Install node.js

At first you need node.js installed on your computer globally. If you already done this before skip this section. If not: You have to install node.js (comes along with npm) before you can proceed. To install node.js visit the node.js website for the latest installer for your OS. Download and install it like any other program, too.

We also recommend using [NVM - Node Version Manager](https://github.com/nvm-sh/nvm) to keep closer control over your version of node.js and switch between versions.

### Installing dependencies

-   Make sure you have installed Node.js on your computer globally
-   Then open your terminal and browse to the location of your Understrap copy
-   Run: `npm install`

### Running

To work and compile your Sass and JS files on the fly start:

```
npm run watch
```

### Distribution

Once the plugin changes are ready, rub this command to create the final distribution

```
npm run dist-build
```

Run this command to delete any previous distribution

```
npm run dist-clean
```
## Performance

The plugin builds a matrix of number fields using the attributes selected in he backend (taxonomy X and taxonomy Y)

The user can add quantities to these fields and can simultaneously add them to the cart.
 To add more than one variation simultaneously we are using the functions `cdg_add_multiple_variations_to_cart()` and  `woo_hack_invoke_private_method()`, following the istructions of [WooCommerce: add multiple products to cart via url](https://www.webroomtech.com/woocommerce-add-multiple-products-to-cart-via-url/)

 The plugin aslos add a new Cookie `cgWooGrid` that can be useful to get information for any custom addon.

## Required Plugins
* [WooCommerce](https://woocommerce.com/)


## Copyright and License

Copyright 2024 Codigo Woo Grid plugin released under the [MIT](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/main/LICENSE) license.

## Versioning

We use [SemVer](https://semver.org/) for versioning. For the versions available, [list of tags can be found in this page](https://github.com/pablorica/wordpress_codigo_theme/tags).

### Changelog

[CHANGELOG.md](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/plugins/cg-woo-grid/CHANGELOG.md)