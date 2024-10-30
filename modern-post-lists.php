<?php
/*
Plugin Name: Modern Post Lists
Plugin URI: https://mpl.ondokuzon.com
Description: Modern Post Lists is a showcase plugin that enables you to present various types of content in an advanced grid layout. It's suitable for displaying blog entries, multimedia, clients, project portfolios, image collections, and beyond. Plus, it boasts a robust filtering, categorizing, and searching mechanism!
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Version: 1.0.0
Author: Ondokuzon
Author URI: https://ondokuzon.com
*/
if (!defined('ABSPATH')) exit; // Exit if accessed directly
function modern_post_lists_external_link_meta_box()
{
    add_meta_box(
        'external_link_id', // Meta box ID
        'External Link', // Başlık
        'modern_post_lists_external_link_meta_box_callback', // Callback fonksiyonu
        'post', // 'post' türü için
        'normal', // Ekranın yan tarafında
        'low' // Öncelik
    );
}
add_action('add_meta_boxes', 'modern_post_lists_external_link_meta_box');

function modern_post_lists_external_link_meta_box_callback($post)
{
    // nonce oluşturma (güvenlik için)
    wp_nonce_field('save_external_link', 'external_link_nonce');

    // Mevcut dış link değerini alma
    $external_link = get_post_meta($post->ID, '_external_link', true);

    echo '<input type="url" name="external_link" value="' . esc_html($external_link) . '" style="width:100%;" placeholder="https://example.com">';
}


function modern_post_lists_save_external_link($post_id)
{
    // nonce kontrolü
    if (!isset($_POST['external_link_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['external_link_nonce'])), 'save_external_link')) {
        return;
    }

    // Otomatik kaydetme sırasında meta verilerin kaydedilmesini engelleme
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['external_link'])) {
        $external_link_value = esc_html(sanitize_text_field($_POST['external_link']));
        update_post_meta($post_id, '_external_link', $external_link_value);
    }
}
add_action('save_post', 'modern_post_lists_save_external_link');

require_once plugin_dir_path(__FILE__) . 'admin/admin.php';
require_once plugin_dir_path(__FILE__) . 'class_modern_post_lists_shortcode.php';
