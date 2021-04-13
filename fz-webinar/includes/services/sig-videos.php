<?php

namespace Fuerza\Services;

use Fuerza\Helpers\Utils;
use WP_REST_Request;
use DateTime;

class SigVideos {

	protected static $instance = null;

	protected static $filters = [
		'_topic',
		'_interest_group',
		'_company',
	];

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route(
			'fuerza/v1',
			'sig-videos',
			[
				'methods'  => 'GET',
				'callback' => array( $this, 'get_rest_data' )
			]
		);
	}

	public function get_rest_data( WP_REST_Request $request, $echo = true ) {
		$per_page         = -1;
		$paged            = $request->get_param( 'page' );
		$paged            = empty( $paged ) ? 1 : intval( $paged );
		$clicked          = $request->get_param( 'clicked' );
		$is_mobile        = $request->get_param( 'is_mobile' );
        $local = (string)filter_input(INPUT_GET,'local');
		$_company         = $request->get_param( empty($local)?'_company':$local.'__company' );
		$_topic           = $request->get_param(  empty($local)?'_topic':$local.'__topic');
		$_interest_group  = $request->get_param( empty($local)?'_interest_group':$local.'__interest_group' );
		$is_load_more     = $request->get_param( 'is_load_more' );
		$form_args        = compact( '_company', '_topic', '_interest_group' );
		$current_filters  = [];
		$filters          = false;
		$load_more_button = false;

		foreach ( $form_args as $filter_name => $value ) {
			if ( ! empty( $value ) ) {
			    if(!empty($local)){
			        $filter_name = str_replace($local.'_','',$filter_name);
                }
				$current_filters[ $filter_name ] = $value;
			}
		}
		$query = array(
            'relation' => 'AND',
            array(
                'relation' => 'OR',
                array(
                    'key'     => 'sig_video_recording',
                    'value' => false,
                    'compare' => '!='
                ),
                array(
                    'key' => 'sig_video_url',
                    'value' => false,
                    'compare' => '!='
                ),
                array(
                    'key'     => 'sig_video_youtube',
                    'value'   => false,
                    'compare' => '!='
                ),
            ),
            array(
                'key'     => 'sig_video_date',
                'value'   => date_i18n( 'Y-m-d' ),
                'compare' => '<=',
            ),
        );
		if($local == 'upcomming'){
            $query =  array(
                'relation' => 'AND',
                array(
                    'relation' => 'OR',
                    array(
                        'key'   => 'sig_video_recording',
                        'value' => '',
                    ),
                    array(
                        'key'   => 'sig_video_url',
                        'value' => '',
                    )
                ),
                array(
                    'key'     => 'sig_video_date',
                    'value'   => date_i18n( 'Y-m-d' ),
                    'compare' => '>=',
                )
            );
        }

		$args = [
			'paged'              => $paged,
			'posts_per_page'     => $per_page,
			'ignore_custom_sort' => true,
			'post_type'          => \Fuerza\PostTypes\SigVideos::POST_TYPE,
			'post_status'        => 'publish',
			'meta_key'           => 'sig_video_date',
			'order'              => 'DESC',
			'orderby'            => 'meta_value',
			'meta_query'         => $query
		];

		if ( intval( $is_mobile ) === 1 ) {
			unset( $args['paged'] );
			$args['posts_per_page'] = -1;
		}

		$query_args       = $this->build_query_args( $form_args, $args );

		$query            = new \WP_Query( $query_args );
		$search_fields    = self::get_filter_keys();
		$taxonomy_options = $this->get_taxonomies_options( $query_args, $current_filters, $clicked );
		$has_filter       = ! empty( $current_filters );
		if ( ! $is_load_more ) {
			// Filters HTML
			ob_start();

			Utils::get_template(
				'sig-videos/filters',
				compact( 'current_filters', 'search_fields', 'taxonomy_options' )
			);

			$filters = ob_get_contents();

			ob_end_clean();
		}

		// Content HTML
		ob_start();

		while ( $query->have_posts() ) :
			$query->the_post();
			Utils::get_template( 'sig-videos/webinar-card' );
		endwhile;
		wp_reset_postdata();

		$content = ob_get_contents();

		ob_end_clean();

		// Load more HTML
		ob_start();

		if ( $query->max_num_pages > $paged && intval( $is_mobile ) === 0 ) :

		?>
		<button class="button-transparent" data-component="load-more"
			data-ajax-url="<?php echo rest_url( '/fuerza/v1/sig-videos' ); ?>"
			data-wrapper=".webinars-wrapper"
			data-paged="<?php echo $paged; ?>"
			data-max="<?php echo $query->max_num_pages; ?>">
			REVEAL MORE
		</button>
		<?php

		endif;

		$load_more_button = ob_get_contents();

		ob_end_clean();

		$has_filter       = ! empty( $current_filters );
		$results_quantity = $has_filter ? $query->found_posts . ' of' : '';
		$found_total      = $query->found_posts;

		wp_send_json_success( compact( 'results_quantity', 'filters', 'content', 'load_more_button', 'found_total' ) );

		die();
	}

	public function get_taxonomies_options( $args, $current_filters, $clicked = false ) {
		unset( $args['paged'] );

		$defaults = [
			'posts_per_page' => -1,
			'fields'         => 'ids'
		];

		$params       = wp_parse_args( $defaults, $args );
		$last_clicked = $clicked ? $clicked : Utils::get( '_lc', false );

		if ( isset( $params['tax_query'] ) && ! empty( $params['tax_query'] ) && $last_clicked ) {
			foreach ( $params['tax_query'] as $key => $tax_item ) {
				if ( $tax_item['taxonomy'] == $this->get_taxonomy_by_filter( $last_clicked ) ) {
					unset( $params['tax_query'][ $key ] );
				}
			}
		}

		$post_ids = get_posts( $params );
		$data     = [];

		foreach ( self::get_filter_keys() as $filter_key ) {
			$taxonomy = $this->get_taxonomy_by_filter( $filter_key );

			if ( $last_clicked && $last_clicked != $filter_key ) {
				$tax_args = $this->handle_taxonomy_args( $params, $taxonomy, $last_clicked, $current_filters );
				$terms    = $this->prepare_term( get_posts( $tax_args ), $taxonomy );
			} else {
				$terms = $this->prepare_term( $post_ids, $taxonomy );
			}

			$data[ $filter_key ] = $terms;
		}

		return $data;
	}

	public function handle_taxonomy_args( $params, $taxonomy, $last_clicked, $current_filters ) {
		$index = array_search( $taxonomy, array_column( $params['tax_query'], 'taxonomy') );

		if ( $index !== false) {
			unset( $params['tax_query'][ $index ] );
		}

		$value = isset( $current_filters[ $last_clicked ] ) ? $current_filters[ $last_clicked ] : false;

		if ( $last_clicked && ! empty( $value ) ) {
			$params['tax_query'][] = array(
				'taxonomy' => $this->get_taxonomy_by_filter( $last_clicked ),
				'field'    => 'slug',
				'terms'    => $value
			);
		}

		return $params;
	}

	public function prepare_term( $post_ids, $taxonomy, $terms = [] ) {
		if ( empty( $post_ids ) ) {
			return false;
		}

		if ( empty( $terms ) ) {
			$terms = wp_get_object_terms( $post_ids, $taxonomy );
		}

		return array_map( function( $term ) use ( $post_ids, $taxonomy ) {
			$count = 0;
			foreach ( $post_ids as $post_id ) {
				if ( has_term( $term->term_id, $taxonomy, $post_id ) ) {
					$count++;
				}
			}
			if ( $count ) {
				$term->count = $count;
			}
			return $term;
		}, $terms );
	}

	public function build_query_args( $filters, $defaults ) {
		$args  = array(
			'tax_query'  => [],
		);
		foreach ( $filters as $filter_key => $filter_value ) {
			$taxonomy = $this->get_taxonomy_by_filter( $filter_key );


			if ( ! $taxonomy ) {
				continue;
			}
			if ( empty( $filter_value ) ) {
				continue;
			}

			$args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $filter_value
			);
		}

		return wp_parse_args( $args, $defaults );
	}

	public function get_taxonomy_by_filter( $filter_name ) {
		$taxonomies = [
			'_company'        => 'sig_video_company',
			'_topic'          => 'sig_video_topic',
			'_interest_group' => 'sig_video_interest_group',
		];

		return isset( $taxonomies[ $filter_name ] ) ? $taxonomies[ $filter_name ] : false;
	}

	public static function get_filter_keys() {
		return self::$filters;
	}

	public static function get_total()
	{
		$transient_name = 'fuerza_total_sig_videos';
		$transient      = get_transient( $transient_name );

		if ( empty( $transient ) ) {
			$ids = get_posts([
				'post_type'      => \Fuerza\PostTypes\SigVideos::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => 'sig_video_date',
						'value'   => date_i18n( 'Y-m-d' ),
						'compare' => '<',
					  )
				)
			]);
			$count = count( $ids );
			set_transient( $transient_name, $count, HOUR_IN_SECONDS );
			return $count;
		}

		return $transient;
	}

	public static function display_time( $date, $time ) {
		if ( empty( $time ) || ! ( $date instanceof DateTime ) ) {
			return;
		}

		$suffix      = trim( substr( $time, -3 ) );
		$time        = $time . ' (UTC';
		$is_daylight = $date->format( 'I' ) == 1;
		$hours       = ! $is_daylight ? '8' : '7';
		$hours       = $suffix == 'PDT' ? 7 : $hours;
		$signal      = $suffix == 'CST' ? '+' : '-';
		$time       .= "{$signal}{$hours})";

		return $time;
	}

	public static function get_companies_text( $companies ) {
		$text          = '';
		$company_count = count( $companies );

		foreach ( $companies as $key => $company_name ) :
			if ( $company_count == 1 ) :
				$text .= $company_name;
			endif;

			if ( $company_count == 2 ) :
				$text .= $key == 0 ? $company_name . ' and ' : $company_name;
			endif;

			if ( $company_count > 2 ) :
				$real_key = $key + 1;
				if ( $real_key == $company_count ) :
					$text = rtrim( $text, ', ' );
				endif;
				$text .= $real_key < $company_count ? $company_name . ', ' : ' and ' . $company_name;
			endif;
		endforeach;

		return $text;
	}

	public static function get_recording_url( $post_id ) {
		$youtube = get_field( 'sig_video_youtube', $post_id );

		if ( ! empty( $youtube ) ) {

			return $youtube;

		} else {
			$uploaded = get_field( 'sig_video_recording', $post_id );
			$external = get_field( 'sig_video_url', $post_id );

			if ( ! empty( $external ) ) {
				$video_src = $external;
			}

			if ( ! empty( $uploaded ) ) {
				$video_src = $uploaded['url'];
			}

			if ( empty( $video_src ) ) {
				return false;
			}

			$video = wp_video_shortcode([
				'mp4'     => preg_replace( '/\?.*/', '', $external ),
				'preload' => 'auto',
				'poster'  => get_the_post_thumbnail_url( $post_id,'large' )
			]);

			return $video;
		}
	}

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}

SigVideos::get_instance();
