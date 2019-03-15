<?php

/**
 * Post types
 *
 * Registering post types
 *
 * @class       LSFS_Post_Types
 * @version     0.1.0 
 * @package     LSFS/Classes
 * @category    Class
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}
 
class LSFS_Post_types {

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 ); 
        add_filter( 'sportspress_config_types', array( $this, 'config_types') );
        add_filter( 'sportspress_post_types', array( $this, 'config_types' ) );
        add_filter( 'sportspress_meta_boxes', array( $this, 'meta_boxes' ), 99 );
        add_filter( 'sportspress_after_event_template', array( $this, 'event_templates' ) );
            
    }

    /**
     * Adding Event Templates
     * @param  array $templates 
     * @return array            
     */
    public function event_templates( $templates ) {
        $templates['live_results'] = array(
            'title' => __( 'Live Results', 'live-scores-for-sportspress' ),
            'option' => 'sportspress_event_show_live_results',
            'action' => 'lsfs_output_event_live_results',
            'default' => 'yes',
        );
        return $templates;
    }

    /**
     * Register core post types
     */
    public static function register_post_types() {

        register_post_type( 'lsfs_live_parts',
            apply_filters( 'lsfs_register_post_type_live_parts',
                array(
                    'labels' => array(
                        'name'                  => __( 'Live Parts', 'sportspress' ),
                        'menu_name'             => __( 'Live Parts', 'sportspress' ),
                        'singular_name'         => __( 'Live Part', 'sportspress' ),
                        'add_new_item'          => __( 'Add New Live Part', 'sportspress' ),
                        'edit_item'             => __( 'Edit Live Part', 'sportspress' ),
                        'new_item'              => __( 'New', 'sportspress' ),
                        'view_item'             => __( 'View', 'sportspress' ),
                        'search_items'          => __( 'Search', 'sportspress' ),
                        'not_found'             => __( 'No results found.', 'sportspress' ),
                        'not_found_in_trash'    => __( 'No results found.', 'sportspress' ),
                    ),
                    'public'                => false,
                    'show_ui'               => true,
                    'capability_type'       => 'sp_config',
                    'map_meta_cap'          => true,
                    'publicly_queryable'    => false,
                    'exclude_from_search'   => true,
                    'hierarchical'          => false,
                    'supports'              => array( 'title', 'page-attributes', 'excerpt' ),
                    'has_archive'           => false,
                    'show_in_nav_menus'     => false,
                    'can_export'            => false,
                    'show_in_menu'          => false,
                )
            )
        );
    }

    /**
     * Adding Life Parts CPT as SP CPT
     * @param  array $types 
     * @return array        
     */
    public function config_types( $types ) {

        $types[] = 'lsfs_live_parts';

        return $types; 
    } 

    /**
     * Adding Boxes in SportsPress save and output
     * @param  array $boxes 
     * @return array        
     */
    public function meta_boxes( $boxes ) {
        
        $boxes[ 'lsfs_live_parts' ] = array(
            'details' => array(
                'title' => __( 'Details', 'sportspress' ),
                'save' => 'LSFS_Meta_Box_Live_Parts_Details::save',
                'output' => 'LSFS_Meta_Box_Live_Parts_Details::output',
                'context' => 'side',
                'priority' => 'default',
            ),
        );

        $boxes[ 'sp_event' ]['live'] = array(
            'title' => __( 'Live Details', 'sportspress' ),
            'save' => 'LSFS_Meta_Box_SP_Event_Live::save',
            'output' => 'LSFS_Meta_Box_SP_Event_Live::output',
            'context' => 'advanced',
            'priority' => 'default',
        );

        $boxes[ 'sp_event' ]['scorers'] = array(
            'title' => __( 'Live Scorers', 'sportspress' ),
            'save' => 'LSFS_Meta_Box_SP_Event_Scorers::save',
            'output' => 'LSFS_Meta_Box_SP_Event_Scorers::output',
            'context' => 'advanced',
            'priority' => 'default',
        );

        
        $boxes['sp_calendar']['shortcode']['output'] = 'LSFS_Meta_Box_Calendar_Shortcode::output';
        $boxes['sp_calendar']['details']['output'] = 'LSFS_Meta_Box_Calendar_Details::output';
        $boxes['sp_calendar']['details']['save'] = 'LSFS_Meta_Box_Calendar_Details::save';
       

        return $boxes;
    }
}

new LSFS_Post_types();
