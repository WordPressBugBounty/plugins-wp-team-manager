
<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)):

    $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   

    $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';

    $desktop_column = isset($settings['columns_desktop']) ? $settings['columns_desktop'] : (
        isset($settings['columns']) ? $settings['columns'] : '4');

    $tablet_column = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : 3;
    
    $mobile_column = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : 1;
    
        ?>
            <table>
                <thead>
                    <tr>
                        <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                            <th>Image</th>
                        <?php endif; ?>

                        <?php if('yes'== $settings['show_title']  ): ?>
                            <th>Name</th>
                        <?php endif; ?>

                        <?php if( 'yes'== $settings['show_sub_title'] ): ?>
                            <th>Designation</th>
                        <?php endif; ?>

                        <?php if( 'yes' === $show_shortBio ) : ?>
                            <th>Short Bio</th>
                        <?php endif; ?>

                        <?php if( isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info'] ) : ?>
                            <th>EMAIL</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    
                        foreach ($data as $key => $teamInfo):

                            $job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
                            $short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
                            $tm_email = get_post_meta($teamInfo->ID,'tm_email',true);?>
                            
                            <tr class="dwl-table-row">
                                <?php if("yes" == $settings['show_image'] || 'yes' == $settings['show_social'] ): ?>
                                    <td class="dwl-table-data">
                                        <?php if("yes" == $settings['show_image']): ?>
                                            <div class="dwl-table-img-wraper">
                                                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                                                    <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                </a>
                                            </div>
                                        <?php endif;?>

                                        <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
                                            <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
                                        <?php endif; ?>
                                    </td>
                                <?php endif;?>
                                <?php if('yes'== $settings['show_title']  ): ?>
                                    <td class="dwl-table-data">
                                        <h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
                                    </td>
                                <?php endif;?>

                                <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
                                    <td class="dwl-table-data">
                                        <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
                                    </td>
                                <?php endif;?>

                                <?php if( 'yes' === $show_shortBio ) : ?>
                                    <td class="dwl-table-data-short-bio">
                                        <div class="team-short-bio">
                                            <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                                                <?php echo esc_html( wp_trim_words( $short_bio, 20, '...' ) ); ?>
                                            <?php else: ?>
                                                <?php echo esc_html( wp_trim_words( get_the_content(null, false, $teamInfo->ID), 20, '...' ) ); ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>

                                <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info'] && isset($tm_email)) : ?>
                                    <td class="dwl-table-data">
                                        <div class="team-member-info">
                                            <a href="mailto:<?php echo esc_html($tm_email) ?>" target="_blank">
                                                <i class="fas fa-envelope"></i>
                                                <?php echo esc_html($tm_email) ?>
                                            </a>
                                        </div>
                                    </td>
                                <?php endif; ?>

                            </tr>

                        <?php

                    ?>
                        <?php endforeach; ?>
                <tbody>    
            </table>
        <?php
    endif;
?>