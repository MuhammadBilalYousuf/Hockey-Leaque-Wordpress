<?php

/**
 * Class to handle the SportsPress configuration page
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class LSFS_Admin_Config {

    public function __construct() {
        add_filter( 'sportspress_config_page', array( $this, 'config_page' ), 1 );
    }

    public function config_page() {
        include_once( 'views/html-admin-config.php' );
    }
}

new LSFS_Admin_Config();