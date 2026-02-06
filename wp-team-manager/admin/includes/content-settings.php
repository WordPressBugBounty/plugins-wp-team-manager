<?php 
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tm_social_size           = get_option('tm_social_size', 16);
$tm_custom_css            = get_option('tm_custom_css', '');
$tm_link_new_window       = get_option('tm_link_new_window', '');
$single_team_member_view  = get_option('single_team_member_view', '');
$old_team_manager_style   = get_option( 'old_team_manager_style', '' );
$tm_slug                  = get_option('tm_slug','team-details');
$tm_single_fields         = get_option('tm_single_fields', array());
$tm_taxonomy_fields       = get_option('tm_taxonomy_fields', array());
$tm_image_size_fields     = get_option('image_size_fields', array());
$team_image_size_change   = get_option('team_image_size_change', 'medium');
$tm_single_team_lightbox  = get_option('tm_single_team_lightbox', '');
$custom_labels            = get_option('tm_custom_labels', array());
$fields = array(
    'tm_web_url'         => 'Web URL',
    'tm_vcard'           => 'Add vCard File',
);
?>
<div class="wp-core-ui">
    <!-- Tab items -->
    <div class="tm-tabs">
        <div class="tab-item active">
            ⚙️ <?php esc_html_e('General Settings','wp-team-manager'); ?>
        </div>
        <div class="tab-item">
            📄 <?php esc_html_e('Details Page Settings','wp-team-manager'); ?>
        </div>
        <div class="tab-item">
            ♿ <?php esc_html_e('Accessibility & SEO','wp-team-manager'); ?>
        </div>
        <div class="tab-item">
            🔧 <?php esc_html_e('Advanced','wp-team-manager'); ?>
        </div>
        <div class="line"></div>
    </div>

    <!-- Tab content -->
    <div class="tm-tab-content-wrapper tab-content">
        <div class="tab-pane active">
            <div class="wtm-settings-grid">
                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-share"></span><?php esc_html_e('Social Media', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Configure social media display settings', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label for="tm_social_size"><?php esc_html_e('Social icon size (PX)', 'wp-team-manager'); ?></label>
                            <input class="form-control" id="tm_social_size" name="tm_social_size" type="number" value="<?php echo esc_html($tm_social_size); ?>" placeholder="16">
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Open social links on new window', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_link_new_window" id="tm_link_new_window" value="1" <?php checked( $tm_link_new_window, '1' ); ?>>
                                <label for="tm_link_new_window"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-layout"></span><?php esc_html_e('Dashboard Mode', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Choose a preset that matches your use case. This changes field labels throughout the plugin.', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label for="tm_dashboard_mode"><?php esc_html_e('Plugin Mode', 'wp-team-manager'); ?></label>
                            <select name="tm_dashboard_mode" id="tm_dashboard_mode" class="form-control">
                                <?php
                                $current_mode = \DWL\Wtm\Classes\Helper::get_active_mode();
                                $mode_options = \DWL\Wtm\Classes\Helper::get_mode_options();
                                foreach ( $mode_options as $key => $label ) {
                                    printf(
                                        '<option value="%s" %s>%s</option>',
                                        esc_attr( $key ),
                                        selected( $current_mode, $key, false ),
                                        esc_html( $label )
                                    );
                                }
                                ?>
                            </select>
                            <p class="description"><?php esc_html_e('Changing this will update field labels in the admin area and frontend. Your data remains unchanged.', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-edit"></span><?php esc_html_e('Field Customization', 'wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                            <?php endif; ?>
                        </h3>
                        <p><?php esc_html_e('Customize field labels and display options', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Customize Field Labels', 'wp-team-manager'); ?></label>
                            <div class="wtm-custom-labels">
                                <?php foreach ($fields as $key => $default_label) {
                                    $label_value = isset($custom_labels[$key]) ? $custom_labels[$key] : $default_label; ?>
                                    <div class="wtm-label-row">
                                        <span class="wtm-label-name"><?php echo esc_html($default_label); ?>:</span>
                                        <input type="text" name="tm_custom_labels[<?php echo esc_attr($key); ?>]" <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) { echo 'disabled'; } ?> value="<?php echo esc_attr($label_value); ?>">
                                    </div>
                                <?php } ?>
                            </div>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a> <?php esc_html_e('to customize field labels', 'wp-team-manager'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-admin-settings"></span><?php esc_html_e('Display Options', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Control how team members are displayed', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Disable single team member view', 'wp-team-manager'); ?>
                                <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                    <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                                <?php endif; ?>
                            </label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="single_team_member_view" id="single_team_member_view" value="1" <?php checked( $single_team_member_view, '1' ); ?> <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>>
                                <label for="single_team_member_view"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a></p>
                            <?php endif; ?>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Use "Old" Team-manager style', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="old_team_manager_style" id="old_team_manager_style" value="1" <?php checked( $old_team_manager_style, '1' ); ?>>
                                <label for="old_team_manager_style"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane">
            <div class="wtm-settings-grid">
                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-visibility"></span><?php esc_html_e('Field Visibility', 'wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                            <?php endif; ?>
                        </h3>
                        <p><?php esc_html_e('Control which fields appear on team member detail pages', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Show/Hide Fields', 'wp-team-manager'); ?></label>
                            <div class="wtm-fields-container">
                                <?php 
                                if (class_exists('DWL\Wtm\Classes\Helper') && method_exists('DWL\Wtm\Classes\Helper', 'generate_single_fields')) {
                                    Helper::generate_single_fields(); 
                                } else {
                                    echo '<p>Helper class method not available</p>';
                                }
                                ?>
                            </div>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a> <?php esc_html_e('to control field visibility', 'wp-team-manager'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-format-gallery"></span><?php esc_html_e('Gallery Settings', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Configure image gallery display options', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Enable Gallery Lightbox', 'wp-team-manager'); ?>
                                <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                    <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                                <?php endif; ?>
                            </label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_single_team_lightbox" id="tm_single_team_lightbox" value="1" <?php checked( $tm_single_team_lightbox, '1' ); ?> <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>>
                                <label for="tm_single_team_lightbox"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a></p>
                            <?php endif; ?>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Gallery Columns', 'wp-team-manager'); ?></label>
                            <select name="tm_single_gallery_column" class="wtm-select">
                                <?php 
                                if (class_exists('DWL\Wtm\Classes\Helper') && method_exists('DWL\Wtm\Classes\Helper', 'get_gallery_columns')) {
                                    Helper::get_gallery_columns(); 
                                } else {
                                    echo '<option value="four_columns">Four Columns</option>';
                                }
                                ?>
                            </select>
                            <p class="wtm-field-description"><?php esc_html_e('Number of columns in the image gallery', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-format-image"></span><?php esc_html_e('Image Settings', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Configure image display and sizing options', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Single Page Image Size', 'wp-team-manager'); ?>
                                <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                    <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                                <?php endif; ?>
                            </label>
                            <select name="team_image_size_change" class="wtm-select" <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>>
                                <?php 
                                if (class_exists('DWL\Wtm\Classes\Helper') && method_exists('DWL\Wtm\Classes\Helper', 'get_image_sizes')) {
                                    Helper::get_image_sizes(); 
                                } else {
                                    echo '<option value="medium">Medium</option>';
                                }
                                ?>
                            </select>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane">
            <div class="wtm-settings-grid">
                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-universal-access-alt"></span><?php esc_html_e('Accessibility Features', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Enhance accessibility for users with disabilities', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <?php 
                        $tm_alt_text = get_option('tm_alt_text', 1);
                        $tm_keyboard_nav = get_option('tm_keyboard_nav', 1);
                        $tm_screen_reader = get_option('tm_screen_reader', 1);
                        $tm_high_contrast = get_option('tm_high_contrast', 0);
                        $tm_focus_style = get_option('tm_focus_style', 'default');
                        ?>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Enable Alt Text for Images', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_alt_text" id="tm_alt_text" value="1" <?php checked( $tm_alt_text, 1 ); ?>>
                                <label for="tm_alt_text"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Automatically generate descriptive alt text for team member images', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Keyboard Navigation Support', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_keyboard_nav" id="tm_keyboard_nav" value="1" <?php checked( $tm_keyboard_nav, 1 ); ?>>
                                <label for="tm_keyboard_nav"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Enable full keyboard navigation for team layouts and interactions', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Screen Reader Optimization', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_screen_reader" id="tm_screen_reader" value="1" <?php checked( $tm_screen_reader, 1 ); ?>>
                                <label for="tm_screen_reader"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Add ARIA labels and semantic markup for screen readers', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('High Contrast Mode Support', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_high_contrast" id="tm_high_contrast" value="1" <?php checked( $tm_high_contrast, 1 ); ?>>
                                <label for="tm_high_contrast"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Optimize colors and contrast for users with visual impairments', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Focus Indicators', 'wp-team-manager'); ?></label>
                            <select name="tm_focus_style" class="wtm-select">
                                <option value="default" <?php selected( $tm_focus_style, 'default' ); ?>><?php esc_html_e('Default Browser', 'wp-team-manager'); ?></option>
                                <option value="enhanced" <?php selected( $tm_focus_style, 'enhanced' ); ?>><?php esc_html_e('Enhanced Outline', 'wp-team-manager'); ?></option>
                                <option value="custom" <?php selected( $tm_focus_style, 'custom' ); ?>><?php esc_html_e('Custom Color', 'wp-team-manager'); ?></option>
                            </select>
                            <p class="wtm-field-description"><?php esc_html_e('Choose focus indicator style for keyboard navigation', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-search"></span><?php esc_html_e('SEO Optimization', 'wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"><?php esc_html_e('Pro', 'wp-team-manager') ?></span>
                            <?php endif; ?>
                        </h3>
                        <p><?php esc_html_e('Improve search engine visibility and structured data', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <?php 
                        $tm_schema_markup = get_option('tm_schema_markup', 0);
                        $tm_meta_description = get_option('tm_meta_description', '');
                        ?>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Enable Schema Markup', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_schema_markup" id="tm_schema_markup" value="1" <?php checked( $tm_schema_markup, 1 ); ?> <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>>
                                <label for="tm_schema_markup"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Add structured data for better search engine understanding', 'wp-team-manager'); ?></p>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <p class="wtm-pro-notice"><a href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"><?php esc_html_e('Upgrade to Pro', 'wp-team-manager') ?></a></p>
                            <?php endif; ?>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Meta Description Template', 'wp-team-manager'); ?></label>
                            <textarea name="tm_meta_description" class="wtm-textarea" rows="3" placeholder="{name} - {job_title} at {company}" <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>><?php echo esc_textarea( $tm_meta_description ); ?></textarea>
                            <p class="wtm-field-description"><?php esc_html_e('Template for team member meta descriptions. Use {name}, {job_title}, {company} placeholders', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-performance"></span><?php esc_html_e('Performance & Loading', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Optimize loading speed and user experience', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <?php 
                        $tm_lazy_loading = get_option('tm_lazy_loading', 0);
                        $tm_preload_images = get_option('tm_preload_images', 3);
                        ?>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Enable Lazy Loading', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <input type="checkbox" name="tm_lazy_loading" id="tm_lazy_loading" value="1" <?php checked( $tm_lazy_loading, 1 ); ?>>
                                <label for="tm_lazy_loading"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('Load images only when they become visible', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Preload Critical Images', 'wp-team-manager'); ?></label>
                            <input type="number" name="tm_preload_images" class="wtm-input" value="<?php echo esc_attr( $tm_preload_images ); ?>" min="0" max="10">
                            <p class="wtm-field-description"><?php esc_html_e('Number of images to preload for faster initial display', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane">
            <div class="wtm-settings-grid">
                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-admin-links"></span><?php esc_html_e('URL & Permalink Settings', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Configure URL structure and permalink options', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label for="tm_slug"><?php esc_html_e('Team Member Slug', 'wp-team-manager'); ?></label>
                            <input type="text" placeholder="team-details" class="wtm-input" name="tm_slug" id="tm_slug" value="<?php echo esc_html($tm_slug); ?>">
                            <p class="wtm-field-description"><?php esc_html_e('Customize Team Members Post Type Slug, by default it is set to team-details', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-category"></span><?php esc_html_e('Taxonomy Settings', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Control which taxonomies are available for team organization', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <?php 
                        // Get current taxonomy fields that should be hidden
                        $tm_taxonomy_fields = get_option('tm_taxonomy_fields', array());
                        
                        // Get individual taxonomy enable settings with defaults
                        $tm_taxonomy_designation_enable = get_option('tm_taxonomy_designation_enable', 1);
                        $tm_taxonomy_department_enable = get_option('tm_taxonomy_department_enable', 1);
                        $tm_taxonomy_gender_enable = get_option('tm_taxonomy_gender_enable', 1);
                        $tm_taxonomy_groups_enable = get_option('tm_taxonomy_groups_enable', 1);
                        
                        $tm_show_taxonomy_filter = get_option('tm_show_taxonomy_filter', 1);
                        $tm_show_taxonomy_count = get_option('tm_show_taxonomy_count', 0);
                        $tm_hierarchical_taxonomy = get_option('tm_hierarchical_taxonomy', 0);
                        ?>
                        <div class="wtm-field-group">
                            <?php $current_mode = \DWL\Wtm\Classes\Helper::get_active_mode(); ?>
                            <label><?php esc_html_e('Available Taxonomies', 'wp-team-manager'); ?></label>
                            <div class="wtm-taxonomy-grid">
                                <div class="wtm-taxonomy-item">
                                    <div class="wtm-taxonomy-header">
                                        <span class="dashicons dashicons-id-alt"></span>
                                        <strong><?php echo $current_mode === 'sports' ? esc_html__('Positions', 'wp-team-manager') : esc_html__('Designations', 'wp-team-manager'); ?></strong>
                                    </div>
                                    <div class="wtm-toggle-switch">
                                        <input type="checkbox" name="tm_taxonomy_designation_enable" id="tm_taxonomy_designation" value="1" <?php checked( $tm_taxonomy_designation_enable, 1 ); ?>>
                                        <label for="tm_taxonomy_designation"><?php esc_html_e('Enable', 'wp-team-manager'); ?></label>
                                    </div>
                                    <p class="wtm-taxonomy-desc"><?php echo $current_mode === 'sports' ? esc_html__('Organize team members by their positions', 'wp-team-manager') : esc_html__('Organize team members by their job designations', 'wp-team-manager'); ?></p>
                                </div>
                                
                                <div class="wtm-taxonomy-item">
                                    <div class="wtm-taxonomy-header">
                                        <span class="dashicons dashicons-building"></span>
                                        <strong><?php echo $current_mode === 'sports' ? esc_html__('Teams', 'wp-team-manager') : esc_html__('Departments', 'wp-team-manager'); ?></strong>
                                    </div>
                                    <div class="wtm-toggle-switch">
                                        <input type="checkbox" name="tm_taxonomy_department_enable" id="tm_taxonomy_department" value="1" <?php checked( $tm_taxonomy_department_enable, 1 ); ?>>
                                        <label for="tm_taxonomy_department"><?php esc_html_e('Enable', 'wp-team-manager'); ?></label>
                                    </div>
                                    <p class="wtm-taxonomy-desc"><?php echo $current_mode === 'sports' ? esc_html__('Group members by their teams', 'wp-team-manager') : esc_html__('Group members by their departments', 'wp-team-manager'); ?></p>
                                </div>
                                
                                <div class="wtm-taxonomy-item">
                                    <div class="wtm-taxonomy-header">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <strong><?php esc_html_e('Gender', 'wp-team-manager'); ?></strong>
                                    </div>
                                    <div class="wtm-toggle-switch">
                                        <input type="checkbox" name="tm_taxonomy_gender_enable" id="tm_taxonomy_gender" value="1" <?php checked( $tm_taxonomy_gender_enable, 1 ); ?>>
                                        <label for="tm_taxonomy_gender"><?php esc_html_e('Enable', 'wp-team-manager'); ?></label>
                                    </div>
                                    <p class="wtm-taxonomy-desc"><?php esc_html_e('Categorize team members by gender', 'wp-team-manager'); ?></p>
                                </div>
                                
                                <div class="wtm-taxonomy-item">
                                    <div class="wtm-taxonomy-header">
                                        <span class="dashicons dashicons-groups"></span>
                                        <strong><?php echo $current_mode === 'sports' ? esc_html__('Leagues', 'wp-team-manager') : esc_html__('Groups', 'wp-team-manager'); ?></strong>
                                    </div>
                                    <div class="wtm-toggle-switch">
                                        <input type="checkbox" name="tm_taxonomy_groups_enable" id="tm_taxonomy_groups" value="1" <?php checked( $tm_taxonomy_groups_enable, 1 ); ?>>
                                        <label for="tm_taxonomy_groups"><?php esc_html_e('Enable', 'wp-team-manager'); ?></label>
                                    </div>
                                    <p class="wtm-taxonomy-desc"><?php echo $current_mode === 'sports' ? esc_html__('Organize team members into different leagues', 'wp-team-manager') : esc_html__('Organize team members into different groups', 'wp-team-manager'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="wtm-field-group">
                            <label><?php esc_html_e('Taxonomy Display Options', 'wp-team-manager'); ?></label>
                            <div class="wtm-taxonomy-display-options">
                                <div class="wtm-display-option">
                                    <input type="checkbox" name="tm_show_taxonomy_filter" id="tm_show_taxonomy_filter" value="1" <?php checked( $tm_show_taxonomy_filter, 1 ); ?>>
                                    <label for="tm_show_taxonomy_filter"><?php esc_html_e('Show taxonomy filters on frontend', 'wp-team-manager'); ?></label>
                                </div>
                                <div class="wtm-display-option">
                                    <input type="checkbox" name="tm_show_taxonomy_count" id="tm_show_taxonomy_count" value="1" <?php checked( $tm_show_taxonomy_count, 1 ); ?>>
                                    <label for="tm_show_taxonomy_count"><?php esc_html_e('Display member count for each taxonomy', 'wp-team-manager'); ?></label>
                                </div>
                                <div class="wtm-display-option">
                                    <input type="checkbox" name="tm_hierarchical_taxonomy" id="tm_hierarchical_taxonomy" value="1" <?php checked( $tm_hierarchical_taxonomy, 1 ); ?>>
                                    <label for="tm_hierarchical_taxonomy"><?php esc_html_e('Enable hierarchical taxonomy structure', 'wp-team-manager'); ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-admin-tools"></span><?php esc_html_e('Developer Tools', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Debug logging and development utilities', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label for="wtm_debug_log"><?php esc_html_e('Enable Debug Log', 'wp-team-manager'); ?></label>
                            <div class="wtm-toggle-switch">
                                <?php $wtm_debug_log = get_option('wtm_debug_log', false); ?>
                                <input type="checkbox" name="wtm_debug_log" id="wtm_debug_log" value="1" <?php checked( (bool) $wtm_debug_log, true ); ?>>
                                <label for="wtm_debug_log"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                            </div>
                            <p class="wtm-field-description"><?php esc_html_e('When enabled, the plugin writes debug information to a log file. Default location is uploads/wp-team-manager/logs/wtm.log unless a custom path is set below.', 'wp-team-manager'); ?></p>
                        </div>
                        <div class="wtm-field-group">
                            <label for="wtm_debug_log_path"><?php esc_html_e('Log File Path', 'wp-team-manager'); ?></label>
                            <input type="text" placeholder="/absolute/path/to/wtm.log" class="wtm-input" name="wtm_debug_log_path" id="wtm_debug_log_path" value="<?php echo esc_attr(get_option('wtm_debug_log_path', '')); ?>">
                            <p class="wtm-field-description"><?php esc_html_e('Optional absolute path. Leave empty to use the default path in the uploads directory.', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="wtm-setting-card">
                    <div class="wtm-card-header">
                        <h3><span class="dashicons dashicons-editor-code"></span><?php esc_html_e('Custom Styling', 'wp-team-manager'); ?></h3>
                        <p><?php esc_html_e('Add custom CSS to override default styles', 'wp-team-manager'); ?></p>
                    </div>
                    <div class="wtm-card-body">
                        <div class="wtm-field-group">
                            <label for="tm_custom_css"><?php esc_html_e('Custom CSS', 'wp-team-manager'); ?></label>
                            <textarea name="tm_custom_css" id="tm_custom_css" class="wtm-textarea wtm-code-editor" rows="10"><?php echo esc_textarea($tm_custom_css); ?></textarea>
                            <p class="wtm-field-description"><?php esc_html_e('Add custom CSS for Team Manager. Changes will be applied to all team displays.', 'wp-team-manager'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>