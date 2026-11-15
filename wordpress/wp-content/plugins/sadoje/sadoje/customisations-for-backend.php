<?php

/**
 *
 * This class contains hooks to manipulate the Storefornt Template
 *
 */


 namespace Sadoje;


class Customisations_For_Backend extends Sadoje_Singleton {


    /**** Plugin properties ****/

    protected $element_name;

    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {
        $this->element_name = 'customisations-for-backend';
        $this->add_actions();
    }

    /**** Plugin methods ****/

    private function add_actions()
    {

        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $this, 
        //         'hello_world' 
        //     )
        // );

        add_action(
            'admin_init', 
            array( 
                $this, 
                'additional_admin_color_schemes' 
            )
        );
    }


    /**
	 * 
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Backend : Hello World!');
        //echo "<h1>Storefront_Template : Hello World!</h1>";
        error_log($this->plugin_url.' '.$this->plugin_path);
	}

    /**
     * Add additional colour schemes to the backend
     *
     * @see https://www.hongkiat.com/blog/wordpress-admin-color-scheme/
     * @see https://developer.wordpress.org/reference/functions/wp_color_scheme_settings/
     * 
     * @return void
     */
    function additional_admin_color_schemes() {
       
        wp_admin_css_color( 
            'yellow',
             __( 'Amarillo', 'sadoje' ),
             $this->plugin_url . 'admin/css/colors/yellow.css?v='.$this->version,
             array( 
                '#ebaf08', 
                '#dd823b', 
                '#fbd564', 
                '#aaa088' 
             ),
             array(
                'base'    => '#fff',
                'focus'   => '#fff',
                'current' => '#fff',
            )
        );

    }

}