<?php 
    use DWL\Wtm\Classes\Helper;
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    if( tmwstm_fs()->is_paying_or_trial() ){
        if(!empty($data['posts'])):

            $image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   
            $show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';
            $actual_taxonomy = ''; 
            ?>
                <div class="dwl-team-wrapper dwl-team-isotope-container">
                    <?php if ( ! empty( $settings['isotope_filter_action'] ) ) : ?>
                        <div class="dwl-team-isotope-filter">
                            <button class="dwl-team-filter-button active" data-filter="*"><?php esc_html_e( 'All', 'wp-team-manager' ); ?></button>
                            <?php if ( ! empty( $data['posts'] ) ) : ?>
                                <?php foreach ( $settings['team_groups'] as $group ) : ?>
                                    <?php $actual_taxonomy = $group->taxonomy; ?>
                                    <button class="dwl-team-filter-button" data-filter=".group-<?php echo esc_attr( $group->term_id ); ?>"><?php echo esc_html( $group->name ); ?></button>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
        
                    <div class="wtm-isotope-grid wtm-row">
                        <?php 
                        
                        unset($settings['team_groups']); 
        
                        foreach ( $data['posts'] as $key => $team_member ) :
        
                            $job_title = get_post_meta( $team_member->ID, 'tm_jtitle', true );
                            $short_bio = get_post_meta( $team_member->ID, 'tm_short_bio', true );
        
                            ?>
                            <div class="wtm-isotope-item team-member-info-wrap <?php echo esc_attr( $settings['bootstrap_class'] ); ?> <?php 
                                // Assign group classes based on taxonomy terms
                                $terms = get_the_terms( $team_member->ID, $actual_taxonomy );
                                if ( ! empty( $terms ) ) {
                                    foreach ( $terms as $term ) {
                                        echo 'group-' . esc_attr( $term->term_id ) . ' ';
                                    }
                                }
                            ?>">
                                <div class="team-member team-member-style-1">
                                    <div class="team-member-info-content">
                                        <?php if( "yes" == $settings['show_image'] ): ?>
                                            <header>
                                                <a href="<?php echo esc_url( get_the_permalink($team_member->ID) ); ?>">
                                                    <?php echo wp_kses_post( Helper::get_team_picture( $team_member->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                                                </a>
                                            </header>
                                        <?php endif; ?>
                                        <div class="team-member-desc">
                                            <?php if ( ! empty(  $settings['isotope_name_switcher'] ) ) : ?><a href="<?php echo esc_url( get_the_permalink($team_member->ID) ); ?>">
                                                <h2 class="team-member-title"><?php echo esc_html( get_the_title($team_member->ID) ); ?></h2>
                                            </a>
                                                
                                            <?php endif; ?>
                                            <?php if ( ! empty(  $settings['isotope_sub_title'] ) ) : ?>
                                                <p class="team-position"><?php echo esc_html( $job_title ); ?></p>
                                            <?php endif; ?>
                                            <?php if ( ! empty(  $settings['isotope_bio_switcher'] ) ) : ?>
                                                <div class="team-short-bio">
                                        
                                                    <?php echo esc_html( $short_bio ); ?>
                                            
                                                    <?php echo esc_html( wp_trim_words( get_the_content(null, false, $team_member->ID), 40, '...' ) ); ?>
                                                
                                                </div>
                                            <?php endif; ?>
                                            <?php if('yes' == $settings['show_other_info']) : ?>
                                                <?php echo wp_kses_post( Helper::get_team_other_infos( $team_member->ID ) ); ?>
                                            <?php endif; ?>
                                            <?php if('yes' == $settings['show_read_more']) : ?>
                                                <div class="wtm-read-more-wrap">
                                                    <a href="<?php echo esc_url( get_the_permalink($team_member->ID) ); ?>" class="wtm-read-more"><?php esc_html_e( 'Read More', 'wp-team-manager' )?></a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if('yes' == $settings['isotope_social_media_switch']) : ?>
                                                <?php echo wp_kses_post( Helper::display_social_profile_output($team_member->ID) ); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
        
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
        
            <?php
        endif;
    }

?>

