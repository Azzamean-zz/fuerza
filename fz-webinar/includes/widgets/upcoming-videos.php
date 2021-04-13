<?php

class Widget_Upcoming_Videos extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'upcoming_videos',
            'Upcoming Videos'
        );
    }

    public function widget( $args, $instance ) {

        echo $args['before_widget'];
        include_once WFZ_WEBINAR_DIR.'public/template-parts/sig-videos/upcoming.php';

        echo $args['after_widget'];
    }
}