<?php

$current_timestamp = time();

$offset = get_option( 'gmt_offset' ) * 3600;

$local_time = $current_timestamp + $offset;

$today = getdate( $local_time );

$args = array(
    
    //Type & Status Parameters
    'post_type'   => 'sp_event',
    'post_status' => array( 'publish', 'future' ),
   
    //Order & Orderby Parameters
    'order'               => 'ASC',
    'orderby'             => 'date',
    'date_query' => array(
        array(
            'year'  => $today['year'],
            'month' => $today['mon'],
            'day'   => $today['mday'],
        ),
    ),
     
    //Pagination Parameters
    'posts_per_page'         => -1,
     
    //Parameters relating to caching
    'no_found_rows'          => true,
    'cache_results'          => true,
    'update_post_term_cache' => false,
    'update_post_meta_cache' => false,
    
);

$query = new WP_Query( $args );

?>
<div class="wrap">
    <h1><?php esc_html_e( 'Live Events', 'live-scores-for-sportspress' ); ?></h1>
    <?php
        if ( $query->have_posts() ) {
            while( $query->have_posts() ) {
                $query->the_post();
                $live_event = new LSFS_Live_Event( get_the_id() );
                $results = $live_event->live_results( false ); 
                ?>
                <div class="lsfs-live-match">
                    <div class="lsfs-header">
                        <div class="time">
	                        <?php echo get_the_time(); ?>
                        </div>
                        <div class="title">
                            <?php the_title(); ?>
                        </div>
                    </div>
                    <div class="lsfs-details">
                        <?php LSFS_Live_Event::form( get_the_id(), $results['results'] ); ?>
                    </div>
                </div>
                <?php
            }
            wp_reset_postdata();
        } else {
            echo '<div class="notice notice-warning notice-alt"><p>' . __( 'There are no scheduled Events for Today', 'live-scores-for-sportspress' ) . '</p></div>';
        }
    ?>
</div>