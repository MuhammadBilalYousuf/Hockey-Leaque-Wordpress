<?php

/**
 * Plugin Name:     Live Scores for SportsPress
 * Plugin URI:      http://www.livesportspress.com
 * Description:     Enhance SportsPress with Live Scores, Live Minutes, Commentary and other
 * Author:          Igor Benić
 * Author URI:      https://www.ibenic.com
 * Text Domain:     live-scores-for-sportspress
 * Domain Path:     /languages
 * Version:         1.5.0
 *
 * @package         Live_Scores_For_Sportspress
 * 
 * @fs_premium_only /assets/js/lsfspro.bundle.js, /premium/
 */
if ( !defined( 'ABSPATH' ) ) {
    return;
}
// Create a helper function for easy SDK access.
function lsfs_fs()
{
    global  $lsfs_fs ;
    
    if ( !isset( $lsfs_fs ) ) {
        // Include Freemius SDK.
        require_once dirname( __FILE__ ) . '/freemius/start.php';
        $lsfs_fs = fs_dynamic_init( array(
            'id'             => '1530',
            'slug'           => 'live-scores-for-sportspress',
            'type'           => 'plugin',
            'public_key'     => 'pk_7fa321a7997d914c1299435d0d721',
            'is_premium'     => false,
            'has_addons'     => false,
            'has_paid_plans' => true,
            'menu'           => array(
            'slug'    => 'live-scores-for-sportspress',
            'contact' => false,
        ),
            'is_live'        => true,
        ) );
    }
    
    return $lsfs_fs;
}

// Init Freemius.
lsfs_fs();
// Signal that SDK was initiated.
do_action( 'lsfs_fs_loaded' );

