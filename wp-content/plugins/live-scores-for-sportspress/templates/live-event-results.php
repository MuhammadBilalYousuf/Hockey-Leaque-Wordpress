<?php
/**
 * Live Event Results
 *
 * @author      Igor Benić  
 * @package     LSFS/Templates
 * @version   0.4.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
if ( get_option( 'sportspress_event_show_results', 'yes' ) === 'no' ) return;
 
if ( ! isset( $id ) )
    $id = get_the_ID();

$event = new LSFS_Live_Event( $id );
$status = $event->status();
 
// Get event result data
$data = $event->live_results();
 
if( false === $data ) {
    return false;
}

if ( empty( $data ) )
    return false;

$results = isset( $data['results'] ) ? $data['results'] : false;

if( ! $results ) {
    return false;
}

if( ! isset( $data['status'] ) ) {
    $data['status'] = '';
}


if ( ! isset( $caption ) ) $caption = __( 'Live', 'sportspress' );

$abbreviate_teams = get_option( 'sportspress_abbreviate_teams', 'yes' ) === 'yes' ? true : false;
 
// Initialize
$output = '';

$live_results = array();
foreach( $results as $team_id => $result ):
     
    $live_results[] = $result;
  
endforeach;

if ( empty( $live_results ) ):

    return false;

else:

    $scorers = $event->get_scorers();
    if ( ! $scorers ) {
        $scorers = array();
    }
    $have_scorers = false;
    if ( $scorers ) {
        foreach ( $scorers as $scorer_team => $scorer_array ) {
            if ( $scorer_array ) {
                $have_scorers = true;
                break;
            }
        }
    }
 
    $output .= '<h4 class="sp-table-caption">' . $caption . '</h4>';

    $output .= '<div data-live-event="'. $id . '" class="sp-data-table lsfs-live-wrapper lsfs-single-event">';
    $output .= '<div class="data-live-results">' . implode( ' - ', $live_results ) . $data['status'] . '</div>';
    $output .= '</div>';
     
    $output .= '<div class="lsfs-live-scorers ' . ( false === $have_scorers ? 'lsfs-hidden' : '' ) . '">';
     
    $teams = (array) get_post_meta( $id, 'sp_team' );
    $teams = array_filter( $teams, 'sp_filter_positive' );
    $scorer_info = get_option( 'lsfs_scorers_information', 'minutes' );
    $show_player_number = get_option( 'lsfs_scorers_number', 'no' );
    foreach( $teams as $team_id ) {
        $team_scorers = isset( $scorers[ $team_id ] ) ? $scorers[ $team_id ] : array();
        $output .= '<div class="lsfs-live-team-scorers ' . ( ! $team_scorers ? 'lsfs-hidden' : '' ) . '" data-team="' . esc_attr( $team_id ) . '">';
        $output .= '<strong>' . get_the_title( $team_id ) . '</strong>';
        $output .= '<ul>';
        $_scorers = array();
        foreach( $team_scorers as $scorer ) {
            $scorer_id = $scorer['id'];
            if ( ! isset( $_scorers[ $scorer_id ] ) ) {
                if ( 'yes' === $show_player_number ) {
                    $sp_number = get_post_meta( $scorer_id, 'sp_number', true );
                    if ( $sp_number ) {
                        $scorer['name'] = $sp_number . ' ' .$scorer['name'];
                    }
                }
                $_scorers[ $scorer_id ] = array(
                    'minutes' => array(),
                    'points'  => array(),
                    'name'    => $scorer['name'],
                );
            }
            $_scorers[ $scorer_id ]['minutes'][] = $scorer['minute'] . '\'';
            $_scorers[ $scorer_id ]['points'][] = $scorer['points'];
        }
        foreach( $_scorers as $player_id => $player ) {
            $info = implode( ', ', $player['minutes'] );
            if ( 'points' === $scorer_info ) {
                $info = array_sum( $player['points'] );
            }
            $output .= '<li>' . $player['name'] . ' <span class="lsfs-scorer-minutes">' . $info . '</span></li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
    }
        
    
    $output .= '</div>';
    

endif;
?>
<div data-lsfs-live="<?php echo $id; ?>" data-lsfs-type="event" class="sp-template sp-template-live-event-result">
    <?php echo $output; ?>
</div>
<?php
    if( current_user_can( 'manage_sportspress' ) ) { ?>
        <form method="POST" class="lsfs-form-live-event-results">
            <input type="hidden" name="event_id" value="<?php echo $id; ?>" />
            <?php LSFS_Live_Event::form( $id, $data['results'] ); ?>
        </form>
    <?php
    }