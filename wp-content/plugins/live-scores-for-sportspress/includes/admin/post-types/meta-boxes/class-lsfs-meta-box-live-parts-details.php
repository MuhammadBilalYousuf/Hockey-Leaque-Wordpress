<?php

/**
 * Live Parts Metabox
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

class LSFS_Meta_Box_Live_Parts_Details {

    /**
     * Output the metabox
     */
    public static function output( $post ) {
        wp_nonce_field( 'sportspress_save_data', 'sportspress_meta_nonce' );
        $lsfs_live      = get_post_meta( $post->ID, 'lsfs_live', true );
        $lsfs_live_type = get_post_meta( $post->ID, 'lsfs_live_type', true );
        
        if( ! $lsfs_live_type ) {
            $lsfs_live_type = 'live';
        }

        $duration           = isset( $lsfs_live['duration'] ) ? $lsfs_live['duration'] : 0;
        $stoppage           = isset( $lsfs_live['stoppage'] ) ? $lsfs_live['stoppage'] : 0;
        $text_after_minutes = isset( $lsfs_live['text_after_minutes'] ) ? $lsfs_live['text_after_minutes'] : 'text';
        $text_after         = isset( $lsfs_live['text_after'] ) ? $lsfs_live['text_after'] : '';
        ?>
        <p>
            <label for="lsfs_live_type"><strong><?php _e( 'Type', 'live-scores-for-sportspress' ); ?></strong></label>
            <br/>
            <label><input <?php checked( $lsfs_live_type, 'live', true ); ?> id="lsfs_live_type" name="lsfs_live_type" type="radio" value="live" placeholder="0" /> <?php _e( 'Live', 'live-scores-for-sportspress' ); ?></label>
            <br/>
            <label><input <?php checked( $lsfs_live_type, 'pause', true ); ?> id="lsfs_live_type" name="lsfs_live_type" type="radio" value="pause" placeholder="0" /> <?php _e( 'Paused Period', 'live-scores-for-sportspress' ); ?></label>
        
        </p>
        <p>
            <label for="lsfs_live[duration]"><strong><?php _e( 'Duration (minutes)', 'live-scores-for-sportspress' ); ?></strong></label>
            <input id="lsfs_live[duration]" name="lsfs_live[duration]" type="number" value="<?php echo $duration; ?>" class="widefat" placeholder="0" />
        </p>
        <p>
            <label for="lsfs_live[stoppage]"><strong><?php _e( 'Stoppage Minutes', 'live-scores-for-sportspress' ); ?></strong></label>
            <label><input <?php checked( $stoppage, '0', true ); ?> id="lsfs_live[stoppage]" name="lsfs_live[stoppage]" type="radio" value="0"  /> No</label>
            <label><input <?php checked( $stoppage, '1', true ); ?> id="lsfs_live[stoppage]" name="lsfs_live[stoppage]" type="radio" value="1"  /> Yes</label>
        </p>
        <p>
            <label for="lsfs_live[text_after_minutes]"><strong><?php _e( 'Text After', 'live-scores-for-sportspress' ); ?></strong></label><br/>
            <span class="description"><?php _e( 'This is the text that will appear instead of minutes after it ends. Or show current minutes.', 'live-scores-for-sportspress' ); ?></span>
            <br/>
            <label>
                <input <?php checked( $text_after_minutes, 'text', true ); ?> type="radio" name="lsfs_live[text_after_minutes]" id="lsfs_live[text_after_minutes]"  value="text" /> 
                <input type="text" name="lsfs_live[text_after]" id="lsfs_live[text_after]" value="<?php echo $text_after; ?>" />
            </label>
            <br/>
            <label><input <?php checked( $text_after_minutes, 'minutes', true ); ?> type="radio" name="lsfs_live[text_after_minutes]" id="lsfs_live[text_after_minutes]" value="minutes" /> <?php _e( 'Minutes', 'live-scores-for-sportspress' ); ?></label>
        </p>
        <?php
    }

    /**
     * Saving the metabox
     */
    public static function save( $post_id, $post ) {
        
        if( isset( $_POST['lsfs_live_type'] ) ) {
            update_post_meta( $post_id, 'lsfs_live_type', $_POST['lsfs_live_type'] );
        }

        if( isset( $_POST['lsfs_live'] ) ) {
            update_post_meta( $post_id, 'lsfs_live', $_POST['lsfs_live'] );
        }
    }
}