if ( !class_exists( 'LSFS' ) ) {
    final class LSFS
    {
        /**
         * Version
         * @var string
         */
        public  $version = '1.5.0' ;
        /**
         * @var LSFS The single instance of the class
         * @since 0.1.0
         */
        protected static  $_instance = null ;
        /**
         * Returning the plugin version
         * @return string 
         */
        public function get_version()
        {
            return $this->version;
        }
        
        /**
         * Main Instance
         *
         * Ensures only one instance of LSFS is loaded or can be loaded.
         *
         * @since 0.1.0
         * @static
         * @see LSFS()
         * @return LSFS - Main instance
         */
        public static function instance()
        {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        
        /**
         * Cloning is forbidden.
         *
         * @since 0.1.0
         */
        public function __clone()
        {
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'live-scores-for-sportspress' ), '0.1.0' );
        }
        
        /**
         * Unserializing instances of this class is forbidden.
         *
         * @since 0.1.0
         */
        public function __wakeup()
        {
            _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'live-scores-for-sportspress' ), '0.1.0' );
        }
        
        /**
         * SportsPress Constructor.
         * @access public
         * @return SportsPress
         */
        public function __construct()
        {
            // Auto-load classes on demand
            if ( function_exists( "__autoload" ) ) {
                spl_autoload_register( "__autoload" );
            }
            spl_autoload_register( array( $this, 'autoload' ) );
            // Define constants
            $this->define_constants();
            // Include required files
            $this->includes();
            if ( class_exists( 'SP_Shortcodes' ) ) {
                add_action( 'init', array( 'LSFS_Shortcodes', 'init' ) );
            }
            add_action( 'init', array( $this, 'check_versions' ) );
            // Loaded action
            do_action( 'live_scores_for_sportspress_loaded' );
            add_action( 'plugins_loaded', array( $this, 'load_integrations' ), 99 );
        }
        
        public function check_versions()
        {
            
            if ( !defined( 'IFRAME_REQUEST' ) && get_option( 'lsfs_version', '1.0.0' ) !== $this->version ) {
                $installer = new LSFS_Installer();
                $installer->update( get_option( 'lsfs_version', '1.0.0' ) );
                do_action( 'lsfs_updated', $this->version );
            }
        
        }
        
        /**
         * Define LSFS Constants.
         */
        private function define_constants()
        {
            define( 'LSFS_PLUGIN_FILE', __FILE__ );
            define( 'LSFS_VERSION', $this->version );
            define( 'LSFS_URI', plugin_dir_url( __FILE__ ) );
            define( 'LSFS_PATH', plugin_dir_path( __FILE__ ) );
        }
        
        /**
         * Returning the plugin path
         * @return void 
         */
        public function plugin_path()
        {
            return untrailingslashit( LSFS_PATH );
        }
        
        /**
         * Get the template path.
         *
         * @return string
         */
        public function template_path()
        {
            return apply_filters( 'LSFS_TEMPLATE_PATH', 'sportspress/' );
        }
        
        /**
         * Auto-load LSFS classes on demand to reduce memory consumption.
         *
         * @param mixed $class
         * @return void
         */
        public function autoload( $class )
        {
            $path = null;
            $class = strtolower( $class );
            $file = 'class-' . str_replace( '_', '-', $class ) . '.php';
            
            if ( strpos( $class, 'lsfs_shortcode_' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/shortcodes/';
            } elseif ( strpos( $class, 'lsfs_meta_box' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/admin/post-types/meta-boxes/';
            } elseif ( strpos( $class, 'lsfs_admin' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/admin/';
            }
            
            if ( strpos( $class, 'lsfs_premium_meta_box' ) === 0 ) {
                $path = $this->plugin_path() . '/premium/admin/post-types/meta-boxes/';
            }
            
            if ( $path && is_readable( $path . $file ) ) {
                include_once $path . $file;
                return;
            }
            
            // Fallback
            if ( strpos( $class, 'lsfs_' ) === 0 ) {
                $path = $this->plugin_path() . '/includes/';
            }
            
            if ( $path && is_readable( $path . $file ) ) {
                include_once $path . $file;
                return;
            }
        
        }
        
        /**
         * Including integrations
         * @return void
         */
        private function include_integrations()
        {
            do_action( 'lsfs_include_integrations_before' );
            include_once 'includes/integrations/class-lsfs-scorespro-free.php';
            include_once 'includes/integrations/class-lsfs-notifications.php';
            do_action( 'lsfs_include_integrations_after' );
        }
        
        /**
         * Including Files
         * @return void 
         */
        private function includes()
        {
            include_once 'includes/abstracts/class-lsfs-settings.php';
            include_once 'includes/abstracts/class-lsfs-integration.php';
            include_once 'includes/lsfs-core-functions.php';
            include_once 'includes/lsfs-log-functions.php';
            include_once 'includes/lsfs-live-functions.php';
            include_once 'includes/lsfs-formatting-functions.php';
            include_once 'includes/lsfs-template-functions.php';
            include_once 'includes/lsfs-integration-functions.php';
            include_once 'includes/lsfs-update-functions.php';
            include_once 'includes/class-lsfs-widgets.php';
            include_once 'includes/class-lsfs-installer.php';
            $this->include_integrations();
            if ( is_admin() ) {
                include_once 'includes/admin/class-lsfs-admin.php';
            }
            if ( defined( 'DOING_AJAX' ) ) {
                $this->ajax_includes();
            }
            if ( !is_admin() || defined( 'DOING_AJAX' ) ) {
                $this->frontend_includes();
            }
            include_once 'includes/class-lsfs-post-types.php';
            include_once 'includes/class-lsfs-templating.php';
        }
        
        private function ajax_includes()
        {
            include_once 'includes/class-lsfs-live-ajax.php';
        }
        
        private function frontend_includes()
        {
            include_once 'includes/class-lsfs-frontend-scripts.php';
        }
        
        /**
         * Loading Integrations
         * @return array 
         */
        public function load_integrations()
        {
            $active_integrations = lsfs_get_active_integrations();
            $integrations = lsfs_get_integrations();
            foreach ( $active_integrations as $slug => $integration ) {
                if ( isset( $integrations[$slug] ) ) {
                    $this->integrations[$slug] = new $integration();
                }
            }
        }
    
    }
    /**
     * Returning the LSFS Instance
     *
     * @since  0.1.0 
     * @return LSFS 
     */
    function LSFS()
    {
        return LSFS::instance();
    }
    
    add_action( 'sportspress_loaded', 'LSFS' );
}

/**
 * Activation Hook
 * @return void
 */
function lsfs_activate()
{
    include_once 'includes/class-lsfs-installer.php';
    $installer = new LSFS_Installer();
    $installer->install();
}

register_activation_hook( __FILE__, 'lsfs_activate' );