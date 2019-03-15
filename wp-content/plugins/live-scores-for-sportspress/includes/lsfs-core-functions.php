<?php

/**
 * Core Functions for Live Scores
 */

if( ! defined('ABSPATH') ) {
    return;
}

/**
 * Get template part.
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function lsfs_get_template_part( $slug, $name = '' ) {
    $template = '';

    // Look in yourtheme/slug-name.php and yourtheme/sportspress/slug-name.php
    if ( $name ) {
        $template = locate_template( array( "{$slug}-{$name}.php", LSFS()->template_path() . "{$slug}-{$name}.php" ) );
    }

    // Get default slug-name.php
    if ( ! $template && $name && file_exists( LSFS()->plugin_path() . "/templates/{$slug}-{$name}.php" ) ) {
        $template = LSFS()->plugin_path() . "/templates/{$slug}-{$name}.php";
    }

    // If template file doesn't exist, look in yourtheme/slug.php and yourtheme/sportspress/slug.php
    if ( ! $template ) {
        $template = locate_template( array( "{$slug}.php", LSFS()->template_path() . "{$slug}.php" ) );
    }

    // Allow 3rd party plugin filter template file from their plugin
    $template = apply_filters( 'lsfs_get_template_part', $template, $slug, $name );

    if ( $template ) {
        load_template( $template, false );
    }
}

/**
 * Get templates passing attributes and including the file.
 *
 * @access public
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function lsfs_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if ( $args && is_array( $args ) ) {
        extract( $args );
    }

    $located = lsfs_locate_template( $template_name, $template_path, $default_path );

    if ( ! file_exists( $located ) ) {
        _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '0.7' );
        return;
    }

    do_action( 'lsfs_before_template', $template_name, $template_path, $located, $args );

    include( $located );

    do_action( 'lsfs_after_template', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path  /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @access public
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function lsfs_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if ( ! $template_path ) {
        $template_path = LSFS()->template_path();
    }

    if ( ! $default_path ) {
        $default_path = LSFS()->plugin_path() . '/templates/';
    }

    // Look within passed path within the theme - this is priority
    $template = locate_template(
        array(
            trailingslashit( $template_path ) . $template_name,
            $template_name
        )
    );

    // Get default template
    if ( ! $template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found
    return apply_filters('lsfs_locate_template', $template, $template_name, $template_path);
}

/**
 * Getting the template from our plugin folder
 * @param  string $template_name Template string, example: event-list.php
 * @return string|bool           Returns string if file exists, otherwise false
 */
function lsfs_return_template( $template_name ) {
    
    $file_path = LSFS_PATH . '/templates/' . $template_name;

    if( file_exists( $file_path ) ) {
        return $file_path;
    }

    return false;
}