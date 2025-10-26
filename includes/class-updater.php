<?php
/**
 * GitHub Updater
 * 
 * Handles automatic updates from GitHub releases
 */

if (!defined('ABSPATH')) {
    exit;
}

class URL_Exporter_Updater {
    
    /**
     * GitHub username
     */
    private $username = 'ahmadreza-log';
    
    /**
     * GitHub repository
     */
    private $repository = 'url-exporter';
    
    /**
     * Plugin file path
     */
    private $plugin_file;
    
    /**
     * Plugin slug
     */
    private $plugin_slug;
    
    /**
     * Plugin data
     */
    private $plugin_data;
    
    /**
     * GitHub API URL
     */
    private $github_api_url;
    
    /**
     * Constructor
     */
    public function __construct($plugin_file) {
        $this->plugin_file = $plugin_file;
        $this->plugin_slug = plugin_basename($plugin_file);
        $this->github_api_url = "https://api.github.com/repos/{$this->username}/{$this->repository}/releases/latest";
        
        add_filter('plugins_api', [$this, 'plugin_info'], 20, 3);
        add_filter('site_transient_update_plugins', [$this, 'check_update']);
        add_filter('upgrader_post_install', [$this, 'after_install'], 10, 3);
    }
    
    /**
     * Get plugin data
     */
    private function get_plugin_data() {
        if (!$this->plugin_data) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            $this->plugin_data = get_plugin_data($this->plugin_file);
        }
        return $this->plugin_data;
    }
    
    /**
     * Get release info from GitHub
     */
    private function get_release_info() {
        $transient_key = 'url_exporter_release_info';
        $cached = get_transient($transient_key);
        
        if ($cached !== false && is_object($cached)) {
            return $cached;
        }
        
        $response = wp_remote_get($this->github_api_url, [
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress-URL-Exporter'
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        // 404 means no releases yet
        if ($response_code == 404) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);
        
        // Validate response data
        if (empty($data) || !is_object($data)) {
            return false;
        }
        
        // Check if it's an error response
        if (isset($data->message)) {
            return false;
        }
        
        // Cache for 12 hours only if valid data
        if (isset($data->tag_name)) {
            set_transient($transient_key, $data, 12 * HOUR_IN_SECONDS);
        }
        
        return $data;
    }
    
    /**
     * Check for updates
     */
    public function check_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        $release = $this->get_release_info();
        
        if (!$release) {
            return $transient;
        }
        
        // Check if release has required properties
        if (!isset($release->tag_name) || !isset($release->zipball_url)) {
            return $transient;
        }
        
        $plugin_data = $this->get_plugin_data();
        $current_version = $plugin_data['Version'];
        $latest_version = ltrim($release->tag_name, 'v');
        
        // Validate version string
        if (empty($latest_version)) {
            return $transient;
        }
        
        // Compare versions
        if (version_compare($current_version, $latest_version, '<')) {
            $plugin_data = [
                'slug' => dirname($this->plugin_slug),
                'plugin' => $this->plugin_slug,
                'new_version' => $latest_version,
                'url' => "https://github.com/{$this->username}/{$this->repository}",
                'package' => $release->zipball_url,
                'icons' => [
                    'default' => plugin_dir_url(dirname(__FILE__)) . 'assets/icon-256x256.png'
                ],
                'banners' => [],
                'tested' => '6.4',
                'requires_php' => '7.2'
            ];
            
            $transient->response[$this->plugin_slug] = (object) $plugin_data;
        }
        
        return $transient;
    }
    
    /**
     * Show plugin information
     */
    public function plugin_info($false, $action, $response) {
        if ($action !== 'plugin_information') {
            return $false;
        }
        
        if (empty($response->slug) || $response->slug !== dirname($this->plugin_slug)) {
            return $false;
        }
        
        $release = $this->get_release_info();
        
        if (!$release) {
            return $false;
        }
        
        // Check if release has required properties
        if (!isset($release->tag_name) || !isset($release->zipball_url)) {
            return $false;
        }
        
        $plugin_data = $this->get_plugin_data();
        
        $info = new stdClass();
        $info->name = $plugin_data['Name'];
        $info->slug = dirname($this->plugin_slug);
        $info->version = ltrim($release->tag_name, 'v');
        $info->author = $plugin_data['Author'];
        $info->author_profile = $plugin_data['AuthorURI'];
        $info->homepage = "https://github.com/{$this->username}/{$this->repository}";
        $info->download_link = $release->zipball_url;
        $info->requires = '5.0';
        $info->tested = '6.4';
        $info->requires_php = '7.2';
        $info->last_updated = isset($release->published_at) ? $release->published_at : '';
        $info->sections = [
            'description' => $plugin_data['Description'],
            'changelog' => $this->parse_changelog(isset($release->body) ? $release->body : '')
        ];
        $info->banners = [];
        
        return $info;
    }
    
    /**
     * Parse changelog from release notes
     */
    private function parse_changelog($body) {
        if (empty($body)) {
            return '<h4>What\'s New</h4><p>Check the GitHub release page for details.</p>';
        }
        
        // Convert markdown to HTML (basic)
        $changelog = wpautop($body);
        $changelog = str_replace(['<h1>', '<h2>'], '<h4>', $changelog);
        $changelog = str_replace(['</h1>', '</h2>'], '</h4>', $changelog);
        
        return $changelog;
    }
    
    /**
     * After install
     */
    public function after_install($response, $hook_extra, $result) {
        global $wp_filesystem;
        
        $install_directory = plugin_dir_path($this->plugin_file);
        $wp_filesystem->move($result['destination'], $install_directory);
        $result['destination'] = $install_directory;
        
        if ($this->plugin_slug) {
            activate_plugin($this->plugin_slug);
        }
        
        return $result;
    }
}

