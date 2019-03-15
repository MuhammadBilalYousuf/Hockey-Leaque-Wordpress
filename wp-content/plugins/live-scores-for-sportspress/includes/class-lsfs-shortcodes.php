<?php
/**
 * LSFS_Shortcodes class.
 *
 * @class       LSFS_Shortcodes
 * @version     1.0
 * @package     LSFS/Classes
 * @category    Class
 * @author      igorbenic
 */
if( class_exists( 'SP_Shortcodes' ) ) {
    class LSFS_Shortcodes extends SP_Shortcodes {

        /**
         * Init shortcodes
         */
        public static function init() {
            // Define shortcodes
            $shortcodes = array(
                 
                'live_event_list' => __CLASS__ . '::live_event_list', 
                'live_event_blocks' => __CLASS__ . '::live_event_blocks', 
                
            );

            foreach ( $shortcodes as $shortcode => $function ) {
                add_shortcode( $shortcode, $function );
            }
        }

         

        /**
         * Event results shortcode.
         *
         * @access public
         * @param mixed $atts
         * @return string
         */
        public static function live_event_list( $atts ) {
            return self::shortcode_wrapper( array( 'LSFS_Shortcode_Live_Event_List', 'output' ), $atts );
        }

        /**
         * Event Box results shortcode.
         *
         * @access public
         * @param mixed $atts
         * @return string
         */
        public static function live_event_blocks( $atts ) {
            return self::shortcode_wrapper( array( 'LSFS_Shortcode_Live_Event_Blocks', 'output' ), $atts );
        }
     
    }
}