<?php

/**
 * Live Scores for SportsPress Main Widgets Class
 * This class is used to load all the widgets
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Main Widget Class
 */
class LSFS_Widgets {

    public function __construct() {
        add_action( 'sportspress_widgets', array( $this, 'widgets' ) );
    }

    /**
     * Loading all the widgets and registering them.
     * @return void 
     */
    public function widgets() {
        include_once 'widgets/class-lsfs-widget-event-list.php';
    }
}

new LSFS_Widgets();