<?php

namespace Fuerza\Services;

class Widgets {

	protected static $instance = null;

	private function __construct() {
		add_action( 'widgets_init', array( $this, 'on_widgets_init' ) );
	}

	public function on_widgets_init() {
		$this->add_sidebars();
		$this->register_widgets();
	}

	public function add_sidebars() {
		register_sidebar([
			'id'            => 'video-library-sidebar',
			'name'          => 'Video Library Sidebar',
			'before_widget' => '<div class = "widget %2$s">',
			'after_widget'  => '</div>',
		]);
	}

	public function register_widgets() {
		register_widget( 'Widget_Upcoming_Videos' );
	}

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

Widgets::get_instance();
