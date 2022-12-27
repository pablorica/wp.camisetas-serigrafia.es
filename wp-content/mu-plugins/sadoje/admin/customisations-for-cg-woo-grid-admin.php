<?php

/**
 *
 * This class contains hooks to manipulate the plugin Codigo Woo Grid
 *
 */


 namespace Sadoje;


class Customisations_For_Cg_Woo_Grid_Admin extends Customisations_For_Cg_Woo_Grid {


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
            $this->get_element_name().'-budgeteer', 
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

        require_once $this->plugin_path. 'admin/'.$this->get_element_name().'-admin-rest-api.php';
        $plugin_admin_restapi = new Customisations_For_Cg_Woo_Grid_Admin_Rest_Api();
        $urlspace = $plugin_admin_restapi->get_url_space();
		require_once $this->plugin_path.'admin/display/'.$this->get_element_name().'-display.php'; 
    }


    public function registerAndBuildBudgeteer() {

        // ID used to identify this section and with which to register options
        $settings_section_id = $this->get_element_name().'_budgeteer_section';
        // Title to be displayed on the administration page
        $settings_title = '';
        // Callback that echos out any content at the top of the section (between heading and fields).
        $settings_callback = null; 
        //Function that fills each field with the desired form inputs. The function should echo its output.
        $field_callback = null;

        $settings_pages = array();
        

        //The slug-name of the settings page on which to show the section.
        $settings_page  = new \stdClass();
        $settings_page->slug   = $this->get_element_name().'_budgeteer_general';
        $settings_page->fields = array();

        $option_lists = array(
            0 => __("Desactivado","sadoje"), 
            1 => __("Activado","sadoje")
        );
        $field_id = $this->get_element_name().'_budgeteer_general_status';
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
        $settings_page->slug   = $this->get_element_name().'_budgeteer_bagged';
        $settings_page->fields = array();

        $field_id = $this->get_element_name().'_budgeteer_bagged_price';
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
        $settings_page->slug   = $this->get_element_name().'_budgeteer_stamping';
        $settings_page->fields = array();

        $field_id = $this->get_element_name().'_budgeteer_stamping_types';
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



     /**
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Hello Admin!');
        //echo "<h1>Hello Admin!</h1>";
        error_log('Plugin URL: '.$this->plugin_url);
        error_log('Plugin PATH: '.$this->plugin_path);
        error_log('Element Name: '.$this->get_element_name());
	}

}