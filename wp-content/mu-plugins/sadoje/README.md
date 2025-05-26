# Codigo Woo Grid add-on
Updates prices in the matrix of Woocommerce Products variations

Website: [codigo.co.uk](https://codigo.co.uk)

[![version](https://img.shields.io/badge/version-0.14.11-blue.svg)](https://semver.org)

## Basic Features




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

Update cart:`cdg_alter_price_cart()` defined in sadoje/public/customisations-for-cg-woo-grid-public.php

## Required Plugins
* [WooCommerce](https://woocommerce.com/)


## Copyright and License

Copyright 2024 Codigo Woo Grid add-on released under the [MIT](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/main/LICENSE) license.

## Versioning

We use [SemVer](https://semver.org/) for versioning. For the versions available, [list of tags can be found in this page](https://github.com/pablorica/wordpress_codigo_theme/tags).

### Changelog

[CHANGELOG.md](https://github.com/pablorica/wp.camisetas-serigrafia.es/blob/develop/wp-content/mu-plugins/sadoje/CHANGELOG.mdd)