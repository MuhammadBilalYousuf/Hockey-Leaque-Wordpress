<?php

/**
 * Documentation for the Scorespro Free Integration
 */

?>
<p>
    <strong>
        <em><?php _e( 'This integration is subject to change since we don\'t control the output by scorespro.com', 'live-scores-for-sportspress' ); ?></em>
    </strong>
</p>
<p>
    <strong>
        <em><?php _e( 'If you see that the output does not work, feel free to contact me through my contact page on: ', 'live-scores-for-sportspress' ); ?><a href="http://www.ibenic.com" target="_blank">ibenic.com</a></em>
    </strong>
</p>

<h4><?php _e( 'Shortcode', 'live-scores-for-sportspress' ); ?></h4>
<p><?php _e( 'Add this shortcode to a page where you want to show the live scores from scorespro.com', 'live-scores-for-sportspress' ); ?></p>
<pre>[live_scorespro_free]</pre>
<p><?php _e( 'You can add a type to the shortcode for each Sport. If the type has not been defined, it will use the default sport', 'live-scores-for-sportspress' ); ?></p>
<table class="form-table">
    <thead>
        <tr>
            <td><strong><?php _e( 'Sport', 'live-scores-for-sportspress' ); ?></strong></td>
            <td><strong><?php _e( 'Shortcode', 'live-scores-for-sportspress' ); ?></strong></td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php _e( 'Default', 'live-scores-for-sportspress' ); ?></td>
            <td><pre>[live_scorespro_free]</pre></td>
        </tr>
        <tr>
            <td><?php _e( 'Soccer', 'live-scores-for-sportspress' ); ?></td>
            <td><pre>[live_scorespro_free type=soccer]</pre></td>
        </tr>
        <tr>
            <td><?php _e( 'Basketball', 'live-scores-for-sportspress' ); ?></td>
            <td><pre>[live_scorespro_free type=basketball]</pre></td>
        </tr>
        <tr>
            <td><?php _e( 'Hockey', 'live-scores-for-sportspress' ); ?></td>
            <td><pre>[live_scorespro_free type=hockey]</pre></td>
        </tr>
        <tr>
            <td><?php _e( 'Volleyball', 'live-scores-for-sportspress' ); ?></td>
            <td><pre>[live_scorespro_free type=volleyball]</pre></td>
        </tr>
    </tbody>
</table>