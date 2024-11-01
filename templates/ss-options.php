<script type="text/javascript">
jQuery(document).ready(function($) {
    $.supersized({
    <?php if ($arrOptions['slideshow'] !== 2):
    ?>

        slideshow               : 1,
        autoplay		: <?php echo $arrOptions['autoplay']; ?>,
        random			: <?php echo $arrOptions['random']; ?>,
        slide_interval          : <?php echo $arrOptions['slide_interval']; ?>,
        transition              : <?php echo $arrOptions['transition']; ?>,
        transition_speed	: <?php echo $arrOptions['transition_speed']; ?>,
        new_window		: <?php echo $arrOptions['new_window']; ?>,
        pause_hover             : <?php echo $arrOptions['pause_hover']; ?>,
        stop_loop               : <?php echo $arrOptions['stop_loop']; ?>,
        keyboard_nav            : <?php echo $arrOptions['keyboard_nav']; ?>,
        performance		: <?php echo $arrOptions['performance']; ?>,
        thumbnail_navigation    : <?php echo $arrOptions['thumbnail_navigation']; ?>,
        thumb_links             : <?php echo $arrOptions['thumb_links']; ?>,
        slide_counter           : <?php echo $arrOptions['slide_counter']; ?>,
        slide_captions          : <?php echo $arrOptions['slide_captions']; ?>,
        progress_bar            : <?php echo $arrOptions['progress_bar']; ?>,
    <?php endif; ?>

        start_slide             : <?php echo $arrOptions['start_slide']; ?>,
        image_protect		: <?php echo $arrOptions['image_protect']; ?>,
        image_path		: '<?php echo content_url()?>/plugins/wp-supersized/<?php if($arrOptions['slideshow'] >= '3') echo 'flickr_';?>img/',
        min_width		: <?php echo $arrOptions['min_width']; ?>,
        min_height		: <?php echo $arrOptions['min_height']; ?>,
        vertical_center         : <?php echo $arrOptions['vertical_center']; ?>,
        horizontal_center       : <?php echo $arrOptions['horizontal_center']; ?>,
        fit_always         	: <?php echo $arrOptions['fit_always']; ?>,
        fit_portrait         	: <?php echo $arrOptions['fit_portrait']; ?>,
        fit_landscape		: <?php echo $arrOptions['fit_landscape']; ?>,
    <?php if ($arrOptions['slideshow'] == 3): ?>
        source                  : <?php echo $arrOptions['flickr_source']; ?>,
        set                     : '<?php echo $arrOptions['flickr_set']; ?>',
        user                    : '<?php echo $arrOptions['flickr_user']; ?>',
        group                   : '<?php echo $arrOptions['flickr_group']; ?>',
        tags                    : '<?php echo $arrOptions['flickr_tags']; ?>',
        total_slides            : <?php echo $arrOptions['flickr_total_slides']; ?>,
        image_size              : '<?php echo $arrOptions['flickr_size']; ?>',
        sort_by                 : <?php echo $arrOptions['flickr_sort_by']; ?>,
        sort_direction          : <?php echo $arrOptions['flickr_sort_direction']; ?>,
        api_key                 : '<?php echo $arrOptions['flickr_api_key']; ?>'
    <?php endif; ?>

    <?php if ($arrOptions['slideshow'] == 4): ?>
        source                  : <?php echo $arrOptions['picasa_source']; ?>,
        album                   : '<?php echo $arrOptions['picasa_album']; ?>',
        user                    : '<?php echo $arrOptions['picasa_user']; ?>',
        tags                    : '<?php echo $arrOptions['picasa_tags']; ?>',
        total_slides            : <?php echo $arrOptions['picasa_total_slides']; ?>,
        image_size              : '<?php echo $arrOptions['picasa_image_size']; ?>',
        sort_by                 : <?php echo $arrOptions['picasa_sort_by']; ?>,
        sort_direction          : <?php echo $arrOptions['picasa_sort_direction']; ?>,
        auth_key                : '<?php echo $arrOptions['picasa_auth_key']; ?>'
    <?php endif; ?>

    <?php if ($arrOptions['slideshow'] == 5): ?>
        source                  : <?php echo $arrOptions['smugmug_source']; ?>,
        keyword                 : '<?php echo $arrOptions['smugmug_keyword']; ?>',
        user                    : '<?php echo $arrOptions['smugmug_user']; ?>',
        gallery                 : '<?php echo $arrOptions['smugmug_gallery']; ?>',
        category                : '<?php echo $arrOptions['smugmug_category']; ?>',
        total_slides            : <?php echo $arrOptions['smugmug_total_slides']; ?>,
        image_size              : '<?php echo $arrOptions['smugmug_image_size']; ?>',
        sort_by                 : <?php echo $arrOptions['smugmug_sort_by']; ?>,
        sort_direction          : <?php echo $arrOptions['smugmug_sort_direction']; ?>,
    <?php endif; ?>
    <?php if ($arrOptions['slideshow'] < 3): ?>

        slides                  :  [<?php echo self::getSlides(); //outputs the list of slides in the correct format
    ?>],
        slide_links             : '<?php echo $arrOptions['slide_links']; ?>',
        progress_bar		: <?php echo $arrOptions['progress_bar']; ?>,
        mouse_scrub		: <?php echo $arrOptions['mouse_scrub']; ?>
    <?php endif; ?>
    });
});
</script>