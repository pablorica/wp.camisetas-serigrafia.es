<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/public
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */

namespace Cg_Woo_Grid_Public;

use Error;

class Functions {

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

    protected $css_filepath;
    protected $js_filepath;
    protected $template_filepath;
    //protected $vue_filepath;
    //protected $vue_version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( 
        $plugin_name, 
        $version 
    ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        //set the variable for the example endpoint. you can use a option plugin to store it and change it later in the admin page
        $this->css_filepath      = CG_WOOGRID_URL.'public/css/cg-woo-grid.css';
        $this->js_filepath       = CG_WOOGRID_URL.'public/js/cg-woo-grid.js';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        wp_enqueue_style( 
            $this->plugin_name, 
            $this->css_filepath, 
            array(),
            $this->version, 
            'all' 
        );
        wp_enqueue_style(
            $this->plugin_name.'_fontawesome',	
            'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', 
            array(), 
            '4.7.1'
        );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

        wp_enqueue_script( 
            $this->plugin_name, 
            $this->js_filepath, 
            array( 
                'jquery',
                 $this->plugin_name.'-vue'
            ),
            $this->version, 
            true 
        );
	}

    /**
     * Checks if cart has been updated
     *
     * @param  bool             $url
     * @param  null|WC_Product  $adding_to_cart
     * @return bool
     */
    public function cdg_updated_cart(
        $url, 
        $adding_to_cart 
    ) {
        if($adding_to_cart instanceof \WC_Product) {
            echo "<success>1</success>";
        }
        return $url;
    }
    //add_filter(  'woocommerce_add_to_cart_redirect',  'cdg_updated_cart' );


    
    /**
     * Add multiple product variations to cart
     *
     * @param boolean $url
     * @return void
     */
    public function cdg_add_multiple_variations_to_cart( 
        $url = false 
    ) {
        // Make sure WC is installed, and add-to-cart query arg exists, and contains at least one comma.
        if ( ! class_exists( 'WC_Form_Handler' ) 
            || empty( $_REQUEST['add-to-cart'] )  
            || false === strpos( $_REQUEST['add-to-cart'], ':' )
        ) {
            return;
        }
    
        // Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
        remove_action( 
            'wp_loaded', 
            array( 'WC_Form_Handler', 'add_to_cart_action' ), 
            20 
        );

        //Only one variation has been selected
        if(false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {

            $id_and_quantity = explode( ':', $_REQUEST['add-to-cart'] );
            $product_id = $id_and_quantity[0];

            $_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

            $_REQUEST['add-to-cart'] = $product_id;
            return \WC_Form_Handler::add_to_cart_action( $url );

        } else { //Multiple variations

            $product_ids = explode( ',', $_REQUEST['add-to-cart'] );
            $count       = count( $product_ids );
            $number      = 0;
        
            foreach ( $product_ids as $id_and_quantity ) {
                // Check for quantities defined in curie notation (<product_id>:<product_quantity>)
                
                $id_and_quantity = explode( ':', $id_and_quantity );
                $product_id = $id_and_quantity[0];
        
                $_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;
        
                if ( ++$number === $count ) {
                    // Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
                    $_REQUEST['add-to-cart'] = $product_id;
                    return \WC_Form_Handler::add_to_cart_action( $url );
                    //$url = apply_filters( 'woocommerce_add_to_cart_redirect', $url, $adding_to_cart );
                }
        
                $product_id = apply_filters( 
                    'woocommerce_add_to_cart_product_id', 
                    absint( $product_id ) 
                );
                //$was_added_to_cart = false;
                $adding_to_cart = wc_get_product( $product_id );
        
                if ( ! $adding_to_cart ) {
                    continue;
                }
        
                $add_to_cart_handler = apply_filters( 
                    'woocommerce_add_to_cart_handler', 
                    $adding_to_cart->get_type(), 
                    $adding_to_cart 
                );
        
                // Variable product handling
                if ( 'variable' === $add_to_cart_handler ) {
                    $this->woo_hack_invoke_private_method( 
                        'WC_Form_Handler', 
                        'add_to_cart_handler_variable', 
                        $product_id 
                    );
        
                // Grouped Products
                } elseif ( 'grouped' === $add_to_cart_handler ) {
                    $this->woo_hack_invoke_private_method( 
                        'WC_Form_Handler', 
                        'add_to_cart_handler_grouped', 
                        $product_id 
                    );
        
                // Custom Handler
                } elseif (
                    has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) 
                ){
                    do_action( 
                        'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, 
                        $url 
                    );
        
                // Simple Products
                } else {
                    $this->woo_hack_invoke_private_method( 
                        'WC_Form_Handler', 
                        'add_to_cart_handler_simple', 
                        $product_id 
                    );
                }
            }
        }
    }
    
    
    
    /**
     * Invoke class private method
     *
     * @since   0.1.0
     *
     * @param   string $class_name
     * @param   string $methodName
     *
     * @return  mixed
     */
    public function woo_hack_invoke_private_method( 
        $class_name, 
        $methodName 
    ) {
        if ( version_compare( phpversion(), '5.3', '<' ) ) {
            throw new \Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
        }
    
        $args = func_get_args();
        unset( $args[0], $args[1] );
        $reflection = new \ReflectionClass( $class_name );
        $method = $reflection->getMethod( $methodName );
        $method->setAccessible( true );
    
        //$args = array_merge( array( $class_name ), $args );
        $args = array_merge( array( $reflection ), $args );
        return call_user_func_array( 
                    array( $method, 'invoke' ), 
                    $args 
                );
    }


    /**
     * Overwrite woocommerce template file
     *
     * @param string $template
     * @param string $template_name
     * @param string $template_path
     * @return string
     */
    function cdg_woo_overwrite_template( 
        $template, 
        $template_name, 
        $template_path 
    ) {
        global $woocommerce;

        $_template = $template;
        if ( ! $template_path ) {
            $template_path = $woocommerce->template_url;
        }
    
        $plugin_path  = CG_WOOGRID_PATH.'/public/template/woocommerce/';
    
        // Look within passed path within the theme - this is priority
        $template = locate_template(
            array(
                $template_path . $template_name,
                $template_name
            )
        );
        
        if( ! $template 
            && file_exists( $plugin_path . $template_name ) 
        ){ 
            $template = $plugin_path . $template_name;
        }
        
        if ( ! $template ) {
            $template = $_template;
        }

        return $template;
    }

    /**
	 * Build the products attributes grid
	 *
	 * @since  1.0.0
     * @see    https://www.webroomtech.com/woocommerce-add-multiple-products-to-cart-via-url/
     * @see    https://avada.io/woocommerce/docs/select-multiple-variations.html
     * @see    https://stackoverflow.com/questions/51606535/how-to-add-multiple-variable-products-to-the-cart-via-the-add-to-cart-query-stri
     * 
     * @return void || WP_Error
	 */
    function cdg_products_grid() {
        $rest_api = new Rest_Api( 
            $this->plugin_name, 
            $this->version
        );
        $args = array();
        
        if( null !== get_queried_object_id()) {
            $args['product'] = wc_get_product(get_queried_object_id());
        } else {
            // If the code somehow executes to here something bad happened return a 500.
            return new \WP_Error( 
                'rest_api_sad', 
                esc_html__( 'Something went wrong', "cg-woo-grid" ), 
                array( 'status' => 500 ) 
            );
        }

        //$args['taxonomy_y'] = 'pa_color';
        //$args['taxonomy_x'] = 'pa_tamano';
        $args['taxonomy_y'] = get_option($this->plugin_name.'_taxonomy_y_setting');
        $args['taxonomy_x'] = get_option($this->plugin_name.'_taxonomy_x_setting');
        

        if($matrix = $rest_api->build_product_matrix($args)) {
            $minimum    = 0;
            include_once 'partials/'.$this->plugin_name.'-public-display.php'; 
        } 

        else {
            // Return a WP_Error because the request product was not found. In this case we return a 404 because the main resource was not found.
            return new \WP_Error( 
                'product_invalid', 
                esc_html__( 'No results', "cg-woo-grid" ), 
                array( 'status' => 404 ) 
            );
        }
    }


    /**
     * Custom order description field
     */
    
    /**
     * Show description field in cart
     *
     * @return void
     */
    function cdg_add_cart_description() {

        $description = '';

        if(isset($_COOKIE['cgWooGrid'])) {

            $cgWooGrid = json_decode( 
                html_entity_decode( 
                    stripslashes (
                        $_COOKIE['cgWooGrid'] 
                    ) 
                ) 
            );

            $description = implode("<br/>", $cgWooGrid->description);
        }

        if($description) {
            echo '<tr><td colspan="6" class="description">'.$description.'</td></tr>';
        }
        
    }


    /**
     * TODO: Update description with variations quantities on cart update
     * $cart_updated = apply_filters( 'woocommerce_update_cart_action_cart_updated', $cart_updated );
     * 
     * @see https://stackoverflow.com/questions/49493085/which-hook-is-running-after-woocommerce-update-cart-button-action
     * @see https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-form-handler.html#source-view.696
     * @see https://www.businessbloomer.com/woocommerce-get-cart-info-total-items-etc-from-cart-object/
     *
     * @param [bool] $cart_updated
     * @return void
     */
    function on_action_cart_updated( $cart_updated ){
        /*
        $description .= $nameproduct." - ".$numberofprods ." ".__("products","cg-woo-grid").": ";
            foreach ($gridattributes as $k => $v) {
                $description .= ($k != 0 ? ' - ' : '') . ucfirst($v['y']) . ', ';
                foreach ($v['x'] as $kx => $vx) {
                    $description .= ($kx != 0 ? ', ' : '') . strtoupper($vx[0]) . ' (' . $vx[1] . ')';
                }
            }
        */

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            $product_id = $cart_item['product_id'];
            $variation_id = $cart_item['variation_id'];
            $quantity = $cart_item['quantity'];
            // Anything related to $product, check $product tutorial
            $attributes = $product->get_attributes();
            $whatever_attribute = $product->get_attribute( 'whatever' );
            $whatever_attribute_tax = $product->get_attribute( 'pa_whatever' );

            $tax_y_slug = get_option($this->plugin_name.'_taxonomy_y_setting');
            $tax_x_slug = get_option($this->plugin_name.'_taxonomy_x_setting');

            $any_attribute = $cart_item['variation']['attribute_whatever'];
            $meta = wc_get_formatted_cart_item_data( $cart_item );

        }
    }

    /**
     * Add custom description field to checkout
     * @see https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
     * @see https://rudrastyh.com/woocommerce/woocommerce_form_field.html
     * 
     * @return void
     */
    function cdg_add_checkout_description() {

        $description = '';

        if(isset($_COOKIE['cgWooGrid'])) {

            $cgWooGrid = json_decode( 
                html_entity_decode( 
                    stripslashes (
                        $_COOKIE['cgWooGrid'] 
                    ) 
                ) 
            );
            
            $description = implode("<br/>", $cgWooGrid->description);
        }
        else {
            $checkout    =  WC()->checkout;
            $description = $checkout->get_value( 'cdg_order_description' );
        }

        
        echo '<div id="cdg_order_description_checkout_field">';

        woocommerce_form_field('cdg_order_description',
            array(
                'type'          => 'textarea',
                'class'         => array('form-row-wide'),
                //'label'         => esc_html__('Order Description', 'cg-woo-grid'),
                'placeholder'   => esc_html__( 'Purchase description', 'cg-woo-grid' ),
            ),
            $description
        );

        echo '</div>';
    }

    /**
     * Update the order meta with field value
     * @see https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
     *
     * @param  int $order_id
     * @return void
     */
    function cdg_checkout_description_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['cdg_order_description'] ) ) {
            update_post_meta( 
                $order_id, 
                'cdg_order_description', 
                sanitize_text_field( $_POST['cdg_order_description'] ) 
            );
        }
    }

    /**
     * Display field value on the order edit page
     * @see https://woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/
     *
     * @param  int  $order_id
     * @return void
     */
    function cdg_checkout_description_display_admin_order_meta($order_id){
        if( get_post_meta( $order_id, 
                'cdg_order_description' 
            ) 
        ){
            echo '<div style="clear:both; text-align:left;background: #d7cad2;
            margin: -1.6em -2em 0 -2em;padding: 5px 0 1px 0;">'
                .'<div style="padding: 1.5em 2em;background:white">' 
                . get_post_meta( 
                    $order_id, 
                    'cdg_order_description', 
                    true 
                ) 
                . '</div>'
                . '</div>';
        }
    }

    /**
     * Add the custom field called 'cdg_order_description' to WooCommerce emails
     * @see https://wpbeaches.com/add-a-required-checkbox-field-in-woocommerce-checkout-page/
     *
     * @param  array $fields
     * @param  bool $sent_to_admin
     * @param  WC_Order $order
     * @return array
     */
    function cdg_checkout_description_add_order_meta_fields_email( 
        $fields, 
        $sent_to_admin, 
        $order 
    ) {

        //this is how its rendered: '<p><strong>' . $field['label'] . ':</strong> ' . $field['value'] . '</p>'; 
        
        $description = get_post_meta( $order->get_id(), 'cdg_order_description', true );

        $fields['cdg_order_description'] = array(
            'label' => '</strong></p><h2 style="color: #7f54b3; display: block; font-family: "Helvetica Neue",Helvetica,Roboto,Arial,sans-serif; font-size: 18px; font-weight: bold; line-height: 130%; margin: 0 0 18px; text-align: left;">'.esc_html__( 'Purchase description', 'cg-woo-grid' ).'</h2><strong style="display:none">',
            'value' => '<p style="margin: 0 0 16px;">'.$description,
        );
        return $fields;
    }



}
