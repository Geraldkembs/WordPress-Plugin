<?php
/**
 * Plugin Name: Social Media Manager
 * Description: Manage and arrange social media icons with drag and drop functionality
 * Version:     1.0.0
 * Author:      Gerald Kipchirchir
 * License:     GPL v2 or later
 * Text Domain: social-media-manager
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SMM_VERSION', '1.0.0');
define('SMM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SMM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Initialize the plugin
function social_media_manager_init() {
    require_once SMM_PLUGIN_DIR . 'includes/class-social-media-manager.php';
    $plugin = Social_Media_Manager::get_instance();

    // Add shortcode
    add_shortcode('social_media_icons', array($plugin, 'display_social_icons'));

    // Add to header or footer based on settings
    $settings = get_option('smm_settings');
    if ($settings && isset($settings['position'])) {
        if ($settings['position'] === 'header') {
            add_action('wp_head', array($plugin, 'display_social_icons'));
        } else {
            add_action('wp_footer', array($plugin, 'display_social_icons'));
        }
    }
}

add_action('plugins_loaded', 'social_media_manager_init');

