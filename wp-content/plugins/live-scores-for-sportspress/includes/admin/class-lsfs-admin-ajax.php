<?php

/**
 * Class to handle AJAX requests on the admin side of LSFS
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * LSFS Admin AJAX
 *
 * @class LSFS_Admin_AJAX
 */
class LSFS_Admin_AJAX {

    public function __construct() {
        add_action( 'wp_ajax_admin_lsfs_ajax_live_start', array( $this, 'live_start' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_live_end', array( $this, 'live_end' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_live_pause', array( $this, 'live_pause' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_live_update', array( $this, 'live_update' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_live_save', array( $this, 'live_save' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_add_scorer', array( $this, 'add_scorer' ) );
        add_action( 'wp_ajax_admin_lsfs_ajax_remove_scorer', array( $this, 'remove_scorer' ) );

        add_action( 'wp_ajax_admin_lsfs_activate_integration', array( $this, 'activate_integration' ) );
        add_action( 'wp_ajax_admin_lsfs_deactivate_integration', array( $this, 'deactivate_integration' ) );

        add_action( 'wp_ajax_admin_lsfs_save_live_part', array( $this, 'save_live_part' ) );
        
    }

    /**
     * Live Start
     * It starts on each new live part or on each end of paused parts
     * @return void 
     */
    public function live_start() {
        check_ajax_referer( 'lsfs-ajax', 'nonce' );

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
     * Live Update
     * Used to update stoppage minutes
     * @return void
     */
    public function live_update() {
        check_ajax_referer( 'lsfs-ajax', 'nonce' );

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
     * Live Pause
     * Pausing the live parts on live part end or on start of a paused part
     * @return void 
     */
    public function live_pause() {
        check_ajax_referer( 'lsfs-ajax', 'nonce' );

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
     * Live End
     * Used only once to end the event
     * @return void 
     */
    public function live_end() {
        check_ajax_referer( 'lsfs-ajax', 'nonce' );

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
     * Saving the Live Results
     *
     * @since  1.2.0
     * @return void 
     */
    public function live_save() {
        check_ajax_referer( 'lsfs-ajax', 'nonce' );

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
            wp_send_json_success( array( 'results' => $original_results, 'message' => __( 'Results Updated.', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }
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

    /**
     * Activating Integration
     * @return void 
     */
    public function activate_integration() {
        if( ! isset( $_POST['nonce'] ) 
            || ! wp_verify_nonce( $_POST['nonce'], 'lsfs-ajax' ) ) {
        
            wp_send_json_error( array( 'message' => __( 'Something went wrong!', 'live-scores-for-sportspress' ) ) );
            die();
        }

        if( ! isset( $_POST['integration'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No Integration Sent', 'live-scores-for-sportspress' ) ) );
            die();
        }

        $integration = $_POST['integration'];

        $active_integrations = lsfs_get_active_integrations();
        
        if( ! isset( $active_integrations[ $integration ] ) ) {
            $integrations = lsfs_get_integrations(); 
            
            if( isset( $integrations[ $integration ] ) ) {
                $active_integrations[ $integration ] = $integrations[ $integration ];
                
                $integration_settings = get_option( $integration, array( 'active' => '0' ) );
                if( isset( $integration_settings['active'] ) && '0' == $integration_settings['active'] ) {
                    $integration_settings['active'] = '1';
                }
                update_option( $integration, $integration_settings );
                lsfs_save_active_integrations( $active_integrations );
                do_action( 'lsfs_' . $integration . '_integration_activated' ); 
                wp_send_json_success( array( 'message' => __( 'Activated', 'live-scores-for-sportspress' ) ) );
                die();
            }
            
        } else {
            wp_send_json_success( array( 'message' => __( 'Already Activated', 'live-scores-for-sportspress' ) ) );
            die();
        }

    
        wp_send_json_error( array( 'message' => __( 'Nothing Happened', 'live-scores-for-sportspress' ) ) );
        die();
    }

    /**
     * Deactivating Integration
     * @return void 
     */
    public function deactivate_integration() {
        if( ! isset( $_POST['nonce'] ) 
            || ! wp_verify_nonce( $_POST['nonce'], 'lsfs-ajax' ) ) {
        
            wp_send_json_error( array( 'message' => __( 'Something went wrong!', 'live-scores-for-sportspress' ) ) );
            die();
        }

        if( ! isset( $_POST['integration'] ) ) {
            wp_send_json_error( array( 'message' => __( 'No Integration Sent', 'live-scores-for-sportspress' ) ) );
            die();
        }

        $integration = $_POST['integration'];

        $active_integrations = lsfs_get_active_integrations();
        
        if( isset( $active_integrations[ $integration ] ) ) {
            unset( $active_integrations[ $integration ] );
            $integration_settings = get_option( $integration, array( 'active' => '0' ) );

            if( isset( $integration_settings['active'] ) && '1' == $integration_settings['active'] ) {
                $integration_settings['active'] = 0;
            }
            update_option( $integration, $integration_settings );
            do_action( 'lsfs_' . $integration . '_integration_deactivated' );
            lsfs_save_active_integrations( $active_integrations );
            wp_send_json_success( array( 'message' => __( 'Deactivated', 'giveasap' ) ) );
            die();
        } else {
            wp_send_json_error( array( 'message' => __( 'Not Activated', 'giveasap' ) ) );
            die();
        }

        wp_send_json_error( array( 'message' => __( 'Nothing Happened', 'giveasap' ) ) );
        die();
    }

    /**
     * This will create a new live part in the admin area.
     */
    public function save_live_part() {
        $data = array();
        parse_str( $_POST['data'], $data );

        if ( isset( $data['lsfs_live_title'] ) && $data['lsfs_live_title'] ) {
            $parts   = lsfs_get_live_parts( 'all' );
            $post_id = wp_insert_post( array(
                'post_title'  => $data['lsfs_live_title'],
                'post_status' => 'publish',
                'post_type'   => 'lsfs_live_parts',
                'menu_order'  => count( $parts->posts ),
            ) );

            if ( $post_id ) {
                if( isset( $data['lsfs_live_type'] ) ) {
                    update_post_meta( $post_id, 'lsfs_live_type', $data['lsfs_live_type'] );
                }

                if( isset( $data['lsfs_live'] ) ) {
                    update_post_meta( $post_id, 'lsfs_live', $data['lsfs_live'] );
                }

                wp_send_json_success( 
                    array( 
                        'id'          => $post_id, 
                        'hasStoppage' => (bool) $data['lsfs_live']['stoppage'],
                        'type'        => $data['lsfs_live_type'],
                        'title'       => get_the_title( $post_id )
                    )
                );
            } else {
                wp_send_json_error( __( 'Live Part could not be created', 'live-scores-for-sportspress' ) );
            }
        } else {
            wp_send_json_error( __( 'You have to enter a title for the live part.', 'live-scores-for-sportspress' ) );
        }

        wp_die();
    }

    /**
     * Add Scorer to the event.
     * It also updates the box score.
     *
     * @return void
     */
    public function add_scorer() {
        $data   = array();
        $output = array();
        parse_str( $_POST['data'], $data );

        $team_id = isset( $data['lsfs_team_id'] ) ? absint( $data['lsfs_team_id'] ) : 0;

        if ( ! $team_id ) {
            wp_send_json_error( array( 'message' => __( 'No Team ID', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $event_id = isset( $data['lsfs_event_id'] ) ? absint( $data['lsfs_event_id'] ) : 0;
        
        if ( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $teams = get_post_meta( $event_id, 'sp_team', false );

        $team_player_id = isset( $data['lsfs_team_player_id'] ) ? absint( $data['lsfs_team_player_id'] ) : 0;
        $team_player_new = isset( $data['lsfs_team_player_new'] ) ? sanitize_text_field( $data['lsfs_team_player_new'] ) : false;
        
        if( ! $team_player_id && ! $team_player_new ) {
            wp_send_json_error( array( 'message' => __( 'Please Select a Player or add a name for a New Player.', 'live-scores-for-sportspress' ) ) );
            wp_die();
        }

        $team_player_points = isset( $data['lsfs_team_player_points'] ) ? absint( $data['lsfs_team_player_points'] ) : 1;

        $team_player_minute = isset( $data['lsfs_team_player_minute'] ) ? absint( $data['lsfs_team_player_minute'] ) : false;

        if( 0 == $team_player_id && $team_player_new ) {
            // We have a new player.
            $team_player_id = wp_insert_post( array(
                'post_type' => 'sp_player',
                'post_status' => 'publish',
                'post_title'  => trim( $team_player_new )
            ), true );

            if( is_wp_error( $team_player_id ) ) {
                wp_send_json_error( array( 'message' => $team_player_id->get_error_message() ) );
                wp_die();
            }
            // Add Player to this Team.
            add_post_meta( $team_player_id, 'sp_current_team', $team_id );
            add_post_meta( $team_player_id, 'sp_team', $team_id );

            // Providing this info back to add it in select.
            $output['new_player'] = array( 'id' => $team_player_id, 'title' => $team_player_new );
        }

        $players = get_post_meta( $event_id, 'sp_players', true );

        if ( ! $players || ! is_array( $players ) ) {
            $players = array();
        }

        foreach ( $teams as $other_team_id ) {
            if( ! isset( $players[ $other_team_id ] ) ) {
                $players[ $other_team_id ] = array();
            }
            if( ! isset( $players[ $other_team_id ][ 0 ] ) ) {
                $event = new SP_Event( $event_id );
                list( $labels, $columns, $stats, $teams, $formats, $order, $timed, $stars ) = $event->performance( true );
                $player_labels = array_keys( $labels );
                
                $players[ $other_team_id ][ 0 ] = array();
                foreach ( $player_labels as $column ) {
                    $value = '';
                    
                    $players[ $other_team_id ][ 0 ][ $column ] = $value;
                }
            }

            $_players = $players[ $other_team_id ];
            ksort( $_players );
            $players[ $other_team_id ] = $_players;
        }

        $primary_performance = sp_get_main_performance_option();
        
        // No such player with ID. Let's put it with all the columns.
        if( ! isset( $players[ $team_id ][ $team_player_id ] ) ) {
            $event = new SP_Event( $event_id );
            list( $labels, $columns, $stats, $teams, $formats, $order, $timed, $stars ) = $event->performance( true );
            $player_labels = array_keys( $labels );
            $player_labels[] = 'position';
            
            $players[ $team_id ][ $team_player_id ] = array();
            foreach ( $player_labels as $column ) {
                $value = '';
                if( $primary_performance == $column ) {
                    $value = $team_player_points;
                }
                $players[ $team_id ][ $team_player_id ][ $column ] = $value;
            }
        } else {
            // Ok, we actually have the player, let's update the primary perf.
            $primary = isset( $players[ $team_id ][ $team_player_id ][ $primary_performance ] ) ? absint( $players[ $team_id ][ $team_player_id ][ $primary_performance ] ) : 0;
            $primary += $team_player_points;
            $players[ $team_id ][ $team_player_id ][ $primary_performance ] = $primary;
        }

        update_post_meta( $event_id, 'sp_players', $players );
        
        $scorers = get_post_meta( $event_id, 'lsfs_scorers', true );
        // Nothing yet, let's create an array.
        if( ! $scorers ) {
            $scorers = array();
        }

        if( ! isset( $scorers[ $team_id ] ) ) {
            $scorers[ $team_id ] = array();
        }

        $scorer_array = array(
            'id'        => $team_player_id,
            'minute'    => $team_player_minute,
            'points'    => $team_player_points,
            'timestamp' => time(),
            'name'      => trim( get_the_title( $team_player_id ) ),
            'row'       => count( $scorers[ $team_id ] ),
        );

        $scorers[ $team_id ][] = $scorer_array;

        update_post_meta( $event_id, 'cf_live_scored', '1' );

        delete_post_meta( $event_id, 'sp_player' );

        foreach ( $players as $team_id => $team_players ) {
            foreach ( $team_players as $player_id => $player ) {
                add_post_meta( $event_id, 'sp_player', $player_id );
            }
        }

        $output['players'] = $players;
        update_post_meta( $event_id, 'lsfs_scorers', $scorers );
        
        $output['player'] = array( 'row' => $scorer_array['row'], 'id' => $team_player_id, 'team_id' => $team_id, 'event_id' => $event_id, 'minute' => $team_player_minute, 'points' => $team_player_points, 'name' => trim( get_the_title( $team_player_id ) ) );
        $output = apply_filters( 'lsfs_ajax_scorer_added', $output, $data );
        wp_send_json_success( $output );
        wp_die();
    }

    /**
     * Remove the scorer from the array
     *
     * @return void
     */
    public function remove_scorer() {
        $output = array();

        $team_id = isset( $_POST['team_id'] ) ? absint( $_POST['team_id'] ) : 0;

        if ( ! $team_id ) {
            wp_send_json_error( array( 'message' => __( 'No Team ID', 'cfootball' ) ) );
            wp_die();
        }

        $event_id = isset( $_POST['event_id'] ) ? absint( $_POST['event_id'] ) : 0;
        
        if ( ! $event_id ) {
            wp_send_json_error( array( 'message' => __( 'No Event ID', 'cfootball' ) ) );
            wp_die();
        }

        $row = isset( $_POST['row'] ) ? absint( $_POST['row'] ) : false;
        
        if ( false === $row ) {
            wp_send_json_error( array( 'message' => __( 'No Scorer selected', 'cfootball' ) ) );
            wp_die();
        }

        $scorers = get_post_meta( $event_id, 'lsfs_scorers', true );

        // We don't even have scorers, let's pass it.
        if ( ! $scorers ) {
            wp_send_json_success();
            wp_die();
        }

        $team_scorers = isset( $scorers[ $team_id ] ) ? $scorers[ $team_id ] : false;

        if( ! $team_scorers ) {
            wp_send_json_success();
            wp_die();
        }

        $_scorer = false;
        $default_row = 0;
        // Just in case, if we don't have it, let's add it.
        foreach ( $team_scorers as $__row => $team_scorer ) {
            if( ! isset( $team_scorer['row'] ) ) {
                $team_scorer['row'] = $default_row;
                $team_scorers[ $__row ] = $team_scorer;
            }
            $default_row++;
        }
        // Let's unset the of this scorer because we are going to add him again. If not false.
        foreach ( $team_scorers as $_row => $scorer ) {
            if( absint( $scorer['row'] ) === $row ) {
                $_scorer = $scorer;
                unset( $team_scorers[ $_row ] );
                break;
            }
        }

        if ( false !== $_scorer ) {
            
            $team_scorers_row = 0;
            // Let's reset the rows
            foreach ( $team_scorers as $_row => $scorer ) {
                $scorer['row'] = $team_scorers_row;
                $team_scorers[ $_row ] = $scorer;
                $team_scorers_row++;
            }
            $scorers[ $team_id ] = $team_scorers;

            update_post_meta( $event_id, 'lsfs_scorers', $scorers );

            $primary_performance = sp_get_main_performance_option();

            $players = get_post_meta( $event_id, 'sp_players', true );
            // Let's update the goals in the players table from SportsPress.
            if( $players 
                && isset( $players[ $team_id ] )
                && isset( $players[ $team_id ][ $_scorer['id'] ] ) ) {
                
                $value = isset( $players[ $team_id ][ $_scorer['id'] ][ $primary_performance ] ) ? $players[ $team_id ][ $_scorer['id'] ][ $primary_performance ] : 1;
                $points = isset( $_scorer['points'] ) ? absint( $_scorer['points'] ) : 1;
                $value -= $points;
                $players[ $team_id ][ $_scorer['id'] ][ $primary_performance ] = $value;
                update_post_meta( $event_id, 'sp_players', $players );

            }
        }
    
        wp_send_json_success( array( 'scorers' => $team_scorers, 'message' => __( 'Scorer Removed', 'live-scores-for-sportspress' ) ) );
        wp_die();
    }
}

return new LSFS_Admin_AJAX();