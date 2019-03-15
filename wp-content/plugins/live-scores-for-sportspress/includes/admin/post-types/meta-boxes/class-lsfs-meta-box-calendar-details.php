<?php
/**
 * Calendar Details
 *
 * @author      igorbenic
 * @category    Admin
 * @package     LSFS/Admin/Meta_Boxes
 * @version     0.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * SP_Meta_Box_Calendar_Details
 */
class LSFS_Meta_Box_Calendar_Details extends SP_Meta_Box_Calendar_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        $live = get_post_meta( $post->ID, 'lsfs_calendar_live', true );
        ?>
        <div>
            <p><label for="lsfs_calendar_live"><strong><?php _e( 'Live', 'live-scores-for-sportspress' ); ?></strong></label></p>
            <p>
                <input <?php checked( $live, 'yes', true ); ?> type="checkbox" name="lsfs_calendar_live" id="lsfs_calendar_live" value="yes" />
                <?php _e( 'Check this to replace with the live list. Used only on the page not in shortcode.', 'live-scores-for-sportspress' ); ?>
            </p>
        </div>
        <?php
        parent::output( $post );
    }

    /**
     * Save meta box data
     */
    public static function save( $post_id, $post ) {
        parent::save( $post_id, $post );
        if( isset( $_POST['lsfs_calendar_live'] ) && 'yes' === $_POST['lsfs_calendar_live'] ) {
            update_post_meta( $post_id, 'lsfs_calendar_live', 'yes' );
        } else {
            delete_post_meta( $post_id, 'lsfs_calendar_live' );
        }
    }
}