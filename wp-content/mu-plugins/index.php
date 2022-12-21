<?php

/**
 *
 * Initialise all of the singleton classes that set up and hook into Wordpress
 *
 * We use singletons so that others can unhook and rehook these functions from their own plugins at a later date if need be.
 *
 */


 /**
  *  Autoload classes defiend in this folder
  *  This autoloader converts to WordPress standard filenaming conventions 
  *  rather than PSR-0 or -4
  */
spl_autoload_register( function ( $classname ) {

	// normalise
	$class = str_replace( 
        '\\', 
        DIRECTORY_SEPARATOR, 
        str_replace( 
            '_', 
            '-', 
            strtolower( $classname ) 
        ) 
    );

	// generate filepath based on current folder
	$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . $class . '.php' ;
    

	// if the file exists, require it
	if ( file_exists( $file ) ) {
		require $file;
	}
} );


// this is where our site classes live
$namespace = 'Sadoje\\';

// and this is what they're called
$classes = array( 
	'Customisations_For_Cg_Woo_Grid',
);

foreach ( $classes as $class ) {

	// get a fully qualified namespace so we can get the instance
	// @see https://stackoverflow.com/a/34019088/2266770
	$namespaced_class = $namespace . $class;

	// get the singleton and initialise it, while suppressing the undefined method inspection
	$class = $namespaced_class::getInstance();

	$class->init();

}


