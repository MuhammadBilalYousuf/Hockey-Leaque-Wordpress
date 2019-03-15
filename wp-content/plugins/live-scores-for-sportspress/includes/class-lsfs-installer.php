<?php

/**
 * LSFS Installer
 * Installing & updating database
 */
if ( !defined( 'ABSPATH' ) ) {
    return;
}
if ( class_exists( 'LSFS_Installer' ) ) {
    return;
}
/**
 * Class to perform creating database and other stuff
 * @since  0.1.0
 */
class LSFS_Installer
{
    public  $updates = array(
        '1.4.0' => 'lsfs_update_140',
    ) ;
    /**
     * Start the installation
     * @return void 
     */
    public function install()
    {
        if ( !defined( 'LSFS_INSTALLING' ) ) {
            define( 'LSFS_INSTALLING', true );
        }
        $this->create_settings();
        $this->create_db();
    }
    
    /**
     * Set the Configuration
     * @param  string $sport Sport to be configured
     * @return void       
     */
    public function configure( $sport )
    {
        $configurations = $this->get_configurations();
        
        if ( isset( $configurations[$sport] ) ) {
            // First Delete All
            $current_live_parts = lsfs_get_live_parts( 'all' );
            
            if ( $current_live_parts->have_posts() ) {
                while ( $current_live_parts->have_posts() ) {
                    $current_live_parts->the_post();
                    wp_delete_post( get_the_id(), true );
                }
                wp_reset_postdata();
            }
            
            $live_parts = $configurations[$sport];
            foreach ( $live_parts as $part ) {
                $post_id = wp_insert_post( array(
                    'post_title'  => $part['title'],
                    'menu_order'  => $part['menu_order'],
                    'post_status' => 'publish',
                    'post_type'   => 'lsfs_live_parts',
                ) );
                
                if ( $post_id ) {
                    $type = $part['type'];
                    update_post_meta( $post_id, 'lsfs_live_type', $type );
                    $conf = $part['conf'];
                    update_post_meta( $post_id, 'lsfs_live', $conf );
                }
            
            }
            return true;
        }
        
        return new WP_Error( 'no-configuration', __( 'Sorry, there seems to be no configuration setup for the provided sport', 'live-scores-for-sportspress' ) );
    }
    
    public function get_configurations()
    {
        $configuration = apply_filters( 'lsfs_configuration', array(
            'soccer'          => array(
            array(
            'type'       => 'live',
            'title'      => __( '1st Half', 'live-scores-for-sportspress' ),
            'menu_order' => 1,
            'conf'       => array(
            'duration'           => '45',
            'stoppage'           => '1',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'Half Time', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '2nd Half', 'live-scores-for-sportspress' ),
            'menu_order' => 2,
            'conf'       => array(
            'duration'           => '45',
            'stoppage'           => '1',
            'text_after_minutes' => 'minutes',
            'text_after'         => '',
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '1st Extra Half', 'live-scores-for-sportspress' ),
            'menu_order' => 3,
            'conf'       => array(
            'duration'           => '15',
            'stoppage'           => '1',
            'text_after_minutes' => 'minutes',
            'text_after'         => '',
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '2nd Extra Half', 'live-scores-for-sportspress' ),
            'menu_order' => 4,
            'conf'       => array(
            'duration'           => '15',
            'stoppage'           => '1',
            'text_after_minutes' => 'minutes',
            'text_after'         => '',
        ),
        ),
            array(
            'type'       => 'pause',
            'title'      => __( 'Penalties', 'live-scores-for-sportspress' ),
            'menu_order' => 5,
            'conf'       => array(
            'duration'           => '',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'Penalties', 'live-scores-for-sportspress' ),
        ),
        )
        ),
            'basketball-fiba' => array(
            array(
            'type'       => 'live',
            'title'      => __( '1st Quarter', 'live-scores-for-sportspress' ),
            'menu_order' => 1,
            'conf'       => array(
            'duration'           => '10',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of 1st Q', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '2nd Quarter', 'live-scores-for-sportspress' ),
            'menu_order' => 2,
            'conf'       => array(
            'duration'           => '10',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of 2nd Q', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '3rd Quarter', 'live-scores-for-sportspress' ),
            'menu_order' => 3,
            'conf'       => array(
            'duration'           => '10',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of 3rd Q', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '4th Quarter', 'live-scores-for-sportspress' ),
            'menu_order' => 4,
            'conf'       => array(
            'duration'           => '10',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of 4th Q', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '1st Overtime', 'live-scores-for-sportspress' ),
            'menu_order' => 5,
            'conf'       => array(
            'duration'           => '5',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of OT', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'live',
            'title'      => __( '2nd Overtime', 'live-scores-for-sportspress' ),
            'menu_order' => 6,
            'conf'       => array(
            'duration'           => '5',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'End of 2OT', 'live-scores-for-sportspress' ),
        ),
        ),
            array(
            'type'       => 'pause',
            'title'      => __( 'Timeout', 'live-scores-for-sportspress' ),
            'menu_order' => 5,
            'conf'       => array(
            'duration'           => '',
            'stoppage'           => '0',
            'text_after_minutes' => 'text',
            'text_after'         => __( 'Timeout', 'live-scores-for-sportspress' ),
        ),
        )
        ),
        ) );
        return $configuration;
    }
    
    /**
     * Start the installation
     * @return void 
     */
    public function update( $from_version )
    {
        if ( !defined( 'LSFS_UPDATING' ) ) {
            define( 'LSFS_UPDATING', true );
        }
        foreach ( $this->updates as $version => $update_function ) {
            if ( version_compare( $from_version, $version, '<' ) ) {
                $update_function();
            }
        }
        update_option( 'lsfs_version', LSFS()->version );
    }
    
    /**
     * Create the Database
     * @return void 
     */
    public function create_db()
    {
        global  $wpdb ;
        $wpdb->hide_errors();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $this->get_schema() );
    }
    
    /**
     * Create Settings
     * @return void
     */
    public function create_settings()
    {
    }
    
    /**
     * Get Table schema.
     * @return string
     */
    private function get_schema()
    {
        global  $wpdb ;
        $collate = '';
        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }
        /*
         * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
         * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
         * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
         *
         * This may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
         * indexes first causes too much load on some servers/larger DB.
         */
        $max_index_length = 191;
        $tables = "";
        return $tables;
    }

}