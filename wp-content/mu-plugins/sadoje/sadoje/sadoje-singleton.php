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

    /**** Plugin properties ****/

    protected $plugin_name;
    protected $version;


    /**** Custom Plugin Properties ****/

    protected $plugin_url;
    protected $plugin_path;
    protected $verbose;


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
        //Initialize all the basics components of the plugin
        $this->plugin_name = 'sadoje';
        $this->version = '0.10.1';

        $this->verbose = false;
        if(defined('DEVELOPMENT_DEBUG')) {
            $this->verbose = DEVELOPMENT_DEBUG;
        }

        //set the variable for the example endpoint. you can use a option plugin to store it and change it later in the admin page
        $this->plugin_url  = str_replace(
            '/sadoje/sadoje',
            '/sadoje',
            plugin_dir_url( __FILE__ ).'/'
        );
        $this->plugin_path = str_replace(
            DIRECTORY_SEPARATOR.'sadoje'.DIRECTORY_SEPARATOR.'sadoje',
            DIRECTORY_SEPARATOR.'sadoje',
            dirname(__FILE__).DIRECTORY_SEPARATOR
        ); 

        $this->load_hooks();
	}

    /**
	 * Register all hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_hooks() {

        add_action( 
            'wp_enqueue_scripts', 
            array( 
                $this, 
                'enqueue_frontend_scripts' 
            )
        );

        add_action( 
            'admin_enqueue_scripts', 
            array( 
                $this, 
                'enqueue_backend_scripts' 
            )
        );        
    }

    /**
	 * Register the frontend styles and scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_frontend_scripts() {

        wp_enqueue_style( 
            $this->plugin_name, 
            $this->plugin_url.'public/css/sadoje.css', 
            array(),
            $this->version, 
            'all' 
        );
    	wp_enqueue_script( 
            $this->plugin_name, 
            $this->plugin_url.'public/js/sadoje.js', 
            array( 
                'cg-woo-grid',
            ),
            $this->version, 
            true 
        );
	}

    /**
	 * Register the backend styles and scripts
	 *
	 * @since    1.0.0
	 */
	public function enqueue_backend_scripts() {
        
        wp_enqueue_style( 
            $this->plugin_name.'-admin', 
            $this->plugin_url.'admin/css/sadoje.css', 
            array(),
            $this->version, 
            'all' 
        );
        wp_enqueue_script( 
            $this->plugin_name.'-admin', 
            $this->plugin_url.'admin/js/sadoje.js', 
            array( 
                'cg-woo-grid',
            ),
            $this->version, 
            true 
        );
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