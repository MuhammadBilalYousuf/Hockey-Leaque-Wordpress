<?php
/*
 * Plugin Name: Sponsors Carousel
 * Plugin URI: http://wordpress.org/extend/plugins/sponsors-carousel
 * Description: Sponsors logos on javascript carousel.
 * Version: 4.00
 * Author: Sergey Panasenko <sergey.panasenko@gmail.com>
 * Text Domain: sponsors-carousel
 * Domain Path: /languages
*/

/*  Copyright 2017  Sergey Panasenko  (email: sergey.panasenko@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define("SPONSORS_CAROUSEL_VERSION", "4.00");

include_once("class-sponsors-carousel.php");

$scwp_plugin_name = __( "Sponsors Carousel", 'sponsors-carousel' );
$scwp_plugin_filename = basename( __FILE__ ); //"sponsors-carousel.php";

load_plugin_textdomain( 'sponsors-carousel', NULL,
			dirname( plugin_basename( __FILE__ ) ) . "/languages" );

add_shortcode( 'sponsors_carousel', function( $arg ) {
	return sponsors_carousel( isset( $arg['id'] ) ? $arg['id'] : 0 );
});

add_action( 'wp_enqueue_scripts',  function () {
	wp_enqueue_script( 'jcarousel', plugins_url( '/js/jquery.jcarousel.min.js', __FILE__ ), ['jquery'], SPONSORS_CAROUSEL_VERSION);
	wp_enqueue_script( 'jcarousel-autoscroll', plugins_url( '/js/jquery.jcarousel-autoscroll.min.js', __FILE__ ), ['jquery','jcarousel'], SPONSORS_CAROUSEL_VERSION );
	wp_enqueue_script( 'sponsors-carousel', plugins_url( '/js/sponsors-carousel.js', __FILE__ ), ['jquery','jcarousel','jcarousel-autoscroll'], SPONSORS_CAROUSEL_VERSION );
} );

add_action('wp_print_styles',  function () {
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'sponsors-carousel', plugins_url( '/css/sponsors-carousel.css', __FILE__ ) );
} );

add_action('admin_init', function () {
	wp_register_script( 'sponsors-carousel-admin', plugins_url( '/js/sponsors-carousel-admin.js', __FILE__ ),['jquery', 'jquery-ui-core', 'jquery-ui-sortable'], SPONSORS_CAROUSEL_VERSION );
	wp_register_style( 'sponsors-carousel-admin', plugins_url( '/css/sponsors-carousel-admin.css', __FILE__ ), [], SPONSORS_CAROUSEL_VERSION );
	add_action( 'wp_ajax_sponsors_carousel_change_link', 'scwp_wp_ajax_sponsors_carousel_change_link');
});

add_action( 'admin_menu', function () {
	global $scwp_plugin_name;
	$icon = 'data:image/svg+xml;base64,' . base64_encode('<svg width="20" height="20" viewBox="0 0 320 320" xmlns="http://www.w3.org/2000/svg"><path fill="black" d="m160 0a160 160 0 0 0 -160 160 160 160 0 0 0 160 160 160 160 0 0 0 160 -160 160 160 0 0 0 -160 -160zm-50.7 110.6c9.3 0 18.7 2.2 26.8 6.9l-6.3 16.5c-5.9-3.6-12.7-5.5-19.5-5.7-6.1-.9-14.85 2.6-12.62 10.2 3.62 8.5 14.42 9.2 21.82 13 10.3 3.6 19.6 12.4 19.8 23.9 1.7 11.4-5 23.3-16.1 26.9-15.7 5.1-33.95 3.5-48.45-4.6 1.99-5.6 3.98-11.1 5.98-16.7 8.69 5.1 18.85 7.5 28.77 6.2 6.4.2 12-6.4 8.7-12.6-6.6-9.4-19.73-9.4-28.86-15.4-9.7-5.1-14.33-16.8-11.85-27.3 2.27-13.9 16.95-21.5 29.91-21.2.7 0 1.3-.1 1.9-.1zm106.9.1c10.1-.1 20.6 1.9 29.3 7.4-2 5.6-4 11.2-6 16.7-6-3.8-13.1-5.8-20.2-6.1-11.1-1.1-22.2 6.4-24.5 17.5-3 12.6-2.4 29.2 9.3 37.4 11.3 6.4 25.8 3.8 36.8-2.2 1.8 5.6 3.6 11.1 5.4 16.6-5.3 3.5-11.7 5.3-17.9 6.2-16.9 3-37.1-.3-47.2-15.6-9.7-14.7-10.6-34.2-5-50.7 5.9-16.3 22.7-27.4 40-27.2zm-173.7 30.6c8.03 0 14.59 6.6 14.59 14.6s-6.56 14.6-14.59 14.6c-8.04 0-14.6-6.6-14.6-14.6s6.56-14.6 14.6-14.6zm230.4 2.7c8 0 14.5 6.6 14.5 14.6 0 8.1-6.5 14.6-14.5 14.6-8.1 0-14.6-6.5-14.6-14.6 0-8 6.5-14.6 14.6-14.6zm-230.4 1.3c-5.88 0-10.6 4.7-10.6 10.6s4.72 10.6 10.6 10.6c5.87 0 10.59-4.7 10.59-10.6s-4.72-10.6-10.59-10.6zm3.42 1.5a2 2 0 0 1 1.43 3.4l-5.63 5.8 5.48 5.6a2 2 0 0 1 -2.54 3l-.23-.1a2 2 0 0 1 -.64 -.7l-6.3-6.4a2 2 0 0 1 0 -2.8l7-7.2a2 2 0 0 1 1.43 -.6zm227 1.2c-5.9 0-10.6 4.7-10.6 10.6s4.7 10.6 10.6 10.6c5.8 0 10.5-4.7 10.5-10.6s-4.7-10.6-10.5-10.6zm-4.4 1.5a2 2 0 0 1 1.4 .7l7 7.1a2 2 0 0 1 0 2.8l-6.3 6.4a2 2 0 0 1 -.6 .7l-.3.2a2 2 0 0 1 -2.5 -3.1l5.5-5.6-5.7-5.7a2 2 0 0 1 1.5 -3.5z"/></svg>');
	$page = add_menu_page( $scwp_plugin_name, $scwp_plugin_name, 'manage_options', 'sponsors_carousel', 'scwp_options_page', $icon, 55 );
	add_action('admin_print_scripts-' . $page, 'scwp_admin_enqueue_scripts');
	add_filter( 'plugin_action_links', 'scwp_add_quick_settings_link', 10, 2 );
} );

function scwp_admin_enqueue_scripts() {
	wp_enqueue_media();
	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'sponsors-carousel-admin' );
	wp_enqueue_style( 'sponsors-carousel-admin' );
}

function scwp_add_quick_settings_link($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$link = admin_url('admin.php?page=sponsors_carousel');
		$dashboard_link = "<a href=\"{$link}\">" . __( 'Settings', 'sponsors-carousel' ) . "</a>";
		array_unshift($links, $dashboard_link);
	}
	return $links;
}

function scwp_wp_ajax_sponsors_carousel_change_link () {
	if ( is_admin() ) {
		$post = array(
			'ID'             => intval($_POST['image_id']),
			'post_excerpt'   => $_POST['link'],
		);
		wp_update_post( $post );
		echo 'Saved';
	}
	wp_die();
}

// Show options page
function scwp_options_page() {

	if ( isset( $_POST['save'] ) ) { // Update options
		$sponsors_carousel = new SponsorsCarousel();
		if ( $sponsors_carousel->update( $_POST ) ) {
			echo "<div class='updated fade'><p><strong>" . __( 'Options updated', 'sponsors-carousel' ) . "</strong></p></div>";
		}
	}

	if ( isset( $_GET['delete'] ) && isset( $_GET['id'] ) && intval( $_GET['id'] ) == intval( $_GET['delete'] ) ) { // Delete carousel
		echo SponsorsCarousel::delete( intval( $_GET['delete'] ) );
		die();
	}

	global $scwp_plugin_name;
	echo '<h2>' . sprintf( __( '%s Settings', 'sponsors-carousel' ), $scwp_plugin_name ) . '</h2>';
	$id = isset( $_REQUEST['id'] ) ? intval( $_REQUEST['id'] ) : 0;
	echo SponsorsCarousel::show_buttons( $id );
	$option = get_option( 'sponsors-carousel-' . $id );
	if ( false == $option ) {
		$sponsors_carousel = new SponsorsCarousel( $id );
	}
	else $sponsors_carousel = unserialize( $option );
	echo $sponsors_carousel->get_form();
}

function sponsors_carousel( $id = 0 ) {
	$id = intval( $id );
	$option = get_option( 'sponsors-carousel-' . $id );
	if ( false == $option ) {
		return false;
	}
	$sponsors_carousel = unserialize( $option );
	return $sponsors_carousel->show();
}

add_action( 'init', function () {

	if ( function_exists( 'register_block_type' )) {
		$list = get_option( 'sponsors-carousels', '0' );
		$ids = explode( ',', $list );
		$list = array( array( 'value' => 0, 'label' => __( 'main', 'sponsors-carousel' )));
		foreach ( $ids as $id ) {
			if ( $id > 0 ) {
				$list[] = array( 'value' => $id, 'label' => $id );
			}
		}

		wp_register_script(
			'sponsors-carousel-block',
			plugins_url( 'js/sponsors-carousel-block.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-editor' )
		);

		register_block_type( 'sponsors-carousel/block', array(
			'attributes'      => array(
				'id' => array(
					'type' => 'number',
					'default' => 0,
				),
				'list' => array(
					'type' => 'array',
					'default' => $list,
				),
				'label' => array(
					'type' => 'string',
					'default' => __( 'ID', 'sponsors-carousel' ),
				),
			),
			'editor_script'   => 'sponsors-carousel-block',
			'render_callback' => 'sponsors_carousel_block_render',
		) );
	}

});

function sponsors_carousel_block_render( $attributes ) {
	wp_enqueue_style( 'sponsors-carousel', plugins_url( '/css/sponsors-carousel.css', __FILE__ ) );
	$sc = sponsors_carousel( isset( $attributes['id'] ) ? $attributes['id'] : 0 );
	return empty( $sc ) ? ( '<p>' . __( 'Please add images on <b>Sponsors carousel</b> plugin page', 'sponsors-carousel' ) . '</p>' ) : $sc;
}
