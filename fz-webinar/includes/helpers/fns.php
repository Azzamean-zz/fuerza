<?php
function getButtonUploadMedia($title,$name,$image)
{
$img = '';
	if(!empty($image) and !is_null($image)){
        $img = "<img src='$image' width='120' style='display: block; margin-top: 10px'>";
    }
	return '<p>
                        <input type="hidden" class="url-image" value="'.$image.'" name="'.$name.'" max="" min="1" step="1">
                        <button class="set_custom_images button">'.$title.'</button>
                   		'.$img.'
                    </p>';

}
function getScriptsButtonUploadMedia(){
    wp_enqueue_media();
    return "<script>
        jQuery(document).ready(function() {
            if (jQuery('.set_custom_images').length > 0) {
                if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                    jQuery('.set_custom_images').on('click', function(e) {
                        e.preventDefault();
                        var button = jQuery(this);
                        var parent = jQuery(this).parent();
                        var url = parent.find('.url-image');
                        wp.media.editor.send.attachment = function(props, attachment) {
                            url.val(attachment.url);
                            parent.find('img').remove();
                            var img = document.createElement('img');
                            jQuery(img).attr('src',attachment.url).width('120px').css({
                                display:'block',
                                'margin-top':'10px'
                            });
                            parent.append(jQuery(img));
                        };
                        wp.media.editor.open(button);
                        return false;
                    });
                }
            }
        });
</script>";
}
