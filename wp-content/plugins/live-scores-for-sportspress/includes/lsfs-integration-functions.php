<?php

/**
 * Integration Functions for Live Scores
 */

if( ! defined('ABSPATH') ) {
    return;
}

/**
 * Return all the registered integrations
 * The array should be in form of
 * array(
 *   'integration_id' => 'integration_class'
 * )
 * 
 * @return array 
 */
function lsfs_get_integrations() {
    return apply_filters( 'lsfs_integrations', array() );
}

/**
 * Return active integrations
 * The array should be in form of
 * array(
 *   'integration_id' => 'integration_class'
 * )
 * 
 * @return array Array of active integration 
 */
function lsfs_get_active_integrations() {
    return get_option( 'lsfs_active_integrations', array() );
}

/**
 * Saving the active integrations
 * @param  array  $integrations Array of integrations
 * @return mixed               
 */
function lsfs_save_active_integrations( $integrations = array() ) {
    return update_option( 'lsfs_active_integrations', $integrations );
}