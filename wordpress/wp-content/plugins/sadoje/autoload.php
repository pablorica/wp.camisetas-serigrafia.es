<?php

 /**
  *  Autoload classes defined in this folder
  *  This autoloader converts to WordPress standard filenaming conventions 
  *  rather than PSR-0 or -4
  *
  * @see https://cybmeta.com/como-registrar-y-utilizar-autoloaders-en-php
  * @see https://www.php.net/manual/en/function.spl-autoload-register.php
  * 
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
