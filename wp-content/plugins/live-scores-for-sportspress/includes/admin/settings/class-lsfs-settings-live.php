<?php
/**
 * Live Settings
 *
 * @author      igorbenic
 * @category    Admin
 * @package     LSFS/Admin
 * @version     0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'LSFS_Settings_Live' ) ) :

/**
 * SP_Settings_Events
 */
class LSFS_Settings_Live extends SP_Settings_Page {

    /**
     * Constructor
     */
    public function __construct() {
        $this->id    = 'lsfs';
        $this->label = __( 'Live', 'sportspress' );
        $this->template  = 'lsfs';

        add_filter( 'sportspress_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
        add_action( 'sportspress_settings_' . $this->id, array( $this, 'output' ) );
        add_action( 'sportspress_admin_field_delimiter', array( $this, 'delimiter_setting' ) );
        add_action( 'sportspress_admin_field_event_layout', array( $this, 'layout_setting' ) );
        add_action( 'sportspress_admin_field_event_tabs', array( $this, 'tabs_setting' ) );
        add_action( 'sportspress_settings_save_' . $this->id, array( $this, 'save' ) );
    }

    /**
     * Get settings array
     *
     * @return array
     */
    public function get_settings() {

        $settings = array_merge(
            array(
                array( 'title' => __( 'General', 'live-scores-for-sportspress' ), 'type' => 'title', 'desc' => '', 'id' => 'lsfs_general_options' ),
            ),
             
            apply_filters( 'lsfs_general_options',
                array(
                    array(
                        'title'     => __( 'Refresh Rate', 'sportspress' ),
                        'desc'      => __( 'Set how many seconds will pass until the results get updated.', 'sportspress' ),
                        'id'        => 'lsfs_refresh_rate',
                        'default'   => '60',
                        'type'      => 'number',
                        'class'     => 'small-text'
                    )
                )
            ),

            array(
                array( 'type' => 'sectionend', 'id' => 'lsfs_general_options' ),
            ),

            array(
                array( 'title' => __( 'Event Options', 'live-scores-for-sportspress' ), 'type' => 'title', 'desc' => '', 'id' => 'lsfs_event_options' ),
            ),
             
            apply_filters( 'lsfs_event_options',
                array(
                    array(
                        'title'     => __( 'Live Status', 'sportspress' ),
                        'desc'      => __( 'Show the status such as minutes, half time etc. next to live results', 'sportspress' ),
                        'id'        => 'lsfs_show_status',
                        'default'   => 'yes',
                        'type'      => 'checkbox',
                    ),
                )
            ),

            array(
                array( 'type' => 'sectionend', 'id' => 'lsfs_event_options' ),
            ),

            array(
                array( 'title' => __( 'Scorer Options (Front)', 'live-scores-for-sportspress' ), 'type' => 'title', 'desc' => '', 'id' => 'lsfs_scorer_options' ),
            ),
             
            apply_filters( 'lsfs_scorer_options',
                array(
                    array(
                        'title'     => __( 'Score Information', 'live-scores-for-sportspress' ),
                        'desc'      => __( 'Which information should you display for scorers. Usually, for soccer is minutes and for basketball is points.', 'sportspress' ),
                        'id'        => 'lsfs_scorers_information',
                        'default'   => 'minutes',
                        'type'      => 'select',
                        'options'   => array(
                            'minutes' => __( 'Minutes', 'live-scores-for-sportspress' ),
                            'points'  => __( 'Points', 'live-scores-for-sportspress' ),
                        ),
                    ),
                    array(
                        'title'     => __( 'Scorer Number', 'live-scores-for-sportspress' ),
                        'desc'      => __( 'Show scorer number alongside name', 'sportspress' ),
                        'id'        => 'lsfs_scorers_number',
                        'default'   => 'no',
                        'type'      => 'checkbox',
                    ),
                )
            ),

            array(
                array( 'type' => 'sectionend', 'id' => 'lsfs_scorer_options' ),
            )

        );

        return apply_filters( 'lsfs_live_settings', $settings );
    }

    /**
     * Save settings
     */
    public function save() {
        parent::save();
        
        if ( isset( $_POST['sportspress_event_teams_delimiter'] ) )
            update_option( 'sportspress_event_teams_delimiter', $_POST['sportspress_event_teams_delimiter'] );
    }

    /**
     * Delimiter settings
     *
     * @access public
     * @return void
     */
    public function delimiter_setting() {
        $selection = get_option( 'sportspress_event_teams_delimiter', 'vs' );
        $limit = get_option( 'sportspress_event_teams', 2 );
        if ( 0 == $limit ) {
            $limit = 2;
        }
        if ( 3 >= $limit ) {
            $example = str_repeat( __( 'Team', 'sportspress' ) . ' %1$s ', $limit );
        } else {
            $example = str_repeat( __( 'Team', 'sportspress' ) . ' %1$s ', 3 ) . '&hellip;';
        }
        $example = rtrim( $example, ' %1$s ' );
        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <?php _e( 'Delimiter', 'sportspress' ); ?>
            </th>
            <td class="forminp">
                <fieldset class="sp-custom-input-wrapper">
                    <legend class="screen-reader-text"><span><?php _e( 'Delimiter', 'sportspress' ); ?></span></legend>
                    <?php $delimiters = array( 'vs', 'v', '&mdash;', '/' ); ?>
                    <?php foreach ( $delimiters as $delimiter ): ?>
                        <label title="<?php echo $delimiter; ?>"><input type="radio" class="preset" name="sportspress_event_teams_delimiter_preset" value="<?php echo $delimiter; ?>" data-example="<?php printf( $example, $delimiter ); ?>" <?php checked( $delimiter, $selection ); ?>> <span><?php printf( $example, $delimiter ); ?></span></label><br>
                    <?php endforeach; ?>
                    <label><input type="radio" class="preset" name="sportspress_event_teams_delimiter_preset" value="\c\u\s\t\o\m" <?php checked( false, in_array( $selection, $delimiters ) ); ?>> <?php _e( 'Custom:', 'sportspress' ); ?> </label><input type="text" class="small-text value" name="sportspress_event_teams_delimiter" value="<?php echo $selection; ?>" data-example-format="<?php printf( $example, '__val__' ); ?>">
                    <span class="example"><?php printf( $example, $selection ); ?></span>
                </fieldset>
            </td>
        </tr>
        <?php
    }
}

endif;

return new LSFS_Settings_Live();
