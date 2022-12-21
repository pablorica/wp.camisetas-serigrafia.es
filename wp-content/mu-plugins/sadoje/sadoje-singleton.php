<?php

/**
 * 
 * This class defines the project singleton
 * 
 *  We use singletons so that others can unhook and rehook the functions defined in this same folder from their own plugins at a later date if need be.
 *
 *
 * @since      1.0.0
 * @package    sadoje
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

namespace Sadoje;


abstract class Sadoje_Singleton {

	/**
	 * Static property to hold our singleton instances
	 *
	 */
    private static $instances = array();

    /**
	 *
	 *  And our protected __construct() function means that we can't ever get a child class
	 *  which isn't a singleton elsewhere by using the new operator
     *  The constructor is protectd (or private) to prevent initiation with outer code.
	 *
	 */
	protected function __construct() {

	}



	/**
	 *
	 * If an instance of our child class exists in our instances array, return it.
	 * If not, then create one, add it to the array, and return it.
	 *
	 *
	 * @return static
	 *
	 */
	public static function getInstance() {
		if ( ! isset( self::$instances[ static::class ] ) ) {
			self::$instances[ static::class ] = new static();
            //self::$instance = new Singleton();
		}

		return self::$instances[ static::class ];
	}

	/**
	 *
	 * If we're a singleton, we need to be initialised
     * init() is an abstract method that should be defined in all the child classes and they should return a mixed.
	 *
	 * @return mixed
	 *
	 */
	abstract public function init();

}