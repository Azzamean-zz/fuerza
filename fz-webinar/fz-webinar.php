<?php
/**
 * Plugin Name: Linux Foundation Fuerza Webinars
 * Plugin URI:  https://www.linuxfoundation.org/
 * Description: Webinar
 * Version:     5.8.3
 * Author:      Andrew Bringaze
 * Author URI:  https://www.linuxfoundation.org/
 */
defined('ABSPATH') || exit;

use Fuerza\PostTypes\SigVideos;
use fzwebinar\Helpers\Filters;
use fzwebinar\Helpers\Url;

define('WFZ_WEBINAR_DIR', __DIR__ . '/');
define('WFZ_WEBINAR_PLUGIN_FILE', __FILE__);

include_once __DIR__ . '/loader.php';

Filters::loadFilters();;

function fzWebinarPostTypes(){
    $postType = SigVideos::get_instance();

    $postType->register_custom_fields();
    $postType->on_vc_before_init();
    $postType->register_taxonomies();

    register_widget(Widget_Upcoming_Videos::class);
}
 
function fzWebinarShortCode() {
    ob_start();

    include_once WFZ_WEBINAR_DIR . 'public/page-templates/sig-videos.php';

    return ob_get_clean();
}

function fzWebinarCreateSinglePostPage(){
    $theme   = get_template_directory();
    $file    = WFZ_WEBINAR_DIR.'public/single-sig_video.php';
    $destino = $theme.'/single-sig_video.php';

    copy($file,$destino);
}
function fzWebinarRemoveSinglePostPage() {
    $theme   = get_template_directory();
    $destino = $theme.'/single-sig_video.php';

    unlink($destino);
}

add_shortcode('fz-webinar','fzWebinarShortCode');
add_action('switch_theme', 'fzWebinarCreateSinglePostPage');
add_action('init', 'fzWebinarPostTypes');
add_action('switch_theme', 'fzWebinarCreateSinglePostPage');

register_activation_hook(__FILE__,'fzWebinarCreateSinglePostPage');
register_deactivation_hook(__FILE__,'fzWebinarRemoveSinglePostPage');

function fzcustomcss() {
    add_submenu_page(
        'edit.php?post_type=sig_video',
        'Settings',
        'Settings',
        'administrator',
        'fz_webinar_custom_css',
        'fzcustomcssAction'
    );

}
add_action('admin_menu', 'fzcustomcss', 100);

function fzcustomcssAction() {
    $url = Url::getCurrentURl();

    Filters::loadFilters();

    $filters = Filters::getFilters();

    if (isset($_POST['css'])) {
        $filters->topics = (bool)(int)($_POST['topic']??'0');
        $filters->projects = (bool)(int)($_POST['projects']??'0');
        $filters->companies = (bool)(int)($_POST['companies']??'0');

        Filters::writeFilters($filters);
        Filters::loadFilters();

        $filters = Filters::getFilters();

        update_option('fz_webinar_custom_css',$_POST['css']);
        update_option('fz_webinar_link_allvideos',$_POST['link_allvideos']);
        update_option('fz_image_banner',$_POST['fz_image_banner']);
        update_option('fz_webinar_background',$_POST['fz_webinar_background']);
        update_option('fz_webinar_banner_text',$_POST['fz_webinar_banner_text']);

    }

    $content       = get_option('fz_webinar_custom_css');
    $linkAllVideos = (string)get_option('fz_webinar_link_allvideos');
    $linkAllVideos = empty($linkAllVideos)?'/webinars':$linkAllVideos;
    $imageBanner = get_option('fz_image_banner');
    $background = get_option('fz_webinar_background');
    $bannerText = get_option('fz_webinar_banner_text');
    $background = !$background?'#ffffff':$background;
    ?>
    <form action="<?php echo esc_url($url); ?>" method="post">
        <h1>Settings</h1><br>
        <hr>
        <h2>Filters</h2>
        <div style="display: flex;">
            <div>Topics: <input <?=$filters->topics?'checked':''?> type="checkbox" value="1" name="topic"></div>
            <div>Projects: <input <?=$filters->projects?'checked':''?>  type="checkbox" value="1" name="projects"></div>
            <div>Companies: <input <?=$filters->companies?'checked':''?> type="checkbox" value="1" name="companies"></div>
        </div>
        <hr>
        <h2>Link to All Videos</h2>
        <input style="width: 80%;" name="link_allvideos" value="<?=$linkAllVideos ?>">
        <br><br>
        <h2>Banner Image</h2>
        <?=getButtonUploadMedia('Set Banner Image','fz_image_banner',$imageBanner); ?>
        <br>
        <h2>Banner Background Color</h2>
        <input type="color" name="fz_webinar_background" value="<?=$background ?>">
        <br>
        <h2>Banner Text</h2>
        <textarea style="width: 80%;height:150px" name="fz_webinar_banner_text"><?=$bannerText ?></textarea>
        <br>
        <h2>Custom CSS</h2>
        <textarea style="width: 80%; height: 400px;" name="css"><?=$content?></textarea>
        <br>
        <button type="submit" class="button">Save</button>
    </form>
    <?php
    echo getScriptsButtonUploadMedia();
}
