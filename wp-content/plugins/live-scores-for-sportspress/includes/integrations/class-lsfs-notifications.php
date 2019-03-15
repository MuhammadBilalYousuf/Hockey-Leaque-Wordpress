<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

if ( ! class_exists( 'LSFS_Notifications' ) ) {
    class LSFS_Notifications extends LSFS_Integration {

        /**
         * Used for displaying a documentation screen and button
         * If true, the method documentation() should be defined 
         * @var boolean
         */
        public $has_documentation = false;

        /**
         * Start the Integration
         */
        public function __construct() {
            
            $this->settings_id = 'lsfs_notifications';
            $this->title = __( 'Live Notifications', 'live-scores-for-sportspress' );
            $this->desc = __( 'Notifications will appear across your site when an event result has changed.', 'live-scores-for-sportspress' );
            $this->image = LSFS_URI . '/assets/images/svg/bell.svg';

            parent::__construct();
        }

        /**
         * Set the fields
         */
        public function set_fields() {
            $this->fields = apply_filters( 'lsfs_settings_fields_' . $this->settings_id, array(
                'sport' => array(
                    'type' => 'select',
                    'name' => 'sport',
                    'title' =>  __( 'Sport', 'live-scores-for-sportspress' ),
                    'default' => 'soccer',
                    'desc' => __( 'Select the default sport for Scorespro Live Scores', 'live-scores-for-sportspress' ),
                    'options' => array(
                        'soccer'     => __( 'Soccer', 'live-scores-for-sportspress' ),
                        //'basketball' => __( 'Basketball', 'live-scores-for-sportspress' ),
                        //'hockey'     => __( 'Hockey', 'live-scores-for-sportspress' ),
                        //'Volleyball' => __( 'Volleyball', 'live-scores-for-sportspress' )
                    )
                )
            ) );
        }

        /**
         * Buttons to be shown on the Integrations screen
         * @return void 
         */
        public function buttons( $integrations ) {

            echo '<a href="' . lsfs_fs()->get_upgrade_url() . '" class="button button-primary">' . __( 'Upgrade to PRO', 'live-scores-for-sportspress' ) . '</a>';
         
        }
    }
}

add_filter( 'lsfs_integrations', 'lsfs_add_notifications_integration' );

/**
 * Adding Notifications integration
 * @param  array $integrations 
 * @return array               
 */
function lsfs_add_notifications_integration( $integrations ) {
    $integrations['lsfs_notifications'] = 'LSFS_Notifications';
    return $integrations;
}