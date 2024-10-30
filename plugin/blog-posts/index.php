<?php

class modern_post_lists_BlogPost
{
  public $post_type = 'post';
  public $categories = [];
  public $layout_type = 'Blog Posts';
  public $name = '';
  public $shortcode = '';
  public $id = '';
  public $show_button = true;
  public $button_text = 'Read more';
  public $show_filter = true;
  public $show_search = true;
  public $show_description = true;
  public $tags = [];
  public $primary_color = '#000000';
  public $posts_per_page = 10;
  public $order_by_date = 'DESC';
  public $column_count = 4;

  public function __construct($id = false)
  {
    if ($id) {
      $this->id = $id;
      $this->load();
    }
  }

  public function load()
  {
    $shortcodes = get_option('modern_post_lists', []);
    $shortcode = $shortcodes[$this->id];
    $this->post_type = $shortcode['post_type'];
    $this->categories = $shortcode['categories'];
    $this->tags = $shortcode['selected_tags'];
    $this->layout_type = $shortcode['layout_type'];
    $this->name = $shortcode['name'];
    $this->shortcode = $shortcode['shortcode'];
    $this->show_button = $shortcode['show_button'] ?? true;
    $this->show_filter = $shortcode['show_filter'] ?? true;
    $this->button_text = $shortcode['button_text'] ?? 'Read more';
    $this->show_search = $shortcode['show_search'] ?? true;
    $this->show_description = $shortcode['show_description'] ?? true;
    $this->primary_color = $shortcode['primary_color'] ?? '#000000';
    $this->posts_per_page = $shortcode['posts_per_page'] ?? 10;
    $this->order_by_date = $shortcode['order_by_date'] ?? 'DESC';
    $this->column_count = $shortcode['column_count'] ?? 4;
  }

