<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/admin
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */
class Cg_Woo_Grid_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**** Custom Plugin Properties ****/

    protected $js_filepath;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $Cg_Woo_Grid       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( 
        $plugin_name, 
        $version 
    ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        $this->js_filepath = CG_WOOGRID_URL.'admin/js/cg-woo-grid-admin.js';

	}

    /**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * An instance of this class should be passed to the run() function
		 * defined in Cg_Woo_Grid_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Cg_Woo_Grid_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 
            $this->plugin_name, 
            $this->js_filepath,
            array( 
                'jquery',
                 $this->plugin_name.'-vue'
            ),
            $this->version, 
            false 
        );

	}


    public function sayHello($msg = null){
        error_log('Cg_Woo_Grid_Admin: Hello! '.$msg);
    }



    /**
     * Add menu to administration area
     * 
     * @see https://blog.wplauncher.com/create-wordpress-plugin-settings-page/
     *
     * @return void
     */
    public function addAdminMenu() {
        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page(  
            esc_html__('Attribute Grid', 'cg-woo-grid'), 
            esc_html__('Attribute Grid', 'cg-woo-grid'), 
            'administrator', 
            $this->plugin_name, 
            array( 
                $this, 
                'displayAdminDashboard' 
            ), 
            'dashicons-grid-view', 
            26 
        );
    }

    /**
     * Build the admin page
     *
     * @return void
     */
    public function displayAdminDashboard() {

        if(isset($_GET['error_message'])){
            add_action(
                'admin_notices', 
                array(
                    $this,
                    'adminMessages'
                )
            );
            do_action( 
                'admin_notices', 
                $_GET['error_message'] 
            );
        }
		require_once 'partials/'.$this->plugin_name.'-admin-display.php';
    }

    /**
     * Administration messages
     *
     * @param string $error_message
     * @return void
     */
    public function adminMessages($error_message){
        switch ($error_message) {
            case '1':
                $message = __( 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.', 'cg-woo-grid' );                 
                $err_code = esc_attr( 'cg-woo-grid_error_setting' );                 
                $setting_field = 'cg-woo-grid_error_setting';                 
            break;
        }
        $type = 'error';
        add_settings_error(
            $setting_field,
            $err_code,
            $message,
            $type
        );
    }

    /**
     * Build administration field
     *
     * @param string   $settings_page
     * @param string   $field_id
     * @param string   $field_title
     * @param callable $callback
     * @param string   $section_id
     * @param array    $field_args
     * @return void
     */
    public function buildFields(
        string $settings_page,
        string $field_id, 
        string $field_title, 
        callable $callback, 
        string $section_id = 'default', 
        array $field_args = array()
    ) {
   
        add_settings_field(
            $field_id,
            $field_title,
            $callback,
            $settings_page,
            $section_id,
            $field_args
        );

        register_setting(
            $settings_page,
            $field_id
        );
    }
    /**
     * Build administration fields
     * 
     * 1 add_settings_section. This is necessary since all future settings must belong to one.
     * 2 add_settings_field
     * 3 register_setting
     *
     * @return void
     */
    public function registerAndBuildFields() {
        
        // ID used to identify this section and with which to register options
        $settings_section_id = $this->plugin_name.'_general_section';
        // Title to be displayed on the administration page
        $settings_title = '';
        // Callback that echos out any content at the top of the section (between heading and fields).
        $settings_callback = null; 
        //Function that fills each field with the desired form inputs. The function should echo its output.
        $field_callback = array( 
            $this, 
            'render_settings_field' 
        );

        $settings_pages = array();
        $settings_page  = new \stdClass();

        //The slug-name of the settings page on which to show the section.
        $settings_page->slug   = $this->plugin_name.'_general_settings';
        $settings_page->fields = array();

        $option_lists = array();
        if($attr_tax = wc_get_attribute_taxonomies()) {
            foreach( $attr_tax as $tax ) {
                $option_lists['pa_'.$tax->attribute_name] = $tax->attribute_label;
            }
        }
        //error_log( print_r($option_lists,true));

        $field_id = $this->plugin_name.'_header_setting';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Header","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Choose colors and sizes to make your budget","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        $field_id = $this->plugin_name.'_taxonomy_x_setting';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Taxonomy X","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'select',
                'subtype'          => '',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => 'required="required"',
                'get_options_list' => $option_lists,
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => '',
                'onchange'         => 'CDGADMIN.changeTextValue(\''.$field_id.'\')'
            )
        );
        $field_id = $this->plugin_name.'_taxonomy_x_setting_title';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Rename","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => '',
            )
        );
        $field_id = $this->plugin_name.'_taxonomy_y_setting';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Taxonomy Y","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'select',
                'subtype'          => '',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => 'required="required"',
                'get_options_list' => $option_lists,
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => 'alternate',
                'onchange'         => 'CDGADMIN.changeTextValue(\''.$field_id.'\')'
            )
        );
        $field_id = $this->plugin_name.'_taxonomy_y_setting_title';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Rename","cg-woo-grid").":",
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


        /* Minimum amount bar */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->plugin_name.'_bar_settings';
        $settings_page->fields = array();

        $field_id = $this->plugin_name.'_minimum_setting';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Minimum amount","cg-woo-grid"),
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'number',
                'id'               => $field_id,
                'name'             => $field_id,
                'min'              => 10, 
                'required'         => 'required="required"',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => 'alternate'
            )
        );
        $field_id = $this->plugin_name.'_bar_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Bar message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Minimum amount to order","cg-woo-grid"),
                'class'            => '',
            )
        );
        $field_id = $this->plugin_name.'_bar_success';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Bar success","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("You can now place your order","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        array_push($settings_pages,$settings_page);

        
        /* Total box */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->plugin_name.'_totalbox_settings';
        $settings_page->fields = array();

        $option_lists = array(
            'right' => __("Right","cg-woo-grid"),
            'bottom' => __("Bottom","cg-woo-grid"),
        );
        $field_id = $this->plugin_name.'_totalbox_position';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Position","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'select',
                'subtype'          => '',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => 'required="required"',
                'get_options_list' => $option_lists,
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => 'alternate',
                'onchange'         => ''
            )
        );
        $field_id = $this->plugin_name.'_totalbox_title';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Title","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Your budget","cg-woo-grid"),
                'class'            => '',
            )
        );
        array_push($settings_pages,$settings_page);


        /* Resume box */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->plugin_name.'_resumebox_settings';
        $settings_page->fields = array();

        $field_id = $this->plugin_name.'_show_resumebox';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Display resume box","cg-woo-grid"),
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'checkbox',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => '',
                'class'            => 'alternate',
                'onchange'         => ''
            )
        );
        $field_id = $this->plugin_name.'_resumebox_priceunit_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Price unit message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("The unit price is","cg-woo-grid"),
                'class'            => '',
            )
        );
        array_push($settings_pages,$settings_page);



        /* Stock */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->plugin_name.'_stock_settings';
        $settings_page->fields = array();

        $field_id = $this->plugin_name.'_minimum_stock';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Low stock threshold","cg-woo-grid"),
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'number',
                'id'               => $field_id,
                'name'             => $field_id,
                'min'              => 0, 
                'required'         => 'required="required"',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'class'            => 'alternate'
            )
        );
        $field_id = $this->plugin_name.'_show_stock';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Show stock in each field","cg-woo-grid"),
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'checkbox',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => '',
                'class'            => '',
                'onchange'         => ''
            )
        );
        $field_id = $this->plugin_name.'_stock_threshold_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Stock threshold message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Sorry, there are only % stocks left for this product","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        $field_id = $this->plugin_name.'_stock_zero_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Stock zero message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Sorry, this product is out of stock","cg-woo-grid"),
                'class'            => '',
            )
        );
        array_push($settings_pages,$settings_page);


        /* Pop up */
        $settings_page         = new \stdClass();
        $settings_page->slug   = $this->plugin_name.'_popup_settings';
        $settings_page->fields = array();

        $field_id = $this->plugin_name.'_popup_message1';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Message 1","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Continue the purchase","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        $field_id = $this->plugin_name.'_popup_message2';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Message 2","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Go to cart","cg-woo-grid"),
                'class'            => '',
            )
        );
        $field_id = $this->plugin_name.'_popup_success_title';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Success Title","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Shopping cart","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        $field_id = $this->plugin_name.'_popup_success_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Success Message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("The cart has been successfully updated","cg-woo-grid"),
                'class'            => '',
            )
        );
        $field_id = $this->plugin_name.'_popup_error_title';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Error Title","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("Insufficient items","cg-woo-grid"),
                'class'            => 'alternate',
            )
        );
        $field_id = $this->plugin_name.'_popup_error_message';
        $settings_page->fields[] = array(
            'id'    => $field_id,
            'title' => __("Pop Up Error Message","cg-woo-grid").":",
            'args'  => array (
                'type'             => 'input',
                'subtype'          => 'text',
                'id'               => $field_id,
                'name'             => $field_id,
                'required'         => '',
                'get_options_list' => '',
                'value_type'       => 'normal',
                'wp_data'          => 'option',
                'placeholder'      => esc_html__("There must be a minimum of %1 products","cg-woo-grid"),
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
                $this->buildFields(
                    $settings_page->slug,
                    $field['id'], 
                    $field['title'], 
                    $field_callback, 
                    $settings_section_id, 
                    $field['args']
                );
            }
        }
    }
    
    /**
     * Administration fields rendering
     *
     * @param [type] $args
     * @return void
     */
    public function render_settings_field($args) {
        /* EXAMPLE INPUT
            'type'            => 'input',
            'subtype'         => '',
            'id'              => $this->plugin_name.'_example_setting',
            'name'            => $this->plugin_name.'_example_setting',
            'required'        => 'required="required"',
            'get_option_list' => "",
            'value_type'      => serialized OR normal,
            'wp_data'         => (option or post_meta),
            'post_id'         =>
        */   

        if($args['wp_data'] == 'option'){

            $wp_data_value = get_option($args['name']);

        } elseif($args['wp_data'] == 'post_meta'){

            $wp_data_value = get_post_meta( 
                $args['post_id'], 
                $args['name'], 
                true 
            );
        }

        $attr_data = null;
        $value     = $wp_data_value;
        $after     = $args['after'] ?? '';


        switch ($args['type']) {

            case 'input':

                switch ($args['subtype']) {

                    case 'checkbox':
                        $checked = ($value) ? 'checked' : '';
                        echo '<input type="'.$args['subtype'].'" 
                                id="'.$args['id'].'" 
                                "'.$args['required'].'" 
                                name="'.$args['name'].'" 
                                value="1" 
                                '.$checked.' 
                            />'.
                            $after;
                    break;
                    case 'radio':
                        $radios = '';
                        foreach($args['get_options_list'] as $val => $title) {
 
                            $checked = ($value == $val) ? 'checked' : '';
                            $radios.= '
                            <input type="'.$args['subtype'].'" 
                                id="'.$args['id'].'_'.$val.'" 
                                "'.$args['required'].'" 
                                name="'.$args['name'].'" 
                                value="'.$val.'"
                                '.$checked.' 
                            ><label for="'.$args['id'].'_'.$val.'">'.$title.'</label>';
                        }
                        echo $radios.$after;
                    break;
                    default:

                        $prependStart = $prependEnd = null;
                        if(isset($args['prepend_value'])) {
                            $prependStart = '<div class="input-prepend"> 
                                                <span class="add-on">'.
                                                    $args['prepend_value'].
                                                '</span>';
                            $prependEnd   = '</div>';
                        }
                        // if($after) {
                        //     $prependStart = $after.
                        //                         '</span>';
                        //     $prependEnd   = '</div>';
                        // }

                        $step        = (isset($args['step'])) ? 'step="'.$args['step'].'"' : '';
                        $min         = (isset($args['min'])) ? 'min="'.$args['min'].'"' : '';
                        $max         = (isset($args['max'])) ? 'max="'.$args['max'].'"' : '';
                        $size        = (isset($args['size'])) ? 'size="'.$args['size'].'"' : 'size="40"';
                        $placeholder = (isset($args['placeholder'])) ? 'placeholder="'.$args['placeholder'].'"' : '';
                        $ref         = 'ref="'.
                            str_replace(
                                "_",
                                "",
                                str_replace(
                                    "-",
                                    "",
                                    $args['id']
                                )
                            ).
                        '"';
                        
                        $inputAttr  = $step.' 
                                    '.$max.' 
                                    '.$min.' 
                                    '.$placeholder.' 
                                    '.$ref.' 
                                    '.$attr_data.'
                                    '.$size.'
                                    value="' . esc_attr($value) . '"';
                        
                        if(isset($args['disabled'])){
                            // hide the actual input bc if it was just a disabled input the information saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                            echo $prependStart.
                                '<input type="'.$args['subtype'].'" 
                                    id="'.$args['id'].'_disabled" 
                                    name="'.$args['name'].'_disabled" 
                                    disabled 
                                    '.$inputAttr.' 
                                />
                                <input type="hidden" 
                                    id="'.$args['id'].'" 
                                    name="'.$args['name'].'" 
                                    '.$inputAttr.' 
                                />'.
                                $prependEnd.
                                $after;
                        } else {
                            echo $prependStart.
                                '<input type="'.$args['subtype'].'" 
                                        id="'.$args['id'].'" 
                                        name="'.$args['name'].'" 
                                        '.$args['required'].'
                                        '.$inputAttr.' 
                                    />'.
                                    $prependEnd.
                                    $after;
                        }
                    break;
                }
            break;

            case 'select':
                
                $options  = '<option disabled selected value> -- select an option -- </option>';
                $selected = get_option($args['name']);
                foreach($args['get_options_list'] as $value => $title) {
                    if($args['value_type'] == 'serialized')  {
                        $serialized = array(
                            'value' => $value,
                            'title' => $title
                        );
                        $value = serialize($serialized);
                    }
                    $options.= "
                    <option value='".$value."' 
                        ".selected( 
                            $selected, 
                            $value,
                            false
                        )."
                    >".$title."</option>";
                }

                $prependStart = $prependEnd = null;
                if(isset($args['prepend_value'])) {
                    $prependStart = '<div class="input-prepend"> 
                                        <span class="add-on">'.
                                            $args['prepend_value'].
                                        '</span>';
                    $prependEnd   = '</div>';
                }
                $onchange = (isset($args['onchange'])) ? 'onchange="'.$args['onchange'].'"' : '';
                
                if(isset($args['disabled'])){
                    // hide the actual input bc if it was just a disabled input the information saved in the database would be wrong - bc it would pass empty values and wipe the actual information
                    echo $prependStart.
                        '<input type="text" 
                            id="'.$args['id'].'_disabled" 
                            name="'.$args['name'].'_disabled" 
                            disabled 
                        />'.
                        $prependEnd.
                        $after;
                } else {
                    echo $prependStart.
                        '<select id="'.$args['id'].'" 
                            name="'.$args['name'].'" 
                            '.$args['required'].' 
                            '.$onchange.'
                        >'.$options.'
                        </select>'.
                        $prependEnd.
                        $after;
                }
            break;

            case 'wp_editor':

                $prependStart = $prependEnd = null;
                if(isset($args['prepend_value'])) {
                    $prependStart = '<div class="editor-prepend"> 
                                        <span class="add-on">'.
                                            $args['prepend_value'].
                                        '</span>';
                    $prependEnd   = '</div>';
                }

                echo $prependStart.
                    wp_editor( $value, 
                        $args['id'], 
                        array()
                    ).
                    $prependEnd.
                    $after;
            break;

            default:
                # code...
            break;
        }
    }

}
