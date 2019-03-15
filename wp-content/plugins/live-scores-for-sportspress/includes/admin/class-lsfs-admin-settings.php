<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * LSFS Admin Settings Class
 * Used to define Live settings on SportsPress Settings Page
 */

class LSFS_Admin_Settings {

    public function __construct() {
        add_filter( 'sportspress_settings_tabs_array', array( $this, 'tabs' ), 99 );
        add_filter( 'sportspress_get_settings_pages', array( $this, 'settings_pages' ) );
    }

    public function tabs( $tabs ) {
        $tabs['lsfs'] = __( 'Live', 'live-scores-for-sportspress' );
        return $tabs;
    }

    public function settings_pages( $pages ) {
        $pages[] = include( 'settings/class-lsfs-settings-live.php' );
    }
}