<?php

/**
 * Abstract class for Integrations
 */

if( ! defined( 'ABSPATH' ) ) {
    return;
}

abstract class LSFS_Integration extends LSFS_Settings {
 
    /**
     * Active Indicator
     * @var boolean
     */
    public $active = false;

    /**
     * Integration Image
     * @var string
     */
    public $image = '';

    /**
     * Integration Title
     * @var string
     */
    public $title = '';

    /**
     * Integration Description
     * @var string
     */
    public $desc = '';

    /**
     * Used for displaying a documentation screen and button
     * If true, the method documentation() should be defined 
     * @var boolean
     */
    public $has_documentation = false;

     /**
     * Setting initial params
     */
    public function __construct() {
        add_action( 'integration_save_' . $this->settings_id, array( $this, 'save_settings' ) );
        add_action( 'integration_save', array( $this, 'save_integration' ) );
    }

     /**
     * Save if the button for this menu is submitted
     * @return void 
     */
    public function save_if_submit() {
        if( isset( $_POST[ $this->settings_id . '_save' ] ) ) {

            do_action( 'integration_save_' . $this->settings_id );
            do_action( 'integration_save', $_POST );

        }
    }

    /**
     * Integration Get Fields
     * @return void 
     */
    public function get_fields() {

        if( ! $this->fields ) {
            $this->set_fields();
        }

        // Making sure the integraiton always have an active checkbox
        $this->fields = array_merge( 
            array( 'active' => array(
                'type' => 'checkbox',
                'name' => 'active',
                'title' =>  __( 'Activate this integration', 'live-scores-for-sportspress' ),
                'default' => 'no',
                'desc' => __( 'Check this option to activate this integration', 'live-scores-for-sportspress' )
            )),
            $this->fields
        );

    }

    /**
     * Saving Active Integrations
     * @param  $_POST $data 
     * @return void       
     */
    public function save_integration( $data ) {
        $active_integrations = lsfs_get_active_integrations();
        
        if( isset( $data['active'] ) ) {
            if( ! isset( $active_integrations[ $this->settings_id ] ) ) {
                $active_integrations[ $this->settings_id ] = get_class( $this );
                do_action( 'lsfs_' . $this->settings_id . '_integration_activated' );
            }
        } elseif( isset( $active_integrations[ $this->settings_id ] ) ) {

            unset( $active_integrations[ $this->settings_id ] );
            do_action( 'lsfs_' . $this->settings_id . '_integration_deactivated' );
        }

        lsfs_save_active_integrations( $active_integrations ); 
    }

    

    /**
     * Buttons to be shown on the Integrations screen
     * @return void 
     */
    public function buttons( $integrations ) {

        if( ! isset( $integrations[ $this->settings_id ] ) ) {
                ?>
                    <button type="button" data-integration="<?php echo $this->settings_id; ?>" class="button button-primary button-lsfs-integration-activate"><?php _e( 'Activate', 'live-scores-for-sportspress' ); ?></button>
                <?php
            } else {
                ?>
                    <button type="button" data-integration="<?php echo $this->settings_id; ?>" class="button button-default button-lsfs-integration-deactivate"><?php _e( 'Deactivate', 'live-scores-for-sportspress' ); ?></button>
                <?php
            }
        ?>
        <div style="float:right;">
            <?php if( $this->has_documentation ) { ?>
                <a type="button" href="<?php echo admin_url( 'admin.php?page=lsfs-integrations&integration=' . $this->settings_id . '&section=documentation' ); ?>" class="button button-secondary"><?php _e( 'Documentation', 'live-scores-for-sportspress' ); ?></a>
            <?php } ?>
            <a type="button" href="<?php echo admin_url( 'admin.php?page=lsfs-integrations&integration=' . $this->settings_id ); ?>" class="button button-secondary"><?php _e( 'Settings', 'live-scores-for-sportspress' ); ?></a>
        </div>

        <?php
    }

    /**
     * Integration Documentation
     * @return void 
     */
    public function documentation() {}
}