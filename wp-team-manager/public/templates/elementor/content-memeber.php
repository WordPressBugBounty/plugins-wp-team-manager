<?php 
use DWL\Wtm\Classes\Helper;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$image_size = isset( $settings['image_size'] ) ? $settings['image_size'] : 'thumbnail';   
$job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
$short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
$show_shortBio = !empty( $settings['team_show_short_bio'] ) ? $settings['team_show_short_bio'] : '';
?>
<div class="team-member-info-content"> 
    <header>
            <?php if("yes" == $settings['show_image']): ?>
                <a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
                    <?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $image_size, 'dwl-box-shadow' ) ); ?>
                </a>
            <?php endif;?>
    </header>
    <div class="team-member-desc">
        <?php if('yes'== $settings['show_title']  ): ?>
            <h2 class="team-member-title"><?php echo wp_kses_post( get_the_title($teamInfo->ID) ); ?></h2>
        <?php endif;?>
        <?php if(!empty( $job_title ) && 'yes'== $settings['show_sub_title']  ): ?>
            <h4 class="team-position"><?php echo wp_kses_post( $job_title ); ?></h4>
        <?php endif;?>

        <?php if( 'yes' === $show_shortBio ) : ?>
        <div class="team-short-bio">
            <?php if( !empty( $short_bio ) && 'yes'== $settings['team_show_short_bio'] ): ?>
                <?php echo wp_kses_post( $short_bio ); ?>
            <?php else: ?>
                <?php echo wp_kses_post( wp_trim_words( get_the_content(null, false,$teamInfo->ID), 40, '...' ) ); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($settings['show_other_info']) AND 'yes' == $settings['show_other_info']) : ?>
            <?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
        <?php endif; ?>

        <?php if(isset($settings['show_social']) && 'yes' == $settings['show_social']) : ?>
            <?php echo wp_kses_post( Helper::display_social_profile_output($teamInfo->ID) ); ?>
        <?php endif; ?>
        
    </div>
</div>