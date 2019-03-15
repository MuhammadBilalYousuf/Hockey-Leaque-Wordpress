<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Functions that are used for displaying and managing live parts
 */

add_filter( 'lsfs_live_event_list_main_results', 'lsfs_live_main_results', 20, 2 );

/**
 * Filtering the Live Results
 * @param  array $main_results 
 * @param  integer $event_id     
 * @return void               
 */
function lsfs_live_main_results( $main_results, $event_id ) {
   
    if( ! $main_results ) {
        if( lsfs_get_live_start( $event_id ) ) {
            $lsfs_live_event = new LSFS_Live_Event( $event_id );
            $main_results = $lsfs_live_event->main_results();
        }
    }

    return $main_results;
}

/**
 * Live Refresh Rate 
 * @return integer Seconds for refreshing the live scores 
 */
function lsfs_live_refresh_rate() {
    $refresh_rate = get_option( 'lsfs_refresh_rate', 60 );
    return apply_filters( 'lsfs_live_refresh_rate', $refresh_rate );
}

/**
 * Check to see if a live event has already ended
 * @param  integer $event_id 
 * @return boolean           
 */
function lsfs_is_live_end( $event_id ) {
    $current_time = time();
    $live_end = get_post_meta( $event_id, 'lsfs_live_end', true );
    if( $live_end && is_numeric( $live_end ) && $current_time > absint( $live_end ) ) {
        return true;
    }

    return false;
}

/**
 * Check if the live has started
 * @param  integer $event_id Event (Post) ID
 * @return boolean 
 *
 * @since  0.4.0 
 */
function lsfs_is_live_started( $event_id ) {
    $current_time = time();
    $live = lsfs_get_live_start( $event_id );
    if( $live && is_numeric( $live ) && $current_time > absint( $live ) ) {
        return true;
    }

    return false;
}

/**
 * Return the start of the live event
 * @param  integer $event_id 
 * @return integer           
 */
function lsfs_get_live_start( $event_id ) {
    return get_post_meta( $event_id, 'lsfs_live_start', true );
}

/**
 * Getting the live parts for Live Scores
 * @param  string $type This is the type of the part. It can be live or pause
 * @return WP_Query       
 */
function lsfs_get_live_parts( $type = 'live' ) {

    $live_parts_args = apply_filters( 'lsfs_get_live_parts', array(
         
        //Type & Status Parameters
        'post_type'   => 'lsfs_live_parts',
        'post_status' => 'publish',
        
        //Order & Orderby Parameter
        'orderby' => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
         
        
        //Pagination Parameters
        'posts_per_page'         => 500,
        
        //Custom Field Parameters
        'meta_key'       => 'lsfs_live_type',
        'meta_value'     => $type,

    ));

    if( 'all' == $type ) {
        unset( $live_parts_args['meta_value'] );
        unset( $live_parts_args['meta_key'] );
    }

    $live_parts = new WP_Query( $live_parts_args );

    return $live_parts;
}

/**
 * Get the live status of Event
 * This can be minutes, half time or any other
 * @param  post_id $event_id 
 * @return array
 */
