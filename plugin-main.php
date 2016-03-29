<?php
/*
Plugin Name: Custom Simple Forum
Plugin URI: http://github.com/tysonchamp/
Description: A simple plugin for creating a simple forum on wordpress based website.
Version: 1.0
Author: Tyson
Author URI: http://fb.com/tysonchampno1
License: GPLv3
*/

// below code to restrict access to the plugin page should be used in every single plugin page
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


// initialization phase.

// creating tables on plugin activation for topics
global $forum_db_version;
$forum_db_version = '1.0';

function db_install() {

  global $wpdb;
  global $jal_db_version;

  $table_name = $wpdb->prefix . 'forum_topic_reply';
  
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
    id int(50) NOT NULL AUTO_INCREMENT,
    topic_id int(50) NOT NULL,
    user_id int(50) NOT NULL,
    replies text NOT NULL,
    UNIQUE KEY id (id)
  ) $charset_collate;";

  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

  dbDelta( $sql );

  add_option( 'forum_db_version', $forum_db_version );
}
register_activation_hook( __FILE__, 'jal_install' );


/*
 * Crating Custom Post types for Forum and Topics
 * https://codex.wordpress.org/Post_Types
 */
add_action( 'init', 'create_custom_post_types' );

function create_custom_post_types() {
    register_post_type( 'forum',
        array(
            'labels' => array(
                'name' => 'Forums',
                'singular_name' => 'Forums',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Forum',
                'edit' => 'Edit',
                'edit_item' => 'Edit Forum',
                'new_item' => 'New Forum',
                'view' => 'View',
                'view_item' => 'View Forums',
                'search_items' => 'Search Forums',
                'not_found' => 'No Forums Found',
                'not_found_in_trash' => 'No Forums found in Trash',
                'parent_item_colon' => '',
                'parent' => 'Parent Forum'
            ),

            'description',
            'public' => true,
            'menu_position' => 5,
            'hierarchical' => true,
            'menu_icon' => 'dashicons-universal-access-alt',
            'supports' => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions', 'custom-fields' ),
            'show_in_admin_bar' => true,
            'taxonomies' => array( '' ),
            'has_archive' => true
        )
    );

    register_post_type( 'topic',
        array(
            'labels' => array(
                'name' => 'Topics',
                'singular_name' => 'Topics',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Topic',
                'edit' => 'Edit',
                'edit_item' => 'Edit Topic',
                'new_item' => 'New Topic',
                'view' => 'View',
                'view_item' => 'View Topics',
                'search_items' => 'Search Topics',
                'not_found' => 'No Topics Found',
                'not_found_in_trash' => 'No Topics found in Trash',
                'parent_item_colon' => '',
                'parent' => 'Parent Topic'
            ),

            'description',
            'public' => true,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-clipboard',
            'hierarchical' => true,
            'supports' => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions', 'page-attributes', 'custom-fields' ),
            'show_in_admin_bar' => true,
            'taxonomies' => array( '' ),
            'has_archive' => true
        )
    );
}


/**
 * Add custom taxonomies
 *
 * Additional custom taxonomies can be defined here
 * http://codex.wordpress.org/Function_Reference/register_taxonomy
 */
function add_custom_forum_taxonomies() {
  // Add new "Locations" taxonomy to Posts
  register_taxonomy('forum-type', 'forum', array(
    // Hierarchical taxonomy (like categories)
    'hierarchical' => true,
    // This array of options controls the labels displayed in the WordPress Admin UI
    'labels' => array(
      'name' => _x( 'Forums Type', 'taxonomy general name' ),
      'singular_name' => _x( 'Forum Type', 'taxonomy singular name' ),
      'search_items' =>  __( 'Search Forum Type' ),
      'all_items' => __( 'All Forums type' ),
      'parent_item' => __( 'Parent Forum Type' ),
      'parent_item_colon' => __( 'Parent Forum Type:' ),
      'edit_item' => __( 'Edit Forum Type' ),
      'update_item' => __( 'Update Forum Type' ),
      'add_new_item' => __( 'Add New Forum Type' ),
      'new_item_name' => __( 'New Forum Type Name' ),
      'menu_name' => __( 'Forum Type' ),
    ),
    // Control the slugs used for this taxonomy
    'rewrite' => array(
      'slug' => 'forum-type', // This controls the base slug that will display before each term
      'with_front' => false, // Don't display the category base before "/locations/"
      'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
    ),
  ));
}
add_action( 'init', 'add_custom_forum_taxonomies', 0 );


