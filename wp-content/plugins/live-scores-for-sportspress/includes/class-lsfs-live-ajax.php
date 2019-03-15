<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * AJAX for Live Events 
 */
class LSFS_Live_AJAX {

    /**
     * Hook into ajax events
     */
    public function __construct() {

        $ajax_events = array(
            'event_results'       => true,
            'event_single_result' => true,
            'ajax_live_pause'     => false,
            'ajax_live_start'     => false,
            'ajax_live_update'    => false,
            'ajax_live_end'       => false,
            'ajax_live_save'      => false,
        );

        foreach ( $ajax_events as $ajax_event => $nopriv ) {
            add_action( 'wp_ajax_lsfs_' . $ajax_event, array( $this, $ajax_event ) );

            if ( $nopriv ) {
                add_action( 'wp_ajax_nopriv_lsfs_' . $ajax_event, array( $this, $ajax_event ) );
            }
        }
    }

    /**
     * Event Results
     * @return void 
     */
    public function event_results() {

        if( ! isset( $_GET['nonce'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No Hacking please', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }
 
        if( ! wp_verify_nonce( $_GET['nonce'], 'lsfs-public-nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No Hacking please', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $current_time = time();

        $live_results = apply_filters( 'lsfs_ajax_event_results', null, $_GET['list'] );

        if( null !== $live_results ) {

            wp_send_json_success( array( 'events' => $live_results ) );
        
            wp_die();

        }

        $list = isset( $_GET['list'] ) ? ( 'all' === $_GET['list'] ? 'all' : absint( $_GET['list'] ) ) : 0;  

        if( $list ) {

            $live_results = get_transient( 'lsfs_live_results_' . $list );

        } else {

            wp_send_json_error( array( 'message' => __( 'There is no list given.', 'live-scores-for-sportspress' ) ) );
            wp_die();

        }

        if( $live_results ) {
            if( isset( $live_results['timestamp'] ) && isset( $live_results['events'] ) ) {
                
                // If we already pulled data within this minute
                // return the results
                $time_spent = $current_time -  intval( $live_results['timestamp'] );
                
                if( $time_spent < lsfs_live_refresh_rate() ) {
                    
                    $events = $live_results['events'];

                    wp_send_json_success( array( 'events' => $events ) );
        
                    wp_die();

                }
            }
        }

        $offset = get_option( 'gmt_offset') * 3600;
    
        $local_time = $current_time + $offset;

        $today = getdate( $local_time );

        
        $args = array(
            'post_type' => 'sp_event',
            'posts_per_page' => 1000,
            'post_status' => array( 'publish', 'future' ),
            // Only today matches
            'date_query' => array(
                array(
                    'year'  => $today['year'],
                    'month' => $today['mon'],
                    'day'   => $today['mday'],
                ),
            ),
            'no_found_rows' => true, 
        );

        if ( 'all' !== $list ) {
            $args['meta_query'] = array(
                array(
                    'key' => 'sp_format',
                    'value' => apply_filters( 'sportspress_competitive_event_formats', array( 'league' ) ),
                    'compare' => 'IN',
                ),
            );

            $league_ids = sp_get_the_term_ids( $list, 'sp_league' );
            $season_ids = sp_get_the_term_ids( $list, 'sp_season' );
            $team_ids = (array)get_post_meta( $list, 'sp_team', false );

            if ( $league_ids ):
                $args['tax_query'][] = array(
                    'taxonomy' => 'sp_league',
                    'field' => 'term_id',
                    'terms' => $league_ids
                );
            endif;

            if ( $season_ids ):
                $args['tax_query'][] = array(
                    'taxonomy' => 'sp_season',
                    'field' => 'term_id',
                    'terms' => $season_ids
                );
            endif;

            if ( sizeof( $team_ids ) ):
                $args['meta_query'][] = array(
                    'key' => 'sp_team',
                    'value' => $team_ids,
                    'compare' => 'IN',
                );
            endif;
        }


        $events_today = new WP_Query( $args );

        $events = array();

        if( $events_today->have_posts() ) {

            while( $events_today->have_posts() ) {
                $events_today->the_post();

                $event_data = array();

                $post = get_post();

                $event = new SP_Event( $post );

                $event_id = $post->ID;

                $event_data['status'] = lsfs_get_live_status( $event_id );
                $main_results = apply_filters( 'sportspress_event_list_main_results', sp_get_main_results( $event ), $event->ID );
                $main_results = apply_filters( 'lsfs_live_event_list_main_results', $main_results, $event->ID );
     
                $main_results = implode( ' - ', $main_results );
                
                $event_data['results'] = $main_results;

                if ( 'all' === $list ) {
                    $event_data['title'] = get_the_title( $post );
                    $teams = array_unique( get_post_meta( $event->ID, 'sp_team' ) );
                    $teams = array_filter( $teams, 'sp_filter_positive' );
                    $event_data['teams'] = array();
                    foreach ( $teams as $team ) {
                        $team_array = array();
                        $team_array['title'] = get_the_title( $team );
                        $team_array['logo'] = '';
                        if ( has_post_thumbnail( $team ) ) {
                            $team_array['logo'] = get_the_post_thumbnail( $team, 'sportspress-fit-icon', array( 'itemprop' => 'logo' ) );
                        }
                        $event_data['teams'][] = $team_array;
                    }
                }

                $events[ $event_id ] = $event_data;
            }

            wp_reset_postdata();
        }

        if( $list ) {
            set_transient( 'lsfs_live_results_' . $list, array( 'timestamp' => $current_time, 'events' => $events ), lsfs_live_refresh_rate() );
        }

        wp_send_json_success( array( 'events' => $events ) );
        wp_die();
    }

    /**
     * Retrieve Single Event Result
     * @return void 
     */
    public function event_single_result() {

        if( ! isset( $_GET['nonce'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No Hacking please', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        if( ! wp_verify_nonce( $_GET['nonce'], 'lsfs-public-nonce' ) ) {
            wp_send_json_error( array( 'message' => __( 'No Hacking please', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $event_id = isset( $_GET['event'] ) ? absint( $_GET['event'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $events = array(); 
        $event_data = array();
        $event_data['status'] = lsfs_get_live_status( $event_id );

        $main_results = apply_filters( 'sportspress_event_list_main_results', sp_get_main_results( $event_id ), $event_id );
        $main_results = apply_filters( 'lsfs_live_event_list_main_results', $main_results, $event_id );

        $main_results = implode( ' - ', $main_results );
        
        $event_data['results'] = $main_results;

        $scorers = get_post_meta( absint( $event_id ), 'lsfs_scorers', true );
        $teams = (array) get_post_meta( absint( $event_id ), 'sp_team' );
        $teams = array_filter( $teams, 'sp_filter_positive' );
        $event_data['data_scorers'] = $scorers;
        $event_data['teams'] = $teams;
        $event_data['scorer_information'] = get_option( 'lsfs_scorers_information', 'minutes' );
        $show_player_number = get_option( 'lsfs_scorers_number', 'no' );
        $scorers_formatted = array();
        foreach( $teams as $team_id ) {
            $team_scorers = isset( $scorers[ $team_id ] ) ? $scorers[ $team_id ] : array();
            if ( ! $team_scorers ) {
                continue;
            }
            $scorers_formatted[ $team_id ] = array();
            $_scorers = array();
            foreach( $team_scorers as $scorer ) {
                $scorer_id = $scorer['id'];
                if ( ! isset( $_scorers[ $scorer_id ] ) ) {
                    if ( 'yes' === $show_player_number ) {
                        $sp_number = get_post_meta( $scorer_id, 'sp_number', true );
                        if ( $sp_number ) {
                            $scorer['name'] = $sp_number . ' ' .$scorer['name'];
                        }
                    }
                    $_scorers[ $scorer_id ] = array(
                        'minutes' => array(),
                        'points'  => array(),
                        'name'    => $scorer['name'],
                    );
                }
                $_scorers[ $scorer_id ]['minutes'][] = $scorer['minute'] . '\'';
                $_scorers[ $scorer_id ]['points'][] = $scorer['points'];
            }
            foreach( $_scorers as $id => $player ) {
                $scorers_formatted[ $team_id ][] = array(
                    'name'    => $player['name'],
                    'minutes' => implode( ', ', $player['minutes'] ),
                    'points'  => array_sum( $player['points'] ),
                );
            }
        }

        if ( $scorers_formatted ) {
            $event_data['scorers'] = $scorers_formatted;
        }

        $events[ $event_id ] = $event_data;

        wp_send_json_success( array( 'events' => $events ) );
        wp_die();

    }

    /**
     * Saving the Live Results
     * @return void 
     */
    public function ajax_live_save() {
        check_ajax_referer( 'lsfs-public-nonce', 'nonce' );

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        // Config ID. If not a number, it means that it is a global one
        $config_id = isset( $_POST['config_id'] ) ? $_POST['config_id'] : false;

        if( ! $config_id ) {
            wp_send_json_error( array( 'message' => __( 'No Config ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if( 'live-results' !== $config_id ) {
            wp_send_json_error( array( 'message' => __( 'Action not supported', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        $results_value = isset( $_POST['value'] ) ? $_POST['value'] : false;

        if ( ! $results_value ) {
            wp_send_json_error( array( 'message' => __( 'No data to update', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if ( is_array( $results_value ) ) {

            $original_results = get_post_meta( $event_id, 'sp_results', true );
            
            // Get main result option, with soccer is goal
            $main_result = get_option( 'sportspress_primary_result', null );

            // Get teams from event
            $teams = get_post_meta( $event_id, 'sp_team', false );

            if( ! $original_results ) {
                $usecolumns = get_post_meta( $event_id, 'sp_result_columns', true );
                $columns = sp_get_var_labels( 'sp_result' ); 
                $results = (array)get_post_meta( $event_id, 'sp_results', true );
                $data = sp_array_combine( $teams, $results, true );
        
                if ( 'yes' === get_option( 'sportspress_event_reverse_teams', 'no' ) ) {
                    $data = array_reverse( $data, true );
                }

                if ( is_array( $usecolumns ) ):
                    if ( 'manual' == get_option( 'sportspress_event_result_columns', 'auto' ) ):
                        foreach ( $columns as $key => $label ):
                            if ( ! in_array( $key, $usecolumns ) ):
                                unset( $columns[ $key ] );
                            endif;
                        endforeach;
                    else:
                        $active_columns = array();
                        foreach ( $data as $team_results ):
                            foreach ( $team_results as $key => $result ):
                                if ( is_string( $result ) && strlen( $result ) ):
                                    $active_columns[ $key ] = $key;
                                endif;
                            endforeach;
                        endforeach;
                        
                        if( $active_columns ) {
                            $columns = array_intersect_key( $columns, $active_columns );
                        }
                        
                    endif;
                endif;

                $original_results = array();
                // Create an empty array with 0 for each column
                $array_results = array_fill(0, count( $columns ), 0 );

                // Combine columns keys with 0
                $array_results = array_combine( array_keys( $columns ), $array_results );

                for( $i = 0; $i < count( $teams ); $i++ ) {
                    $team_id = $teams[ $i ];
                    $original_results[ $team_id ] = $array_results;
                }
                 
            }

            // If we don't have a main result
            if( ! $main_result ) {
                // Get the last result array, convert to keys, get the last key.
                $main_result = end( array_keys( array_pop( $original_results ) ) ); 
            }
 
            foreach ( $results_value as $result ) {
                $value = $result['value'];
                $name = $result['name'];
                $team_id = absint( str_replace( ']', '', str_replace('results[', '', $name ) ) );
                $original_results[ $team_id ][ $main_result ] = $value;
            }

            update_post_meta( $event_id, 'sp_results', $original_results );
            wp_send_json_success( array( 'results' => $original_results ) );
            wp_die();
        }
    }

    /**
     * Live Start
     * It starts on each new live part or on each end of paused parts
     * @return void 
     */
    public function ajax_live_start() {
        check_ajax_referer( 'lsfs-public-nonce', 'nonce' );

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        // Config ID. If not a number, it means that it is a global one
        $config_id = isset( $_POST['config_id'] ) ? $_POST['config_id'] : false;

        if( ! $config_id ) {
            wp_send_json_error( array( 'message' => __( 'No Config ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if( is_numeric( $config_id ) ) {
            // This is a configurable live part
            $config = get_post( $config_id );

            $log = lsfs_event_log_prepare( 'live-start', $config->post_name . '-start' );

            $timestamp = time();

            //Adding Log
            $add = lsfs_event_log_add( $event_id, $log, $timestamp );

            //Adding Config
            $config_data = array(
                'start' => $timestamp
            );

            lsfs_config_log_add( $event_id, $config_id, $config_data );
            $type = get_post_meta( $config_id, 'lsfs_live_type', true );

            if( 'live' === $type ) {
                wp_send_json_success( array( 'type' => 'live', 'disable' => true, 'message' => sprintf( __( '%s Started', 'live-scores-for-sportspress' ), $config->post_title ) ) );
            } else {
                wp_send_json_success( array( 'type' => 'pause', 'disable' => true, 'message' => sprintf( __( '%s Ended', 'live-scores-for-sportspress' ), $config->post_title ) ) ); 
            }
            wp_die();

        } elseif( 'live-start' === $config_id ) {

            // Getting the Old Live Start if any
            $old_timestamp = get_post_meta( $event_id, 'lsfs_live_start', true );
            
            if( $old_timestamp ) {
                // Removing it from the event log
                lsfs_event_log_remove( $event_id, $old_timestamp );
            }
            
            $log = lsfs_event_log_prepare( 'live-start', 'live-start' );

            $timestamp = time();
            $add = lsfs_event_log_add( $event_id, $log, $timestamp );

            if( $add ) {
                update_post_meta( $event_id, 'lsfs_live_start', $timestamp );

                // @todo Check for the first half and update that also
                add_filter( 'lsfs_get_live_parts', array( $this, 'only_first') );
                
                $first_config = lsfs_get_live_parts();

                remove_filter( 'lsfs_get_live_parts', array( $this, 'only_first' ) );

                if( $first_config->have_posts() ) {
                    while( $first_config->have_posts() ) {
                        $first_config->the_post();
                        $data = array(
                            'start' => $timestamp
                        );
                        lsfs_config_log_add( $event_id, get_the_id(), $data );
                        break;
                    }
                    wp_reset_postdata();
                }
                
                wp_send_json_success( array( 'type' => 'live', 'disable' => true, 'message' => __( 'Live Started', 'live-scores-for-sportspress' ) ) );
                wp_die();

            } else {
                wp_send_json_error( array( 'message' => __( 'Something went wrong while updating event log', 'live-scores-for-sportspress') ) );
                wp_die();
            }

        }

        wp_die();
    }

    /**
     * Live Pause
     * Pausing the live parts on live part end or on start of a paused part
     * @return void 
     */
    public function ajax_live_pause() {
        check_ajax_referer( 'lsfs-public-nonce', 'nonce' );

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        // Config ID. If not a number, it means that it is a global one
        $config_id = isset( $_POST['config_id'] ) ? $_POST['config_id'] : false;

        if( ! $config_id ) {
            wp_send_json_error( array( 'message' => __( 'No Config ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if( is_numeric( $config_id ) ) {

            // This is a configurable live part
            $config = get_post( $config_id );
            $lsfs_live = get_post_meta( $config->ID, 'lsfs_live', true );
            $text_after_minutes = isset( $lsfs_live['text_after_minutes'] ) ? $lsfs_live['text_after_minutes'] : 'text';
            $text_after = isset( $lsfs_live['text_after'] ) ? $lsfs_live['text_after'] : '';

            $log = lsfs_event_log_prepare( 'live-pause', $config->post_name . '-pause' );

            if( 'text' === $text_after_minutes && '' !== $text_after ) {
                $log['message'] = $text_after;
            } else {
                $log['show'] = 'minutes';
            }

            $timestamp = time();

            //Adding Log
            $add = lsfs_event_log_add( $event_id, $log, $timestamp );

            //Adding Config
            $config_data = array(
                'pause' => $timestamp
            );

            lsfs_config_log_add( $event_id, $config_id, $config_data );
            $type = get_post_meta( $config_id, 'lsfs_live_type', true );

            if( 'live' === $type ) {
                wp_send_json_success( array( 'type' => 'live', 'disable' => true, 'message' => sprintf( __( '%s Ended', 'live-scores-for-sportspress' ), $config->post_title ) ) );
            } else {
                wp_send_json_success( array( 'type' => 'pause', 'disable' => true, 'message' => sprintf( __( '%s Started', 'live-scores-for-sportspress' ), $config->post_title ) ) ); 
            }
        }

        wp_die();
    }

    /**
     * Live Update
     * Used to update stoppage minutes
     * @return void
     */
    public function ajax_live_update() {
        check_ajax_referer( 'lsfs-public-nonce', 'nonce' );

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        // Config ID. If not a number, it means that it is a global one
        $config_id = isset( $_POST['config_id'] ) ? $_POST['config_id'] : false;

        if( ! $config_id ) {
            wp_send_json_error( array( 'message' => __( 'No Config ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if( is_numeric( $config_id ) ) {

            // This is a configurable live part
            $config = get_post( $config_id );
            $lsfs_live = get_post_meta( $config->ID, 'lsfs_live', true );
            $stoppage = isset( $lsfs_live['stoppage'] ) ? $lsfs_live['stoppage'] : 0;

            if( $stoppage ) {
                $value = isset( $_POST['value'] ) ? $_POST['value'] : 0;

                if( $value ) {
                     //Adding Config
                    $config_data = array(
                        'stoppage' => $value
                    );

                    lsfs_config_log_add( $event_id, $config_id, $config_data ); 
                    
                    wp_send_json_success( array( 'type' => 'update', 'disable' => false, 'message' => __( 'Minutes Updated', 'live-scores-for-sportspress' ) ) ); 
                
                } else {
                   
                    wp_send_json_error( array( 'type' => 'update', 'disable' => false, 'message' => __( 'No Minutes Saved', 'live-scores-for-sportspress' ) ) ); 
           
                }
           
            }
        } elseif( 'minutes-correction' === $config_id ) {
            $value     = isset( $_POST['value'] ) ? $_POST['value'] : 0;
            $corrected = lsfs_correct_live_minutes( $value, $event_id );
            if ( is_wp_error( $corrected ) ) {
                wp_send_json_error( array( 'type' => 'update', 'disable' => false, 'message' => $corrected->get_error_message() ) ); 
            } else {
                wp_send_json_success( array( 'type' => 'update', 'disable' => false, 'message' => __( 'Minutes Corrected', 'live-scores-for-sportspress' ) ) ); 
            }
        }
        wp_die();
    }

    /**
     * Live End
     * Used only once to end the event
     * @return void 
     */
    public function ajax_live_end() {
        check_ajax_referer( 'lsfs-public-nonce', 'nonce' );

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;

        if( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        // Config ID. If not a number, it means that it is a global one
        $config_id = isset( $_POST['config_id'] ) ? $_POST['config_id'] : false;

        if( ! $config_id ) {
            wp_send_json_error( array( 'message' => __( 'No Config ID', 'live-scores-for-sportspress') ) );
            wp_die();
        }

        if( 'live-end' === $config_id ) {

            // Getting the Old Live Start if any
            $old_timestamp = get_post_meta( $event_id, 'lsfs_live_end', true );
            
            if( $old_timestamp ) {
                // Removing it from the event log
                lsfs_event_log_remove( $event_id, $old_timestamp );
            }
            
            $log = lsfs_event_log_prepare( 'live-pause', 'live-end', __( 'Ended', 'live-scores-for-sportspress' ) );

            $timestamp = time();
            $add = lsfs_event_log_add( $event_id, $log, $timestamp );

            if( $add ) {
                update_post_meta( $event_id, 'lsfs_live_end', $timestamp );

                wp_send_json_success( array( 'type' => 'live', 'disable' => true, 'message' => __( 'Live Ended', 'live-scores-for-sportspress' ) ) );
                wp_die();

            } else {
                wp_send_json_error( array( 'message' => __( 'Something went wrong while updating event log', 'live-scores-for-sportspress') ) );
                wp_die();
            }

        }

        wp_die();
    }

    /**
     * Return only one per page
     * @param  array $args 
     * @return array       
     */
    public function only_first( $args ) {
        $args['posts_per_page'] = 1;
        return $args;
    }
}

new LSFS_Live_AJAX();