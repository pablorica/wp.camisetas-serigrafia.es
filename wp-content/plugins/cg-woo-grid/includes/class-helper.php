<?php

// Class name should be capitalized words separated by underscores
class Cg_Woo_Grid
{
    /**** Plugin properties ****/

    protected $plugin_name;
    protected $version;


    /**** Custom Plugin Properties ****/

    protected $css_filepath;
    protected $js_filepath;


    /**** Plugin Constructor and Initializer(Run) ****/

    //The constructor off the class. Initialize all the basics components of the plugin.
    public function __construct()
    {
        //Initialize all the basics components of the plugin
        $this->plugin_name = 'cg-woo-grid';
        $this->version = '1.0.0';

        //set the variable for the example endpoint. you can use a option plugin to store it and change it later in the admin page
        $this->css_filepath = 'public/css/style.css';
        $this->js_filepath = 'public/js/cg-woo-grid.min.js';
    }

    //The run method starts the execution of the plugin. Usually, calls all the following methods needed.
    public function run()
    {
      //call your methods here
      $this->add_actions();
      $this->add_filters();
    }

    /**** Plugin methods ****/

    private function add_actions()
    {
      //put your actions here

      //As an example, we will load a custom style and script for the frontend
      add_action( 'wp_enqueue_scripts', array($this,'codigo_enqueue_style'));
      add_action( 'wp_enqueue_scripts', array($this,'codigo_enqueue_script'));
      add_action('woocommerce_after_single_product_summary', array($this,'codigo_products_grid'), 10);
    }

    private function add_filters()
    {
      //put your filters here

    }

    /**** Hooks methods ****/

    //enqueue a style
    function codigo_enqueue_style() {
    	wp_enqueue_style( $this->plugin_name, CG_WOOGRID_PATH.$this->css_filepath, false );
    }

    //enqueue a script
    function codigo_enqueue_script() {
    	wp_enqueue_script( $this->plugin_name, CG_WOOGRID_PATH.$this->js_filepath, false );
    }

    function codigo_products_grid()
    {
        global $post;
        $minimo = 0;
         ?>
        <div id="pp_popup" style="display: none;">
            <div class="white">
                <h3 class="text-center"></h3>
                <p class="text-center"></p>
                <div class="text-center buttons">
                    <button class="btn btn-danger compra">Seguir comprando</button>
                    <a href="<?php echo get_permalink(wc_get_page_id('cart')); ?>" class="btn btn-danger compra">Ir al carrito de compras</a>
                    <button class="btn btn-danger cerrar">Cerrar</button>
                </div>
            </div>
        </div>

        <form id="pp_form" class="pp_box">

            <h3>Elige colores y tallas para hacer tu presupuesto</h3>
            <div class="row sidebar_configurator">
                <div class="col-xs-12 col-md-5 col-lg-4 pull-right pp_total">
                    <div class="loading" style="display: none;"><i class="fa fa-refresh fa-spin"></i></div>
                    <div>
                        <h3>Tu presupuesto</h3>
                        <div id="pp_unidades" class="unidades">
                            <div>Unidades blancas: <input id="pp_total_b" type="text" name="total_b" value="0 uds." disabled></div>
                            <div>Unidades de color: <input id="pp_total_c" type="text" name="total_c" value="0 uds." disabled></div>
                            <div style="margin-bottom: 15px;text-transform: none;">Precio unitario: <input id="pp_price_u" type="text" name="price_u" value="<?php echo strip_tags(wc_price(0)); ?>" disabled></div>
                            <div style="clear: both;"></div>
                        </div>
                        <div id="pp_tb_precios" class="unidades">
                            
                            <table id="tabla_precios">
                                <thead>
                                    <tr>
                                        <th>Color</th>
                                        <th>Talla</th>
                                        <th>Precio</th>
                                        <th>Unidades</th>
                                        <th class="text-right">Importe</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="total">
                            <h5 style="margin-bottom: 15px;"><small>( Precio unitario:</small> <span id="span_price_u"><?php echo wc_price(0); ?></span> )</h5>
                            <h5><small>Subotal:</small> <span id="TotalPrice"><?php echo wc_price(0); ?></span></h5>
                            <h5><small>IVA:</small> <span id="taxesRow"><?php echo wc_price(0); ?></span></h5>
                            <h4><small>Total:</small> <span id="TotalPriceIVA"><?php echo wc_price(0); ?></span> <small></small></h4>
                        </div>
                    </div>
                    <button id="pp_add_products" disabled class="btn btn-primary text-uppercase text-center">Añadir a la cesta</button>
                    <p>&nbsp;</p>
                    <input type="hidden" name="add-to-cart" value="<?php echo get_queried_object_id(); ?>">
                    <input type="hidden" name="product_id" value="<?php echo get_queried_object_id(); ?>">
                    <input id="pp_description" type="hidden" name="custom_options[description]" value=""><br>
                    <input id="pp_price" type="hidden" name="custom_options[custom_price]" value="0"><br>
                </div>
                <div id="pp_sizes" class="col-xs-12 col-md-7 col-lg-8 sw_precio">
                    <!-- <div class="pp_cbar">
                        <div><span style="color: red">Cantidad mínima de pedido <?=$minimo . ' UDS'; ?></div>
                    </div> -->
                    <div class="pp_cbar">
                        <span class="bar"><span></span></span>
                        0
                        <div class="pull-right"><span class="pull-right__header" style="color: red">Cantidad mínima de pedido | </span><span id="pp_total">0</span><span class="pull-right__slash">/</span><span id="pp_total50"><?php echo (int) $minimo > 0 ? (int) $minimo : 50; ?></span> UNDS.</div>
                    </div>
                    <?php
                    $colores = array();
                    $_product = wc_get_product(get_queried_object_id());
                    $variations1 = $_product->get_children();
                    $variations2 = array();
                    foreach ($variations1 as $value) {
                        $single_variation = new WC_Product_Variation($value);
                        $variations2[] = $single_variation;
                        $v = $single_variation->attributes;
                        $nk = false;
                        foreach ($colores as $k => $vc) {
                            if ($vc['color'] == $v['pa_color']) $nk = $k;
                        }
                        if ($nk === false) {
                            $colores[] = array(
                                'image'	=> wp_get_attachment_url($single_variation->image_id),
                                'color'	=> $v['pa_color'],
                                'sizes'	=> array()
                            );
                            end($colores);
                            $nk = key($colores);
                        }
                        $colores[$nk]['sizes'][] = $v['pa_tamano'];
                    }
                    foreach ($colores as $key => $value) { ?>
                        <div class="pp_product" data-color="<?php echo strtolower($value['color']); ?>">
                            <span>
                                <div>
                                    <img src="<?php echo $value['image']; ?>" alt=""><br>
                                    <?php echo strtolower($value['color']); ?>
                                </div>
                            </span>
                            <div>
                                <div>
                                    <?php
                                    foreach ($value['sizes'] as $k => $v) {
                                        $vid = 0;
                                        foreach ($variations2 as $k2 => $v2) {
                                            if ($v2->attributes['pa_color'] == $value['color'] && $v2->attributes['pa_tamano'] == $v) $vid = $v2->slug;
                                        } ?>
                                        <span data-talla="<?php echo strtoupper($v); ?>">
                                            <?php echo strtoupper($v); ?><br>
                                            <input type="number" name="<?php echo $vid; ?>" value="0" min="0" max="500">
                                        </span>
                                    <?php
                                    } ?>
                                </div>
                            </div>
                        </div>
                    <?php
                    } ?>
                </div>
            </div>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
        </form>
        <?php
    }
}

?>
