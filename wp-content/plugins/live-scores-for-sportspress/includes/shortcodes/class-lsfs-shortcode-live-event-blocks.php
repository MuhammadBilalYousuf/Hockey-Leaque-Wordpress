<?php
/**
 * Live Event List Shortcode
 *
 * @author      igorbenic
 * @category    Shortcodes
 * @package     LSFS/Shortcodes/Event_Blocks
 * @version     1.0
 */
class LSFS_Shortcode_Live_Event_Blocks {

    /**
     * Output the event list shortcode.
     *
     * @param array $atts
     */
    public static function output( $atts ) {

        if ( ! isset( $atts['id'] ) && isset( $atts[0] ) && is_numeric( $atts[0] ) )
            $atts['id'] = $atts[0];

        lsfs_get_template( 'live-event-blocks.php', $atts );
    }
}