// Add our post parent meta box
function my_add_meta_boxes() {
  add_meta_box( 'topic-parent', 'Forum', 'topic_attributes_meta_box', 'topic', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'my_add_meta_boxes' );


// Get the Post dropdown
function topic_attributes_meta_box( $post ) {
  $post_type_object = get_post_type_object( $post->post_type );
  $pages = wp_dropdown_pages( array( 'post_type' => 'forum', 'selected' => $post->post_parent, 'name' => 'parent_id', 'show_option_none' => __( '(no parent)' ), 'sort_column'=> 'menu_order, post_title', 'echo' => 0 ) );
  if ( ! empty( $pages ) ) {
    echo $pages;
  }
}


// Add our own URL strucutre and rewrite rules
function my_add_rewrite_rules() {
  add_rewrite_tag('%topic%', '([^/]+)', 'topic=');
  add_permastruct('topic', '/topic/%forum%/%topic%', false);
  add_rewrite_rule('^topic/([^/]+)/([^/]+)/?','index.php?topic=$matches[2]','top');
}
add_action( 'init', 'my_add_rewrite_rules' );


// Set permalink for topics
function my_permalinks($permalink, $post, $leavename) {
  $post_id = $post->ID;
  if($post->post_type != 'topic' || empty($permalink) || in_array($post->post_status, array('draft', 'pending', 'auto-draft')))
    return $permalink;
  $parent = $post->post_parent;
  $parent_post = get_post( $parent );
  $permalink = str_replace('%forum%', $parent_post->post_name, $permalink);
  return $permalink;
}
add_filter('post_type_link', 'my_permalinks', 10, 3);


// Creating admin menu for topic replies
add_action( 'admin_menu', array ( 'forum_topic_replies', 'admin_menu' ) );

class forum_topic_replies
{

  /**
   * Register the pages and the style and script loader callbacks.
   *
   * @wp-hook admin_menu
   * @return  void
   */
  public static function admin_menu()
  {

    // $main is now a slug named "toplevel_page_custom-plugin"
    // built with get_plugin_page_hookname( $menu_slug, '' )
    $main = add_menu_page(
      'Forum Replies',                    // page title
      'Forum Reply',                      // menu title
      // Change the capability to make the pages visible for other users.
      // See http://codex.wordpress.org/Roles_and_Capabilities
      'manage_options',                   // capability
      'forum-reply',                      // menu slug
      array ( __CLASS__, 'manage_reply' ),// callback function
      'dashicons-testimonial',         // menu icon
      '5'                               // menu position
    );
    
    
    /* See http://wordpress.stackexchange.com/a/49994/73 for the difference
     * to "'admin_enqueue_scripts', $hook_suffix"
     */
    foreach ( array ( $main, $sub ) as $slug )
    {

      // make sure the style callback is used on our page only
      add_action(
        "admin_print_styles-$slug",
        array ( __CLASS__, 'enqueue_style' )
      );
      // make sure the script callback is used on our page only
      add_action(
        "admin_print_scripts-$slug",
        array ( __CLASS__, 'enqueue_script' )
      );
    }

    // $text is now a slug named "custom-plugin_page_t5-text-included"
    // built with get_plugin_page_hookname( $menu_slug, $parent_slug)
    $text = add_submenu_page(
      'forum-reply',              // parent slug
      'Forum Help',                     // page title
      'Forum Help',                     // menu title
      'manage_options',           // capability
      'forum-help',               // menu slug
      array ( __CLASS__, 'render_text_included' ) // callback function, same as above
    );
  }

  /**
   * Print page output.
   *
   * @wp-hook toplevel_page_custom-plugin In wp-admin/admin.php do_action($page_hook).
   * @wp-hook custom-plugin_page_custom-plugin-sub
   * @return  void
   */
  public static function manage_reply()
  {
    global $title;
    print '<div class="wrap">';
    print "<h1>$title</h1>";
    // Creat a page name add-post.php
    include('topics.php');
    print '</div>';
  }
  
  /**
   * Print included HTML file.
   *
   * @wp-hook custom-plugin_page_t5-text-included
   * @return  void
   */
  public static function render_text_included()
  {
    global $title;
    print '<div class="wrap">';
    print "<h1>$title</h1>";
    // create a page name readme.php
    include('readme.php');
    
    print '</div>';
  }
  /**
   * Load stylesheet on our admin page only.
   *
   * @return void
   */
  public static function enqueue_style()
  {
    wp_register_style(
      't5_demo_css',
      plugins_url( 'custom-plugin.css', __FILE__ )
    );
    wp_enqueue_style( 't5_demo_css' );
  }
  
  /**
   * Load JavaScript on our admin page only.
   *
   * @return void
   */
  public static function enqueue_script()
  {
    wp_register_script(
      't5_demo_js',
      plugins_url( 'custom-plugin.js', __FILE__ ),
      array(),
      FALSE,
      TRUE
    );
    wp_enqueue_script( 't5_demo_js' );
    add_action( 'admin_head', 'wp_tiny_mce' );
  }
  
}
