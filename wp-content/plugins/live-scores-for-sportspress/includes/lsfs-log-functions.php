<?php

/**
 * Functions for the Event Log
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Prepare the Event Log Data array
 * @return array
 */
function lsfs_event_log_prepare( $status = '', $type = '', $message = '' ) {
    $log = array();
    $log['status']  = $status;
    $log['type']    = $type;
    $log['message'] = $message;
    
    return apply_filters( 'lsfs_event_log_prepare', $log );
}

/**
 * Get the Event Log
 * @param  integer $post_id ID of the event
 * @return array|boolean          
 */
function lsfs_event_log_get( $post_id ) {
    return get_post_meta( $post_id, 'lsfs_event_log', true );
}

/**
 * Remove an Event Log based on timestamp
 * @param  integer $post_id 
 * @param  integer $timestamp 
 * @return bool            
 */
function lsfs_event_log_remove( $post_id, $timestamp ) {
    $event_log = lsfs_event_log_get( $post_id );
    
    if( isset( $event_log[ $timestamp ] ) ) {
        unset( $event_log[ $timestamp ] );
    }

    return update_post_meta( $post_id, 'lsfs_event_log', $event_log );
}

/**
 * Remove the whole event log.
 * 
 * @since 1.3.0
 *
 * @param int $post_id
 * @return void
 */
function lsfs_event_log_remove_all( $post_id ) {
    return delete_post_meta( $post_id, 'lsfs_event_log' );
}

/**
 * Add a new event log
 * @param  integer $post_id   ID of the event  
 * @param  array $data       Array of all the data to pass
 * @param  integer $timestamp Timestamp, if false we will add the current time
 * @return boolean
 */
function lsfs_event_log_add( $post_id, $data, $timestamp = null ) {
    $event_log = lsfs_event_log_get( $post_id );

    if( ! $event_log ) {
        $event_log = array();
    }

    if( ! $timestamp ) {
        $timestamp = time();
    }

    $event_log[ $timestamp ] = $data;

    return update_post_meta( $post_id, 'lsfs_event_log', $event_log );
}

/**
 * Get the Configuration Log
 * @param  integer $post_id ID of the event
 * @return array|boolean          
 */
function lsfs_config_log_get( $post_id ) {
    return get_post_meta( $post_id, 'lsfs_config_log', true );
}

/**
 * Adding a Config Log. This is different from event log since it contains the data used on the admin side
 * @param  integer $post_id     Event ID 
 * @param  integer $config_id   Configuration ID (Live Part)
 * @param  array $data          Array with data to save
 * @return bool            
 */
function lsfs_config_log_add( $post_id, $config_id, $data ) {
    
    $config_log = lsfs_config_log_get( $post_id );

    if( ! $config_log ) {
        $config_log = array();
    }

    if( ! $timestamp ) {
        $timestamp = time();
    }

    $live_part = isset( $config_log[ $config_id ] ) ? $config_log[ $config_id ] : array();

    if( is_array( $data ) ) {
        $live_part = array_merge( $live_part, $data );
    }

    $config_log[ $config_id ] = $live_part;

    return update_post_meta( $post_id, 'lsfs_config_log', $config_log );
}