<h2><?php _e( 'Settings', 'live-scores-for-sportspress' ); ?></h2>
<p><?php _e( 'To set Live Scores for SportsPress, you have to go to SportsPress > Settings and under the tab Live choose your preferences', 'live-scores-for-sportspress' ); ?></p>
<a href="<?php echo admin_url( 'admin.php?page=sportspress&tab=lsfs' ); ?>" class="button button-secondary"><?php _e( 'Go to Live Settings', 'live-scores-for-sportspress' ); ?></a>

<h2><?php _e( 'Live Scores', 'live-scores-for-sportspress' ); ?></h2>
<h3><?php _e( 'Setting a Live Page', 'live-scores-for-sportspress' ); ?></h3>
<p><?php _e( 'For now, you use the shortcode [live_event_list CALENDAR_ID]. The <strong>CALENDAR_ID</strong> can be seen from Events > Calendars.', 'live-scores-for-sportspress' ); ?></p>
<p><?php _e( 'When you create a new event list (not block or calendar) you will get that shortcode built for you.', 'live-scores-for-sportspress' ); ?></p>
<h3><?php _e( 'Updating the event', 'live-scores-for-sportspress' ); ?></h3>
<p><?php _e( 'You can update the event from the edit screen of each event. You can start, stop the event or even create halves, timeouts etc', 'live-scores-for-sportspress' ); ?></p>
<p><?php _e( 'To update the results, you just have to fill the main result and hit Save', 'live-scores-for-sportspress' ); ?></p>
<h3><?php _e( 'Configuring Live Details', 'live-scores-for-sportspress' ); ?></h3>
<p><?php _e( 'Under SportsPress > Configuration, you will see Live Parts. There are two types of live parts:', 'live-scores-for-sportspress' ); ?></p>
<ol>
    <li><?php _e( 'Timed Parts. Those are parts such as 1st & 2nd Half in Soccer with 45minutes. You can only start and stop them once.', 'live-scores-for-sportspress' );?></li>
    <li><?php _e( 'Non-times Parts. Those are parts such as timeouts in Basketball where the minutes are paused. Or even penalties at the end of a soccer match. You can start and stop them as many times as you want', 'live-scores-for-sportspress' ); ?></li>
</ol>
<p>
    <strong><em><?php _e( 'Clicking on the buttons below will delete all previous live parts that were configured. This should be used only for the first time setup.', 'live-scores-for-sportspress' ) ; ?></em></strong><br/>
    <a href="<?php echo admin_url('admin.php?page=live-scores-for-sportspress&config=soccer'); ?>" class="button button-secondary"><?php _e( 'Configure for Soccer', 'live-scores-for-sportspress' ); ?></a>
    <a href="<?php echo admin_url('admin.php?page=live-scores-for-sportspress&config=basketball-fiba'); ?>" class="button button-secondary"><?php _e( 'Configure for Basketball FIBA', 'live-scores-for-sportspress' ); ?></a>
</p>
<em><?php _e( 'Video Tutorials will be available as soon.', 'live-scores-for-sportspress' ); ?></em>      
