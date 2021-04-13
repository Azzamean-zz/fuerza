<?php

namespace FZ\Webinar\Helpers;

use Fuerza\Helpers\Utils;

defined( 'ABSPATH' ) || exit;

class Scripts {

    public static function render_shortcode_scripts($format, $files, $dir) {
        foreach ($files as $file) :
            printf(
                $format,
                Utils::get_uri('public'),
                $dir,
                $file,
                Utils::filemtime($dir . '/' . $file)
            );
        endforeach;
    }

    public static function render_shortcode_css() {
        static $rendered = null;

        if ($rendered) :
            return;
        endif;

        $files = [
            'swiper.min.css',
            'sig-videos.css',
        ];

        self::render_shortcode_scripts(
            '<link rel="stylesheet" href="%s/%s/%s?v=%s">',
            $files,
            'css'
        );
        printf('<style>%s</style>', get_option('fz_webinar_custom_css'));

        $rendered = true;
    }

    public static function render_shortcode_js() {
        static $rendered = null;

        if ($rendered) :
            return;
        endif;

        $files = [
            'swiper.min.js',
            'chosen.jquery.min.js',
            'imagesloaded.pkgd.min.js',
            'url-search-params.js',
            'filters-component.js',
            'load-more.js',
            'sig-videos.js',
        ];

        self::render_shortcode_scripts(
            '<script src="%s/%s/%s?v=%s"></script>',
            $files,
            'js'
        );

        $rendered = true;
    }
}