function lsfs_get_live_status( $event_id ) {

    if( 'no' === get_option( 'lsfs_show_status', 'yes' ) ) {
        return '';
    }

    // The event has ended, no further calculations needed
    if( lsfs_is_live_end( $event_id ) ) {
        return '<span class="lsfs-event-live-status">' . __( 'Full Time', 'sportspress' ) . '</span>';
    }

    $start_timestamp = lsfs_get_live_start( $event_id );

    // The event has not started
    if( ! $start_timestamp ) {
        return '';
    }
 
    // Current Timestamp
    $timestamp = time();

    $live_parts = lsfs_get_live_parts();
    $event_log  = lsfs_event_log_get( $event_id );
    $config_log = lsfs_config_log_get( $event_id );
    $config = array();
    if( $live_parts->have_posts() ) {
        foreach ( $live_parts->posts as $live_part) {
            $part_id     = $live_part->ID;
            $lsfs_live   = get_post_meta( $part_id, 'lsfs_live', true );
            $duration    = isset( $lsfs_live['duration'] ) ? $lsfs_live['duration'] : 0;
            $stoppage    = isset( $lsfs_live['stoppage'] ) ? $lsfs_live['stoppage'] : 0;
            $text_after  = isset( $lsfs_live['text_after'] ) ? $lsfs_live['text_after'] : '';
            $_config_log = isset( $config_log[ $part_id ] ) ? $config_log[ $part_id ] : false;
            if( $stoppage ) {
                $stoppage = true;
            } else {
                $stoppage = false;
            }

            $config_array = array(
                'name' => $live_part->post_title,
                'minutes' => $duration,
                'stoppage_time' => $stoppage,
                'after_end' => $text_after
            );

            if( $_config_log && isset( $_config_log['stoppage'] ) ) {
                $config_array['stoppage'] = $_config_log['stoppage'];
            }

            $config[ $live_part->post_name ] = $config_array;
        }
    }

    $event_statuses = array(
        'live-start',
        'live-end',
        'live-pause'
    );

    /**
     * Adding All Config Statuses just in case we need that?
     * @var [type]
     */
    foreach( $config as $slug => $part ) {
        $event_statuses[] = $slug . '-start';
        $event_statuses[] = $slug . '-pause';
    }

    // Difference
    $diff = $timestamp - $start_timestamp;
    $diff_min = lsfs_seconds_to_minutes( $diff );

    $passed_minutes  = 0;
    $current_minutes = 0;
    $paused_seconds  = 0;
    $pause_stamp     = 0;
    $current_status  = '';
 
    if( $event_log ) {
 
        /**
         * Getting all paused minutes to deduct from the difference
         * @var void
         */
        foreach ( $event_log as $log_timestamp => $log ) {
            
            if( $timestamp < $log_timestamp ) {
                continue;
            }

            $status = $log['status'];
            $current_status = $log;
            $current_timestamp = $log_timestamp;

            if( 'live-pause' == $status ) {
                $pause_stamp += ( $log_timestamp - $pause_stamp );
            }

            if( 'live-start' == $status ) {
                if( $pause_stamp > 0 ) {
                    $paused_seconds += (int) $log_timestamp - $pause_stamp;
                    $pause_stamp = 0; 
                }
            }

        }
 
        // If the current is not a start and it does not show minutes, let's show the text
        if( $current_status && 'live-start' !== $current_status['status'] ) {
            if( ! isset( $current_status['show'] )
                || 'minutes' !== $current_status['show'] ) {
                return '<span class="lsfs-event-live-status">' . $current_status['message'] . '</span>';   
            }
        }
    }
     
    $diff -= $paused_seconds;

    // We will show the current paused minutes instead of the message
    if( $current_status && 'live-pause' === $current_status['status'] ) {
       $diff = $current_timestamp - $start_timestamp; 
    }

    $diff_min = lsfs_seconds_to_minutes( $diff );

    $current_part = false;

    // Passed Stoppage minutes
    $_stoppage_minutes = 0;

    /**
     * For each configured live part (which has minutes)
     * @var void
     */
    foreach( $config as $slug => $part ) {
        
        $part_minutes = (int) $part['minutes'];
        $current_part = $part;

        $stoppage_minutes = isset( $part['stoppage'] ) ? absint( $part['stoppage'] ) : 0;

        if( $stoppage_minutes < 0 ) {
            $stoppage_minutes = 0;
        }
 
        // 0 + 45 + 3 => 48
        if( $diff_min <= ( $passed_minutes + $part_minutes + $stoppage_minutes ) ) {
 
            if( $diff_min <= ( $passed_minutes + $part_minutes ) ) {
                // Current time difference - the previous stoppage minutes
                $current_minutes = (int) $diff_min - $_stoppage_minutes;
            } else {
                // Passed time + the current period time (such as 45+5' before and +45 now but - the previous stoppage time to be exact -5 );
                $current_minutes = ( (int) $passed_minutes + $part_minutes - $_stoppage_minutes ) . '+' . ( $diff_min - $passed_minutes - $part_minutes );
            }
           
            break;
        }

        if( $stoppage_minutes ) {
            $_stoppage_minutes += (int) $stoppage_minutes;
        }

        $passed_minutes += (int) $part['minutes'] + $stoppage_minutes;
        
    }

    if( ! $current_minutes ) {
        $current_minutes = $diff_min;
    }

    return '<span class="lsfs-event-live-status">' . $current_minutes . '<span class="minute">\'</span></span>';
}

/**
 * Correct Live Minutes.
 *
 * @param int $minutes
 * @param int $event_id
 * @return void
 */
function lsfs_correct_live_minutes( $minutes, $event_id ) {
    
    if ( ! $minutes ) {
        return new WP_Error( 'no-minutes', __( 'No minutes provided.', 'live-scores-for-sportspress' ) );
    }

    if ( ! $event_id ) {
        return new WP_Error( 'no-event', __( 'No event provided.', 'live-scores-for-sportspress' ) );
    }

    lsfs_event_log_remove_all( $event_id );

    $timestamp = strtotime( 'now -' . $minutes . ' minutes' );
    update_post_meta( $event_id, 'lsfs_live_start', $timestamp );

    return true;
}