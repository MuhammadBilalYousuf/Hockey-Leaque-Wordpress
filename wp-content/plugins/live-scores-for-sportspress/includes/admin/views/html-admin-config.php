<?php

/**
 * Output for the Live parts of Configuration Page
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

global $wpdb;

    $live_parts = lsfs_get_live_parts();

    $pause_parts = lsfs_get_live_parts( 'pause' );

?>
<table class="form-table">
    <body>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php _e( 'Live Parts', 'live-scores-for-sportspress' ); ?>
                <p class="description"><?php _e( 'Start & End are essential parts. Define Halves if any. Used for minutes.', 'live-scores-for-sportspress' ); ?></p>
            </th>
            <td class="forminp">
                <table class="widefat sp-admin-config-table">
                    <thead>
                        <tr>
                            <th scope="col"><?php _e( 'Order', 'live-scores-for-sportspress'); ?></th>
                            <th scope="col"><?php _e( 'Name', 'live-scores-for-sportspress' ); ?></th>
                            <th scope="col"><?php _e( 'Duration (min)', 'live-scores-for-sportspress' ); ?></th>
                            <th scope="col"><?php _e( 'Stoppage Minutes', 'live-scores-for-sportspress' ); ?></th>
                            <th scope="col"><?php _e( 'Text after End', 'live-scores-for-sportspress' ); ?></th>
                            <th scope="col" class="edit"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if( $live_parts->have_posts() ) { 
                            while( $live_parts->have_posts() ) {
                                $live_parts->the_post();
                                $order = $wpdb->get_var( 'SELECT menu_order FROM ' . $wpdb->posts . ' WHERE ID=' . get_the_id() );
                                $lsfs_live = get_post_meta( get_the_id(), 'lsfs_live', true );
                                $duration = isset( $lsfs_live['duration'] ) ? $lsfs_live['duration'] : 0;
                                $stoppage = isset( $lsfs_live['stoppage'] ) ? $lsfs_live['stoppage'] : 0;
                                $text_after_minutes = isset( $lsfs_live['text_after_minutes'] ) ? $lsfs_live['text_after_minutes'] : 'text';
                                $text_after = isset( $lsfs_live['text_after'] ) ? $lsfs_live['text_after'] : '';
                            ?>
                            <tr class="alternate">
                                <td><?php echo $order; ?></td>
                                <td class="row-title"><?php the_title(); ?></td>
                                <td><?php echo $duration; ?></td>
                                <td><?php if( $stoppage ) { echo __( 'Yes', 'live-scores-for-sportspress' ); } else { echo __( 'No', 'live-scores-for-sportspress' ); } ?></td>
                                <td><?php if( 'minutes' === $text_after_minutes ) { echo '<em>' . __( 'Minutes', 'live-scores-for-sportspress' ) . '</em>'; } else { echo $text_after; } ?></td>
                                <td class="edit"><a class="button" href="<?php echo admin_url( 'post.php?post=' . get_the_id() . '&amp;action=edit' ); ?>"><?php _e( 'Edit', 'live-scores-for-sportspress' ); ?></a></td>
                            </tr>
                            <?php
                            }
                            wp_reset_postdata();
                        } else {
                            ?>
                        <tr>
                            <td colspan="6" align="center"><?php _e( 'No Live Parts Configured', 'live-scores-for-sportspress' ); ?>
                        </tr>
                        <?php } ?>
                        
                        
                        <tr class="black">
                            <td style="background:rgba(0,0,0,0.1);" colspan="4"><strong>Paused Periods</strong> (Timeouts - Basketball, Penalties - Soccer)</td>
                            <td style="background:rgba(0,0,0,0.1);" colspan="2"><strong>Text while paused</strong></td>
                        </tr>
                        
                        <?php if( $pause_parts->have_posts() ) { 
                            while( $pause_parts->have_posts() ) {
                                $pause_parts->the_post();
                                $order = $wpdb->get_var( 'SELECT menu_order FROM ' . $wpdb->posts . ' WHERE ID=' . get_the_id() );
                                $lsfs_live = get_post_meta( get_the_id(), 'lsfs_live', true );
                                $duration = isset( $lsfs_live['duration'] ) ? $lsfs_live['duration'] : 0;
                                $stoppage = isset( $lsfs_live['stoppage'] ) ? $lsfs_live['stoppage'] : 0;
                                $text_after_minutes = isset( $lsfs_live['text_after_minutes'] ) ? $lsfs_live['text_after_minutes'] : 'text';
                                $text_after = isset( $lsfs_live['text_after'] ) ? $lsfs_live['text_after'] : '';
                            ?>
                            <tr class="alternate">
                                <td><?php echo $order; ?></td>
                                <td class="row-title" colspan="3"><?php the_title(); ?></td>
                                <td><?php if( 'minutes' === $text_after_minutes ) { echo '<em>' . __( 'Minutes', 'live-scores-for-sportspress' ) . '</em>'; } else { echo $text_after; } ?></td>
                                <td class="edit"><a class="button" href="<?php echo admin_url( 'post.php?post=' . get_the_id() . '&amp;action=edit' ); ?>"><?php _e( 'Edit', 'live-scores-for-sportspress' ); ?></a></td>
                            </tr>
                            <?php
                            }
                            wp_reset_postdata();
                        } else { ?>
                            <tr>
                                <td colspan="6" align="center"><?php _e( 'No Paused Periods Configured', 'live-scores-for-sportspress' ); ?>
                            </tr>
                        <?php } ?>
                        
                        
                    </tbody>
                </table>
                <div class="tablenav bottom">
                    <a class="button alignleft" href="<?php echo admin_url( 'edit.php?post_type=lsfs_live_parts' ); ?>">View All</a>
                    <a class="button button-primary alignright" href="<?php echo admin_url( 'post-new.php?post_type=lsfs_live_parts' ); ?>">Add New</a>
                    <br class="clear">
                </div>
            </td>
        </tr>
    </body>
</table>