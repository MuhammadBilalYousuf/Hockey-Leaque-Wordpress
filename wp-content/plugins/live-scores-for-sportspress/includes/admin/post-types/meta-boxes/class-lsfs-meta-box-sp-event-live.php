<?php
/**
 * Adding a Metabox to the SP Events for Live Details
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class LSFS_Meta_Box_SP_Event_Live {
    
     /**
     * Output the metabox
     */
    public static function output( $post ) {
        $event_id = $post->ID;
        LSFS_Live_Event::form( $event_id );
    }

    /**
     * Saving the metabox
     */
    public static function save( $post_id, $post ) {
        
         
    }
}