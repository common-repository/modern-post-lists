<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function modern_post_lists_shortcode_handler($atts)
{
    $atts = shortcode_atts(
        array(
            'id' => 0
        ),
        $atts
    );

    $shortcodes = get_option('modern_post_lists', []);

    if (isset($shortcodes[$atts['id']])) {
        $shortcode_data = $shortcodes[$atts['id']];
        // Burada $shortcode_data'yı kullanarak istediğiniz çıktıyı oluşturabilirsiniz
        if ($shortcode_data['layout_type'] == 'Blog Posts') {
            require_once plugin_dir_path(__FILE__) . 'plugin/blog-posts/index.php';
            $output = new modern_post_lists_BlogPost($atts['id']);
            $output = $output->render();
        } elseif ($shortcode_data['layout_type'] == 'Portfolio') {
            require_once plugin_dir_path(__FILE__) . 'plugin/portfolio/index.php';
            $output = new modern_post_lists_Portfolio($atts['id']);
            $output = $output->render();
        } elseif ($shortcode_data['layout_type'] == 'Full Width Gallery') {
            require_once plugin_dir_path(__FILE__) . 'plugin/full-width-gallery/index.php';
            $output = new modern_post_lists_FullWidthGallery($atts['id']);
            $output = $output->render();
        } elseif ($shortcode_data['layout_type'] == 'Masonary Gallery') {
            require_once plugin_dir_path(__FILE__) . 'plugin/masonary-gallery/index.php';
            $output = new modern_post_lists_MasonaryGallery($atts['id']);
            $output = $output->render();
        }

        return $output;
    }
}

add_shortcode('modern_post_lists', 'modern_post_lists_shortcode_handler');
