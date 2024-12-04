<?php
if (!defined('ABSPATH')) {
    exit;
}

class Social_Media_Manager
{
    private static $instance = null;

    // Default social media platforms
    private $default_socials = [
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

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Initialize hooks
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_action('wp_ajax_save_social_media_settings', array($this, 'save_social_media_settings'));
        add_action('wp_ajax_update_social_media_order', array($this, 'update_social_media_order'));

    
        add_action('init', array($this, 'display_social_icons_wrapper'));
    }

    public function init()
    {
        // Initialize plugin settings
        if (!get_option('smm_settings')) {
            update_option('smm_settings', array(
                'display_type' => 'header',
                'float_position' => 'left',
                'socials' => $this->default_socials
            ));
        }
    }

    public function admin_enqueue_scripts($hook)
    {
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

    public function frontend_enqueue_scripts()
    {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        wp_enqueue_style('smm-front-style', SMM_PLUGIN_URL . 'assets/css/public.css');
    }

    public function add_admin_menu()
    {
        add_options_page(
            'Social Media Manager',
            'Social Media Manager',
            'manage_options',
            'social-media-manager',
            array($this, 'render_admin_page')
        );
    }

    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        require_once SMM_PLUGIN_DIR . 'admin/views/settings-page.php';
    }

    public function save_social_media_settings() {
        if (!check_ajax_referer('smm_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
    
        $settings = array(
            'display_type' => sanitize_text_field($_POST['display_type']),
            'float_position' => sanitize_text_field($_POST['float_position']),
            'socials' => array()
        );
    
        foreach ($_POST['socials'] as $key => $social) {
            $settings['socials'][$key] = array(
                'name' => $this->default_socials[$key]['name'],
                'icon' => $this->default_socials[$key]['icon'],
                'url' => esc_url_raw($social['url']),
                'active' => isset($social['active']) && $social['active'] === 'true',
                'order' => intval($social['order'])
            );
        }
    
        update_option('smm_settings', $settings);
        wp_send_json_success('Settings saved successfully');
    }

    public function update_social_media_order()
    {
        if (!check_ajax_referer('smm_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }

        $order = $_POST['order'];
        $settings = get_option('smm_settings');

        foreach ($order as $position => $key) {
            $settings['socials'][$key]['order'] = $position + 1;
        }

        update_option('smm_settings', $settings);
        wp_send_json_success('Order updated successfully');
    }

    public function display_social_icons() {
        $settings = get_option('smm_settings');
        if (!$settings || empty($settings['socials'])) {
            return '';
        }
    
        $socials = $settings['socials'];
        $display_type = isset($settings['display_type']) ? $settings['display_type'] : 'header';
        $float_position = isset($settings['float_position']) ? $settings['float_position'] : 'left';
        
        // Sort by order
        uasort($socials, function($a, $b) {
            return $a['order'] - $b['order'];
        });
    
        ob_start();
        ?>
        <div class="smm-social-container <?php echo $display_type === 'floating' ? 'smm-floating smm-float-' . esc_attr($float_position) : ''; ?>">
            <div class="smm-social-icons <?php echo $display_type === 'floating' ? 'smm-icons-floating' : ''; ?>">
                <?php foreach ($socials as $key => $social): ?>
                    <?php if ($social['active'] && !empty($social['url'])): ?>
                        <a href="<?php echo esc_url($social['url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="smm-social-icon smm-<?php echo esc_attr($key); ?>" 
                           title="<?php echo esc_attr($social['name']); ?>">
                            <i class="<?php echo esc_attr($social['icon']); ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function display_social_icons_wrapper() {
        $settings = get_option('smm_settings');
        $display_type = isset($settings['display_type']) ? $settings['display_type'] : 'header';
        $float_position = isset($settings['float_position']) ? $settings['float_position'] : 'left';
    
        switch ($display_type) {
            case 'header':
                add_action('wp_head', array($this, 'maybe_display_header'));
                break;
            case 'footer':
                add_action('wp_footer', array($this, 'maybe_display_footer'));
                break;
            case 'floating':
                add_action('wp_footer', array($this, 'maybe_display_floating'));
                break;
        }
    }

    public function maybe_display_header() {
        $settings = get_option('smm_settings');
        if (isset($settings['display_type']) && $settings['display_type'] === 'header') {
            echo $this->display_social_icons();
        }
    }
    
    public function maybe_display_footer() {
        $settings = get_option('smm_settings');
        if (isset($settings['display_type']) && $settings['display_type'] === 'footer') {
            echo $this->display_social_icons();
        }
    }
    
    public function maybe_display_floating() {
        $settings = get_option('smm_settings');
        if (isset($settings['display_type']) && $settings['display_type'] === 'floating') {
            echo $this->display_social_icons();
        }
    }
}
