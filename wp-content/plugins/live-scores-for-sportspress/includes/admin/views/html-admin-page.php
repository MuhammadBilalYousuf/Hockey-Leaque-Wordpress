<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

if( isset( $_GET['config'] ) ) {
    include( LSFS_PATH . '/includes/class-lsfs-installer.php' );
    $config = $_GET['config'];
    $installer = new LSFS_Installer();
    $status = $installer->configure( $config );
    if( is_wp_error( $status ) ) {
        echo '<div class="notice error is-dismissible"><p>' . $status->get_error_message() . '</p></div>';
    } else {
        echo '<div class="notice updated is-dismissible"><p>' . __( 'Live Parts Configured', 'live-scores-for-sportspress' ) . '</p></div>';
    }
}

$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';

?>
<div class="wrap">
    <h1><img width="32" style="display:inline-block;vertical-align:middle;height:32px;margin-right:5px;" src="<?php echo LSFS_URI; ?>/assets/images/icon-256x256.png" /><?php esc_html_e( 'Live Scores for SportsPress', 'live-scores-for-sportspress' ); ?></h1>
    
    <h2 class="nav-tab-wrapper sp-nav-tab-wrapper">
        <a href="<?php echo admin_url( 'admin.php?page=live-scores-for-sportspress' ); ?>" class="nav-tab <?php if ( $tab === 'general' ) { echo 'nav-tab-active'; } ?>">General</a>
        <a href="<?php echo admin_url( 'admin.php?page=live-scores-for-sportspress&tab=about' ); ?>" class="nav-tab <?php if ( $tab === 'about' ) { echo 'nav-tab-active'; } ?>">About</a>
    </h2>
    
    <?php
    
    include_once 'tabs/' . $tab . '.php';
    
    ?>
</div>