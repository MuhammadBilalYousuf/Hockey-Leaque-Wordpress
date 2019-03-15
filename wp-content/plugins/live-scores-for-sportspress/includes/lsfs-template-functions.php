<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Functions that are used for displaying and managing templates and parts
 */

/**
 * Calling the Live Event Results
 * @return void 
 */
function lsfs_output_event_live_results() {
    lsfs_get_template( 'live-event-results.php' );
}