  public function render()
  {
    // import css
    $this->import_css_and_js();
    $paged = (get_query_var('paged')) ? sanitize_text_field(get_query_var('paged')) : 1;
    $search = isset($_GET['stext']) ? sanitize_text_field($_GET['stext']) : '';

    if (isset($_GET['category'])) {
      $this->categories = [sanitize_text_field($_GET['category'])];
    }
    $args = [
      'orderby' => 'date',
      'order' => $this->order_by_date,
      'post_type__in' => $this->post_type,
      'posts_per_page' => $this->posts_per_page,
      'category__in' => $this->categories,
      'tag__in' => $this->tags,
      's' => $search,
      'paged' => $paged,
    ];
    $query = new WP_Query($args);
    $posts = $query->posts;
    $all_categories = get_categories();
    //array_unshift($all_categories, (object) ['name' => 'Hepsi']);
    $html = '<div class="mpl">';
    $html .= '<div class="m-auto">';
    $html .= '<div class="flex justify-between m-auto mt-md flex-wrap filter-padding">';
    $html .= '<div class="flex flex-wrap justify-center">';
    if ($this->show_filter) {
      //$html .= '<button class="btn btn-category bg-white mb-xxs" id="category-all" onclick="filterCategories(\'all\')">Hepsi</button>';
      foreach ($all_categories as $category) {
        $active_class = isset($_GET['category']) && sanitize_text_field($_GET['category']) == $category->cat_ID ? 'active' : '';
        $html .= '<button class="btn btn-category bg-white mb-xxs ' . esc_attr($active_class) . '" id="category-' . esc_attr($category->name) . '" onclick="filterCategories(' . esc_js($category->cat_ID) . ')">' . esc_html($category->name) . '</button>';
      }
    }
    $html .= '</div>';
    $html .= '<div class="flex flex-1 justify-end items-stretch mb-xxs">';
    if ($this->show_search) {
      $html .= '<div class="search-container flex items-stretch">';
      $html .= '<input id="search-input" placeholder="Search" class="search-input" value=' . esc_attr($search) . '>';
      $html .= '<button class="btn search-clear-btn" onclick="clearSearch()">×</button>';
      $html .= '</div>';
    }
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="flex justify-center flex-wrap" id="card-container">';
    foreach ($posts as $post) {
      $categories = get_the_category($post->ID);
      $category_names = array_map(function ($category) {
        return $category->name;
      }, $categories);
      //array_unshift($category_names, 'Hepsi');
      $image_url = has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID) : plugins_url('default.jpg', __FILE__);
      $category_names = implode(' ', $category_names);
      $html .= '<div class="card-container"><div class="card bg-white" data-categories="' . esc_attr($category_names) . '">';
      $html .= '<div class="card-image-container relative">';
      $html .= '<img class="card-image" alt="Advanced Media Boxes" src="' . esc_url($image_url) . '">';
      $html .= '<div class="overlay absolute flex items-center justify-center">';
      $html .= '<button id="overlay-zoom-btn" onclick="show(`' . esc_js($image_url) . '`)" class="btn overlay-btn flex items-center justify-center">';
      $html .= '<span class="dashicons dashicons-search"></span>';
      $html .= '</button>';
      $html .= '</div>';
      $html .= '</div>';
      $html .= '<div class="card-body">';
      $html .= '<h2 class="card-title">' . get_the_title($post->ID) . '</h2>';
      $html .= '<p class="card-date">' . get_the_date('j F, Y', $post->ID) . '</p>';
      if ($this->show_description) {
        $html .= '<p class="card-description">';
        $html .= get_the_excerpt($post->ID);
        $html .= '</p>';
      }
      // go to post link
      if ($this->show_button) {
        $link = get_permalink($post->ID);
        $external_link = get_post_meta($post->ID, '_external_link', true);
        if ($external_link) {
          $link = $external_link;
        }
        $html .= '<button onclick="window.location.href=\'' . esc_js($link) . '\'"  class="btn card-more-btn">' . esc_html($this->button_text) . '</button>';
      }
      $html .= '</div>';
      $html .= '</div>';
      $html .= '</div>';
    }
    $big = 999999999;
    $html .= '</div>';
    $html .= '<div class="amb-page">';
    $html .= paginate_links(array(
      'base'    => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
      'format'  => '?paged=%#%',
      'current' => max(1, $paged),
      'total'   => $query->max_num_pages
    ));
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div class="modal alignfull" id="modal">';
    $html .= '<div class="moda-tool absolute">';
    $html .= '<button id="modal-close-btn" class="btn modal-btn">×</button>';
    $html .= '</div>';
    $html .= '<div class="modal-image-container">';
    $html .= '<img class="modal-image" id="modal-image" alt="modal-image">';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<script>';
    $html .= 'const show = (url) => {';
    $html .= 'const modalImage = document.getElementById("modal-image");';
    $html .= 'modalImage.src = url;';
    $html .= 'jQuery("#modal").show();';
    $html .= '};';
    $html .= 'jQuery("#modal-close-btn").click(function () {';
    $html .= 'jQuery("#modal").hide();';
    $html .= '});';
    //$html .= 'feather.replace();';
    $html .= "document.getElementById('search-input').addEventListener('keyup', function(e) {
      if (e.key === 'Enter') {
          window.location.href = '?stext=' + this.value;
          e.preventDefault();
      }
    }); ";

    $html .= "
          function clearSearch() {
            document.getElementById('search-input').value = '';
            window.location.href = '?stext=';
          }
    ";
    $html .= "
        function filterCategories(id) {
          var search = document.getElementById('search-input').value;
          window.location.href = '?category=' + id + '&stext=' + search;
        }
    ";
    $html .= '</script>';
    return $html;
  }

  // wordpress import css

  public function import_css_and_js()
  {
    wp_enqueue_style('amb_blog_posts_css', plugins_url('blog.css', __FILE__));
    wp_enqueue_script('amb_blog_posts_js', plugins_url('blog.js', __FILE__));
    //import custom css
    $custom_css = "
    .mpl .btn-category:hover {
      background-color: {$this->primary_color} !important;
      border-color: {$this->primary_color} !important;
    }
    .mpl .overlay-btn {
      color: {$this->primary_color} !important;
    }
    .mpl .card-more-btn {
      background-color: {$this->primary_color} !important;
    }
    
    .mpl .card-more-btn:hover {
      background-color: {$this->primary_color} !important;
    }

    .mpl .amb-page .page-numbers {
      color: {$this->primary_color} !important;
    }

    .mpl .overlay {
      background-color: {$this->primary_color}90 !important;
    }
    
    .mpl .search-clear-btn {
      color: {$this->primary_color} !important;
    }

    .mpl .card-container {
      width: calc(100% / {$this->column_count}) !important;
    }

    .mpl .btn-category.active {
      color: white;
      background-color: {$this->primary_color} !important;
    }
    ";

    wp_add_inline_style('amb_blog_posts_css', $custom_css);
  }
}
