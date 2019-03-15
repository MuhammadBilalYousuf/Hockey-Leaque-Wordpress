<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

$time_format = get_option( 'sportspress_event_list_time_format', 'combined' );

/**
 * ScoresPro Free Live Events
 */

 if ( ! $events ) {
     ?>
     <p><?php esc_html_e( 'No Live Events for now.', 'live-scores-for-sportspress' ); ?></p>
     <?php
     return;
 }

?>
<div class="sp-template sp-template-event-list">
    <div class="sp-table-wrapper">
        <table data-lsfs-live="<?php echo $id; ?>" class="sp-event-list sp-event-list-scorespro-free sp-event-list-format-list sp-data-table">
            <thead>
                <tr>
                    <?php
                    echo '<th class="data-time">' . __( 'Time', 'sportspress' ) . '</th>';
                    echo '<th class="data-home">' . __( 'Home', 'sportspress' ) . '</th>';
                    echo '<th class="data-results">' . __( 'Results', 'sportspress' ) . '</th>';
                    echo '<th class="data-away">' . __( 'Away', 'sportspress' ) . '</th>';
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php

                foreach ( $events as $league => $_events ):
                    $i = 0;

                    echo '<tr>';
                        echo '<td class="data-league sp-table-caption" colspan="4"><strong>' . $league . '</strong></td>';
                    echo '</tr>';
                    foreach ( $_events as $event ) {
                         if( ! $event ) { continue; }
                        echo '<tr data-live-event="' . $event['id'] . '" class="sp-row sp-post' . ( $i % 2 == 0 ? ' alternate' : '' ) . ' sp-row-no-' . $i . '">';
                            
                            $status = trim( $event['status'] );

                            if( is_numeric( $status ) ) {
                                $status = $status . '<span class="minute">\'</span>';
                            }

                            $status = '<span class="lsfs-event-live-status">' . $status . '</span>';

                            echo '<td class="data-time">' . trim( $event['time'] ) . '</td>';
                            echo '<td class="data-home">' . trim( $event['t1'] ) . '</td>';
                            echo '<td class="data-results data-live-results">' . trim( $event['score'] ) . $status . '</td>';
                            echo '<td class="data-away">' . trim( $event['t2'] ) . '</td>';
    
                        echo '</tr>';

                        $i++;
                    }
                endforeach;
                ?>
            </tbody>
        </table>
    </div>
    <?php
    if ( $id && $show_all_events_link )
        echo '<div class="sp-calendar-link sp-view-all-link"><a href="' . get_permalink( $id ) . '">' . __( 'View all events', 'sportspress' ) . '</a></div>';
    ?>
</div>