<?php 
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$tm_social_size           = get_option('tm_social_size');
$tm_custom_css            = get_option('tm_custom_css');
$tm_link_new_window       = get_option('tm_link_new_window');
$single_team_member_view  = get_option('single_team_member_view');
$old_team_manager_style   = get_option( 'old_team_manager_style' );
$tm_slug                  = get_option('tm_slug','team-details');
$tm_single_fields         = get_option('tm_single_fields');
$tm_taxonomy_fields       = get_option('tm_taxonomy_fields');
$tm_image_size_fields     = get_option('image_size_fields');
$team_image_size_change   = get_option('team_image_size_change');
$tm_single_team_lightbox  = get_option('tm_single_team_lightbox');
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
            <i class="tab-icon fas fa-code"></i>
            <?php esc_html_e('General Settings','wp-team-manager'); ?>
        </div>
        <div class="tab-item">
            <i class="tab-icon fas fa-cog"></i>
            <?php esc_html_e('Details Page Settings','wp-team-manager'); ?>
        </div>
        <div class="tab-item">
            <i class="tab-icon fas fa-plus-circle"></i>
            <?php esc_html_e('Advance','wp-team-manager'); ?>
        </div>
        <div class="line"></div>
    </div>

    <!-- Tab content -->
    <div class="tm-tab-content-wrapper tab-content">
        <div class="tab-pane active">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Social icon size (PX)','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <input class="form-control" id="tm_social_size" name="tm_social_size" type="number" value="<?php echo esc_html($tm_social_size); ?>" placeholder="16">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Open social links on new window','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td class="wtm-toggle-switch">
                        <input type="checkbox" name="tm_link_new_window" id="tm_link_new_window" value="True" <?php checked( $tm_link_new_window, 'True' ); ?>>
                        <label for="tm_link_new_window"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Customize Field Labels', 'wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"> <?php esc_html_e( ' Pro ', 'wp-team-manager' ) ?> </span> <a class="wptm-pro-link" href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"> <?php esc_html_e('Upgrade Now!', 'wp-team-manager') ?> </a>
                            <?php endif; ?>
                        </label>
                    </th>
                    <td class="team-satting-customize-field-labels">
                        <?php 
                        
                        foreach ($fields as $key => $default_label) {
                            $label_value = isset($custom_labels[$key]) ? $custom_labels[$key] : $default_label;
                            ?>
                            <p>
                                <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($default_label); ?>:</label>
                                <input type="text" class="regular-text" name="tm_custom_labels[<?php echo esc_attr($key); ?>]" <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) { echo 'disabled'; } ?> id="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($label_value); ?>">
                            </p>
                        <?php } ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Disable single team member view','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td class="wtm-toggle-switch">
                        <input type="checkbox" name="single_team_member_view" id="single_team_member_view" value="True" <?php checked( $single_team_member_view, 'True' ); ?>>
                        <label for="single_team_member_view"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Use "Old" Team-manager style','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td class="wtm-toggle-switch">
                        <input type="checkbox" name="old_team_manager_style" id="old_team_manager_style" value="True" <?php checked( $old_team_manager_style, 'True' ); ?>>
                        <label for="old_team_manager_style"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                    </td>
                </tr>

            </table>
        </div>
        <div class="tab-pane">
            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="">
                    <?php esc_html_e('Show/Hide Fields','wp-team-manager'); ?>

                    </label>
                </div>
                <div class="tm-field">
                    <?php Helper::generate_single_fields(); ?>
                </div>   
            </div><!-- .tm-field-wrapper -->
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Enable Gallery Lightbox','wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"> <?php esc_html_e( ' Pro ', 'wp-team-manager' ) ?> </span> <a class="wptm-pro-link" href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"> <?php esc_html_e('Upgrade Now!', 'wp-team-manager') ?> </a>
                            <?php endif; ?>
                        </label>
                    </th>
                    <td class="wtm-toggle-switch">
                        <input type="checkbox" name="tm_single_team_lightbox" id="tm_single_team_lightbox" value="True" <?php checked( $tm_single_team_lightbox, 'True' ); ?>
                            <?php if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) echo 'disabled'; ?>>
                            <label for="tm_single_team_lightbox"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                    </td>
                    
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Change Single Image size','wp-team-manager'); ?>
                            <?php if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) : ?>
                                <span class="wptm-pro-text"> <?php esc_html_e( ' Pro ', 'wp-team-manager' ) ?> </span> <a class="wptm-pro-link" href="<?php echo esc_url(tmwstm_fs()->get_upgrade_url()) ?>"> <?php esc_html_e('Upgrade Now!', 'wp-team-manager') ?> </a>
                            <?php endif; ?>
                        </label>
                        
                    </th>
                    <td>

                        <select name="team_image_size_change">
                            <?php Helper::get_image_sizes(); ?>
                        </select>

                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Gallery Columns','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="tm_single_gallery_column">
                            <?php Helper::get_gallery_columns(); ?>
                        </select>

                        <p><?php esc_html_e('Number of Columns on Image Gallery ', 'wp-team-manager'); ?></p>
                    </td>
                </tr>
            </table> 
        </div>
        <div class="tab-pane">
            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="tm_slug">
                        <?php esc_html_e('Slug','wp-team-manager'); ?>
                    </label>
                </div>
                <div class="tm-field">
                    <input type="text" placeholder="team-details" class="form-control regular-text" name="tm_slug" id="tm_slug"
                        value="<?php echo esc_html($tm_slug); ?>">
                    <p class="description">
                        <?php esc_html_e('Customize Team Members Post Type Slug, by default it is set to team-details','wp-team-manager'); ?>
                    </p>
                </div>
            </div><!-- .tm-field-wrapper -->
            <div class="tm-field-wrapper">
                <div class="tm-label">
                    
                    <?php esc_html_e('Show/Hide Taxonomy','wp-team-manager'); ?>
                </div>
                <div class="tm-field">
                    <?php Helper::get_taxonomy_settings(); ?>
                </div>
            </div><!-- .tm-field-wrapper -->

            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="wtm_debug_log">
                        <?php esc_html_e('Enable Debug Log', 'wp-team-manager'); ?>
                    </label>
                </div>
                <div class="tm-field wtm-toggle-switch">
                    <?php $wtm_debug_log = get_option('wtm_debug_log', false); ?>
                    <input type="checkbox" name="wtm_debug_log" id="wtm_debug_log" value="1" <?php checked( (bool) $wtm_debug_log, true ); ?> />
                    <label for="wtm_debug_log"><?php esc_html_e('Yes', 'wp-team-manager'); ?></label>
                    <p class="description">
                        <?php esc_html_e('When enabled, the plugin writes debug information to a log file. Default location is uploads/wp-team-manager/logs/wtm.log unless a custom path is set below.', 'wp-team-manager'); ?>
                    </p>
                </div>
            </div><!-- .tm-field-wrapper -->

            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="wtm_debug_log_path">
                        <?php esc_html_e('Log File Path', 'wp-team-manager'); ?>
                    </label>
                </div>
                <div class="tm-field">
                    <?php $wtm_debug_log_path = get_option('wtm_debug_log_path', ''); ?>
                    <input type="text" placeholder="/absolute/path/to/wtm.log" class="form-control regular-text" name="wtm_debug_log_path" id="wtm_debug_log_path" value="<?php echo esc_attr($wtm_debug_log_path); ?>">
                    <p class="description">
                        <?php esc_html_e('Optional absolute path. Leave empty to use the default path in the uploads directory.', 'wp-team-manager'); ?>
                    </p>
                </div>
            </div><!-- .tm-field-wrapper -->

            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="tm_custom_css">
                        <?php esc_html_e('Custom CSS', 'wp-team-manager'); ?>
                    </label>
                </div>
                <div class="tm-field">
                    <textarea name="tm_custom_css" id="tm_custom_css" class="wp-editor-area" rows="10" cols="80"><?php echo esc_textarea($tm_custom_css); ?></textarea>
                    <p class="description">
                        <?php esc_html_e('Add custom CSS for Team Manager', 'wp-team-manager'); ?>
                    </p>
                </div>
            </div><!-- .tm-field-wrapper -->
        </div>
    </div>
</div>