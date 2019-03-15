<h2><?php echo sprintf( __( 'Version: %s', 'live-scores-for-sportspress' ), LSFS_VERSION ); ?></h2>

<p><?php _e( 'Live Scores for SportsPress will allow you to create and manage live events in SportsPress', 'live-scores-for-sportspress' ); ?></p>

<h2><?php _e( 'Changelog', 'live-scores-for-sportspress' ); ?></h2>
<em><?php printf( __( 'See what has changed with %s', 'live-scores-for-sportspress' ), LSFS_VERSION ); ?></em>
<ul>
    <li>Added Scorespro live scores for Basketball, Volleyball and Hockey</li>
    <li>Added Option to Display Scorer Number</li>
    <li>Added Option to Display Scored Points instead of minutes</li>
    <li>Added Option to define scored Points when adding Scorer</li>
    <li>PRO: Added option to add Scorer info directly to Commentary (Admin)</li>
    <li>PRO: Added Player dropdown in Commentary Form on Front</li>
</ul>
<a href="<?php echo lsfs_fs()->get_upgrade_url(); ?>" class="button button-primary"><?php _e( 'Upgrade to PRO', 'live-scores-for-sportspress' ); ?></a>
