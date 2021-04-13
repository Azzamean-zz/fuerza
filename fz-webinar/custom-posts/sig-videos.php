<?php
namespace Fuerza\PostTypes;

class SigVideos {

	protected static $instance = null;

	const POST_TYPE = 'sig_video';

	public function __construct() {
		add_action( 'init', array( $this, 'on_vc_before_init' ) );
		add_action( 'acf/init', array( $this, 'register_custom_fields' ) );
		// add_action( 'acf/init', array( $this, 'register_top_fields' ));
		add_action( 'acf/init', array( $this, 'register_speaker_fields' ));
		add_filter( 'acf/update_value/key=field_sig_video_date', array( $this, 'prepare_date_field' ), 10, 3 );
	}

	public function on_vc_before_init() {
		$this->register_post_type();
		$this->register_taxonomies();
	}

	public function register_post_type() {
		$labels = [
			'menu_name'     => 'Webinars',
			'name'          => 'Videos',
			'singular_name' => 'Webinar',
			'add_new'       => 'Add New',
			'add_new_item'  => 'Add New Webinar',
			'new_item'      => 'New Webinar',
			'edit_item'     => 'Edit Webinar',
			'view_item'     => 'View Webinar',
            'all_items'     => 'All Webinars',
			'search_items'  => 'Search Webinars',
		];

		$args = [
			'labels'       => $labels,
			'public'       => true,
			'show_in_rest' => false,
			'rewrite'      => array( 'slug' => 'webinars', 'with_front' => true ),
			'menu_icon'    => 'dashicons-video-alt',
			'supports'     => array( 'title', 'editor', 'author', 'thumbnail' ),
		];

		register_post_type( self::POST_TYPE, $args );

	}

	public function register_taxonomies() {
		$args = array(
			'labels' => array(
				'name'          => 'Companies',
				'singular_name' => 'Company',
			),
			'public'             => false,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'show_in_nav_menus'  => true,
			'show_admin_column'  => true,
			'show_in_rest'       => false,
			'hierarchical'       => true,
			'show_in_quick_edit' => true,
		);

		register_taxonomy( 'sig_video_company', [self::POST_TYPE], $args );

		$args['labels'] = [
			'name'          => 'Topics',
			'singular_name' => 'Topic',
		];

		register_taxonomy( 'sig_video_topic', [self::POST_TYPE], $args );

		$args['labels'] = [
			'name'          => 'Projects',
			'singular_name' => 'Project',
		];

		register_taxonomy( 'sig_video_interest_group', [self::POST_TYPE], $args );
	}

	public function prepare_date_field( $value, $post_id, $field ) {
		if ( ! empty( $value ) ) {
			$date  = \DateTime::createFromFormat( 'Ymd', $value );
			$value = $date->format( 'Y-m-d' );
		}

		return $value;
	}

