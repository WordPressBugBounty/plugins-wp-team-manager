
<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)):
    $style_type_name = $settings['layout_type'].'_'.'style_type';
	$style_type = !empty( $settings[$style_type_name] ) ? $settings[$style_type_name] : '';
    $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   

    $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';

    $desktop_column = isset($settings['columns_desktop']) ? $settings['columns_desktop'] : (
        isset($settings['columns']) ? $settings['columns'] : '4');

    $tablet_column = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : 3;
    
    $mobile_column = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : 1;


   
    
        ?>
            <div class="dwl-team-table-responsive team-table-<?php echo esc_attr( $style_type )?>">
            <table class="table">
                    <thead>
                        <tr>
                            <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_title'] ): ?>
                                <th><?php esc_html_e( "Name", " wp-team-manager " )?></th>
                            <?php endif; ?>

                            <?php if('yes'== $settings['show_department']  ): ?>
                                <th><?php esc_html_e( "Department", " wp-team-manager " )?></th>
                            <?php endif; ?>

                            <?php if( 'yes'== $settings['show_designation'] ): ?>
                                <th><?php esc_html_e( "Designation", " wp-team-manager " )?></th>
                            <?php endif; ?>

                            <?php if( 'yes' === $settings['show_team_mobile_number'] ) : ?>
                                <th><?php esc_html_e( "Number", " wp-team-manager " )?></th>
                            <?php endif; ?>

                            <?php if("yes" == $settings['show_social'] || 'yes' == $settings['show_full_biograph'] ) : ?>
                                <th><?php esc_html_e( "Contact", " wp-team-manager " )?></th>
                            <?php endif; ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        
                            foreach ($data as $key => $teamInfo):
                                
                                $team_department = wp_get_post_terms( $teamInfo->ID, 'team_department');
                                $team_designation = wp_get_post_terms( $teamInfo->ID, 'team_designation');
                                $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
                                $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
                                $tm_email = get_post_meta($teamInfo->ID,'tm_email',true);
                                $tm_mobile = get_post_meta($teamInfo->ID,'tm_mobile',true);?>
                                
                                
                                <tr class="dwl-table-row">
                                    <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_title'] ): ?>
                                        <td class="dwl-table-data">
                                            <div class="dwl-table-img-wraper">

                                                <?php if("yes" == $settings['show_image']): ?>
                                                    <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                        <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                    </a>
                                                <?php endif;?>
                                                
                                                <?php if('yes'== $settings['show_title']  ): ?>
                                                    <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                                                <?php endif;?>
                                                
                                            </div>
                                        </td>
                                    <?php endif;?>

                                    <?php if('yes'== $settings['show_department']  ): ?>
                                        <td class="dwl-table-data">
                                            <?php echo wp_kses_post(Helper::render_terms($teamInfo->ID, 1, 'team_department')); ?>
                                        </td>
                                    <?php endif;?>

                                    <?php if( 'yes'== $settings['show_designation'] ): ?>
                                        <td class="dwl-table-data">
                                            <?php echo wp_kses_post(Helper::render_terms($teamInfo->ID, 1, 'team_designation')); ?>
                                        </td>
                                    <?php endif;?>
                                    
                                    <?php if( 'yes' === $settings['show_team_mobile_number'] ) : ?>
                                        <td class="dwl-table-data dwl-table-data-short-bio">
                                            <?php if(isset($tm_mobile) && !empty($tm_mobile)): ?>
                                            <div class="team-member-mobile-info">
                                                <a href="tel://<?php echo esc_html($tm_mobile) ?>" target="_blank">
                                                    <!-- <i class="fas fa-phone-alt"></i> -->
                                                    <?php echo esc_html($tm_mobile) ?>
                                                </a>
                                            </div>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>

                                    <?php if("yes" == $settings['show_social'] || 'yes' == $settings['show_full_biograph'] ) : ?>
                                        <td class="dwl-table-data">
                                            <div class="dwl-table-icon-wraper">
                                                <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                                                    <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                                <?php endif; ?>
                                                <?php if(isset($settings['show_full_biograph']) && 'yes' == $settings['show_full_biograph']) : ?>
                                                    <div class="dwl-table-full-biograph">
                                                        <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>" class="dwl-table-button"><?php echo esc_attr_e( 'Full Biograph', 'wp-team-manager' )?></a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endif; ?>

                                </tr>

                            <?php

                        ?>
                            <?php endforeach; ?>
                    </tbody>   
                </table>
            </div>   
        <?php
    endif;
?>