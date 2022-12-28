<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 * 
 * Class name should be capitalized words separated by underscores
 *
 * @since      1.0.0
 * @package    cg-woo-grid
 * @subpackage cg-woo-grid/includes
 * @author     Pablo Rica <pablo@codigo.co.uk>
 */


class Cg_Woo_Grid
{
    /**** Plugin properties ****/

    /**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Cg_Woo_Grid_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

    /**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;


    /**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

    /**** Custom Plugin Properties ****/

    protected $vue_filepath;
    protected $vue_version;
    protected $axios_filepath;

    /**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
     * 
     * Initialize all the basics components of the plugin (run).
	 *
	 * @since    1.0.0
	 */
    public function __construct()
    {
        if ( defined( 'CG_WOOGRID_VERSION' ) ) {
			$this->version = CG_WOOGRID_VERSION;
		} else {
			$this->version = '1.0.0';
		}

        if ( defined( 'CG_WOOGRID_NAME' ) ) {
			$this->plugin_name = CG_WOOGRID_NAME;
		} else {
			$this->plugin_name = 'cg-woo-grid';
		}

        if ( defined( 'CG_WOOGRID_STATUS' ) ) {
            if(CG_WOOGRID_STATUS == 'development'){
                //https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.js
                //$this->vue_filepath = CG_WOOGRID_URL.'vue/js/vue.js';
                $this->vue_filepath = 'https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.js';
            }
            if(CG_WOOGRID_STATUS == 'production'){
                $this->vue_filepath = 'https://cdnjs.cloudflare.com/ajax/libs/vue/3.2.45/vue.global.prod.min.js';
                //$this->vue_filepath = CG_WOOGRID_URL.'vue/js/vue.global.prod.min.js';
            }
        }
        $this->vue_version = '3.2.45';

        $this->axios_filepath      = "https://unpkg.com/axios/dist/axios.min.js";
        $this->vuecookies_filepath = "https://unpkg.com/vue-cookies@1.8.2/vue-cookies.js";

		$this->load_dependencies();
        $this->define_global_hooks();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
    }

    /**
	 * Register the JavaScript for the global site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_global_scripts() {

        //vue.js loads first
        wp_enqueue_script( 
            $this->plugin_name.'-vue',  
            $this->vue_filepath,
            array(), 
            $this->vue_version, 
            false
        );
        //axios.js 
        wp_enqueue_script( 
            $this->plugin_name.'-axios',  
            $this->axios_filepath,
            array(
                $this->plugin_name.'-vue'
            ), 
            '', 
            false
        );
        //vue-cookies
        wp_enqueue_script( 
            $this->plugin_name.'-vuecookies',  
            $this->vuecookies_filepath,
            array(
                $this->plugin_name.'-vue'
            ), 
            '', 
            false
        );
	}

    

    /**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Cg_Woo_Grid_Loader. Orchestrates the hooks of the plugin.
	 * - Cg_Woo_Grid_i18n. Defines internationalization functionality.
	 * - Cg_Woo_Grid_Admin. Defines all hooks for the admin area.
	 * - Cg_Woo_Grid_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once 'class-cg-woo-grid-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once 'class-cg-woo-grid-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once CG_WOOGRID_PATH . '/admin/class-cg-woo-grid-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
        require_once CG_WOOGRID_PATH . '/public/class-cg-woo-grid-public.php';
        require_once CG_WOOGRID_PATH . '/public/class-cg-woo-grid-public-rest-api.php';
        

		$this->loader = new Cg_Woo_Grid_Loader();

	}

    /**
	 * Register all global hooks
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_global_hooks() {

		$this->loader->add_action( 
            'wp_enqueue_scripts', 
            $this, 
            'enqueue_global_scripts' 
        );

        $this->loader->add_action( 
            'admin_enqueue_scripts', 
            $this, 
            'enqueue_global_scripts' 
        );

        
    }

    /**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Cg_Woo_Grid_i18n();
        
		$this->loader->add_action( 
            'wp_loaded', //'plugins_loaded', 
            $plugin_i18n, 
            'load_plugin_translations'
        );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Cg_Woo_Grid_Admin( 
            $this->get_plugin_name(), 
            $this->get_version() 
        );

        $this->loader->add_action(
            'admin_menu',
            $plugin_admin, 
            'addAdminMenu',
            9
        );   
        
        $this->loader->add_action(
            'admin_init', 
            $plugin_admin, 
            'registerAndBuildFields'
        );


		$this->loader->add_action( 
            'admin_enqueue_scripts', 
            $plugin_admin, 
            'enqueue_scripts' 
        );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Cg_Woo_Grid_Public\Functions( 
            $this->get_plugin_name(), 
            $this->get_version() 
        );

		$this->loader->add_action( 
            'wp_enqueue_scripts', 
            $plugin_public, 
            'enqueue_styles' 
        );
		$this->loader->add_action( 
            'wp_enqueue_scripts', 
            $plugin_public, 
            'enqueue_scripts' 
        );

        $this->loader->add_action( 
            'woocommerce_after_single_product_summary', 
            $plugin_public, 
            'cdg_products_grid',
            19
        );

		//add_action( 'wp_loaded', 'cdg_add_multiple_variations_to_cart', 15 );
		$this->loader->add_action( 
            'wp_loaded', 
            $plugin_public, 
            'cdg_add_multiple_variations_to_cart',
			15
        );

       //add_filter( 'woocommerce_locate_template', 'cdg_woo_overwrite_template', 1, 3 );
        $this->loader->add_filter( 
            'woocommerce_locate_template',
            $plugin_public, 
            'cdg_woo_overwrite_template',
            1,
            3
        );


        $this->loader->add_filter( 
            'woocommerce_add_to_cart_redirect',
            $plugin_public, 
            'cdg_updated_cart',
            20,
            2
        );



        $plugin_public_restapi = new Cg_Woo_Grid_Public\Rest_Api( 
            $this->get_plugin_name(), 
            $this->get_version() 
        );

        $this->loader->add_action( 
            'rest_api_init', 
            $plugin_public_restapi, 
            'add_routes_api'
        );
        $this->loader->add_action( 
            'wp_head', 
            $plugin_public_restapi, 
            'custom_constants_js',
            '1'
        );
	}

    /**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Cg_Woo_Grid_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}

?>
