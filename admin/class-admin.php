<?php
if (!defined('ABSPATH')) {
    exit;
}

class Admin {
    private $settings;

    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_social_media_settings', array($this, 'save_settings'));
        add_action('wp_ajax_update_social_media_order', array($this, 'update_order'));
    }

    public function enqueue_scripts($hook) {
        if ('settings_page_social-media-manager' !== $hook) {
            return;
        }

        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        wp_enqueue_style('smm-admin-style', SMM_PLUGIN_URL . 'assets/css/admin.css');

        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('smm-admin-script', SMM_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), SMM_VERSION, true);

        wp_localize_script('smm-admin-script', 'smmAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('smm_nonce')
        ));
    }

    public function add_admin_menu() {
        add_options_page(
            'Social Media Manager',
            'Social Media Manager',
            'manage_options',
            'social-media-manager',
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once SMM_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    public function save_settings() {
        if (!check_ajax_referer('smm_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $settings = array(
            'position' => sanitize_text_field($_POST['position']),
            'socials' => array()
        );

        foreach ($_POST['socials'] as $key => $social) {
            $default_socials = $this->settings->get_default_socials();
            $settings['socials'][$key] = array(
                'name' => $default_socials[$key]['name'],
                'icon' => $default_socials[$key]['icon'],
                'url' => esc_url_raw($social['url']),
                'active' => isset($social['active']) && $social['active'] === 'true',
                'order' => intval($social['order'])
            );
        }

        $this->settings->update_settings($settings);
        wp_send_json_success('Settings saved successfully');
    }

    public function update_order() {
        if (!check_ajax_referer('smm_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $order = $_POST['order'];
        $settings = $this->settings->get_settings();

        foreach ($order as $position => $key) {
            $settings['socials'][$key]['order'] = $position + 1;
        }

        $this->settings->update_settings($settings);
        wp_send_json_success('Order updated successfully');
    }
}