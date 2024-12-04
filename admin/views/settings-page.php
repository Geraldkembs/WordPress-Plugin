<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('smm_settings');
$display_type = isset($settings['display_type']) ? $settings['display_type'] : 'header';
$float_position = isset($settings['float_position']) ? $settings['float_position'] : 'left';
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="smm-container">
        <form id="smm-form" method="post">
            <div class="smm-position-settings">
                <h2>Display Settings</h2>
                <div class="smm-setting-group">
                    <label for="smm-display-type">Display Type:</label>
                    <select name="display_type" id="smm-display-type">
                        <option value="header" <?php selected($display_type, 'header'); ?>>Header</option>
                        <option value="footer" <?php selected($display_type, 'footer'); ?>>Footer</option>
                        <option value="floating" <?php selected($display_type, 'floating'); ?>>Floating</option>
                    </select>
                </div>

                <div class="smm-setting-group" id="float-position-group" style="<?php echo $display_type === 'floating' ? 'display:block;' : 'display:none;'; ?>">
                    <label for="smm-float-position">Float Position:</label>
                    <select name="float_position" id="smm-float-position">
                        <option value="left" <?php selected($float_position, 'left'); ?>>Left Side</option>
                        <option value="right" <?php selected($float_position, 'right'); ?>>Right Side</option>
                    </select>
                </div>
            </div>

            <h2>Social Media Icons</h2>
            <p>Drag and drop to reorder. Toggle visibility using the switch.</p>

            <ul id="smm-sortable">
                <?php
                $socials = $settings['socials'];
                uasort($socials, function ($a, $b) {
                    return $a['order'] - $b['order'];
                });

                foreach ($socials as $key => $social):
                ?>
                    <li class="smm-social-item" data-id="<?php echo esc_attr($key); ?>">
                        <div class="smm-drag-handle">
                            <i class="fas fa-grip-vertical"></i>
                        </div>
                        <div class="smm-social-icon">
                            <i class="<?php echo esc_attr($social['icon']); ?>"></i>
                        </div>
                        <div class="smm-social-name">
                            <?php echo esc_html($social['name']); ?>
                        </div>
                        <div class="smm-social-url">
                            <input type="url"
                                name="socials[<?php echo esc_attr($key); ?>][url]"
                                value="<?php echo esc_url($social['url']); ?>"
                                placeholder="Enter <?php echo esc_attr($social['name']); ?> URL">
                        </div>
                        <div class="smm-social-toggle">
                            <label class="switch">
                                <input type="checkbox"
                                    name="socials[<?php echo esc_attr($key); ?>][active]"
                                    <?php checked($social['active'], true); ?>>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="smm-submit">
                <?php wp_nonce_field('smm_save_settings', 'smm_nonce'); ?>
                <button type="submit" class="button button-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>