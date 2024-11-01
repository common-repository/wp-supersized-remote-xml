<?php
if ($arrOptions['navigation'] && $arrOptions['slideshow'] >= 3): // only for old flickr look (also for Picasa and Smugmug mode)
?>
    <div id="prevthumb"></div>
    <div id="nextthumb"></div>
    <div id="controls-wrapper">
        <div id="controls">
        <?php if ($arrOptions['slide_counter']): ?>
            <div id="slidecounter">
                <span class="slidenumber"></span>/<span class="totalslides"></span>
            </div>
        <?php endif; ?>
            <div id="slidecaption"></div>
        <?php if ($arrOptions['navigation_controls']): ?>
            <div id="navigation">
                <img id="prevslide" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-supersized/flickr_img/back_dull.png"/><img id="pauseplay" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-supersized/flickr_img/pause_dull.png"/><img id="nextslide" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-supersized/flickr_img/forward_dull.png"/>
            </div>
            <?php endif; ?>
        </div>
    </div>

<?php else:
    global $totalSlides;
    if ($arrOptions['navigation'] && $arrOptions['slideshow'] == '1' && $totalSlides > 1):
?>
<?php
    endif;

    if ($arrOptions['navigation_controls'] && $arrOptions['slideshow'] == '1' && $totalSlides > 1):
?>
        <!-- Arrow Navigation -->
        <a id="prevslide" class="load-item"></a>
        <a id="nextslide" class="load-item"></a>
        <?php
    endif;
        if ($arrOptions['navigation'] && $arrOptions['slideshow'] == '1' && $totalSlides > 1):
?>

        <div id="thumb-tray" class="load-item">
            <div id="thumb-back"></div>
            <div id="thumb-forward"></div>
        </div>
<?php if ($arrOptions['progress_bar'] == 1): ?>
        <!--Time Bar-->
        <div id="progress-back" class="load-item">
            <div id="progress-bar"></div>
        </div>
<?php endif; ?>
        <!--Control Bar-->
        <div id="controls-wrapper" class="load-item">
            <div id="controls">
                <a id="play-button"><img id="pauseplay" src="<?php echo get_bloginfo('wpurl'); ?>/wp-content/plugins/wp-supersized/img/pause.png"/></a>

                <?php if ($arrOptions['slide_counter']):
                ?>
                <!--Slide counter-->
                <div id="slidecounter">
                        <span class="slidenumber"></span>/<span class="totalslides"></span>
                </div>
                <?php
                    endif;
                endif;

                if ($arrOptions['slide_captions'] && !$arrOptions['navigation'] && $arrOptions['slideshow'] == '1'):
                ?>
                    <div id="controls-wrapper" style="background: none;">
                        <div id="slidecaption"></div>
                    </div>
                <?php elseif ($arrOptions['slide_captions'] && $arrOptions['navigation'] && $arrOptions['slideshow'] == '1'): ?>
                <div id="slidecaption"></div>
                <?php endif; ?>

                <?php if ($arrOptions['navigation'] && $arrOptions['slideshow'] == '1' && $totalSlides > 1): ?>
                    <?php if ($arrOptions['thumb_tray']): ?>
                        <a id="tray-button">
                            <img style="height:42px;" id="tray-arrow" src="<?php echo get_bloginfo('wpurl').'/wp-content/plugins/wp-supersized/img/button-tray-' . ($arrOptions['tray_visible'] ? 'down' : 'up'); ?>.png"/>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
<?php endif; ?>
