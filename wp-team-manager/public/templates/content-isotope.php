<?php
use DWL\Wtm\Classes\Helper;

  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

  wp_enqueue_script([
    'wtm-image-load-js',
    'wtm-isotope-js'
   ]);

  //Show isotop Filter
  ?>
  <div class="button-group filter-button-group">
  <button data-filter="*">show all</button>
  <button data-filter=".metal">metal</button>
  <button data-filter=".transition">transition</button>
  <button data-filter=".alkali, .alkaline-earth">alkali & alkaline-earth</button>
  <button data-filter=":not(.transition)">not transition</button>
  <button data-filter=".metal:not(.transition)">metal but not transition</button>
</div>

  <?php
  while ( $tm_loop->have_posts() ) :
  
      $tm_loop->the_post();
      
      $job_title = get_post_meta( $tm_loop->post->ID, 'tm_jtitle', true );
      $short_bio = get_post_meta( $tm_loop->post->ID, 'tm_short_bio', true );

      /**
       * @todo need fucntion to generate class based on column
       */
      $desktop_column = ($settings['desktop_column']) ?? ($settings['columns']) ?? '4';
      $tablet_column = ($settings['tablet_column']) ?? ($settings['columns']) ?? '3';
      $mobile_column = ($settings['mobile_column']) ?? ($settings['columns']) ?? '12';
      ?>
  
          <div <?php post_class('team-member-info-wrap wtm-col-'.esc_attr($mobile_column).' wtm-col-lg-'.esc_attr($tablet_column).' wtm-col-md-'.esc_attr( $desktop_column ).''); ?>>
          <div class="team-member-info-content"> 
          <header>
            <?php if(!$disable_single_template): ?>
           <a href="<?php echo esc_url( Helper::get_team_social_links($teamInfo->ID) ); ?>">
           <?php endif;?>
           <?php echo wp_kses_post( Helper::get_team_picture( $tm_loop->post->ID, $image_size, 'dwl-box-shadow' ) ); ?>
           <?php if(!$disable_single_template): ?>
           </a>
           <?php endif;?>
          </header>
          
          <div class="team-member-desc">
            <h2 class="team-member-title"><?php esc_html( the_title() ); ?></h2>
            <?php if( !empty( $job_title ) ): ?>
              <h4 class="team-position"><?php echo esc_html( $job_title ); ?></h4>
            <?php endif;?>
            <div class="team-short-bio">
            <?php if( !empty( $short_bio ) ): ?>
              <?php echo esc_html( $short_bio ); ?>
              <?php else: ?>
                <?php echo esc_html( wp_trim_words( get_the_content(), 40, '...' ) ); ?>
            <?php endif; ?>
            </div>
            <?php if('yes' == $settings['show_other_info']) : ?>
            <?php echo wp_kses_post( Helper::get_team_other_infos( $tm_loop->post->ID ) ); ?>
            <?php endif; ?>
            <?php if('yes' == $settings['show_social']) : ?>
              <?php echo wp_kses_post( Helper::get_team_social_links($tm_loop->post->ID) ); ?>
            <?php endif; ?>
            
          </div>
          </div>
          </div>
  
          <?php
  
  endwhile;