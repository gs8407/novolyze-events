<?php

/**
 * package Novolyze Events
 */

/*
    Plugin Name: Novolyze Events
    Description: Adds the Events
    Version: 1.0.1
    Author: Mediavista
    Licence: GPLv2 or later
    Text Domain: events
 */

if (!function_exists('add_action')) {
  die;
}

define('MY_PLUGIN_PATH_EVENTS', plugin_dir_url(__FILE__));

class Events
{

  function __construct()
  {
    add_action('init', array($this, 'novolyze_events_register'));
  }

  function register()
  {
    add_action('wp_enqueue_scripts', array($this, 'enqueue'));
  }

  function activate()
  {
    $this->novolyze_events_register();
    flush_rewrite_rules();
  }


  function deactivate()
  {
    flush_rewrite_rules();
  }

  // Register Custom Post Type
  function novolyze_events_register()
  {

    $labels = array(
      'name'                  => _x('Events', 'Post Type General Name', 'events'),
      'singular_name'         => _x('Event', 'Post Type Singular Name', 'events'),
      'menu_name'             => __('Events', 'events'),
      'name_admin_bar'        => __('Events', 'events'),
      'archives'              => __('Events', 'events'),
      'attributes'            => __('Events Attributes', 'events'),
      'parent_item_colon'     => __('Parent Item:', 'events'),
      'all_items'             => __('All Items', 'events'),
      'add_new_item'          => __('Add New Item', 'events'),
      'add_new'               => __('Add New', 'events'),
      'new_item'              => __('New Item', 'events'),
      'edit_item'             => __('Edit Item', 'events'),
      'update_item'           => __('Update Item', 'events'),
      'view_item'             => __('View Item', 'events'),
      'view_items'            => __('View Items', 'events'),
      'search_items'          => __('Search Item', 'events'),
      'not_found'             => __('Not found', 'events'),
      'not_found_in_trash'    => __('Not found in Trash', 'events'),
      'featured_image'        => __('Featured Image', 'events'),
      'set_featured_image'    => __('Set featured image', 'events'),
      'remove_featured_image' => __('Remove featured image', 'events'),
      'use_featured_image'    => __('Use as featured image', 'events'),
      'insert_into_item'      => __('Insert into item', 'events'),
      'uploaded_to_this_item' => __('Uploaded to this item', 'events'),
      'items_list'            => __('Items list', 'events'),
      'items_list_navigation' => __('Items list navigation', 'events'),
      'filter_items_list'     => __('Filter items list', 'events'),
    );
    $args = array(
      'label'                 => __('Events', 'events'),
      'labels'                => $labels,
      'supports'              => array('title', 'editor', 'revisions', 'thumbnail'),
      'hierarchical'          => false,
      'public'                => true,
      'show_ui'               => true,
      'show_in_menu'          => true,
      'menu_position'         => 5,
      'menu_icon'             => 'dashicons-media-spreadsheet',
      'show_in_admin_bar'     => true,
      'show_in_nav_menus'     => true,
      'can_export'            => true,
      'has_archive'           => 'events',
      'exclude_from_search'   => false,
      'publicly_queryable'    => true,
      'capability_type'       => 'page',
      'query_var' => false
    );
    register_post_type('events', $args);
  }

  // Add CSS and JS
  function enqueue()
  {
    wp_enqueue_style('events-styles', plugins_url('/assets/events-main.css', __FILE__));
    wp_enqueue_script('events-scripts', plugins_url('/assets/events.js', __FILE__));
    wp_add_inline_script('search', 'ajax_url', admin_url('admin-ajax.php'));
  }

  function portfolios_shortcode()
  {

    $args = array(
      'post_type' => 'events'
    );

    $the_query = new WP_Query($args);
?>
    <div class="events-wrapper">
      <?php if ($the_query->have_posts()) :
        while ($the_query->have_posts()) : $the_query->the_post(); ?>
          <div class="<?php if ($the_query->current_post === 0 || $the_query->current_post % 6 === 0) echo "big";  ?>" style="background-image: url(<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>);">
            <a href="<?php echo get_the_permalink(); ?>"><?php the_title(); ?></a>
          </div>
      <?php endwhile;
      endif;
      wp_reset_postdata(); ?>
    </div>

<?php
  }
}

