<?php
if (!defined('ABSPATH')) {
    exit;
}

class Settings {
    private $default_socials;

    public function __construct() {
        $this->default_socials = [
            'facebook' => [
                'name' => 'Facebook',
                'icon' => 'fab fa-facebook',
                'url' => '',
                'active' => true,
                'order' => 1
            ],
            'twitter' => [
                'name' => 'Twitter',
                'icon' => 'fab fa-twitter',
                'url' => '',
                'active' => true,
                'order' => 2
            ],
            'instagram' => [
                'name' => 'Instagram',
                'icon' => 'fab fa-instagram',
                'url' => '',
                'active' => true,
                'order' => 3
            ],
            'linkedin' => [
                'name' => 'LinkedIn',
                'icon' => 'fab fa-linkedin',
                'url' => '',
                'active' => true,
                'order' => 4
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'icon' => 'fab fa-whatsapp',
                'url' => '',
                'active' => true,
                'order' => 5
            ],
            'youtube' => [
                'name' => 'YouTube',
                'icon' => 'fab fa-youtube',
                'url' => '',
                'active' => true,
                'order' => 6
            ]
        ];
    }

    public function init() {
        if (!get_option('smm_settings')) {
            $this->set_default_settings();
        }
    }

    public function set_default_settings() {
        update_option('smm_settings', array(
            'position' => 'header',
            'socials' => $this->default_socials
        ));
    }

    public function get_settings() {
        return get_option('smm_settings');
    }

    public function get_default_socials() {
        return $this->default_socials;
    }

    public function update_settings($settings) {
        return update_option('smm_settings', $settings);
    }
}