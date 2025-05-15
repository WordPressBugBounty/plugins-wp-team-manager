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

    /**
     * Retrieves the team member's picture as an HTML image element.
     *
     * @param int $post_id The ID of the post for which the picture is being retrieved.
     * @param string $thumb_image_size The size of the thumbnail image to retrieve.
     * @param string $class Optional. Additional CSS class(es) to apply to the image. Default is an empty string.
     *
     * @return string|null The HTML image element or null if no thumbnail ID is found.
     */
    public static function get_team_picture($post_id, $thumb_image_size = 'thumbnail', $class = '') {
        // Ensure a valid post ID
        $post_id = intval($post_id);
        
        // Get the thumbnail ID once
        $thumbnail_id = get_post_thumbnail_id($post_id);
    
        // Return default image if no thumbnail is found
        if (!$thumbnail_id) {
            return ''; // Or return a default placeholder image
        }
    
        // Return the formatted image with proper escaping for class attributes
        return apply_filters('wp_team_manager_team_picture_html', wp_get_attachment_image($thumbnail_id, $thumb_image_size, false, ["class" => esc_attr($class)]), $post_id, $thumb_image_size, $class);
    }


    /**
     * @todo Need to remove
     * Retrieves the team member's social media links as an HTML structure.
     *
     * This function retrieves the social media links associated with a team member
     * and generates an HTML structure containing anchor tags for each link. The
     * HTML structure includes a wrapper element with a class attribute specifying
     * the size of the links. Each anchor tag also includes a class attribute specifying
     * the social media network and its size.
     *
     * @param int $post_id The ID of the post for which the social media links are being retrieved.
     *
     * @return string The HTML structure containing the social media links.
     */
    public static function get_team_social_links($post_id) {
        // Retrieve settings once and cache them
        static $social_size = null;
        static $link_window = null;
    
        if (is_null($social_size)) {
            $social_size = intval(get_option('tm_social_size', 16));
        }
    
        if (is_null($link_window)) {
            $link_window = (get_option('tm_link_new_window') === 'True') ? 'target="_blank"' : '';
        }
    
        // Fetch all social links in a single call to reduce DB queries
        $meta = get_post_meta($post_id);
    
        // Define supported social networks with their metadata keys and icons
        $social_links = [
            'facebook'    => ['key' => 'tm_flink', 'icon' => 'fab fa-facebook-f', 'title' => __('Facebook', 'wp-team-manager')],
            'twitter'     => ['key' => 'tm_tlink', 'icon' => 'fab fa-twitter', 'title' => __('Twitter', 'wp-team-manager')],
            'linkedin'    => ['key' => 'tm_llink', 'icon' => 'fab fa-linkedin', 'title' => __('LinkedIn', 'wp-team-manager')],
            'googleplus'  => ['key' => 'tm_gplink', 'icon' => 'fab fa-google-plus-g', 'title' => __('Google Plus', 'wp-team-manager')],
            'dribbble'    => ['key' => 'tm_dribbble', 'icon' => 'fab fa-dribbble-square', 'title' => __('Dribbble', 'wp-team-manager')],
            'youtube'     => ['key' => 'tm_ylink', 'icon' => 'fab fa-youtube', 'title' => __('YouTube', 'wp-team-manager')],
            'vimeo'       => ['key' => 'tm_vlink', 'icon' => 'fab fa-vimeo', 'title' => __('Vimeo', 'wp-team-manager')],
            'email'       => ['key' => 'tm_emailid', 'icon' => 'far fa-envelope', 'title' => __('Email', 'wp-team-manager')],
        ];
    
        // Start output buffering
        ob_start();
    
        echo '<div class="team-member-socials size-' . esc_attr($social_size) . '">';
        do_action('wp_team_manager_before_social_links', $post_id);
        $social_links = apply_filters('wp_team_manager_social_links', $social_links, $post_id);
    
        foreach ($social_links as $network => $data) {
            // Get the social URL from metadata
            $value = isset($meta[$data['key']][0]) ? trim($meta[$data['key']][0]) : '';
            if (!empty($value)) {
                $href = ($network === 'email') ? 'mailto:' . sanitize_email($value) : esc_url($value);
                echo '<a class="' . esc_attr($network . '-' . $social_size) . '" href="' . $href . '" ' . esc_attr($link_window) . ' title="' . esc_attr($data['title']) . '">';
                echo '<i class="' . esc_attr($data['icon']) . '"></i></a>';
            }
        }
    
        echo '</div>';
        do_action('wp_team_manager_after_social_links', $post_id);
    
        return ob_get_clean();
    }

    /**
     * Displays the social media profile output.
     *
     * This function retrieves the social media data associated with a team member
     * and then iterates over the retrieved data to generate a set of HTML
     * elements representing the social media profiles.
     *
     * The generated HTML includes a wrapper, a set of labels, and a set of
     * anchor tags. The labels are the social media types, and the anchor tags
     * are styled to represent the social media icons.
     *
     * @param int $post_id The team member post ID.
     *
     * @return string The social media profile output.
     */
    public static function display_social_profile_output($post_id = 0) {
        // Ensure a valid post ID is retrieved
        $post_id = $post_id ? intval($post_id) : get_the_ID();
    
        // Retrieve and cache social settings
        static $social_size = null;
        static $link_window = null;
    
        if (is_null($social_size)) {
            $social_size = intval(get_option('tm_social_size', 16));
        }
    
        if (is_null($link_window)) {
            $link_window = (get_option('tm_link_new_window') === 'True') ? 'target="_blank"' : '';
        }
    
        // Fetch all metadata at once (reducing database queries)
        $post_meta = get_post_custom($post_id);
        $wptm_social_data = isset($post_meta['wptm_social_group'][0]) ? maybe_unserialize($post_meta['wptm_social_group'][0]) : [];
    
        // Return early if no social data exists
        if (empty($wptm_social_data) || !is_array($wptm_social_data)) {
            return '';
        }
    
        // Define social media mappings (Font Awesome classes)
        $social_media_icons = [
            'email'          => ['icon' => 'far fa-envelope', 'title' => __('Email', 'wp-team-manager')],
            'facebook'       => ['icon' => 'fab fa-facebook-f', 'title' => __('Facebook', 'wp-team-manager')],
            'twitter'        => ['icon' => 'fab fa-twitter', 'title' => __('Twitter', 'wp-team-manager')],
            'linkedin'       => ['icon' => 'fab fa-linkedin', 'title' => __('LinkedIn', 'wp-team-manager')],
            'googleplus'     => ['icon' => 'fab fa-google-plus-g', 'title' => __('Google Plus', 'wp-team-manager')],
            'dribbble'       => ['icon' => 'fab fa-dribbble', 'title' => __('Dribbble', 'wp-team-manager')],
            'youtube'        => ['icon' => 'fab fa-youtube', 'title' => __('YouTube', 'wp-team-manager')],
            'vimeo'          => ['icon' => 'fab fa-vimeo', 'title' => __('Vimeo', 'wp-team-manager')],
            'instagram'      => ['icon' => 'fab fa-instagram', 'title' => __('Instagram', 'wp-team-manager')],
            'discord'        => ['icon' => 'fab fa-discord', 'title' => __('Discord', 'wp-team-manager')],
            'tiktok'         => ['icon' => 'fab fa-tiktok', 'title' => __('TikTok', 'wp-team-manager')],
            'github'         => ['icon' => 'fab fa-github', 'title' => __('GitHub', 'wp-team-manager')],
            'stack-overflow' => ['icon' => 'fab fa-stack-overflow', 'title' => __('Stack Overflow', 'wp-team-manager')],
            'medium'         => ['icon' => 'fab fa-medium', 'title' => __('Medium', 'wp-team-manager')],
            'telegram'       => ['icon' => 'fab fa-telegram', 'title' => __('Telegram', 'wp-team-manager')],
            'pinterest'      => ['icon' => 'fab fa-pinterest', 'title' => __('Pinterest', 'wp-team-manager')],
            'square-reddit'  => ['icon' => 'fab fa-reddit-square', 'title' => __('Reddit', 'wp-team-manager')],
            'tumblr'         => ['icon' => 'fab fa-tumblr', 'title' => __('Tumblr', 'wp-team-manager')],
            'quora'          => ['icon' => 'fab fa-quora', 'title' => __('Quora', 'wp-team-manager')],
            'snapchat'       => ['icon' => 'fab fa-snapchat', 'title' => __('Snapchat', 'wp-team-manager')],
            'goodreads'      => ['icon' => 'fab fa-goodreads', 'title' => __('Goodreads', 'wp-team-manager')],
            'twitch'         => ['icon' => 'fab fa-twitch', 'title' => __('Twitch', 'wp-team-manager')],
        ];
    
        // Start output buffering for better performance
        ob_start();
        ?>
        <div class="team-member-socials size-<?php echo esc_attr($social_size); ?>">
            <?php
            foreach ($wptm_social_data as $data) {
                if (!isset($data['type'], $data['url']) || !isset($social_media_icons[$data['type']])) {
                    continue;
                }
    
                $type  = sanitize_key($data['type']);
                $icon  = esc_attr($social_media_icons[$type]['icon']);
                $title = esc_attr($social_media_icons[$type]['title']);
                $url   = ($type === 'email') ? 'mailto:' . sanitize_email($data['url']) : esc_url($data['url']);
    
                ?>
                <a class="<?php echo esc_attr($type . '-' . $social_size); ?>" href="<?php echo $url; ?>" <?php echo $link_window; ?> title="<?php echo $title; ?>">
                    <i class="<?php echo $icon; ?>"></i>
                </a>
                <?php
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }


        /**
         * Retrieves additional information about a team member and formats it into HTML.
         *
         * This function fetches metadata for a specified team member, including fields
         * like mobile, experience, email, and more. It sanitizes the data and generates
         * an HTML structure displaying this information. The output can include icons,
         * text, and links based on the metadata and user settings.
         *
         * @param int $post_id The ID of the team member post.
         *
         * @return string The HTML representation of the team member's additional information.
         */
        
        public static function get_team_other_infos($post_id, $tm_single_fields = []) {
            
            // Get custom label settings
            $custom_labels = get_option('tm_custom_labels', []);
            $web_btn_text = isset($custom_labels['tm_web_url']) ? $custom_labels['tm_web_url'] : 'Bio';
            $vcard_btn_text = isset($custom_labels['tm_vcard']) ? $custom_labels['tm_vcard'] : 'Download CV';
            
            // Fetch all metadata for the post at once
            $meta = get_post_meta($post_id);
            
            // Define all required fields with safe sanitization
            $fields = [
                'tm_mobile'          => !empty($meta['tm_mobile'][0]) ? sanitize_text_field($meta['tm_mobile'][0]) : '',
                'tm_year_experience' => !empty($meta['tm_year_experience'][0]) ? sanitize_text_field($meta['tm_year_experience'][0]) : '',
                'tm_email'           => !empty($meta['tm_email'][0]) ? sanitize_email($meta['tm_email'][0]) : '',
                'tm_telephone'       => !empty($meta['tm_telephone'][0]) ? sanitize_text_field($meta['tm_telephone'][0]) : '',
                'tm_location'        => !empty($meta['tm_location'][0]) ? sanitize_text_field($meta['tm_location'][0]) : '',
                'tm_web_url'         => !empty($meta['tm_web_url'][0]) ? esc_url($meta['tm_web_url'][0]) : '',
                'tm_vcard'           => !empty($meta['tm_vcard'][0]) ? esc_url($meta['tm_vcard'][0]) : '',
            ];
            
            // Return early if no fields have values
            if (empty(array_filter($fields))) {
                return '';
            }
            
            $output = '<div class="team-member-other-info">';
            
            // Define field mappings for icons and text
            $field_mappings = [
                'tm_mobile'          => ['icon' => 'fas fa-mobile-alt', 'prefix' => 'tel:', 'is_link' => true],
                'tm_telephone'       => ['icon' => 'fas fa-phone-alt', 'prefix' => 'tel:', 'is_link' => true],
                'tm_year_experience' => ['icon' => 'fas fa-history', 'is_link' => false],
                'tm_location'        => ['icon' => 'fas fa-map-marker', 'is_link' => false],
                'tm_email'           => ['icon' => 'fas fa-envelope', 'prefix' => 'mailto:', 'is_link' => true],
                'tm_web_url'         => ['icon' => 'fas fa-link', 'prefix' => '', 'is_link' => true, 'link_text' => $web_btn_text],
                'tm_vcard'           => ['icon' => 'fas fa-download', 'prefix' => '', 'is_link' => true, 'link_text' => $vcard_btn_text],
            ];
        
            
            // Intersect the selected fields with field mappings
            if(!empty($tm_single_fields && is_array($tm_single_fields) && tmwstm_fs()->is_paying_or_trial())){
                $tm_selected_fields = array_intersect_key($field_mappings, array_flip($tm_single_fields));
            }else{
                $tm_selected_fields = apply_filters('wp_team_manager_other_info_fields', $field_mappings, $fields, $post_id); 
            }
        
            
            // Allow filtering
            $field_mappings = apply_filters('wp_team_manager_other_info_fields', $tm_selected_fields, $fields, $post_id);
        
            // Generate HTML with filtering logic
            foreach ($field_mappings as $key => $info) {
                // Apply filter only on singular team pages
                if (is_singular('team_manager') && in_array($key, $tm_single_fields)) {
                    continue; // Skip hidden fields on team single pages
                }
        
                if (!empty($fields[$key])) {
                    $output .= '<div class="team-member-info">';
                    
                    // Ensure the icon class is safe
                    if (!empty($info['icon'])) {
                        $output .= '<i class="' . esc_attr($info['icon']) . '"></i> ';
                    }
        
                    // Properly escape and validate link
                    if (!empty($info['is_link']) && isset($info['prefix'])) {
                        $url = esc_url($info['prefix'] . sanitize_text_field($fields[$key]));
                        $text = isset($info['link_text']) ? esc_html($info['link_text']) : esc_html($fields[$key]);
        
                        // Validate URL before output
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $output .= '<a href="' . $url . '" target="_blank" rel="noopener noreferrer"><span>' . $text . '</span></a>';
                        } else {
                            $output .= '<span>' . $text . '</span>'; // If URL is invalid, display text only
                        }
                    } else {
                        $output .= '<span>' . esc_html($fields[$key]) . '</span>'; // Wrap plain text in span
                    }
        
                    $output .= '</div>';
                }
            }
        
            $output .= '</div>';
            
            $output = apply_filters('wp_team_manager_other_info_html', $output, $post_id);
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
    
        /**
         * Generate custom css and save in uploads folder
         *
         * @param int $scID Shortcode id
         *
         * @return void
         */
        public static function generatorShortcodeCss($scID)
    {
        global $wp_filesystem;

        // Validate `$scID` to prevent injection
        if (!is_numeric($scID)) {
            die('Invalid shortcode ID.');
        }

        // Ensure the user has the capability to modify files
        if (!current_user_can('manage_options')) {
            die('Unauthorized access.');
        }

        // Initialize WP filesystem securely
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $upload_dir = wp_upload_dir();
        $upload_basedir = $upload_dir['basedir'];

        // Validate and securely construct the CSS file path
        $allowedPath = realpath($upload_basedir);
        $cssFile = $allowedPath . '/wp-team-manager/team.css';

        if (!$allowedPath || strpos(realpath($cssFile), $allowedPath) !== 0) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Team: Error Invalid file path team.css');
            }
        }

        // Generate CSS content
        if ($css = self::render('style', compact('scID'), true)) {
            $css = sprintf('/*wp_team-%2$d-start*/%1$s/*wp_team-%2$d-end*/', $css, (int)$scID);

            if (file_exists($cssFile)) {
                $oldCss = $wp_filesystem->get_contents($cssFile);
                if ($oldCss && strpos($oldCss, '/*wp_team-' . $scID . '-start') !== false) {
                    $oldCss = preg_replace('/\/\*wp_team-' . $scID . '-start[\s\S]+?wp_team-' . $scID . '-end\*\//', '', $oldCss);
                    $oldCss = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $oldCss);
                }
                $css = $oldCss . $css;
            } else {
                $wp_filesystem->mkdir($allowedPath . '/wp-team-manager');
            }

            // Use secure file writing with WP Filesystem API
            if (!$wp_filesystem->put_contents($cssFile, $css, FS_CHMOD_FILE)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Team: Error generating CSS file');
                }
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
    public static function removeGeneratorShortcodeCss($scID)
    {
    // Ensure the user has admin privileges
    if (!current_user_can('manage_options')) {
        die('Unauthorized access.');
    }

    // Validate `$scID` to prevent injection
    if (!is_numeric($scID)) {
        die('Invalid shortcode ID.');
    }

    // Load the WordPress filesystem API securely
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!WP_Filesystem()) {
        return; // Failed to initialize, handle error appropriately
    }

    global $wp_filesystem;

    $upload_dir = wp_upload_dir();
    $upload_basedir = realpath($upload_dir['basedir']);

    // Validate the path to prevent path traversal
    if (!$upload_basedir || strpos($upload_basedir, realpath(WP_CONTENT_DIR . '/uploads')) !== 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Team: Invalid file path uploads for team.css');
        }
    }

    $cssFile = realpath($upload_basedir . '/wp-team-manager/team.css');

    // Ensure `$cssFile` is inside the allowed directory
    if (!$cssFile || strpos($cssFile, $upload_basedir) !== 0) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('Team: Invalid file path team.css');
        }
    }

    // Securely read the existing CSS file
    if (file_exists($cssFile)) {
        $oldCss = $wp_filesystem->get_contents($cssFile);

        if ($oldCss !== false && strpos($oldCss, '/*wp_team-' . $scID . '-start') !== false) {
            $css = preg_replace('/\/\*wp_team-' . $scID . '-start[\s\S]+?wp_team-' . $scID . '-end\*\//', '', $oldCss);
            $css = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", '', $css);

            // Securely write the updated CSS file
            if (!$wp_filesystem->put_contents($cssFile, $css, FS_CHMOD_FILE)) {
               
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('Team: Error updating CSS file');
                }
            }
        }
    }
}


    /**
     * Retrieve content from meta key and apply wpautop and do_shortcode to it
     *
     * @param string $meta_key
     * @param int $post_id
     * @return string
     */
    public static function get_wysiwyg_output( $meta_key, $post_id = 0 ) {
        $post_id = $post_id ? intval($post_id) : get_the_ID();
    
        // Retrieve post meta safely
        $content = get_post_meta( $post_id, $meta_key, true );
    
        if (!empty($content)) {
            // Process content using WordPress filters (including autoembed, shortcodes, and autop)
            $content = apply_filters( 'the_content', $content );
    
            // Sanitize output to prevent XSS
            $content = wp_kses_post( $content );
        }
    
        return $content;
    }

    /**
     * Outputs the image gallery for a team member.
     *
     * This function retrieves the image gallery meta data for a team member,
     * then iterates over the retrieved data to generate a set of HTML
     * elements representing the image gallery.
     *
     * The generated HTML includes a wrapper, a set of links, and a set of
     * images. Each link points to a full-size image, and the images are
     * displayed in a grid layout.
     *
     * @param int $post_id The team member post ID.
     *
     * @return void
     */
    public static function get_image_gallery_output( $post_id = 0 ) {
        $post_id              = $post_id ? $post_id : get_the_ID();
        $team_gallery_data    = get_post_meta( $post_id, 'wptm_cm2_gallery_image' );
        $light_box_selector   = '';
        $is_lightbox_selected = get_option( 'tm_single_team_lightbox' );
        $team_image_column    = get_option( 'tm_single_gallery_column' );

        if ( 'True' === $is_lightbox_selected && tmwstm_fs()->is_paying_or_trial()) {
            $light_box_selector = 'wtm-image-gallery-lightbox';
        }

        if( is_array($team_gallery_data) AND  empty($team_gallery_data) ){
            return false;
        }
        ?>
        <div class="wtm-image-gallery-wrapper <?php echo esc_attr($team_image_column) ?? '' ?> <?php echo esc_attr($light_box_selector) ?? ''; ?>">
            <?php foreach( $team_gallery_data[0] as $attachment_id => $attachment_url ): ?>
                <div class="wtm-single-image">
                    <a href="<?php echo esc_url( wp_get_attachment_url( $attachment_id ) ); ?>" title="">
                        <?php echo wp_get_attachment_image( $attachment_id , 'medium'); ?>
                    </a>
                </div>
            <?php endforeach;?>
        </div>
       <?php
    }

    /**
     * Generates a set of HTML checkbox inputs for single fields.
     *
     * This function retrieves single field options from the WordPress database,
     * then iterates over a predefined list of field keys and labels to generate
     * corresponding checkbox inputs. Each checkbox represents a field option,
     * and it will be checked if its key exists in the retrieved options.
     *
     * The generated HTML includes a wrapper, input checkbox, label, and display
     * name for each field option.
     */
    public static function generate_single_fields(){

        $tm_single_fields =  get_option('tm_single_fields')
        ? get_option('tm_single_fields') : 
        [];
        $fields = array(
            'tm_email'           => 'Email',
            'tm_jtitle'          => 'Job Title',
            'tm_telephone'       => 'Telephone (Office)',
            'tm_mobile'          => 'Mobile (Personal)',
            'tm_location'        => 'Location',
            'tm_year_experience' => 'Years of Experience',
            'tm_web_url'         => 'Web URL',
            'tm_vcard'           => 'vCard',
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

    /**
     * Outputs a select dropdown list of image sizes to use for the team member pictures.
     *
     * The list of options is generated from the $fields array, which includes the
     * 'medium', 'thumbnail', 'medium_large', 'large', and 'full' image sizes.
     *
     * The selected attribute is set based on the value of the 'team_image_size_change'
     * option in the database. If no value is set, the default value is 'medium'.
     */
    public static function get_image_sizes() {
        $selected = get_option('team_image_size_change', 'medium');
    
        $fields = array(
            'medium'       => __('Medium', 'wp-team-manager'),
            'thumbnail'    => __('Thumbnail', 'wp-team-manager'),
            'medium_large' => __('Medium Large', 'wp-team-manager'),
            'large'        => __('Large', 'wp-team-manager'),
            'full'         => __('Full', 'wp-team-manager'),
        );
    
        $fields = apply_filters('wp_team_manager_image_sizes', $fields);
    
        $is_locked = tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial();
    
        foreach ($fields as $key => $value) {
            printf(
                '<option value="%s" %s %s>%s</option>',
                esc_attr($key),
                selected($selected, $key, false),
                $is_locked ? 'disabled' : '',
                esc_html($value)
            );
        }
    
        if ($is_locked) {
            echo '<p style="color: #d63638; margin-top: 5px;">' . __('Image size change is only available in the premium version.', 'wp-team-manager') . '</p>';
        }
    }
    /**
     * Generates and outputs HTML <option> elements for gallery column selection.
     *
     * This function retrieves the current setting for the number of gallery columns
     * from the WordPress options table and then generates a set of HTML <option>
     * elements. Each <option> represents a possible column configuration (e.g., one column,
     * two columns, etc.), and the current setting is marked as selected.
     *
     * The generated HTML is intended for use in a <select> dropdown, allowing users
     * to select the number of columns to display in an image gallery.
     */

     public static function get_gallery_columns() {
        // Retrieve selected gallery column option
        $selected = get_option('tm_single_gallery_column', 'four_columns');
    
        // Default gallery column options
        $fields = array(
            'one_column'    => __('One', 'wp-team-manager'),
            'two_columns'   => __('Two', 'wp-team-manager'),
            'three_columns' => __('Three', 'wp-team-manager'),
            'four_columns'  => __('Four', 'wp-team-manager'),
        );
    
        // Allow developers to modify the gallery columns list
        $fields = apply_filters('wp_team_manager_gallery_columns', $fields);
    
        // Return early if no valid options are available
        if (empty($fields) || !is_array($fields)) {
            return;
        }
    
        foreach ($fields as $key => $value) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($key),
                selected($selected, $key, false),
                esc_html($value)
            );
        }
    }

    /**
     * Outputs HTML checkboxes for taxonomy settings.
     *
     * This function retrieves taxonomy field options from the WordPress database
     * and iterates over a predefined list of taxonomy keys and labels to generate
     * corresponding checkbox inputs. Each checkbox represents a taxonomy option and
     * will be checked if its key exists in the retrieved options.
     *
     * The generated HTML includes a wrapper, input checkbox, label, and display name
     * for each taxonomy option.
     */

    public static function get_taxonomy_settings(){

        $tm_taxonomy_fields =  get_option('tm_taxonomy_fields')
        ? get_option('tm_taxonomy_fields') : 
        [];
        $fields = array(
            'team_designation' => 'Designations',
            'team_department'  => 'Departments',
            'team_groups'      => 'Groups',
            'team_genders'     => 'Genders',
        );
        
        foreach ($fields as $key => $value) {

            printf(
                '<div class="tm-nice-checkbox-wrapper">
                <input type="checkbox" class="tm-checkbox" id="tm_%s" name="tm_taxonomy_fields[]" value="%s" %s/>
                <label for="tm_%s" class="toggle"><span></span></label>
                <span>%s</span>  
                </div><!--.tm-nice-checkbox-wrapper-->',
                esc_attr( $key ) ,
                esc_attr( $key ),
                in_array($key,$tm_taxonomy_fields) ? 'checked' : '',
                esc_attr( $key ),
                esc_html( $value ) ,
                
            );

        }

    }

