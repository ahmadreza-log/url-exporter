<?php

/**
 * Plugin Name: Url Exporter
 * Plugin URI: https://github.com/ahmadreza-log/url-exporter
 * Description: A powerful tool for exporting URLs from WordPress with pagination and batch processing.
 * Version: 1.1.0
 * Author: Ahmadreza Ebrahimi
 * Author URI: https://ahmadreza.me
 * Text Domain: url-exporter
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

defined('ABSPATH') || exit;

// Load updater class
require_once plugin_dir_path(__FILE__) . 'includes/class-updater.php';

/**
 * Main URL Exporter Class
 */
class URL_Exporter {
    
    /**
     * Plugin version
     */
    const VERSION = '1.1.0';
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_init', [$this, 'add_taxonomy_actions']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_url_exporter_get_urls', [$this, 'ajax_get_urls']);
    }
    
    /**
     * Load plugin textdomain for translations
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'url-exporter',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Add export action to all taxonomies
     */
    public function add_taxonomy_actions() {
        $taxonomies = get_taxonomies(['public' => true], 'names');
        
        foreach ($taxonomies as $taxonomy) {
            add_filter("{$taxonomy}_row_actions", [$this, 'add_export_action'], 10, 2);
        }
    }
    
    /**
     * Add export URLs action to taxonomy row
     *
     * @param array $actions Existing actions
     * @param WP_Term $tag Term object
     * @return array Modified actions
     */
    public function add_export_action($actions, $tag) {
        // Check user capability
        if (!current_user_can('manage_options')) {
            return $actions;
        }
        
        $taxonomy = $tag->taxonomy;
        $url = add_query_arg([
            'taxonomy' => $taxonomy,
            'tag_ID'   => $tag->term_id
        ], admin_url('edit-tags.php'));
        
        $actions['export-urls'] = sprintf(
            '<a href="%s" class="url-exporter-trigger" data-taxonomy="%s" data-term-id="%d">%s</a>',
            esc_url($url),
            esc_attr($taxonomy),
            absint($tag->term_id),
            esc_html__('Export URLs', 'url-exporter')
        );
        
        return $actions;
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_assets() {
        // Only load on taxonomy pages
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'edit-tags') {
            return;
        }
        
        wp_enqueue_script(
            'url-exporter',
            plugin_dir_url(__FILE__) . 'assets/js/script.js',
            ['jquery'],
            self::VERSION,
            true
        );
        
        wp_localize_script('url-exporter', 'UrlExporter', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('url_exporter_nonce'),
            'i18n'    => [
                'loading'    => __('Loading URLs...', 'url-exporter'),
                'error'      => __('Error loading URLs', 'url-exporter'),
                'noResults'  => __('No posts found', 'url-exporter'),
                'title'      => __('URL List', 'url-exporter'),
                'urlHeader'  => __('URL', 'url-exporter'),
            ]
        ]);
        
        wp_enqueue_style(
            'url-exporter',
            plugin_dir_url(__FILE__) . 'assets/css/style.css',
            [],
            self::VERSION
        );
    }
    
    /**
     * AJAX handler to get total count of posts
     */
    public function ajax_get_urls() {
        // Check if this is a count request or data request
        $request_type = isset($_GET['request_type']) ? sanitize_text_field($_GET['request_type']) : 'data';
        
        if ($request_type === 'count') {
            $this->ajax_get_post_count();
        } else {
            $this->ajax_get_post_data();
        }
    }
    
    /**
     * Get total post count for pagination
     */
    private function ajax_get_post_count() {
        // Verify nonce
        check_ajax_referer('url_exporter_nonce', 'nonce');
        
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission to access this section', 'url-exporter')
            ]);
        }
        
        // Sanitize and validate inputs
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : '';
        $term_id  = isset($_GET['ID']) ? absint($_GET['ID']) : 0;
        
        if (empty($taxonomy) || empty($term_id)) {
            wp_send_json_error([
                'message' => __('Invalid parameters provided', 'url-exporter')
            ]);
        }
        
        // Check if taxonomy exists
        if (!taxonomy_exists($taxonomy)) {
            wp_send_json_error([
                'message' => __('Taxonomy does not exist', 'url-exporter')
            ]);
        }
        
        // Check if term exists
        $term = get_term($term_id, $taxonomy);
        if (is_wp_error($term) || !$term) {
            wp_send_json_error([
                'message' => __('Term not found', 'url-exporter')
            ]);
        }
        
        // Get total count using WP_Query for better performance
        $query = new WP_Query([
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id
                ]
            ]
        ]);
        
        wp_send_json_success([
            'total_count' => $query->found_posts,
            'term_name'   => $term->name,
            'batch_size'  => 50 // Posts per batch
        ]);
    }
    
    /**
     * Get post data in batches
     */
    private function ajax_get_post_data() {
        // Increase time limit for large datasets
        set_time_limit(300); // 5 minutes
        
        // Verify nonce
        check_ajax_referer('url_exporter_nonce', 'nonce');
        
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission to access this section', 'url-exporter')
            ]);
        }
        
        // Sanitize and validate inputs
        $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : '';
        $term_id  = isset($_GET['ID']) ? absint($_GET['ID']) : 0;
        $page     = isset($_GET['page']) ? absint($_GET['page']) : 1;
        $per_page = isset($_GET['per_page']) ? absint($_GET['per_page']) : 50;
        
        // Limit per_page to prevent abuse
        $per_page = min($per_page, 100);
        
        if (empty($taxonomy) || empty($term_id)) {
            wp_send_json_error([
                'message' => __('Invalid parameters provided', 'url-exporter')
            ]);
        }
        
        // Check if taxonomy exists
        if (!taxonomy_exists($taxonomy)) {
            wp_send_json_error([
                'message' => __('Taxonomy does not exist', 'url-exporter')
            ]);
        }
        
        // Check if term exists
        $term = get_term($term_id, $taxonomy);
        if (is_wp_error($term) || !$term) {
            wp_send_json_error([
                'message' => __('Term not found', 'url-exporter')
            ]);
        }
        
        // Get posts in batches using WP_Query for better performance
        $query = new WP_Query([
            'post_type'      => 'any',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'tax_query'      => [
                [
                    'taxonomy' => $taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_id
                ]
            ],
            'orderby'        => 'date',
            'order'          => 'DESC',
            'no_found_rows'  => false
        ]);
        
        // Collect URLs
        $urls = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $permalink = get_permalink($post_id);
                
                if ($permalink) {
                    $urls[] = [
                        'url'   => $permalink,
                        'title' => get_the_title($post_id),
                        'date'  => get_the_date('', $post_id)
                    ];
                }
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success([
            'urls'         => $urls,
            'current_page' => $page,
            'total_pages'  => $query->max_num_pages,
            'total_posts'  => $query->found_posts,
            'has_more'     => $page < $query->max_num_pages,
            'term'         => $term->name
        ]);
    }
}

// Initialize the plugin
$url_exporter = new URL_Exporter();

// Initialize updater
if (is_admin()) {
    new URL_Exporter_Updater(__FILE__);
}