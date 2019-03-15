<?php

/**
 * The basic admin class that will handle everything related to Admin
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class LSFS_Admin {

    public function __construct() {
        add_action( 'init', array( $this, 'includes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue') );
        //add_filter( 'sportspress_event_shortcodes', array( $this, 'event_shortcodes' ) );
    }

    public function includes() {
        include_once( 'class-lsfs-admin-menus.php' );
        include_once( 'class-lsfs-admin-config.php' );
        include_once( 'class-lsfs-admin-ajax.php' );
    }

    public function enqueue( $hook ) {

        wp_enqueue_style( 'lsfs-admin-css', LSFS_URI . 'assets/css/admin.min.css', '', '', 'screen' );

        wp_register_script( 'lsfs-admin-js', LSFS_URI . 'assets/js/admin.bundle.js', array( 'jquery', 'wp-util' ), '', true );

        wp_localize_script( 'lsfs-admin-js', 'lsfs', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'lsfs-ajax' ),
            'text'    => array(
                'activate' => __( 'Activate', 'live-scores-for-sportspress' ),
                'deactivate' => __( 'Deactivate', 'live-scores-for-sportspress' ),
            ),
        ));

        wp_enqueue_script( 'lsfs-admin-js' );
    }

    public function event_shortcodes( $shortcodes ) {
        $shortcodes['live_event_list'] = __( 'Live Event List', 'live-scores-for-sportspress' ); 
        return $shortcodes;
    }

}

return new LSFS_Admin();