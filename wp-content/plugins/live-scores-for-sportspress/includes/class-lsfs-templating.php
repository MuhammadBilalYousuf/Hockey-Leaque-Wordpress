<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Class for templating
 */
class LSFS_Templating
{
    public function __construct() {
        add_filter( 'sportspress_locate_template', array( $this, 'templates' ) );
    }

    public function templates( $template, $template_name = '', $template_path = '' ) {
        
        if( '' === $template_name ) {
            $template_name = str_replace( SP()->plugin_path() . '/templates/', '', $template );
        }
     
        switch ( $template_name ) {
    
            case 'event-list.php': 
            
                if( is_singular( 'sp_calendar' ) ) {

                    if( get_post_meta( get_the_id(), 'lsfs_calendar_live', true ) ) {
                        $_template = lsfs_return_template( 'live-event-list.php' );

                        if( $_template ) {
                            $template = $_template;
                        }
                    } 
                }
                

                break;
             
            default:
                # code...
                break;
        }


        return $template;
    }
}

new LSFS_Templating();