/**
 * Migrates old social icon metadata to a unified social group format.
 *
 * This function retrieves individual social media links (e.g., Facebook, Twitter)
 * from the post meta of a given post ID and consolidates them into a single
 * 'wptm_social_group' meta entry. Each entry in this group contains the social
 * media type and its corresponding URL.
 *
 * @param int $post_id The ID of the post whose social icons are to be migrated.
 */
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
    
        // Sanitize template name to prevent directory traversal
        $template_name = basename($template_name);
    
        // // Allowlist of valid template files
        // $allowed_templates = ['content-memeber.php', 'footer.php', 'team-template.php','content-grid.php'];
    
        // if (!in_array($template_name, $allowed_templates, true)) {
        //     return ''; // Block unauthorized template names
        // }
    
        $template = locate_template( trailingslashit( $template_path ) . $template_name );
    
        // Get default template securely
        if ( ! $template ) {
            $real_path = realpath($default_path . $template_name);
            
            if ($real_path && strpos($real_path, realpath($default_path)) === 0 && file_exists($real_path)) {
                $template = $real_path;
            } else {
                return ''; // Prevent file inclusion attacks
            }
        }
    
        return $template;
    }

    /**
     * Retrieves team data based on the provided query arguments.
     *
     * This function performs a WordPress query using the specified arguments
     * to fetch team-related posts and returns the results along with the maximum
     * number of pages available for pagination.
     *
     * @param array $args Arguments for the WP_Query to retrieve team posts.
     * @return array An associative array containing 'posts' (list of team posts)
     *               and 'max_num_pages' (total number of pagination pages), or an
     *               empty array if no posts are found.
     */

    public static function get_team_data($args){
        $args = apply_filters('wp_team_manager_query_args', $args);
        $tmQuery = new \WP_Query( $args );
        return ($tmQuery->posts) ? ['posts' => $tmQuery->posts,'max_num_pages' => $tmQuery->max_num_pages] : ['posts'=>[],'max_num_pages'=>0];
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
    $allowedLayouts = ['grid', 'list', 'slider', 'table', 'isotope']; // Allowed layouts

    if (!in_array($layout, $allowedLayouts, true)) {
        wp_die(__('Invalid layout.', 'wp-team-manager'));
    }

    $styleTypeKey = "{$layout}_style_type";
    $styleType = $settings[$styleTypeKey] ?? '';

    // Ensure only safe characters (alphanumeric + underscores)
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $styleType)) {
        wp_die(__('Invalid style type.', 'wp-team-manager'));
    }

    // Ensure constants exist before using them
    if (!defined('TM_PATH')) {
        wp_die(__('TM_PATH is not defined.', 'wp-team-manager'));
    }

    // Define Free path (always available)
    $basePath = realpath(TM_PATH . '/public/templates/elementor/layouts/');

    // Define Pro path if available
    $proPath = defined('TM_PRO_PATH') ? realpath(TM_PRO_PATH . '/public/templates/elementor/layouts/') : null;

    // Ensure the free path is valid
    if (!$basePath) {
        wp_die(__('Invalid base template path.', 'wp-team-manager'));
    }

    $templateName = sanitize_file_name($styleType . '.php');

    // Define possible template paths (Pro first, then Free)
    $proFullPath = $proPath ? $proPath . '/' . $layout . '/' . $templateName : null;
    $freeFullPath = $basePath . '/' . $layout . '/' . $templateName;

    // Check if Pro template exists and is readable
    if ($proFullPath && is_readable($proFullPath) && strpos(realpath($proFullPath), $proPath) === 0) {
        include $proFullPath;
        return;
    }

    // Check if Free template exists and is readable
    if (is_readable($freeFullPath) && strpos(realpath($freeFullPath), $basePath) === 0) {
        include $freeFullPath;
        return;
    }

    // If neither file is found, show an error
    wp_die(__('Template not found or invalid file.', 'wp-team-manager'));
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
        public static function renderTeamLayout(string $layout, array $data, string $styleType, array $settings = []): void
        {
            $allowedLayouts = ['grid', 'list', 'slider', 'table', 'isotope']; // Define valid layouts
        
            if (!in_array($layout, $allowedLayouts, true)) {
                die('Invalid layout.');
            }
        
          // Allow only alphanumeric + underscores to prevent directory traversal
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $styleType)) {
            die('Invalid style type.');
        }
        
        
            $path = realpath(TM_PATH . '/public/templates/layouts/' . $layout . '/');
            if (!$path || strpos($path, realpath(TM_PATH . '/public/templates/layouts/')) !== 0) {
                die('Invalid path.');
            }
        
            $templateName = sanitize_file_name($styleType . '.php');
            $fullPath = $path . '/' . $templateName;
        
            // Validate that the file exists and is within the intended directory
            if (file_exists($fullPath) && pathinfo($fullPath, PATHINFO_EXTENSION) === 'php') {
                include $fullPath;
            } else {
                die('Template not found or invalid file.');
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
            // Ensure template name is safe (allow only alphanumeric, dashes, and underscores)
            if (!preg_match('/^[a-zA-Z0-9_-]+\.php$/', $templateName)) {
                die('Invalid template name.');
            }
        
            $templatePath = $templatePath ?: 'public/templates';
            $defaultPath = $defaultPath ?: TM_PATH . '/public/templates/';
        
            // Ensure paths are properly resolved
            $resolvedDefaultPath = realpath($defaultPath);
            $resolvedTemplatePath = realpath(trailingslashit($templatePath));
        
            // Validate resolved paths
            if (!$resolvedDefaultPath || strpos($resolvedDefaultPath, realpath(TM_PATH . '/public/templates/')) !== 0) {
                die('Invalid default path.');
            }
        
            if ($resolvedTemplatePath && strpos($resolvedTemplatePath, realpath(TM_PATH . '/public/templates/')) === 0) {
                $template = locate_template($resolvedTemplatePath . '/' . $templateName);
                if ($template && file_exists($template)) {
                    return $template;
                }
            }
        
            // Build the final safe path
            $finalPath = $resolvedDefaultPath . '/' . $templateName;
        
            // Ensure the final path is within the allowed directory
            if (file_exists($finalPath) && strpos(realpath($finalPath), $resolvedDefaultPath) === 0) {
                return $finalPath;
            }
        
            die('Template not found or invalid.');
        }
        
        /**
         * Displays the HTML output of a given layout, with the given data and settings.
         * 
         * @param string $layout The name of the layout to display. Defaults to 'grid'. Valid values are 'grid', 'list', and 'slider'.
         * @param array $data The data to pass to the layout template.
         * @param array $settings The settings for the layout.
         * 
         * @return void
         */
        public static function show_html_output($layout = 'grid', $data = [], $settings = [])
        {
            // Define allowed layouts to prevent arbitrary input
            $allowedLayouts = [
                'grid'   => 'content-grid.php',
                'list'   => 'content-list.php',
                'slider' => 'content-slider.php'
            ];
        
            // Ensure the layout is valid
            if (!array_key_exists($layout, $allowedLayouts)) {
                $layout = 'grid'; // Default fallback
            }
        
            $templateFile = $allowedLayouts[$layout];
            $templateFile = apply_filters('wp_team_manager_template_file', $templateFile, $layout, $settings);
        
            // Locate and validate template path
            $templatePath = self::wtm_locate_template($templateFile);
        
            // Ensure the template file exists and is inside the intended directory
            if (file_exists($templatePath) && strpos(realpath($templatePath), realpath(TM_PATH . '/public/templates/')) === 0) {
                include $templatePath;
            } else {
                die('Invalid template file.');
            }
        }
        
    

    /**
     * Renders a specified number of terms for a given post and taxonomy.
     *
     * @param int $post_id The ID of the post from which to retrieve terms.
     * @param int $term_to_show The number of terms to display. Defaults to 1.
     * @param string $term The taxonomy from which to retrieve terms. Defaults to 'team_designation'.
     * @return bool False if the post ID is empty or no terms are found; otherwise, outputs the terms HTML.
     */
    public static function render_terms( $post_id, $term_to_show = 1, $term = 'team_designation' ){
		if( empty( $post_id ) ){
			return false;
		}

		$get_the_terms = get_the_terms( $post_id, $term);

		if( ! is_array( $get_the_terms ) ){
			return false;
		}

		$terms = array_slice($get_the_terms, 0, $term_to_show);

		$terms_html = '<div class="team-'.$term.'">';
		foreach( $terms as $term ){
			$terms_html .= '<span class="team-position">'. esc_html( $term->name ) .'</span>';
		}
		$terms_html .= '</div>';

		$terms_html = apply_filters('wp_team_manager_terms_output', $terms_html, $term, $post_id);
		return $terms_html;
	}

    /**
     * Shows a label indicating that a feature is only available in the Pro version.
     *
     * @return string The label HTML, or an empty string if the current user has a paid license.
     */
    public static function showProFeatureLabel(){

        if(tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()){
            return esc_html__(' (Pro Feature)', 'wp-team-manager');
        }

        return '';
    }

    /**
     * Returns a link to upgrade to the Pro version if the current user does not have a paid license.
     *
     * @return string The link HTML, or an empty string if the current user has a paid license.
     */
    public static function showProFeatureLink( $link_text = 'Upgrade Now!') {
        // Validate the link text to ensure it contains safe characters
        $link_text = sanitize_text_field($link_text);
    
        // Ensure the upgrade URL is retrieved once
        $upgrade_url = esc_url(tmwstm_fs()->get_upgrade_url());
    
        // Check if the user is not paying and is not on a trial
        if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) {
            return '<a href="' . $upgrade_url . '" target="_blank" rel="noopener noreferrer">' . esc_html($link_text) . '</a>';
        }
    
        return '';
    }
    

}