<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Live Event Class
 */

class LSFS_Live_Event extends SP_Event {

    /**
     * Get the live results
     *
     * @since  0.4.0
     * 
     * @return array|string|boolean If the match is not now, return false. If the match is not live return time. Otherwise, return array.
     */
    public function live_results( $local_check = true ) {

        /**
         * @since  1.2.0 Adding a local check.
         */
        if( $local_check ) {
            $time = strtotime( $this->post->post_date );
            $now = time(); 
            $offset = get_option( 'gmt_offset') * 3600;
            $local_now = $now + $offset;
            // The game still has not started
            if ( $local_now < $time ) {
                return false;
            }
        }

        if( ! lsfs_is_live_started( $this->post->ID ) ) {
            return array( 
                'results' => $this->main_results_with_team_ids(), 
                'status' => '<span class="lsfs-event-live-status">' . sp_get_time( $this->post ) . '</span>'
            );
        }

        return array( 
            'results' => $this->main_results_with_team_ids(), 
            'status' => lsfs_get_live_status( $this->post->ID ) 
        );
    }

    /**
     * Return main results but also with team IDs as keys
     *
     * @since  0.4.0 
     * @return array
     */
    public function main_results_with_team_ids() {
        // Get main result option
        $main_result = get_option( 'sportspress_primary_result', null );

        // Get teams from event
        $teams = get_post_meta( $this->ID, 'sp_team', false );
        
        // Initialize output
        $output = array();

        // Return empty array if there are no teams
        if ( ! $teams ) return $output;

        // Get results from event
        $results = get_post_meta( $this->ID, 'sp_results', true );

        // Loop through teams           
        foreach ( $teams as $team_id ) {

            // Skip if not a team
            if ( ! $team_id ) continue;

            // Get team results from all results
            $team_results = sp_array_value( $results, $team_id, null );

            // Get main or last result
            if ( $main_result ) {
            
                // Get main result from team results
                $team_result = sp_array_value( $team_results, $main_result, null );
            } else {

                // If there are any team results available
                if ( is_array( $team_results ) ) {

                    // Get last result that is not outcome
                    unset( $team_results['outcome'] );
                    $team_result = end( $team_results );
                } else {

                    // Give team null result
                    $team_result = '0';
                }
            }

            if ( null != $team_result ) {
                $output[ $team_id ] = $team_result;
            } else {
                $output[ $team_id ] = '0';
            }
        }

        return $output;
    }

    /**
     * Get Main Results
     * @return array 
     */
    public function main_results() {
        // Get main result option
        $main_result = get_option( 'sportspress_primary_result', null );

        // Get teams from event
        $teams = get_post_meta( $this->ID, 'sp_team', false );
        
        // Initialize output
        $output = array();

        // Return empty array if there are no teams
        if ( ! $teams ) return $output;

        // Get results from event
        $results = get_post_meta( $this->ID, 'sp_results', true );

        // Loop through teams           
        foreach ( $teams as $team_id ) {

            // Skip if not a team
            if ( ! $team_id ) continue;

            // Get team results from all results
            $team_results = sp_array_value( $results, $team_id, null );

            // Get main or last result
            if ( $main_result ) {
            
                // Get main result from team results
                $team_result = sp_array_value( $team_results, $main_result, null );
            } else {

                // If there are any team results available
                if ( is_array( $team_results ) ) {

                    // Get last result that is not outcome
                    unset( $team_results['outcome'] );
                    $team_result = end( $team_results );
                } else {

                    // Give team null result
                    $team_result = '0';
                }
            }

            if ( null != $team_result ) {
                $output[] = $team_result;
            } else {
                $output[] = '0';
            }
        }

        return $output;
    }

    /**
     * Get Status of the Live Event
     * @return string
     */
    public function status() {
        $post_status = $this->post->post_status;

        $results = get_post_meta( $this->ID, 'sp_results', true );
        if ( is_array( $results ) ) {
            foreach( $results as $result ) {
                $result = array_filter( $result );
                if ( count( $result ) > 0 ) {
                    return 'results';
                }
            }
        }

        return $post_status;
    }

