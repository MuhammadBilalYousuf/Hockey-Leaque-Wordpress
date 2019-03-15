<?php
/**
 * Handle frontend forms
 *
 * @class       LSFS_Frontend_Scripts
 * @version     1.0
 * @package     LSFS/Classes
 * @category    Class
 * @author      igorbenic
 */
class LSFS_Frontend_Scripts {

    public $theme;

    /**
     * Constructor
     */
    public function __construct () {
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) ); 
    }

    /**
     * Register/queue frontend scripts.
     *
     * @access public
     * @return void
     */
    public function load_scripts() {
        // Scripts
        if( ! wp_script_is( 'lsfs' ) ) {
            wp_enqueue_script( 'lsfs', plugin_dir_url( LSFS_PLUGIN_FILE ) .'assets/js/lsfs.bundle.js', array( 'jquery', 'wp-util' ), LSFS()->version, true);
        }
        
        // Localize scripts
        wp_localize_script( 'lsfs', 'lsfs', apply_filters( 'lsfs_js', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'lsfs-public-nonce' ),
            'live_refresh' => absint( lsfs_live_refresh_rate() ) * 1000
        )));

        wp_enqueue_style( 'lsfs-css', plugin_dir_url( LSFS_PLUGIN_FILE ) . 'assets/css/lsfs.min.css' );
        
    } 
}

new LSFS_Frontend_Scripts();