	public function register_custom_fields() {
		acf_add_local_field_group([
			'key'        => 'sig_video_fields',
			'title'      => 'Custom Fields',
			'position'   => 'side',
			'style'      => 'default',
			'fields'     => array(),
			'location'   => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => self::POST_TYPE,
					),
				),
			),
		]
		
	);

		$this->get_fields( 'sig_video_fields' );
	}

	// public function register_top_fields() {
	// 	acf_add_local_field_group([
	// 		'key'        => 'sig_video_fields_top',
	// 		'title'      => 'Banner',
	// 		'position' => 'acf_after_title',
	// 		'style'      => 'default',
	// 		'fields'     => array(),
	// 		'location'   => array(
	// 			array(
	// 				array(
	// 					'param'    => 'post_type',
	// 					'operator' => '==',
	// 					'value'    => self::POST_TYPE,
	// 				),
	// 			),
	// 		),
	// 	]
		
	// );

	// 	$this->get_fields_top( 'sig_video_fields_top' );
	// }

	public function register_speaker_fields() {
		acf_add_local_field_group([
			'key'        => 'sig_video_fields_speaker',
			'title'      => 'Speakers',
			'position' => 'acf_after_title',
			'style'      => 'default',
			'fields'     => array(),
			'location'   => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => self::POST_TYPE,
					),
				),
			),
		]
		
	);

		$this->get_fields_speaker( 'sig_video_fields_speaker' );
	}

	private function get_fields( $group_name ) {
		acf_add_local_field([
			'key'            => 'field_sig_video_date',
			'label'          => 'Date',
			'name'           => 'sig_video_date',
			'type'           => 'date_picker',
			'required'       => true,
			'parent'         => $group_name,
			'display_format' => 'Y-m-d',
			'return_format'  => 'Y-m-d',
			'first_day'      => 1
		]);

		acf_add_local_field([
			'key'      => 'field_sig_video_time',
			'label'    => 'Time',
			'required' => true,
			'name'     => 'sig_video_time',
			'type'     => 'text',
			'parent'   => $group_name,
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_registration',
			'label'  => 'Registration',
			'name'   => 'sig_video_registration',
			'type'   => 'text',
			'parent' => $group_name,
		]);

		acf_add_local_field([
			'key'           => 'field_sig_video_recording',
			'label'         => 'Recording',
			'name'          => 'sig_video_recording',
			'type'          => 'file',
			'parent'        => $group_name,
			'return_format' => 'array',
			'library'       => 'all',
			'max_size'      => 100,
			'mime_types'    => 'mp4'
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_url',
			'label'  => 'Wiki Video Url',
			'name'   => 'sig_video_url',
			'type'   => 'text',
			'parent' => $group_name,
			'instructions' => 'This will replace Recording if it exists',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_youtube',
			'label'  => 'Youtube Video Url',
			'name'   => 'sig_video_youtube',
			'type'   => 'oembed',
			'parent' => $group_name,
			'instructions' => 'This will replace Recording and Wiki Video Url if they exist',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_slides',
			'label'  => 'Slides',
			'name'   => 'sig_video_slides',
			'type'   => 'text',
			'parent' => $group_name,
		]);
	}
	// private function get_fields_top( $group_name_top ) {
	// 	acf_add_local_field([
	// 		'key'    => 'field_sig_video_banner_img',
	// 		'label'  => 'Banner Image',
	// 		'name'   => 'sig_video_banner_img',
	// 		'type'   => 'image',
	// 		'preview_size' => 'medium',
	// 		'parent' => $group_name_top,
	// 	]);
	// 	acf_add_local_field([
	// 		'key'    => 'field_sig_video_banner_color',
	// 		'label'  => 'Background Color',
	// 		'name'   => 'sig_video_banner_color',
	// 		'type'   => 'color_picker',
	// 		'parent' => $group_name_top,
	// 	]);
	// 	acf_add_local_field([
	// 		'key'    => 'field_sig_video_banner_text',
	// 		'label'  => 'Banner Text',
	// 		'name'   => 'sig_video_banner_text',
	// 		'type'   => 'textarea',
	// 		'parent' => $group_name_top,
	// 	]);
	// }
	private function get_fields_speaker( $group_name_speaker ) {
		acf_add_local_field([
			'key'    => 'field_sig_video_company_logo',
			'label'  => 'Sponsor Logo',
			'name'   => 'sig_video_company_logo',
			'type'   => 'image',
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'parent' => $group_name_speaker,
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers',
			'label'  => 'Speakers',
			'name'   => 'sig_video_speakers_repeater',
			'type'   => 'repeater',
			'layout' => 'block',
			'button_label' => 'Add Speaker',
			'parent' => $group_name_speaker,
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers_picture',
			'label'  => 'Speaker\'s Picture',
			'name'   => 'sig_video_speakers_picture',
			'type'   => 'image',
			'return_format' => 'url',
			'preview_size' => 'thumbnail',
			'parent' => 'field_sig_video_speakers',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers_name',
			'label'  => 'Speaker\'s Name',
			'name'   => 'sig_video_speakers_name',
			'type'   => 'text',
			'parent' => 'field_sig_video_speakers',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers_title',
			'label'  => 'Speaker\'s Title',
			'name'   => 'sig_video_speakers_title',
			'type'   => 'text',
			'parent' => 'field_sig_video_speakers',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers_company',
			'label'  => 'Speaker\'s Company',
			'name'   => 'sig_video_speakers_company',
			'type'   => 'text',
			'parent' => 'field_sig_video_speakers',
		]);

		acf_add_local_field([
			'key'    => 'field_sig_video_speakers_description',
			'label'  => 'Speaker\'s Description',
			'name'   => 'sig_video_speakers_description',
			'type'   => 'wysiwyg',
			'tabs' => 'visual',
			'toolbar' => 'basic',
					'media_upload' => 0,
			'parent' => 'field_sig_video_speakers',
		]);
	}

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

SigVideos::get_instance();