if (class_exists('Events')) {
  $Events = new Events();
  $Events->register();
}

// Set archive template
function events_template($template)
{
  global $post;
  $plugin_root_dir = WP_PLUGIN_DIR . '/novolyze-events/';
  if (is_archive() && get_post_type($post) == 'events') {
    $template = $plugin_root_dir . '/inc/templates/archive-events.php';
  }

  return $template;
}
add_filter('archive_template', 'events_template');

// Add Solutions category
function events_type_taxonomy()
{
  register_taxonomy(
    'events_type',  // The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
    'events',             // post type name
    array(
      'hierarchical' => true,
      'label' => 'Type', // display name
      'query_var' => true,
      'rewrite' => array(
        'slug' => 'type-taxonomy',    // This controls the base slug that will display before each term
        'with_front' => false  // Don't display the category base before
      )
    )
  );
}
add_action('init', 'events_type_taxonomy');

// Activation
register_activation_hook(__FILE__, array($Events, 'activate'));

// Deactivation
register_activation_hook(__FILE__, array($Events, 'deactivate'));

/**
 * Filter
 */

add_action('wp_ajax_events_filter', 'events_filter_callback');
add_action('wp_ajax_nopriv_events_filter', 'events_filter_callback');

function events_filter_callback()
{

  header("Content-Type: application/json");

  $result = array();

  if (!empty($_GET['search'])) {
    $search = sanitize_text_field($_GET['search']);
  }

  $paged = 1;
  $paged = sanitize_text_field($_GET['paginate']);

  if (!empty($_GET['type'])) {
    $type_operator = "IN";
  } else {
    $type_operator = "NOT IN";
  }

  $args = array(
    'post_type' => 'events',
    'post_status' => 'publish',
    's' => $search,
    'posts_per_page' => 20,
    'paged' => $paged,
    'tax_query' => array(
      array(
        'taxonomy' => 'events_type',
        'field' => 'slug',
        'terms' => $_GET['type'],
        'operator' => $type_operator
      ),
    )
  );

  $filter_query = new WP_Query($args);

  while ($filter_query->have_posts()) {
    $filter_query->the_post();
    $result[] = array(
      'title' => get_the_title(),
      'image' => get_the_post_thumbnail_url(),
      'category' => get_the_terms($post->ID, 'events_type'),
      'button_text' => get_field('button_text'),
      'button_url' => get_field('button_url'),
      'current_page' => $paged,
      'max' => $filter_query->max_num_pages,
    );
  }

  echo json_encode($result);

  wp_die();
};

if (function_exists('acf_add_local_field_group')) :

  acf_add_local_field_group(array(
    'key' => 'group_6254373cab843',
    'title' => 'Event',
    'fields' => array(
      array(
        'key' => 'field_62543740d05f7',
        'label' => 'Button',
        'name' => '',
        'type' => 'tab',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'placement' => 'top',
        'endpoint' => 0,
      ),
      array(
        'key' => 'field_62543751d05f8',
        'label' => 'Button Text',
        'name' => 'button_text',
        'type' => 'text',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'default_value' => '',
        'placeholder' => '',
        'prepend' => '',
        'append' => '',
        'maxlength' => '',
      ),
      array(
        'key' => 'field_62543757d05f9',
        'label' => 'Button URL',
        'name' => 'button_url',
        'type' => 'url',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'default_value' => '',
        'placeholder' => '',
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'events',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => array(
      0 => 'permalink',
      1 => 'the_content',
      2 => 'excerpt',
      3 => 'discussion',
      4 => 'comments',
      5 => 'slug',
    ),
    'active' => true,
    'description' => '',
    'show_in_rest' => 0,
  ));

endif;
