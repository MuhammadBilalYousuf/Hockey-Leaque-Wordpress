<?php
/**
 * Calendar Shortcode
 *
 * @author      ThemeBoy
 * @category    Admin
 * @package     SportsPress/Admin/Meta_Boxes
 * @version     1.6.1
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * SP_Meta_Box_Calendar_Shortcode
 */
class LSFS_Meta_Box_Calendar_Shortcode extends SP_Meta_Box_Calendar_Shortcode {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        parent::output( $post );
        $the_format = get_post_meta( $post->ID, 'sp_format', true );
        if ( ! $the_format ) $the_format = 'calendar';

        $shortcodes = apply_filters( 'lsfs_calendar_shortcodes', array(
            'list' => __( 'Live List', 'sportspress' ),
            'blocks' => __( 'Live Box Score', 'sportspress' ),
           // 'calendar' => __( 'Live Calendar', 'sportspress' ),
           // 'blocks' => __( 'Live Box Score', 'sportspress' ),
        ) );
        ?>
        <?php 
            $current_format_shortcode = isset( $shortcodes[ $the_format ] ) ? $shortcodes[ $the_format ] : false;

            if( ! $current_format_shortcode ) {
                return;
            }
            ?>
            <p>
                <strong><?php echo $current_format_shortcode; ?></strong>
            </p>
            <p><input type="text" value="<?php sp_shortcode_template( 'live_event_' . $the_format, $post->ID ); ?>" readonly="readonly" class="code widefat"></p>
        
        <?php
    }
}