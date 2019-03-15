<?php

if( ! defined( 'ABSPATH' ) ) {
    return;
}

/**
 * Live Scores Event List Widget
 * Used to display live events from today
 */
class LSFS_Widget_Event_List extends WP_Widget {

    /**
     * Construct method 
     */
    public function __construct() {
        $widget_ops = array( 'classname' => 'widget_sportspress widget_sp_event_list widget_sp_live_event_list', 'description' => __( 'A list of live events.', 'live-scores-for-sportspress' ) );
        parent::__construct('sportspress-lsfs-event-list', __( 'Live Event List', 'live-scores-for-sportspress' ), $widget_ops);
    }

    /**
     * Render method
     * @param  array $args     
     * @param  array $instance 
     * @return void           
     */
    public function widget( $args, $instance ) {

        extract( $args );
        
        $id = empty($instance['id']) ? null : $instance['id'];
        if ( $id && 'yes' == get_option( 'sportspress_widget_unique', 'no' ) && get_the_ID() === $id ) {
            $format = get_post_meta( $id, 'sp_format', true );
            if ( 'list' == $format ) return;
        }
        
        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title'], $instance, $this->id_base);
        $columns = empty($instance['columns']) ? null : $instance['columns'];
        $show_title = ! empty( $instance['show_title'] ) ? '1' : '0';
 
        do_action( 'sportspress_before_widget', $args, $instance, 'live-event-list' );
        echo $before_widget;

        if ( $title )
            echo $before_title . $title . $after_title;

        // Action to hook into
        do_action( 'sportspress_before_widget_template', $args, $instance, 'live-event-list' );

        lsfs_get_template( 'live-event-list.php', array( 'id' => $id, 'columns' => $columns, 'date' => 'day', 'show_title' => $show_title ) );

        // Action to hook into
        do_action( 'sportspress_after_widget_template', $args, $instance, 'live-event-list' );

        echo $after_widget;
        do_action( 'sportspress_after_widget', $args, $instance, 'live-event-list' );
    }

    /**
     * Settings Update
     * @param  array $new_instance New settings
     * @param  array $old_instance Old Settings
     * @return array               Merged new and old settings.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['id'] = intval($new_instance['id']);
        $instance['columns'] = (array)$new_instance['columns'];
        $instance['show_title'] = isset( $new_instance['show_title'] ) ? '1' : '0';

        // Filter to hook into
        $instance = apply_filters( 'sportspress_widget_update', $instance, $new_instance, $old_instance, 'live-event-list' );

        return $instance;
    }

    /**
     * The form on the admin dashboard
     * @param  [type] $instance [description]
     * @return [type]           [description]
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'id' => '' ) );
        $title = strip_tags($instance['title']);
        $show_title = isset( $instance['show_title'] ) ? (bool) $instance['show_title'] : false;
        $id = intval($instance['id']);
        $columns = $instance['columns'];

        $time_format = get_option( 'sportspress_event_list_time_format', 'combined' );

        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'sportspress' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
        
        <p><label for="<?php echo $this->get_field_id('id'); ?>"><?php printf( __( 'Select %s:', 'sportspress' ), __( 'Calendar', 'sportspress' ) ); ?></label>
        <?php
        $args = array(
            'post_type' => 'sp_calendar',
            'show_option_all' => __( 'All', 'sportspress' ),
            'name' => $this->get_field_name('id'),
            'id' => $this->get_field_id('id'),
            'selected' => $id,
            'values' => 'ID',
            'class' => 'sp-event-calendar-select widefat',
        );
        if ( ! sp_dropdown_pages( $args ) ):
            sp_post_adder( 'sp_calendar', __( 'Add New', 'sportspress' ) );
        endif;
        ?>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('show_title'); ?>"> 
                <input id="<?php echo $this->get_field_id('show_title'); ?>"   name="<?php echo $this->get_field_name('show_title'); ?>" type="checkbox" <?php checked( $show_title ); ?> />
                <?php _e( 'Show Calendar title', 'sportspress' ); ?>
            </label>
        </p>


        <p class="sp-prefs">
            <?php _e( 'Columns:', 'sportspress' ); ?><br>
            <?php
            $the_columns = array();
            $the_columns['event'] = __( 'Event', 'sportspress' );

            if ( 'combined' === $time_format ) {

                $the_columns['time'] = __( 'Time/Results', 'sportspress' );

            } else {

                if ( in_array( $time_format, array( 'time', 'separate' ) ) ) {
                    $the_columns['time'] = __( 'Time', 'sportspress' );
                }

                if ( in_array( $time_format, array( 'results', 'separate' ) ) ) {
                    $the_columns['results'] = __( 'Results', 'sportspress' );
                }
            }

            $the_columns['venue'] = __( 'Venue', 'sportspress' );
            $the_columns['article'] = __( 'Article', 'sportspress' );
            $the_columns['day'] = __( 'Match Day', 'sportspress' );

            $field_name = $this->get_field_name('columns') . '[]';
            $field_id = $this->get_field_id('columns');
            ?>
            <?php foreach ( $the_columns as $key => $label ): ?>
                <label class="button"><input name="<?php echo $field_name; ?>" type="checkbox" id="<?php echo $field_id . '-' . $key; ?>" value="<?php echo $key; ?>" <?php if ( $columns === null || in_array( $key, $columns ) ): ?>checked="checked"<?php endif; ?>><?php echo $label; ?></label>
            <?php endforeach; ?>
        </p>
 
        <?php
        // Action to hook into
        do_action( 'sportspress_after_widget_template_form', $this, $instance, 'live-event-list' );
    }
}

register_widget( 'LSFS_Widget_Event_List' );
