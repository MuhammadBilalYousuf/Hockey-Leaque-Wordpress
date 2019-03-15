<?php

/**
 * Class to Adding all the menus
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class LSFS_Admin_Menus {

    public function __construct() {
        // Live Matches Page waiting for a future version
        add_filter( 'admin_menu', array( $this, 'live_matches' ), 10 );
        add_filter( 'admin_menu', array( $this, 'menu_pages' ), 5 ); 
        add_action( 'sportspress_settings_start', array( $this, 'settings_page') );
    }

    public function menu_pages() {
        add_menu_page( __( 'Live', 'live-scores-for-sportspress' ), __( 'Live', 'live-scores-for-sportspress' ), 'manage_sportspress', 'live-scores-for-sportspress', array( $this, 'live_page' ), 'dashicons-marker', '51' );
        add_submenu_page( 'live-scores-for-sportspress', __( 'Integrations', 'live-scores-for-sportspress' ), __( 'Integrations', 'live-scores-for-sportspress' ), 'manage_sportspress', 'lsfs-integrations', array( $this, 'integrations' ), 'dashicons-marker', '51' );
   
    }

    public function live_matches() {
        add_submenu_page( 'edit.php?post_type=sp_event', __( 'Live Events', 'sportspress' ), __( 'Live Events', 'sportspress' ), 'manage_sportspress', 'lsfs-live-matches', array( $this, 'live_matches_page' ) );
    }

    public function live_matches_page() {
        include_once( 'views/html-admin-live-matches.php' );
    }

    public function live_page() {
        include_once( 'views/html-admin-page.php' );
    }

    public function integrations() {
        include_once( 'views/html-admin-integrations.php' );
    }

    public function settings_page() {
        new LSFS_Admin_Settings();
    }

}

new LSFS_Admin_Menus();