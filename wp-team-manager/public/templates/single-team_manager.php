<?php
use DWL\Wtm\Classes\Helper;

/**
 * The template for displaying a single Team Manager
 */

get_header();

/**
 * Enqueue scripts and styles for the Single Team Member page if Lightbox is enabled
 */
if (get_option('tm_single_team_lightbox') === 'True' && function_exists('tmwstm_fs') && tmwstm_fs()->is_paying_or_trial()) {
    wp_enqueue_script('wp-team-magnific-popup');
    wp_enqueue_script('wp-team-pro');
    wp_enqueue_style('wp-team-magnific-popup');
}

$tm_single_fields = (array) get_option('tm_single_fields', []); // Use default value directly

?>

<div id="primary" class="content-area dwl-team-wrapper dwl-team-single wtm-container single-style">
    <div id="main" class="wtm-row site-main" role="main">
        <article id="post-<?php the_ID(); ?>" <?php post_class('wtm-col-12'); ?>>
            <?php while (have_posts()) : the_post();
                $post_id   = absint(get_the_ID()); // Ensure post ID is an integer
                $job_title = esc_html(get_post_meta($post_id, 'tm_jtitle', true)); // Escape early
                $short_bio = wp_kses_post(get_post_meta($post_id, 'tm_short_bio', true)); 
                $long_bio = Helper::get_wysiwyg_output('tm_long_bio', $post_id);
            ?>
                <div class="entry-content wtm-row">
                    <div class="team-bio-image wtm-col-12 wtm-col-md-6">
                        <?php 
                        if (has_post_thumbnail()) {
                            the_post_thumbnail(get_option('team_image_size_change', 'medium')); 
                        }
                        ?>
                    </div>

                    <div class="wtm-col-12 wtm-col-md-6">
                        <?php the_title('<h2 class="single-team-member-title">', '</h2>'); ?>

                        <?php if (!empty($job_title) && !in_array('tm_jtitle', $tm_single_fields)) : ?>
                            <h3 class="team-position my-0"><?php echo $job_title; ?></h3>
                        <?php endif; ?>

                        <?php if (!empty($short_bio)) : ?>
                            <div class="team-short-bio">
                                <?php echo $short_bio; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (tmwstm_fs()->is_paying_or_trial()): ?>
                                <div class="wtm-progress-bar">
                                <?php
                                        if (class_exists('DWL_Wtm_Pro_Helper')) {

                                            echo DWL_Wtm_Pro_Helper::display_skills_output($team_member->ID);

                                        } ?>
                                </div>
                        <?php endif; ?>

                        <?php 
                        echo wp_kses_post(Helper::get_team_other_infos($post_id));
                        echo wp_kses_post(Helper::display_social_profile_output($post_id));
                        ?>
                    </div>

                    <?php if (!empty($long_bio)) : ?>
                        <div class="wtm-col-12 py-md-3 wp-team-manager-long-bio">
                            <?php echo $long_bio; ?>
                        </div>
                    <?php endif; ?>

                    <div class="wtm-col-12 py-md-3">
                        <?php the_content(); ?>
                    </div>

                    <?php echo wp_kses_post(Helper::get_image_gallery_output($post_id)); ?>

                </div>
            <?php endwhile; ?>
        </article>
    </div>
</div>

<?php get_footer(); ?>