    /**
     * Get live scorers
     *
     * @return void
     */
    public function get_scorers() {
        return get_post_meta( $this->ID, 'lsfs_scorers', true );
    }

    /**
     * Live Event Form
     * @param  integer $event_id 
     * @param  array   $results  Array of results in form team_id => result
     * @return void   
     */
    public static function form( $event_id, $results = array() ) {
        
        $live_start = get_post_meta( $event_id, 'lsfs_live_start', true );
        $live_end = get_post_meta( $event_id, 'lsfs_live_end', true );
        $event_log  = lsfs_event_log_get( $event_id );
        $config_log = lsfs_config_log_get( $event_id );
    
        $start_button = __( 'Start Live', 'live-scores-for-sportspress' );
        $start_button_atts = '';
        if( $live_start ) {
            $start_button = __( 'Live Started', 'live-scores-for-sportspress' );
            $start_button_atts = 'disabled="disabled" ';
        }

        $end_button = __( 'End Live', 'live-scores-for-sportspress' );
        $end_button_atts = '';
        if( $live_end ) {
            $end_button = __( 'Live Ended', 'live-scores-for-sportspress' );
            $end_button_atts = 'disabled="disabled" ';
        }

        if( $results ) {
            
            ?>
            <table id="lsfs-event-result-<?php echo $event_id; ?>" class="sp-data-table">
                <tbody>
                <?php 
                    foreach ( $results as $team_id => $result ) {
                        ?>
                        <tr>
                            <td class="live-event-team">
                                <?php echo get_the_title( $team_id ); ?>
                            </td>
                            <td class="live-event-result">
                                <input type="text"  name="results[<?php echo $team_id; ?>]" value="<?php echo $result; ?>" />
                            </td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">
                            <button data-id="<?php echo $event_id; ?>" data-input="#lsfs-event-result-<?php echo $event_id; ?> .live-event-result input" data-config="live-results" data-event="save" class="button button-primary lsfs-button-live"><?php _e( 'Save Results', 'live-scores-for-sportspress' ); ?></button>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <?php
        }

        ?>
        <button <?php echo $start_button_atts; ?> data-id="<?php echo $event_id; ?>" data-config="live-start" data-event="start" class="button button-primary lsfs-button-live"><?php echo $start_button; ?></button>
        <button <?php echo $end_button_atts; ?> data-id="<?php echo $event_id; ?>" data-config="live-end" data-event="end" class="button button-primary lsfs-button-live right"><?php echo $end_button; ?></button>
        <hr/>
        <?php 
        if ( is_admin() ) { 
            ?>
            <button id="lsfs_add_live_part" class="button button-secondary"><?php esc_html_e( 'Add a Live Part', 'live-scores-for-sportspress' ); ?></button>
            <div class="lsfs-add-live-part hidden">
            <p>
                <label for="lsfs_live_title"><strong><?php _e( 'Live Part Title', 'live-scores-for-sportspress' ); ?></strong></label>
                <input id="lsfs_live_title" name="lsfs_live_title" type="text" value="" class="widefat" placeholder="<?php esc_attr_e( 'Enter a Title for the Live Part', 'live-scores-for-sportspress' ); ?>" />
            </p>
            <?php 
            $object = new stdClass();
            $object->ID = 0;
            LSFS_Meta_Box_Live_Parts_Details::output( $object );
            ?>
            <button id="lsfs_save_live_part" class="button button-primary"><?php esc_html_e( 'Save Live Part', 'live-scores-for-sportspress' ); ?></button>
            </div>
            <?php  
        } 
        ?>
        <?php

        $live_parts = lsfs_get_live_parts();

        if( $live_parts->have_posts() ) {
            $count = 1;
          
            echo '<div id="lsfs-type-live" class="lsfs-live-parts">';
            while( $live_parts->have_posts() ) {
                $live_parts->the_post();
                $lsfs_live = get_post_meta( get_the_id(), 'lsfs_live', true );
                $_config_log = isset( $config_log[ get_the_id() ] ) ? $config_log[ get_the_id() ] : array();
               
                echo '<div class="lsfs-live-part">';
                    
                    the_title('<h4>', '</h4>');
                    
                    if( $count > 1 ) { 
                        $button_start = sprintf( __( 'Start %s', 'live-scores-for-sportspress' ), get_the_title() ); 
                        $button_start_atts = '';
                        if( isset( $_config_log['start'] ) ) { 
                            $button_start_atts = ' disabled="disabled"';
                            $button_start = sprintf( __( '%s Started', 'live-scores-for-sportspress' ), get_the_title() ); 

                        }
                        ?>
                        
                        <button <?php echo $button_start_atts; ?> data-id="<?php echo $event_id; ?>" data-config="<?php echo get_the_id(); ?>" data-event="start" class="button button-primary lsfs-button-live"><?php  echo $button_start; ?></button>
                    
                    <?php } else {

                        echo '<p class="description">' . __( 'This is the first live part. It will start immediatelly once Live Starts', 'live-scores-for-sportspress' ) . '</p>';
                    
                    } ?>
                    
                    <?php 
                    $button_pause = sprintf( __( 'End %s', 'live-scores-for-sportspress' ), get_the_title() ); 
                    $button_pause_atts = '';
                    if( isset( $_config_log['pause'] ) ) { 
                        $button_pause_atts = ' disabled="disabled"';
                        $button_pause = sprintf( __( '%s Ended', 'live-scores-for-sportspress' ), get_the_title() ); 

                    }

                    ?>
                    <button <?php echo $button_pause_atts; ?> data-id="<?php echo $event_id; ?>" data-config="<?php echo get_the_id(); ?>" data-event="pause" class="button button-primary lsfs-button-live right"><?php echo $button_pause; ?></button>
                    
                    <?php
                    
                    if( isset( $lsfs_live['stoppage'] ) && $lsfs_live['stoppage'] ) {
                        $stoppage_minutes = 0;
                        if( isset( $_config_log['stoppage'] ) ) {
                            $stoppage_minutes = $_config_log['stoppage'];
                        }
                        echo '<input type="number" value="' . $stoppage_minutes . '" id="stoppage-' . get_the_id() . '" />';
                        echo '<button data-id="' . $event_id . '" data-input="#stoppage-' . get_the_id() . '" data-config="' . get_the_id() . '" data-event="update" class="button button-secondary lsfs-button-live">' . __( 'Update Stoppage Minutes', 'live-scores-for-sportspress' ) . '</button>';
                    }

                echo '</div>';
                $count++;
            }
            echo '</div>';
            wp_reset_postdata();
        }

        $paused_parts = lsfs_get_live_parts( 'pause' );

        if( $paused_parts->have_posts() ) {
            echo '<hr/>';
            echo '<p class="description">' . __( 'Ending Pause Periods will continue the event minutes', 'live-scores-for-sportspress' ) . '</p>';
            echo '<div id="lsfs-type-pause" class="lsfs-live-parts">';
            while( $paused_parts->have_posts() ) {
                $paused_parts->the_post();
                
                $_config_log = isset( $config_log[ get_the_id() ] ) ? $config_log[ get_the_id() ] : array();
                $started = false;
              
                if( isset( $_config_log['start'] ) && isset( $_config_log['pause'] ) ) {

                    /**
                     * If we have both of them, it means that we have used it already (even more than once)
                     * If the pause is bigger, it means that the event started already because
                     * we are pausing the live event
                     */
                    if( absint( $_config_log['pause'] ) > absint( $_config_log['start'] ) ) {
                        $started = true;
                    }

                }

                $button_start_atts = '';
                $button_pause_atts = ' disabled="disabled"';

                if( $started ) {
                    $button_pause_atts = '';
                    $button_start_atts = ' disabled="disabled"';
                }

                echo '<div class="lsfs-live-part">';
                    the_title('<h4>', '</h4>');
                    ?>
                    <button <?php echo $button_start_atts; ?> data-id="<?php echo $event_id; ?>" data-config="<?php echo get_the_id(); ?>" data-event="pause" class="button button-primary lsfs-button-live"><?php printf( __( 'Start %s', 'live-scores-for-sportspress' ), get_the_title()); ?></button>
                    <button <?php echo $button_pause_atts; ?> data-id="<?php echo $event_id; ?>" data-config="<?php echo get_the_id(); ?>" data-event="start" class="button button-primary lsfs-button-live"><?php printf( __( 'End %s', 'live-scores-for-sportspress' ), get_the_title()); ?></button>
                    <?php
                echo '</div>';
            }
            echo '</div>';
            wp_reset_postdata();
        }

        ?>
        <hr />
        <div class="lsfs-live-parts">
            <h4><?php esc_html_e( 'Minutes Correction', 'live-scores-for-sportspress' ); ?></h4>
            <p class="description"><?php esc_html_e( 'Type the current minutes of the match to correct the live details', 'live-scores-for-sportspress' ); ?></p>
            <p class="description"><em><?php esc_html_e( 'This will not take into account the previously submitted live parts and stoppage minutes', 'live-scores-for-sportspress' ); ?></em></p>
            <input type="number" value="" placeholder="<?php esc_attr_e( 'Minutes', 'live-scores-for-sportspress' ); ?>" id="minutes-correction">
            <button data-id="<?php echo esc_attr( $event_id ); ?>" data-input="#minutes-correction" data-config="minutes-correction" data-event="update" class="button button-secondary lsfs-button-live"><?php esc_html_e( 'Correct current minutes', 'live-scores-for-sportspress' ); ?></button>
        </div>
        <script type="text/template" id="tmpl-live-part-live">
            <div class="lsfs-live-part">
                <h4>{{ data.title }}</h4>
                <button data-id="<?php echo $event_id; ?>" data-config="{{ data.id }}" data-event="start" class="button button-primary lsfs-button-live"><?php _e( 'Start', 'live-scores-for-sportspress' ); ?> {{ data.title }}</button>
                <button data-id="<?php echo $event_id; ?>" data-config="{{ data.id }}" data-event="pause" class="button button-primary lsfs-button-live right"><?php _e( 'End', 'live-scores-for-sportspress' ); ?> {{ data.title }}</button>
                <#
                data.hasStoppage = data.hasStoppage || false;
                if ( false !== data.hasStoppage ) {
                #>
                    <input type="number" value="0" id="stoppage-{{ data.id }}" />
                    <button data-id="<?php echo $event_id; ?>" data-input="#stoppage-{{ data.id }}" data-config="{{ data.id }}" data-event="update" class="button button-secondary lsfs-button-live"><?php _e( 'Update Stoppage Minutes', 'live-scores-for-sportspress' ); ?></button>
                <# } #>
            </div>
        </script>
        <script type="text/template" id="tmpl-live-part-pause">
            <div class="lsfs-live-part">
                <h4>{{ data.title }}</h4>
                <button data-id="<?php echo $event_id; ?>" data-config="{{ data.id }}" data-event="pause" class="button button-primary lsfs-button-live"><?php _e( 'Start', 'live-scores-for-sportspress' ); ?> {{ data.title }}</button>
                <button disabled="disabled" data-id="<?php echo $event_id; ?>" data-config="{{ data.id }}" data-event="start" class="button button-primary lsfs-button-live"><?php _e( 'End', 'live-scores-for-sportspress' ); ?> {{ data.title }}</button>
            </div>
        </script>
        <?php
    }

}