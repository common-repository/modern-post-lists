<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
function modern_post_lists_admin_page()
{
    echo '<h1>Modern Post Lists</h1>';

    // Yeni Ekle butonunu oluşturma
    echo '<a href="' . esc_url(admin_url('admin.php?page=modern-post-lists-add-new')) . '" class="button button-primary" style="margin-bottom: 15px;">New</a>';

    // Tabloyu oluşturma
    $shortcodes = get_option('modern_post_lists', []);
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Name</th><th>Shortcode</th><th>Actions</th></tr></thead><tbody>';
    foreach ($shortcodes as $name => $shortcode) {
        echo '<tr>';
        echo '<td>' . esc_html($shortcode['name']) . '</td>';
        echo '<td>[modern_post_lists id=\'' . esc_attr($shortcode['id']) . '\']</td>';
        echo '<td>';
        echo '<button class="copy-shortcode-button button" style="margin-right: 5px;" data-shortcode="[modern_post_lists id=\'' . esc_attr($shortcode['id']) . '\']">Copy Shortcode</button>';
        echo '<a href="' . esc_url(admin_url('admin.php?page=modern-post-lists-add-new&edit=' . urlencode($name))) . '" class="button">Edit</a> ';
        echo '<a href="' . esc_url(admin_url('admin.php?page=modern-post-lists&delete_shortcode=' . urlencode($name))) . '" class="button button-delete">Delete</a>';
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

function modern_post_lists_admin_menu()
{
    add_menu_page(
        'Modern Post Lists', // sayfa başlığı
        'Modern Post Lists', // menü başlığı
        'manage_options', // yetenek (kimlerin bu menüyü görebileceğini kontrol eder)
        'modern-post-lists', // menü slug (URL'deki benzersiz tanımlayıcı)
        'modern_post_lists_admin_page', // yukarıda tanımlanan fonksiyon
        'dashicons-grid-view', // ikon (isteğe bağlı)
        7 // menüdeki pozisyon (isteğe bağlı)
    );

    add_submenu_page('modern-post-lists', 'New Shortcode', 'New', 'manage_options', 'modern-post-lists-add-new', 'modern_post_lists_add_new_page');
}

add_action('admin_menu', 'modern_post_lists_admin_menu');


function modern_post_lists_handle_actions()
{
    if (isset($_GET['delete_shortcode'])) {
        $name_to_delete = sanitize_text_field($_GET['delete_shortcode']);
        $shortcodes = get_option('modern_post_lists', []);
        unset($shortcodes[$name_to_delete]);
        update_option('modern_post_lists', $shortcodes);
        wp_redirect(admin_url('admin.php?page=modern-post-lists'));
        exit;
    }
}

add_action('admin_init', 'modern_post_lists_handle_actions');


// Yeni ekleme formu sayfası
function modern_post_lists_add_new_page()
{
    $shortcodes = get_option('modern_post_lists', []);
    $editing_name = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : false;
    $editing_shortcode = $editing_name && isset($shortcodes[$editing_name]) ? $shortcodes[$editing_name]['shortcode'] : false;
    $layout_type = $editing_name && isset($shortcodes[$editing_name]['layout_type']) ? $shortcodes[$editing_name]['layout_type'] : 'Blog Posts';
    $selected_post_types = $editing_name && isset($shortcodes[$editing_name]['post_type']) ? $shortcodes[$editing_name]['post_type'] : [];
    $selected_categories = $editing_name && isset($shortcodes[$editing_name]['categories']) ? $shortcodes[$editing_name]['categories'] : [];
    $selected_tags = $editing_name && isset($shortcodes[$editing_name]['selected_tags']) ? $shortcodes[$editing_name]['selected_tags'] : [];
    $shortcode_name = $editing_name && isset($shortcodes[$editing_name]['name']) ? $shortcodes[$editing_name]['name'] : '';
    $button_text = $editing_name && isset($shortcodes[$editing_name]['button_text']) ? $shortcodes[$editing_name]['button_text'] : 'Read More';

    echo '<h1>';
    echo $editing_name ? 'Edit Shortcode' : 'New Shortcode';
    echo '</h1>';

    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
    // nonce oluşturma (güvenlik için)
    wp_nonce_field('modern_post_lists_add_shortcode', 'modern_post_lists_nonce');
    echo '<input type="hidden" name="action" value="modern_post_lists_add_shortcode">';
    if ($editing_name) {
        echo '<input type="hidden" name="editing_name" value="' . esc_attr($editing_name) . '">';
    }
    echo '<table class="form-table">';
    echo '<tr><th>Name</th><td><input name="shortcode_name" required value="' . esc_html($shortcode_name) . '"></td></tr>';

    // Layout Type radio butonları
    echo '<tr><th>Layout Type</th><td>';
    $layout_options = ['Blog Posts', 'Full Width Gallery', 'Masonary Gallery', 'Portfolio'];
    foreach ($layout_options as $option) {
        echo '<label>';
        echo '<input type="radio" name="layout_type" value="' . esc_attr($option) . '"';
        if ($editing_name && $layout_type == $option) echo ' checked';
        echo '> ' . esc_html($option) . '</label><br>';
    }
    echo '</td></tr>';

    echo '<tr><th>Source Type</th><td>';
    $post_types = get_post_types(['public' => true], 'objects');
    foreach ($post_types as $post_type_slug => $post_type_option) {
        $count = wp_count_posts($post_type_slug)->publish;
        echo '<label>';
        echo '<input type="checkbox" name="post_type[]" value="' . esc_attr($post_type_option->labels->singular_name) . '"';
        if ($editing_name && in_array(esc_html($post_type_option->labels->singular_name), $selected_post_types)) echo ' checked';
        echo '> ' . esc_html($post_type_option->name) . ' (' . esc_html($count) . ') </label><br>';
    }
    echo '</td></tr>';




    echo '<tr><th>Categories</th><td>';
    // Tüm kategorileri çek
    $categories = get_categories([
        'hide_empty' => false,  // Boş kategorileri de göster
    ]);

    foreach ($categories as $category) {
        echo '<label>';
        echo '<input type="checkbox" name="selected_cats[]" value="' . esc_attr($category->term_id) . '"';
        if ($editing_name && in_array($category->term_id, $selected_categories)) echo ' checked';
        echo '> ' . esc_html($category->name) . ' (' . esc_html($category->count) . ')</label><br>';
    }
    echo '</td></tr>';

    // Etiketleri çek

    $tags = get_terms([
        'taxonomy' => 'post_tag',
        'hide_empty' => false
    ]);

    if ($tags && !is_wp_error($tags)) {
        echo '<tr><th>Tags</th><td>';

        foreach ($tags as $tag) {
            // Seçili etiketleri kontrol et (eğer bir shortcode düzenleniyorsa)
            //$is_tag_selected = isset($editing_shortcode['selected_tags']) && in_array($tag->term_id, $editing_shortcode['selected_tags']);

            $is_tag_selected = $editing_name && in_array($tag->term_id, $selected_tags);

            echo '<label>';
            echo '<input type="checkbox" name="selected_tags[]" value="' . esc_attr($tag->term_id) . '" ' . checked($is_tag_selected, true, false) . '>';
            echo ' ' . esc_html($tag->name) . ' (' . esc_html($tag->count) . ')';
            echo '</label><br>';
        }

        echo '</td></tr>';
    }

    $is_button_visible = isset($shortcodes[$editing_name]['show_button']) ? $shortcodes[$editing_name]['show_button'] : true;

    echo '<tr><th>Show Button</th><td><label>';
    echo '<input type="checkbox" name="show_button" value="1" ' . checked($is_button_visible, true, false) . '>';
    echo ' Show Button';
    echo '</label></td></tr>';

    echo '<tr><th>Button Text</th><td><input name="button_text" placeholder="Read More" required value="' . esc_attr($button_text) . '"></td></tr>';

    $is_filter_visible = isset($shortcodes[$editing_name]['show_filter']) ? $shortcodes[$editing_name]['show_filter'] : true;

    echo '<tr><th>Show Filter</th><td><label>';
    echo '<input type="checkbox" name="show_filter" value="1" ' . checked($is_filter_visible, true, false) . '>';
    echo ' Show Filter';
    echo '</label></td></tr>';

    $is_description_visible = isset($shortcodes[$editing_name]['show_description']) ? $shortcodes[$editing_name]['show_description'] : true;

    echo '<tr><th>Show Description</th><td><label>';
    echo '<input type="checkbox" name="show_description" value="1" ' . checked($is_description_visible, true, false) . '>';
    echo ' Show Description';
    echo '</label></td></tr>';

    $is_search_visible = isset($shortcodes[$editing_name]['show_search']) ? $shortcodes[$editing_name]['show_search'] : true;

    echo '<tr><th>Show Search Bar</th><td><label>';
    echo '<input type="checkbox" name="show_search" value="1" ' . checked($is_search_visible, true, false) . '>';
    echo ' Show Search Bar';
    echo '</label></td></tr>';

    $posts_per_page = isset($shortcodes[$editing_name]['posts_per_page']) ? $shortcodes[$editing_name]['posts_per_page'] : 10;

    echo '<tr><th>Posts Per Page</th><td><label>';
    echo '<input type="number" id="posts_per_page" name="posts_per_page" value="' . esc_attr($posts_per_page) . '" min="1">';
    echo ' Posts Per Page';
    echo '</label></td></tr>';


    $primary_color = isset($shortcodes[$editing_name]['primary_color']) ? $shortcodes[$editing_name]['primary_color'] : '#000000';

    echo '<tr><th>Primary Color</th><td><label>';
    echo '<input type="color" id="primary_color" name="primary_color" value="' . esc_attr($primary_color) . '">';
    echo ' Primary Color';
    echo '</label></td></tr>';

    $order_by_date = isset($shortcodes[$editing_name]['order_by_date']) ? $shortcodes[$editing_name]['order_by_date'] : 'DESC';

    echo '<tr><th>Order By Date</th><td><label>';
    echo '<select id="order_by_date" name="order_by_date">';
    echo '<option value="ASC"' . ($order_by_date == 'ASC' ? ' selected' : '') . '>ASC (Oldest First)</option>';
    echo '<option value="DESC"' . ($order_by_date == 'DESC' ? ' selected' : '') . '>DESC (Newest First)</option>';
    echo '</select>';
    echo '</label></td></tr>';

    $column_count = isset($shortcodes[$editing_name]['column_count']) ? $shortcodes[$editing_name]['column_count'] : '4';

    echo '<tr><th>Column Count</th><td><label>';
    echo '<select id="column_count" name="column_count">';
    echo '<option value="1"' . ($column_count == '1' ? ' selected' : '') . '>1</option>';
    echo '<option value="2"' . ($column_count == '2' ? ' selected' : '') . '>2</option>';
    echo '<option value="3"' . ($column_count == '3' ? ' selected' : '') . '>3</option>';
    echo '<option value="4"' . ($column_count == '4' ? ' selected' : '') . '>4</option>';
    echo '<option value="5"' . ($column_count == '5' ? ' selected' : '') . '>5</option>';
    echo '<option value="6"' . ($column_count == '6' ? ' selected' : '') . '>6</option>';
    echo '</select>';
    echo '</label></td></tr>';


    echo '</table>';
    echo '<p><input type="submit" class="button button-primary" value="Save"></p>';
    echo '</form>';
}



function modern_post_lists_form_submission()
{
    if (!isset($_POST['modern_post_lists_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['modern_post_lists_nonce'])), 'modern_post_lists_add_shortcode')) {
        return;
    }
    $name = sanitize_text_field($_POST['shortcode_name']);
    $shortcode = sanitize_text_field($_POST['shortcode_value']);
    $layout_type = sanitize_text_field($_POST['layout_type']); // yeni eklenen alan
    $shortcodes = get_option('modern_post_lists', []);
    $post_type = array_map('sanitize_text_field', $_POST['post_type']);
    $selected_cats = array_map('sanitize_text_field', $_POST['selected_cats']);
    $selected_tags = array_map('sanitize_text_field', $_POST['selected_cats']);
    $selected_post_types = isset($post_type) && is_array($post_type)
        ? array_map('sanitize_text_field', $post_type)  // Kategori ID'lerini tam sayıya dönüştür
        : [];
    // Seçilen kategorileri yakala
    $selected_categories = isset($selected_cats) && is_array($selected_cats)
        ? array_map('intval', $selected_cats)  // Kategori ID'lerini tam sayıya dönüştür
        : [];

    $selected_tags = isset($selected_tags) && is_array($selected_tags)
        ? array_map('intval', $selected_tags)  // Kategori ID'lerini tam sayıya dönüştür
        : [];

    //$selected_tags = isset($selected_tags) ? array_map('intval', $selected_tags) : [];

    // Eğer bir shortcode düzenleniyorsa, orijinalini silin
    if (isset($_POST['editing_name'])) {
        $editing_name = sanitize_text_field($_POST['editing_name']);
        unset($shortcodes[$editing_name]);
    }

    $id = $editing_name ? $editing_name : uniqid();

    $shortcodes[$id] = [
        'name' => $name,
        'shortcode' => $shortcode,
        'layout_type' => $layout_type, // yeni eklenen alanı burada kaydediyoruz
        'post_type' => $selected_post_types, // yeni eklenen alanı burada kaydediyoruz
        'categories' => $selected_categories,
        'selected_tags' => $selected_tags,
        'id' => $id,
        'show_button' => isset($_POST['show_button']) ? true : false,
        'show_filter' => isset($_POST['show_filter']) ? true : false,
        'show_search' => isset($_POST['show_filter']) ? true : false,
        'show_description' => isset($_POST['show_description']) ? true : false,
        'button_text' => sanitize_text_field($_POST['button_text']),
        'primary_color' => sanitize_text_field($_POST['primary_color']),
        'posts_per_page' => intval(sanitize_text_field($_POST['posts_per_page'])) ? intval(sanitize_text_field($_POST['posts_per_page'])) : 10,
        'order_by_date' => sanitize_text_field($_POST['order_by_date']),
        'column_count' => in_array($_POST['column_count'], ['1', '2', '3', '4', '5', '6']) ? sanitize_text_field($_POST['column_count']) : '4',
    ];

    update_option('modern_post_lists', $shortcodes);
    wp_redirect(admin_url('admin.php?page=modern-post-lists'));
    exit;
}

add_action('admin_post_modern_post_lists_add_shortcode', 'modern_post_lists_form_submission');

function modern_post_lists_scripts()
{
    echo "
    <style>
        .copy-shortcode-button {
            cursor: pointer;
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var buttons = document.querySelectorAll('.copy-shortcode-button');
            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var textarea = document.createElement('textarea');
                    textarea.value = button.getAttribute('data-shortcode');
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                    alert('Shortcode copied!');
                });
            });
        });
    </script>
    ";
}
add_action('admin_footer', 'modern_post_lists_scripts');
