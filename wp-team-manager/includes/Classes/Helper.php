<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class Helper {

    /**
     * Classes instatiation.
     *
     * @param array $classes Classes to init.
     *
     * @return void
     */
    public static function instances( array $classes ) {
        if ( empty( $classes ) ) {
            return;
        }

        foreach ( $classes as $class ) {
            $class::get_instance();
        }
    }

    public static function get_team_picture($post_id, $thumb_image_size,$class=""){

        $thumbnail_id = get_post_thumbnail_id( $post_id );

        if (isset($thumbnail_id)) {

            return wp_get_attachment_image($thumbnail_id,$thumb_image_size, "", array( "class" => $class ));

        }

    }

    /**
     * Deprecated Method
     */
    public static function get_team_social_links($post_id){

        $output = '';
        $social_size = ( false !== get_option('tm_social_size') ) ? get_option('tm_social_size') : 16;
        $link_window = ( false !== get_option('tm_link_new_window')  && 'True' == get_option('tm_link_new_window') ) ? 'target="_blank"' : '';

        $facebook = get_post_meta($post_id,'tm_flink',true);
        $twitter = get_post_meta($post_id,'tm_tlink',true);
        $linkedIn = get_post_meta($post_id,'tm_llink',true);
        $googleplus = get_post_meta($post_id,'tm_gplink',true);
        $dribbble = get_post_meta($post_id,'tm_dribbble',true);
        $youtube = get_post_meta($post_id,'tm_ylink',true);
        $vimeo = get_post_meta($post_id,'tm_vlink',true);
        $emailid = get_post_meta($post_id,'tm_emailid',true);

        $output .= '<div class="team-member-socials size-'.esc_attr($social_size).'">';

        if (!empty($facebook)) {
        $output .= '<a class="facebook-'.esc_attr($social_size).'" href="' .esc_url($facebook) . '" '.esc_attr($link_window).' title="'.__('Facebook','wp-team-manager').'"><i class="fab fa-facebook-f"></i></a>';
        }
        if (!empty($twitter)) {
        $output .= '<a class="twitter-'.esc_attr($social_size).'" href="' . esc_url($twitter). '" '.esc_attr($link_window).' title="'.__('Twitter','wp-team-manager').'"><i class="fab fa-twitter"></i></a>';
        }
        if (!empty($linkedIn)) {
        $output .= '<a class="linkedIn-'.esc_attr($social_size).'" href="' . esc_url($linkedIn). '" '.esc_attr($link_window).' title="'.__('LinkedIn','wp-team-manager').'"><i class="fab fa-linkedin"></i></a>';
        }
        if (!empty($googleplus)) {
        $output .= '<a class="googleplus-'.esc_attr($social_size).'" href="' . esc_url($googleplus). '" '.esc_attr($link_window).' title="'.__('Google Plus','wp-team-manager').'"><i class="fab fa-google-plus-g"></i></a>';
        }
        if (!empty($dribbble)) {
        $output .= '<a class="dribbble-'.esc_attr($social_size).'" href="' . esc_url($dribbble). '" '.esc_attr($link_window).' title="'.__('Dribbble','wp-team-manager').'"><i class="fab fa-dribbble-square"></i></a>';
        }        
        if (!empty($youtube)) {
        $output .= '<a class="youtube-'.esc_attr($social_size).'" href="' . esc_url($youtube). '" '.esc_attr($link_window).' title="'.__('Youtube','wp-team-manager').'"><i class="fab fa-youtube"></i></a>';
        }
        if (!empty($vimeo)) {
        $output .= '<a class="vimeo-'.esc_attr($social_size).'" href="' . esc_url($vimeo). '" '.esc_attr($link_window).' title="'.__('Vimeo','wp-team-manager').'"><i class="fab fa-vimeo"></i></a>';
        }
        if (!empty($emailid)) {
        $output .= '<a class="emailid-'.esc_attr($social_size).'" href="mailto:' .sanitize_email($emailid). '" title="'.__('Email','wp-team-manager').'"><i class="far fa-envelope"></i></a>';
        } 

        $output .= '</div>';

        return $output;

    }

    public static function display_social_profile_output( $post_id = 0 ) {
        $post_id           = $post_id ? $post_id : get_the_ID();
        $wptm_social_infos = get_post_meta( $post_id, 'wptm_social_group', true );
       
        $wptm_social_data  = !empty($wptm_social_infos) ? $wptm_social_infos : [];


        $social_size = !empty( get_option('tm_social_size') ) ? get_option('tm_social_size') : 16;
        $link_window = ( false !== get_option('tm_link_new_window')  && 'True' == get_option('tm_link_new_window') ) ? 'target="_blank"' : '';

        if( !isset( $wptm_social_data[0]['url'] ) && ( isset($wptm_social_data[0]['type']) &&  $wptm_social_data[0]['type'] == 'select_type') ){
            return false;
        }


        $output = '';
        $output .= '<div class="team-member-socials size-'.esc_attr($social_size).'">';
        ?>
        <?php 
            // Define an associative array mapping social media types to Font Awesome icons and titles
            $social_media_icons = array(
                'email'          => array('icon' => 'far fa-envelope', 'title'       => __('Email', 'wp-team-manager')),
                'facebook'       => array('icon' => 'fab fa-facebook-f', 'title'     => __('Facebook', 'wp-team-manager')),
                'twitter'        => array('icon' => 'fab fa-twitter', 'title'        => __('Twitter', 'wp-team-manager')),
                'linkedin'       => array('icon' => 'fab fa-linkedin', 'title'       => __('LinkedIn', 'wp-team-manager')),
                'googleplus'     => array('icon' => 'fab fa-google-plus-g', 'title'  => __('Google Plus', 'wp-team-manager')),
                'dribbble'       => array('icon' => 'fab fa-dribbble', 'title'       => __('Dribbble', 'wp-team-manager')),
                'youtube'        => array('icon' => 'fab fa-youtube', 'title'        => __('Youtube', 'wp-team-manager')),
                'vimeo'          => array('icon' => 'fab fa-vimeo', 'title'          => __('Vimeo', 'wp-team-manager')),
                'instagram'      => array('icon' => 'fab fa-instagram', 'title'      => __('Instagram', 'wp-team-manager')),
                'discord'        => array('icon' => 'fab fa-discord', 'title'        => __('Discord', 'wp-team-manager')),
                'tiktok'         => array('icon' => 'fab fa-tiktok', 'title'         => __('Tiktok', 'wp-team-manager')),
                'github'         => array('icon' => 'fab fa-github', 'title'         => __('Github', 'wp-team-manager')),
                'stack-overflow' => array('icon' => 'fab fa-stack-overflow', 'title' => __('Stack overflow', 'wp-team-manager')),
                'medium'         => array('icon' => 'fab fa-medium', 'title'         => __('Medium', 'wp-team-manager')),
                'telegram'       => array('icon' => 'fab fa-telegram', 'title'       => __('Telegram', 'wp-team-manager')),
                'pinterest'      => array('icon' => 'fab fa-pinterest', 'title'      => __('Pinterest', 'wp-team-manager')),
                'square-reddit'  => array('icon' => 'fab fa-reddit-square', 'title'  => __('Square reddit', 'wp-team-manager')),
                'tumblr'         => array('icon' => 'fab fa-tumblr', 'title'         => __('Tumblr', 'wp-team-manager')),
                'quora'          => array('icon' => 'fab fa-quora', 'title'          => __('Quora', 'wp-team-manager')),
                'snapchat'       => array('icon' => 'fab fa-snapchat', 'title'       => __('Snapchat', 'wp-team-manager')),
                'goodreads'      => array('icon' => 'fab fa-goodreads', 'title'      => __('Goodreads', 'wp-team-manager')),
                'twitch'         => array('icon' => 'fab fa-twitch', 'title'         => __('Twitch', 'wp-team-manager')),
            );

            foreach( $wptm_social_data as $data ) {
                if ( isset($data['type']) AND isset( $social_media_icons[$data['type']] ) ) {
                    $icon_class = $social_media_icons[$data['type']]['icon'];
                    $title = $social_media_icons[$data['type']]['title'];
                    $url  = !empty( $data['url'] ) ? $data['url'] : '';
                    if( isset($data['type']) AND 'email' == $data['type'] ) {
                        $output .= '<a class="'. esc_attr($data['type']) . '-' .esc_attr($social_size). '"  href="mailto:' .sanitize_email( $url ). '" ' . esc_attr($link_window) . ' title="' . esc_attr($title) . '"><i class="' . esc_attr($icon_class) . '"></i></a>';
                    }else{
                        $output .= '<a class="' . esc_attr($data['type']) . '-' . esc_attr($social_size) . '" href="' . esc_url( $url ) . '" ' . esc_attr($link_window) . ' title="' . esc_attr($title) . '"><i class="' . esc_attr($icon_class) . '"></i></a>';
                    }
                }
            }
        ?>
    <?php
        $output .= '</div>';
        return $output;
    
    }

    public static function get_team_other_infos($post_id){
        
        $tm_single_fields = get_option('tm_single_fields') 
        ? get_option('tm_single_fields') : 
        [];

        $output = '';

        $tm_mobile = get_post_meta($post_id,'tm_mobile',true);
        $tm_year_experience = get_post_meta($post_id,'tm_year_experience',true);
        $tm_email = get_post_meta($post_id,'tm_email',true);
        $telephone = get_post_meta($post_id,'tm_telephone',true);
        $location = get_post_meta($post_id,'tm_location',true);
        $web_url = get_post_meta($post_id,'tm_web_url',true);
        $vcard = get_post_meta($post_id,'tm_vcard',true);
        $output .= '<div class="team-member-other-info">';

        if (!empty($tm_mobile) AND !in_array('tm_mobile',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-mobile-alt"></i></i> <a href="tel://'.esc_html($tm_mobile).'">'.esc_html($tm_mobile).'</a></div>';
        }
        if (!empty($telephone) AND !in_array('tm_telephone',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-phone-alt"></i> <a href="tel://'.esc_html($telephone).'">'.esc_html($telephone).'</a></div>';
        }
        if (!empty($tm_year_experience) AND !in_array('tm_year_experience',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-history"></i>'.esc_html($tm_year_experience).'</div>';
        }
        if (!empty($location) AND !in_array('tm_location',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-map-marker"></i> '.esc_html($location).'</div>';
        }
        if (!empty($tm_email) AND !in_array('tm_email',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-envelope"></i><a href="mailto:'. esc_html($tm_email) .'" target="_blank">' . esc_html($tm_email) . '</a></div>';
        }
        if (!empty($web_url) AND !in_array('tm_web_url',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-link"></i> <a href="'. esc_url($web_url) .'" target="_blank">'.__('Bio','wp-team-manager').'</a></div>';
        }
        if (!empty($vcard) AND !in_array('tm_vcard',$tm_single_fields)) {
            $output .= '<div class="team-member-info"><i class="fas fa-download"></i> <a href="'.esc_url($vcard).'" target="_blank">'.__('Download CV','wp-team-manager').'</a></div>';
        }   
                                                    
        $output .= '</div>';

        return $output;

    }

    /**
	 * Get Post Pagination, Load more & Scroll markup
	 *
	 * @param $query
	 * @param $data
	 *
	 * @return false|string|void
	 */
	public static function get_pagination_markup( $query, $posts_per_page ) {

        $big = 999999999; // need an unlikely integer

		if ( $query->max_num_pages > 0 ) {
			$html = "<div class='wtm-pagination-wrap' data-total-pages='{$query->max_num_pages}' data-posts-per-page='{$posts_per_page}' data-type='pagination' >";   
            $html .= paginate_links( array(
                'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
                'format' => '?paged=%#%',
                'current' => max( 1, get_query_var('paged') ),
                'total' => $query->max_num_pages
            ) );
            $html .= "</div>";
			return $html;
		}

		return false;
	}


    /**
     * Get all post status
     *
     * @return boolean
     */
    public static function getPostStatus() {
        return [
            'publish'    => esc_html__( 'Publish', 'wp-team-manager' ),
            'pending'    => esc_html__( 'Pending', 'wp-team-manager' ),
            'draft'      => esc_html__( 'Draft', 'wp-team-manager' ),
            'auto-draft' => esc_html__( 'Auto draft', 'wp-team-manager' ),
            'future'     => esc_html__( 'Future', 'wp-team-manager' ),
            'private'    => esc_html__( 'Private', 'wp-team-manager' ),
            'inherit'    => esc_html__( 'Inherit', 'wp-team-manager' ),
            'trash'      => esc_html__( 'Trash', 'wp-team-manager' ),
        ];
    }

    /**
     * Get all Order By
     *
     * @return boolean
     */
    public static function getOrderBy() {
        return [
            'date'          => esc_html__( 'Date', 'wp-team-manager' ),
            'ID'            => esc_html__( 'Order by post ID', 'wp-team-manager' ),
            'author'        => esc_html__( 'Author', 'wp-team-manager' ),
            'title'         => esc_html__( 'Title', 'wp-team-manager' ),
            'modified'      => esc_html__( 'Last modified date', 'wp-team-manager' ),
            'parent'        => esc_html__( 'Post parent ID', 'wp-team-manager' ),
            'comment_count' => esc_html__( 'Number of comments', 'wp-team-manager' ),
            'menu_order'    => esc_html__( 'Menu order', 'wp-team-manager' ),
        ];
    }

    /**
     * Get bootstrap layout class
     *
     * @return string
     */

    public static function get_grid_layout_bootstrap_class( $desktop = '1' , $tablet = '1', $mobile = '1' ){

        $desktop_class = '';
        $tablet_class = '';
        $mobile_class = '';

        $desktop_layouts = [
            '1' => 'lg-12',
            '2' => 'lg-6',
            '3' => 'lg-4',
            '4' => 'lg-3'
        ];

        $tablet_layouts = [
            '1' => 'md-12',
            '2' => 'md-6',
            '3' => 'md-4',
            '4' => 'md-3'
        ];

        $mobile_layouts = [
            '1' => '12',
            '2' => '6',
            '3' => '4',
            '4' => '3'
        ];

        if( array_key_exists( $desktop, $desktop_layouts ) ){
            $desktop_class = $desktop_layouts[$desktop];
        }

        if( array_key_exists( $tablet, $tablet_layouts ) ){
            $tablet_class = $tablet_layouts[$tablet];
        }

        if( array_key_exists( $mobile, $mobile_layouts ) ){
            $mobile_class = $mobile_layouts[$mobile];
        }

        return "wtm-col-{$desktop_class} wtm-col-{$tablet_class} wtm-col-{$mobile_class}";

    }

    /**
	 * Render.
	 *
	 * @param string  $view_name View name.
	 * @param array   $args View args.
	 * @param boolean $return View return.
	 *
	 * @return string|void
	 */
	public static function render( $view_name, $args = [], $return = false ) {
		$path = str_replace( '.', '/', $view_name );
        $template_file = TM_PATH . '/public/templates/' . $path.'.php';

        if ( $args ) {
			extract( $args );
		}

		if ( ! file_exists( $template_file ) ) {
			return;
		}

		if ( $return ) {
			ob_start();
			include $template_file;

			return ob_get_clean();
		} else {
			include $template_file;
		}
	}
    
    public static function generatorShortcodeCss( $scID ) {
		global $wp_filesystem;
		// Initialize the WP filesystem, no more using 'file-put-contents' function
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}
        
		$upload_dir     = wp_upload_dir();
		$upload_basedir = $upload_dir['basedir'];
		$cssFile        = $upload_basedir . '/wp-team-manager/team.css';

		if ( $css = self::render( 'style', compact( 'scID' ), true ) ) {
			$css = sprintf( '/*wp_team-%2$d-start*/%1$s/*wp_team-%2$d-end*/', $css, $scID );
			if ( file_exists( $cssFile ) && ( $oldCss = $wp_filesystem->get_contents( $cssFile ) ) ) {
				if ( strpos( $oldCss, '/*wp_team-' . $scID . '-start' ) !== false ) {
					$oldCss = preg_replace( '/\/\*wp_team-' . $scID . '-start[\s\S]+?wp_team-' . $scID . '-end\*\//', '', $oldCss );
					$oldCss = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $oldCss );
				}
				$css = $oldCss . $css;
			} elseif ( ! file_exists( $cssFile ) ) {
				$upload_basedir_trailingslashit = trailingslashit( $upload_basedir );
				$wp_filesystem->mkdir( $upload_basedir_trailingslashit . 'wp-team-manager' );
			}
			if ( ! $wp_filesystem->put_contents( $cssFile, $css ) ) {
				error_log( print_r( 'Team: Error Generated css file ', true ) );
			}
		}
	}

    /**
     * Generate Shortcode for remove css
     *
     * @param integer $scID
     *
     * @return void
    */
    public static function removeGeneratorShortcodeCss( $scID ) {
        // Load the WordPress filesystem API.
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Initialize the filesystem.
        if ( ! WP_Filesystem() ) {
            // Failed to initialize the filesystem, handle error here.
            return;
        }

        global $wp_filesystem;

        $upload_dir     = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir'];
        $cssFile        = $upload_basedir . '/wp-team-manager/team.css';
        
        if( file_exists( $cssFile ) && $response = wp_remote_get($cssFile) ){
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $oldCss = wp_remote_retrieve_body($response);
            
                if ($oldCss !== false && strpos( $oldCss, '/*wp_team-' . $scID . '-start') !== false) {
                    $css = preg_replace( '/\/\*wp_team-' . $scID . '-start[\s\S]+?wp_team-' . $scID . '-end\*\//', '', $oldCss);
                    $css = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $css);
                    $wp_filesystem->put_contents( $cssFile, $css, FS_CHMOD_FILE );
                }
            } else {
                
                $error_message = is_wp_error($response) ? $response->get_error_message() : 'HTTP request failed';
            }
        }
        
    }

    public static function get_wysiwyg_output( $meta_key, $post_id = 0 ) {
        global $wp_embed;
        $post_id = $post_id ? $post_id : get_the_ID();
        $content = get_post_meta( $post_id, $meta_key, 1 );

        if($content){
            $content = $wp_embed->autoembed( $content );
            $content = $wp_embed->run_shortcode( $content );
            $content = wpautop( $content );
            $content = do_shortcode( $content );
        }
        return $content;
    }

    public static function get_image_gallery_output( $post_id = 0 ) {
        $post_id           = $post_id ? $post_id : get_the_ID();
        $team_gallery_data = get_post_meta( $post_id, 'wptm_cm2_gallery_image' );

        if( is_array($team_gallery_data) AND  empty($team_gallery_data) ){
            return false;
        }
        
        ?>
            <div class="wtm-image-gallery-wrapper">
                <?php foreach( $team_gallery_data[0] as $attachment_id => $attachment_url ): ?>
                    <div class="wtm-single-image">
                        <a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>">
                            <?php echo wp_get_attachment_image( $attachment_id ); ?>
                        </a>
                    </div>
                <?php endforeach;?>
            </div>
       <?php
    }

    public static function generate_single_fields(){

        $tm_single_fields =  get_option('tm_single_fields')
        ? get_option('tm_single_fields') : 
        [];
        $fields = array(
            'tm_email' => 'Email',
            'tm_jtitle' => 'Job Title',
            'tm_telephone' => 'Telephone (Office)',
            'tm_mobile' => 'Mobile (Personal)',
            'tm_location' => 'Location',
            'tm_year_experience' => 'Years of Experience',
            'tm_web_url' => 'Web URL',
            'tm_vcard' => 'vCard',
        );
        
        foreach ($fields as $key => $value) {

            printf(
                '<div class="tm-nice-checkbox-wrapper">
                <input type="checkbox" class="tm-checkbox" id="tm_%s" name="tm_single_fields[]" value="%s" %s/>
                <label for="tm_%s" class="toggle"><span></span></label>
                <span>%s</span>  
                </div><!--.tm-nice-checkbox-wrapper-->',
                esc_attr( $key ) ,
                esc_attr( $key ),
                in_array($key,$tm_single_fields) ? 'checked' : '',
                esc_attr( $key ),
                esc_html( $value ) ,
                
            );

        }

    }

    public static function team_social_icon_migration( $post_id ) {

        $post_id     = $post_id ? $post_id: get_the_ID();
        $entries     = get_post_meta( $post_id,  'wptm_social_group', false );
        $facebook    = get_post_meta( $post_id,  'tm_flink', true );
        $twitter     = get_post_meta( $post_id,  'tm_tlink', true );
        $link_in     = get_post_meta( $post_id,  'tm_llink', true );
        $google_plus = get_post_meta( $post_id,  'tm_gplink', true );
        $dribble     = get_post_meta( $post_id,  'tm_dribbble', true );
        $youtube     = get_post_meta( $post_id,  'tm_ylink', true );
        $vimeo       = get_post_meta( $post_id,  'tm_vlink', true );
        $email       = get_post_meta( $post_id,  'tm_emailid', true );
    
        if( $facebook ) {
            array_push($entries, [
                'type' => 'facebook',
                'url' => $facebook
            ]);
        }
    
        if( $twitter ) {
            array_push($entries, [
                'type' => 'twitter',
                'url' => $twitter
            ]);
        }
    
        if( $link_in ) {
            array_push($entries, [
                'type' => 'linkedin',
                'url' => $link_in
            ]);
        }
    
        if( $google_plus ) {
            array_push($entries, [
                'type' => 'googleplus',
                'url' => $google_plus
            ]);
        }
    
        if( $dribble ) {
            array_push($entries, [
                'type' => 'dribbble',
                'url' => $dribble
            ]);
        }
    
        if( $youtube ) {
            array_push($entries, [
                'type' => 'youtube',
                'url' => $youtube
            ]);
        }
    
        if( $vimeo ) {
            array_push($entries, [
                'type' => 'vimeo',
                'url' => $vimeo
            ]);
        }
    
        if( $email ) {
            array_push($entries, [
                'type' => 'email',
                'url' => $email
            ]);
        }
    
        update_post_meta( $post_id, 'wptm_social_group', $entries );
        
    }

    /**
	 * Custom Template locator.
	 *
	 * @param  mixed $template_name template name.
	 * @param  mixed $template_path template path.
	 * @param  mixed $default_path default path.
	 * @return string
	 */
	public static function wtm_locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = 'public/templates';
		}
		if ( ! $default_path ) {
			$default_path = TM_PATH . '/public/templates/';
		}
		$template = locate_template( trailingslashit( $template_path ) . $template_name );
		// Get default template.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}
		// Return what we found.
		return $template;
	}

    public static function get_team_data($args){
        $tmQuery = new \WP_Query( $args );
        return ($tmQuery->posts) ? $tmQuery->posts : [];
    }

    /**
     * Renders the Elementor layout based on the given layout, data, and settings.
     *
     * @param string $layout The name of the layout to render.
     * @param array $data The data to pass to the layout template.
     * @param array $settings The settings for the layout.
     * @throws None
     * @return void
     */
    public static function renderElementorLayout(string $layout, array $data, array $settings): void
    {
        $styleTypeKey = "{$layout}_style_type";
        $styleType = stripslashes($settings[$styleTypeKey]);
        $path = stripslashes(TM_PATH . '/public/templates/elementor/layouts/' . $layout . '/');
        $templateName = sanitize_file_name( $styleType . '.php' );

        //allowed file type
        $allowedFileTypes = [
            'php'
        ];
        
        $ext = pathinfo($path . $templateName, PATHINFO_EXTENSION);

        if (in_array($ext, $allowedFileTypes)) {

            if (file_exists($path . $templateName)) {

                include self::locateTemplate($templateName, '', $path);
    
            }
        }

    }
    

       /**
     * Renders the Elementor layout based on the given layout, data, and settings.
     *
     * @param string $layout The name of the layout to render.
     * @param array $data The data to pass to the layout template.
     * @param array $settings The settings for the layout.
     * @throws None
     * @return void
     */
    public static function renderTeamLayout(string $layout, array $data, string $styleType , $settings = []): void
    {
        $styleType = stripslashes($styleType);
        $path = stripslashes(TM_PATH . '/public/templates/layouts/' . $layout . '/');
        $templateName = sanitize_file_name( $styleType . '.php' );

        //allowed file type
        $allowedFileTypes = [
            'php'
        ];
        
        $ext = pathinfo($path . $templateName, PATHINFO_EXTENSION);

        if (in_array($ext, $allowedFileTypes)) {

            if (file_exists($path . $templateName)) {

                include self::locateTemplate($templateName, '', $path);
    
            }
        }

    }

    /**
     * Locates a template file based on the given template name, template path, and default path.
     *
     * @param string $templateName The name of the template file to locate.
     * @param string $templatePath The path to search for the template file. Defaults to 'public/templates'.
     * @param string $defaultPath The default path to use if the template file is not found in the template path. Defaults to TM_PATH . '/public/templates/'.
     * @return string The path to the located template file, or the default path if the template file is not found.
     */
    private static function locateTemplate(string $templateName, string $templatePath = '', string $defaultPath = ''): string
    {
        $templatePath = $templatePath ?: 'public/templates';
        $defaultPath = $defaultPath ?: TM_PATH . '/public/templates/';
        $template = locate_template(trailingslashit($templatePath) . $templateName);
        return $template ?: "{$defaultPath}{$templateName}";
    }

    public static function show_html_output($layout = 'grid', $data = [], $settings = []){
     
        switch ($layout) {
            case 'grid':
                include self::wtm_locate_template('content-grid.php');
                break;
            
            case 'list':
                include self::wtm_locate_template('content-list.php');
                break;
            
            case 'slider':
                include self::wtm_locate_template('content-slider.php');
                break;

            default:
                include self::wtm_locate_template('content-grid.php');
                break;
        }

    }
    

}