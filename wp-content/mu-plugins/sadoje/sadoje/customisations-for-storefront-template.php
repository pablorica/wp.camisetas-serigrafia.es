<?php

/**
 *
 * This class contains hooks to manipulate the Storefornt Template
 *
 */


 namespace Sadoje;


class Customisations_For_Storefront_Template extends Sadoje_Singleton {


    /**** Plugin properties ****/

    protected $element_name;

    /**** Plugin Initialize ****/


    //The init method is defined by the singleton abstract and it starts the execution of the plugin. Usually, calls all the following methods needed.
    function init() {
        $this->element_name = 'customisations-for-storefront-template';
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
            'get_header', 
            array( 
                $this, 
                'cdg_remove_storefront_sidebar' 
            ),
            18
        );


        //New price fields 
        add_action( 
            'woocommerce_variation_options_pricing', 
            array( 
                $this, 
                'cdg_add_variation_options_pricing_box_price'
            ),
            10, 
            3 
        );
        add_action( 
            'woocommerce_save_product_variation',
            array( 
                $this, 
                'cdg_save_variation_options_custom_prices'
            ),
            10 ,
            2 
        );
        // add_filter( 
        //     'woocommerce_available_variation',
        //     array( 
        //         $this, 
        //         'cdg_display_variation_box_price'
        //     ), 
        //     10, 
        //     3 
        // );
    }


    /**
	 * Frontend_scripts function. 
	 * Hello World (for testing purposes)
	 *
	 * @access public
	 * @return void
	 */
	public function hello_world() {
        error_log('Storefront_Template : Hello World!');
        echo "<h1>Storefront_Template : Hello World!</h1>";
        error_log($this->plugin_url.' '.$this->plugin_path);
	}

    /**
     * Remove Storefront Sidebar
     *
     * @return void
     */
    function cdg_remove_storefront_sidebar() {
        /*
        if ( is_woocommerce() 
            || is_checkout() 
        ) {
            remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
        }
        */
        remove_action( 'storefront_sidebar', 'storefront_get_sidebar', 10 );
    }


    /**
     * This snippet tells WooCommerce to allow 300 variations for a product before it stops 
     * embedding the JavaScript that allows the web browser to dynamically enable and 
     * disable the different dropdown lists. 
     * https://techprimeweb.com/modify-the-ajax-variation-threshold/
     *
     * @param int $qty
     * @param int $product
     * @return void
    */
    function codigo_wc_ajax_variation_threshold( $qty, $product ) {
        return 300;
    }
    //add_filter( 'woocommerce_ajax_variation_threshold', 'codigo_wc_ajax_variation_threshold', 10, 2 );



    /*
    * PRICE BOX FIELDS
    */

    /**
     * Backend Variation - Add / Display Custom Price Fields
     *
     * @param string $loop
     * @param string $variation_data
     * @param WP_POST $variation
     * @return void
     */
    function cdg_add_variation_options_pricing_box_price( 
        $loop, 
        $variation_data,
        $variation 
    ){
        //Price unit (default)
        echo '
            <style>
                label[for="variable_regular_price_'.$loop.'"] {
                    visibility: hidden;
                    position: relative;
                }
                label[for="variable_regular_price_'.$loop.'"]:after{
                    visibility: visible;
                    position: absolute;
                    top: 0;
                    left: 0;
                    min-width: 140px;
                    content:"Precio Unidad (€)";
                }
                div.variable_pricing p[class*="variable_sale_price"]{
                    display:none
                }
            </style>
        ';

        //Price pack
        woocommerce_wp_text_input( array(
            'id'            => 'variable_pack_price_'.$loop,
            'name'          => 'variable_pack_price['.$loop.']',
            'wrapper_class' => 'form-row form-row-last',
            'class'         => 'short wc_input_price',
            'data_type'     => 'price',
            'label'         => __('Precio Paquete','sadoje').' (' . get_woocommerce_currency_symbol() . ')',
            'value'         => wc_format_localized_price( 
                get_post_meta( 
                    $variation->ID, 
                    '_pack_price', 
                    true 
                ) 
            )
        ));

        //Price box
        woocommerce_wp_text_input( array(
            'id'            => 'variable_box_price_'.$loop,
            'name'          => 'variable_box_price['.$loop.']',
            'wrapper_class' => 'form-row form-row-first',
            'class'         => 'short wc_input_price',
            'data_type'     => 'price',
            'label'         => __('Precio Caja','sadoje').' (' . get_woocommerce_currency_symbol() . ')',
            'value'         => wc_format_localized_price( 
                get_post_meta( 
                    $variation->ID, 
                    '_box_price', 
                    true 
                ) 
            )
        ));
    }

    /**
     * Backend Variation - Save Custom Price Field value
     *
     * @param int $variation_id
     * @param string $loop
     * @return void
     */
    function cdg_save_variation_options_custom_prices( 
        $variation_id, 
        $loop 
    ){

        if( isset($_POST['variable_pack_price'][$loop]) ) {
            update_post_meta( 
                $variation_id, 
                '_pack_price', 
                wc_clean(
                    wp_unslash(
                        str_replace( 
                            ',', 
                            '.', 
                            $_POST['variable_pack_price'][$loop] 
                        ) 
                    ) 
                ) 
            );
        }

        if( isset($_POST['variable_box_price'][$loop]) ) {
            update_post_meta( 
                $variation_id, 
                '_box_price',
                wc_clean(
                    wp_unslash(
                        str_replace(
                            ',',
                            '.',
                            $_POST['variable_box_price'][$loop] 
                        ) 
                    ) 
                ) 
            );
        }
    }

    /**
     * Frontend Variation - Custom Price display
     * 
     * Price Unit:
     * echo wc_format_localized_price( $variation_object->get_regular_price() );
     * Price Pack:
     * echo wc_format_localized_price( get_post_meta( $variation->ID, '_pack_price', true ) );
     * Price Box:
     * echo wc_format_localized_price( get_post_meta( $variation->ID, '_box_price', true ) );
     *
     * @param  array                $data
     * @param  WC_Product           $product
     * @param  WC_Product_Variation $variation
     * @return array 
     */
    function cdg_display_variation_box_price( 
        $data, 
        $product, 
        $variation 
    ) {

        if( $bprice = $variation->get_meta('_pack_price') ) {
            $data['price_html'] = '<div class="woocommerce_pack_price">' 
                . __( 'Precio Paquete: ', 'woocommerce' ) 
                .'<span class="pack_price">' 
                . wc_price( $bprice ) 
                . '</span></div>' 
                . $data['price_html'];
        }

        if( $bprice = $variation->get_meta('_box_price') ) {
            $data['price_html'] = '<div class="woocommerce_box_price">' 
                . __( 'Precio Caja: ', 'woocommerce' ) 
                .'<span class="box_price">' 
                . wc_price( $bprice ) 
                . '</span></div>' 
                . $data['price_html'];
        }

        return $data;
    }



}