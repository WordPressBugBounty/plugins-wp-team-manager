<?php
use DWL\Wtm\Classes\Helper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if(!empty($data)){

	foreach ($data as $key => $teamInfo) {
		
		$job_title = get_post_meta( $teamInfo->ID, 'tm_jtitle', true );
		$short_bio = get_post_meta( $teamInfo->ID, 'tm_short_bio', true );
		$desktop_column = isset($settings['desktop_column']) ? $settings['desktop_column'] : (
		isset($settings['columns']) ? $settings['columns'] : '4');
		$tablet_column = isset($settings['tablet_column']) ? $settings['tablet_column'] : (
		isset($settings['columns']) ? $settings['columns'] : '3');
		$mobile_column = isset($settings['mobile_column']) ? $settings['mobile_column'] : (
		isset($settings['columns']) ? $settings['columns'] : '12');
		$bootstrap_class = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);

		?>
			<div <?php post_class("team-member-info-wrap ". esc_attr( $bootstrap_class )); ?>>

			<div class="team-member-info-content"> 
			<header>
				<?php if(!$disable_single_template): ?>
				<a href="<?php echo esc_url( get_the_permalink($teamInfo->ID) ); ?>">
				<?php endif;?>
				<?php if( 'yes' === $settings['show_image'] ): ?>
				<?php echo wp_kses_post( Helper::get_team_picture( $teamInfo->ID, $settings['show_image'], 'team-member-image dwl-box-shadow' ) ); ?>
				<?php endif; ?>
				<?php if(!$disable_single_template): ?>
				</a>
				<?php endif;?>
			</header>
			<div class="team-member-desc">
			
			<?php if('yes' === $settings['show_title']): ?>
			<h2 class="team-member-title"><?php echo esc_html( get_the_title($teamInfo->ID) ); ?></h2>
			<?php endif;?>
			<?php if(!empty( $job_title ) && 'yes' === $settings['show_sub_title']): ?>
				<h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
			<?php endif;?>

			<div class="team-short-bio">
			<?php if( !empty( $short_bio ) ): ?>
				<?php echo esc_html( $short_bio ); ?>
				<?php else: ?>
				<?php echo esc_html( wp_trim_words( get_the_content(null, false, $teamInfo->ID), 40, '...' ) ); ?>
			<?php endif; ?>
			</div>
			<?php if('yes' == $settings['show_other_info']) : ?>
			<?php echo wp_kses_post( Helper::get_team_other_infos( $teamInfo->ID ) ); ?>
			<?php endif; ?>
			<?php if('yes' == $settings['show_social']) : ?>
				<?php echo wp_kses_post( Helper::get_team_social_links($teamInfo->ID) ); ?>
			<?php endif; ?>
			
			</div>
			</div>
			</div>

<?php

	}

}