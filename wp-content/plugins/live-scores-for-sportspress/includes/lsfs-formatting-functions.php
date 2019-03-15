<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Live Formatting functions
 */

/**
 * Returning minutes based on seconds
 * @param  integer $seconds 
 * @return integer          
 */
function lsfs_seconds_to_minutes( $seconds ) {
    return floor( $seconds / 60 );
}