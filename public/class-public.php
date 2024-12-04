// public/class-public.php
<?php
if (!defined('ABSPATH')) {
    exit;
}

class Public_Display {
    private $settings;

    public function __construct($settings) {
        $this->settings = $settings;
        $this->init_hooks();
    }

    private function init_hooks() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_head', array($this, 'maybe_display_header'));
        add_action('wp_footer', array($this, 'maybe_display_footer'));
        add_shortcode('social_media_icons', array($this, 'display_social_icons'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        wp_enqueue_style('smm-front-style', SMM_PLUGIN_URL . 'assets/css/public.css');
    }

    public function display_social_icons() {
        $settings = $this->settings->get_settings();
        if (!$settings || empty($settings['socials'])) {
            return '';
        }

        $socials = $settings['socials'];
        
        // Sort by order
        uasort($socials, function($a, $b) {
            return $a['order'] - $b['order'];
        });

        ob_start();
        ?>
        <div class="smm-social-container">
            <div class="smm-social-icons">
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

    public function maybe_display_header() {
        $settings = $this->settings->get_settings();
        if ($settings['position'] === 'header') {
            echo $this->display_social_icons();
        }
    }

    public function maybe_display_footer() {
        $settings = $this->settings->get_settings();
        if ($settings['position'] === 'footer') {
            echo $this->display_social_icons();
        }
    }
}