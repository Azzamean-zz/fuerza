<?php

namespace Fuerza\Helpers;

defined( 'ABSPATH' ) || exit;

class Utils {

	public static function request( $type, $name, $default, $sanitize = 'rm_tags' ) {
		$request = filter_input_array( $type, FILTER_SANITIZE_SPECIAL_CHARS );

		if ( ! isset( $request[ $name ] ) || empty( $request[ $name ] ) ) {
			return $default;
		}

		return self::sanitize( $request[ $name ], $sanitize );
	}

	public static function post( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_POST, $name, $default, $sanitize );
	}

	public static function get( $name, $default = '', $sanitize = 'rm_tags' ) {
		return self::request( INPUT_GET, $name, $default, $sanitize );
	}

	public static function sanitize( $value, $sanitize ) {
		if ( ! is_callable( $sanitize ) ) {
			return ( false === $sanitize ) ? $value : self::rm_tags( $value );
		}

		if ( is_array( $value ) ) {
			return array_map( $sanitize, $value );
		}

		return call_user_func( $sanitize, $value );
	}

	public static function rm_tags( $value, $remove_breaks = false ) {
		if ( empty( $value ) || is_object( $value ) ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return array_map( __METHOD__, $value );
		}

		return wp_strip_all_tags( $value, $remove_breaks );
	}

	public static function is_request_ajax() {
		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			$request_ajax = $_SERVER['HTTP_X_REQUESTED_WITH'];
		}

		return ( ! empty( $request_ajax ) && strtolower( $request_ajax ) == 'xmlhttprequest' );
	}

	public static function get_template( $file, $args = [] ) {
		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		$locale = WFZ_WEBINAR_DIR . "public/template-parts/" . $file . '.php';

		if ( ! file_exists( $locale ) ) {
			return;
		}

		include $locale;
	}

	public static function get_child_terms( $terms, $parent ) {
		$children = [];

		foreach ( $terms as $term ) {
			if ( $term->parent != 0 ) {
				$children[ $term->parent ][] = $term;
			}
		}

		return isset( $children[ $parent ] ) ? $children[ $parent ] : [];
	}

	public static function get_parent_terms( $terms ) {
		$parents = [];

		foreach ( $terms as $term ) {
			if ( $term->parent == 0 ) {
				$parents[] = $term;
			}
		}

		return $parents;
	}

	public static function get_uri( $path ) {
		return plugins_url( $path, WFZ_WEBINAR_PLUGIN_FILE );
	}

	public static function get_path( $filename ) {
		return plugin_dir_path( WFZ_WEBINAR_PLUGIN_FILE ) . $filename;
	}

	public static function filemtime( $filename ) {
		$file = self::get_path( 'public/' . $filename );

		return file_exists( $file ) ? filemtime( $file ) : '';
	}
}
