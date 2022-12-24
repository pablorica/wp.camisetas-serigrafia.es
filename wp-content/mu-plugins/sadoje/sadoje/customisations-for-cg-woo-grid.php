<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid extends Sadoje_Singleton {


    /**** Plugin properties ****/

    protected $element_name;

    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {

        //TODO: Check if cg-woo-grid is enabled before init()!!!!!
         
        $this->element_name = 'customisations-for-cg-woo-grid';
        $this->add_actions();
      //$this->add_filters();
    }

    /**** Plugin methods ****/

    private function add_actions()
    {

        //Backend actions

        add_action(
            'admin_menu',
            array( 
                $this, 
                'cdg_add_admin_submenu' 
            ),
            10
        );  

        add_action(
            'admin_init',
            array( 
                $this, 
                'registerAndBuildBudgeteer' 
            ),
            20
        ); 


        // add_action( 
        //     'wp_loaded', 
        //     array( 
        //         $this, 
        //         'hello_world' 
        //     )
        // );


        //Frontend actions

        add_action( 
            'woocommerce_after_single_product_summary', 
            array( 
                $this, 
                'cdg_budgeteer' 
            ),
            18
        );
    }

    private function add_filters()
    {


    }
	

    /** ** ** ** ** ** **
     ** ** BACKEND  ** **
     ** ** ** ** ** ** **/

     /**
      * Get the Codigo Woo Grid functions
      *
      * @param string           $function
      * @param array || string  $var
      * @return void
      */
     public function cdg_woo_grid_admin(
        $function,
        $var
    ) {


        //error_log('cdg_woo_grid_admin: '.$function." ".print_r($var,true));

        require_once WP_PLUGIN_DIR . '/cg-woo-grid/includes/class-cg-woo-grid.php';
        $cgWooGrid = new \Cg_Woo_Grid();

        require_once WP_PLUGIN_DIR . '/cg-woo-grid/admin/class-cg-woo-grid-admin.php';
        $cgWooGridAdmin = new \Cg_Woo_Grid_Admin( 
            $cgWooGrid->get_plugin_name(), 
            $cgWooGrid->get_version() 
        );

        if($function == 'sayHello') {
            $cgWooGridAdmin->sayHello($var);
        }
        if($function == 'buildFields') {
            if(!$var['field_callback']) {
                $var['field_callback'] =  array( 
                    $cgWooGridAdmin, 
                    'render_settings_field' 
                );
            }
            $cgWooGridAdmin->buildFields(
                $var['settings_page_slug'],
                $var['field_id'], 
                $var['field_title'], 
                $var['field_callback'], 
                $var['settings_section_id'],
                $var['field_args']
            );
        }
    }

    /**
     * Adds submenu to administration area
     *
     * @see https://blog.wplauncher.com/create-wordpress-plugin-settings-page/
     * 
     * @return void
     */
    public function cdg_add_admin_submenu() {

        //$this->cdg_woo_grid_admin('sayHello','caracola' );


        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
        add_submenu_page( 
            'cg-woo-grid', 
            esc_html__('Presupuestador', 'sadoje'), 
            esc_html__('Presupuestador', 'sadoje'), 
            'administrator', 
            $this->element_name.'-budgeteer', 
            array( 
                $this, 
                'cdg_display_admin_budgeteer' 
            )
        );
    }
    /**
     * Build the admin page
     *
     * @return void
     */
    public function cdg_display_admin_budgeteer() {
        // set this var to be used in the display view
        //$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'general';
		require_once $this->plugin_path.'admin/'.$this->element_name.'-display.php'; 
    }


    public function registerAndBuildBudgeteer() {

        // ID used to identify this section and with which to register options
        $settings_section_id = $this->element_name.'_budgeteer_section';
        // Title to be displayed on the administration page
        $settings_title = '';
        // Callback that echos out any content at the top of the section (between heading and fields).
        $settings_callback = null; 
        //Function that fills each field with the desired form inputs. The function should echo its output.
        $field_callback = null;

        $settings_pages = array();
        

        //The slug-name of the settings page on which to show the section.
        $settings_page  = new \stdClass();
        $settings_page->slug   = $this->element_name.'_budgeteer_general';
        $settings_page->fields = array();

        $option_lists = array(
            0 => __("Desactivado","sadoje"), 
            1 => __("Activado","sadoje")
        );
        $field_id = $this->element_name.'_budgeteer_general_status';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Estado","sadoje").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'radio',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => 'required="required"',
                'get_options_list' => $option_lists,
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => '',
                'class'            => '',
                'onchange'         => ''
            )
        );
        array_push($settings_pages,$settings_page);

        /* Bagged */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->element_name.'_budgeteer_bagged';
        $settings_page->fields = array();

        $field_id = $this->element_name.'_budgeteer_bagged_price';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Precio de embolsado","sadoje").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => 'alternate',
            )
        );
        array_push($settings_pages,$settings_page);

        /* Types of stamping */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->element_name.'_budgeteer_stamping';
        $settings_page->fields = array();

        $field_id = $this->element_name.'_budgeteer_stamping_types';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Tipo de estampación","sadoje").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'serialized',
                'wp_data'          => 'option',
                'class'            => '',
            )
        );
        array_push($settings_pages,$settings_page);

        

        /* Build settings page fields */
        
        foreach($settings_pages as $settings_page) {
            add_settings_section(
                $settings_section_id, 
                $settings_title,  
                $settings_callback,    
                $settings_page->slug                
            );

            foreach($settings_page->fields as $field) {
                $this->cdg_woo_grid_admin(
                    'buildFields',
                    array(
                        'settings_page_slug'  => $settings_page->slug,
                        'field_id'            => $field['id'],
                        'field_title'         => $field['title'],
                        'field_callback'      => $field_callback,
                        'settings_section_id' => $settings_section_id,
                        'field_args'          => $field['args'],
                    )
                );
                
            }

        }
        
    }








    /** ** ** ** ** ** **
     ** ** FRONTEND ** **
     ** ** ** ** ** ** **/

     /**
	 * Frontend_scripts function. 
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Hello World!');
        echo "<h1>Hello World!</h1>";
        error_log($this->plugin_url.' '.$this->plugin_path);
	}


    /**
	 * Build the products attributes grid
	 *
	 * @since    1.0.0
     * 
     * @return void || WP_Error
	 */
    function cdg_budgeteer() {

        $args = array();
        
        if( null !== get_queried_object_id()) {
            //$args['product'] = wc_get_product(get_queried_object_id());
            include_once $this->plugin_path.'public/'.$this->element_name.'-display.php'; 
        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                $this->element_name.'_error', 
                esc_html__( 'Something went wrong', "cg-woo-grid" ), 
                array( 'status' => 500 ) 
            );
        }
    }
}