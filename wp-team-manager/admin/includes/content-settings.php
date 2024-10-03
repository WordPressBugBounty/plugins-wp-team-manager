<?php 
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
                        <input class="form-control" id="tm_social_size" name="tm_social_size" type="number" value="<?php echo esc_html($tm_social_size); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Open social links on new window','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="tm_link_new_window" value="True" <?php checked( $tm_link_new_window, 'True' ); ?>>
                        <?php esc_html_e('Yes', 'wp-team-manager'); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Disable single team member view','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="single_team_member_view" value="True" <?php checked( $single_team_member_view, 'True' ); ?>>
                        <?php esc_html_e('Yes', 'wp-team-manager'); ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Use "Old" Team-manager style','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="checkbox" name="old_team_manager_style" value="True" <?php checked( $old_team_manager_style, 'True' ); ?>>
                        <?php esc_html_e('Yes', 'wp-team-manager'); ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label>
                            <?php esc_html_e('Change Image size','wp-team-manager'); ?>
                        </label>
                    </th>
                    <td>
                        <?php 
                            $options = get_option( 'team_image_size_change' );
                            $selected = isset($options) ? $options : 'medium'; // Default value
                            echo '<select name="team_image_size_change">';
                            echo '<option value="medium" ' . selected($selected, 'medium', false) . '>' . __('Medium', 'wp-team-manager') . '</option>';
                            echo '<option value="thumbnail" ' . selected($selected, 'thumbnail', false) . '>' . __('Thumbnail', 'wp-team-manager') . '</option>';
                            echo '<option value="medium_large" ' . selected($selected, 'medium_large', false) . '>' . __('Medium Large', 'wp-team-manager') . '</option>';
                            echo '<option value="large" ' . selected($selected, 'large', false) . '>' . __('Large', 'wp-team-manager') . '</option>';
                            echo '<option value="full" ' . selected($selected, 'full', false) . '>' . __('Full', 'wp-team-manager') . '</option>';
                            echo '</select>';
                        ?>
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
        </div>
        <div class="tab-pane">
            <div class="tm-field-wrapper">
                <div class="tm-label">
                    <label for="tm_slug">
                        <?php esc_html_e('Slug','wp-team-manager'); ?>
                    </label>
                </div>
                <div class="tm-field">
                    <input type="text" class="form-control regular-text" name="tm_slug" id="tm_slug"
                        value="<?php echo esc_html($tm_slug); ?>">
                    <p class="description">
                        <?php esc_html_e('Slug configuration','wp-team-manager'); ?>
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