<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

$integrations = lsfs_get_integrations();
$active_integrations = lsfs_get_active_integrations();
$integration = isset( $_GET['integration' ] ) ? $_GET['integration'] : false;
$integration_object = false;

if( $integration && isset( $integrations[ $integration ] ) ) { 
    $class = $integrations[ $integration ];
    $integration_object = new $class();
    $integration_object->save_if_submit();
    $integration_object->init_settings();
}

?>
<div class="wrap">
    <h1><?php echo get_admin_page_title(); ?></h1>
    <p><?php _e( 'Integrations for Live Scores.', 'live-scores-for-sportspress' ); ?></p>
         
    <div class="wp-list-table widefat plugin-install">
        <div id="the-list">
            <?php 
                if( $integration_object ) {

                    echo '<h2>' . $integration_object->title . '</h2>';
                    echo '<a href="' . admin_url( 'admin.php?page=lsfs-integrations' ) . '">' . __( 'Back to Integrations', 'giveasap' ) . '</a>';
                    if( isset( $_GET['section'] ) && 'documentation' == $_GET['section'] && $integration_object->has_documentation ) {
                        $integration_object->documentation();
                    } else {
                    ?>
                        <form id="<?php echo $integration_object->settings_id; ?>" method="POST" action="">
             
                            <table class="form-table">
                                <?php
                                    $integration_object->render_fields();
                                ?>
                            </table> 
                            <button type="submit" name="<?php echo $integration_object->settings_id; ?>_save" class="button button-primary">
                                <?php _e( 'Save', 'textdomain' ); ?>
                            </button>
                        </form>
                    <?php
                    }

                } elseif( $integrations ) {
                    foreach ( $integrations as $slug => $class ) {
                        $integration_object = new $class();
                        ?>
                            <div class="plugin-card plugin-card-<?php echo $slug; ?>">
                                <div class="plugin-card-top">
                                    <div class="name column-name">
                                        <h3>
                                            <?php echo $integration_object->title; ?>                   
                                            <img src="<?php echo $integration_object->image; ?>" class="plugin-icon" alt="">  
                                        </h3>
                                    </div>
                                     <div class="desc column-description">
                                        <p><?php echo $integration_object->desc; ?></p>
                                     </div>
                                </div>
                                <div class="plugin-card-bottom">

                                    <?php $integration_object->buttons( $active_integrations ); ?>
        
                                </div>
                            </div>
                        <?php
                    }
                }
            ?>
        </div>
    </div>
</div>