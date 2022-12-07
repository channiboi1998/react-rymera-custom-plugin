<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.linkedin.com/in/christian-verzosa-4917341a1/
 * @since             1.0.0
 * @package           Rymera_Movies
 *
 * @wordpress-plugin
 * Plugin Name:       Rymera Movies
 * Plugin URI:        https://rymera.com.au
 * Description:       A simple plugin that creates a “Movies” custom post type that is exposed through a REST API.
 * Version:           1.0.0
 * Author:            Christian Eclevia Verzosa
 * Author URI:        https://www.linkedin.com/in/christian-verzosa-4917341a1/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rymera-movies
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('RYMERA_MOVIES_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rymera-movies-activator.php
 */
function activate_rymera_movies()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-rymera-movies-activator.php';
    Rymera_Movies_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rymera-movies-deactivator.php
 */
function deactivate_rymera_movies()
{
    require_once plugin_dir_path(__FILE__) . 'includes/class-rymera-movies-deactivator.php';
    Rymera_Movies_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_rymera_movies');
register_deactivation_hook(__FILE__, 'deactivate_rymera_movies');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-rymera-movies.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rymera_movies()
{

    $plugin = new Rymera_Movies();
    $plugin->run();
}
run_rymera_movies();


function custom_post_type()
{

    /**
     * Set UI labels for Custom Post Type
     */
    $labels = array(
        'name'                => _x('Movies', 'Post Type General Name'),
        'singular_name'       => _x('Movie', 'Post Type Singular Name'),
        'menu_name'           => __('Movies'),
        'parent_item_colon'   => __('Parent Movie'),
        'all_items'           => __('All Movies'),
        'view_item'           => __('View Movie'),
        'add_new_item'        => __('Add New Movie'),
        'add_new'             => __('Add New'),
        'edit_item'           => __('Edit Movie'),
        'update_item'         => __('Update Movie'),
        'search_items'        => __('Search Movie'),
        'not_found'           => __('Not Found'),
        'not_found_in_trash'  => __('Not found in Trash'),
    );

    /**
     * Set other options for Custom Post Type
     */
    $args = array(
        'label'               => __('movies'),
        'description'         => __('Movie news and reviews'),
        'labels'              => $labels,

        /**
         * Features this CPT supports in Post Editor
         */
        'supports'            => array('title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields',),

        /**
         * You can associate this CPT with a taxonomy or custom taxonomy. 
         */
        'taxonomies'          => array('genres'),

        /* 
         * A hierarchical CPT is like Pages and can have parent and child items. A non-hierarchical CPT is like Posts.
         */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 1,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
    );

    /**
     * Registering your Custom Post Type
     */
    register_post_type('movies', $args);
}

/**
 * Hook into the 'init' action so that the function containing our post type registration is not unnecessarily executed. 
 */
add_action('init', 'custom_post_type', 0);

/**
 * GET Method: Rest api for fetching movies.
 */
add_action('rest_api_init', function () {
    register_rest_route('rymera/v1', 'movies', array(
        'methods'  => 'GET',
        'callback' => 'get_movies'
    ));
});

function get_movies($request)
{

    $args = array(
        'paged' => ($_REQUEST['paged'] ? $_REQUEST['paged'] : 1),
        'post_type' => 'movies',
        'posts_per_page' => 10
    );

    $query = new WP_Query($args);

    if (empty($query->posts)) {

        /**
         * Means that there are no movies matching the values declared on the args. Send 404.
         */
        return new WP_Error('empty_movies', 'There are no movies to display', array('status' => 404));
    }

    $posts = $query->posts;
    $max_pages = $query->max_num_pages;
    $total = $query->found_posts;

    /**
     * Creating `featuredImage` field to the response data for each posts.
     */
    foreach ($posts as $post) {
        $post->featuredImage = wp_get_attachment_url(get_post_thumbnail_id($post->ID), 'large');
    }

    /**
     * Prepare data for output
     */
    $controller = new WP_REST_Posts_Controller('movies');

    foreach ($posts as $post) {
        $response = $controller->prepare_item_for_response($post, $request);
        $data[] = $controller->prepare_response_for_collection($response);
    }

    /**
     * Set headers and return response    
     */
    $response = new WP_REST_Response($data, 200);
    $response->header('X-WP-Total', $total);
    $response->header('X-WP-TotalPages', $max_pages);
    return $response;
}

/**
 * GET Method: Rest api for fetching one movie.
 */
add_action('rest_api_init', function () {
    register_rest_route('rymera/v1', 'movies/(?P<movie_id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_movie_by_id'
    ));
});

function get_movie_by_id($request) {

    $args = array(
        'post_type' => 'movies',
        'p' => $request['movie_id']
    );

    $posts = get_posts($args);

    if (empty($posts)) {
        /**
         * Means that there is no movie matching the values declared on the args. Send 404.
         */
        return new WP_Error('empty_movie', 'We did not find any movie that match your result.', array('status' => 404));
    }

    /**
     * Means that there is a movie matching the values declared on the args. Send 200.
     */
    $controller = new WP_REST_Posts_Controller('movies');

    foreach ($posts as $post) {
        $response = $controller->prepare_item_for_response($post, $request);
        $data[] = $controller->prepare_response_for_collection($response);
        break;
    }

    /**
     * Set headers and return response    
     */
    $response = new WP_REST_Response($data, 200);
    return $response;
}