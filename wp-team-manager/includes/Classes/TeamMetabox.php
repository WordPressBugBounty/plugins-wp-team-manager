<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * metabox Class
 */
class TeamMetabox {

    use \DWL\Wtm\Traits\Singleton;

    /**
     * Define the metabox and field configurations.
    */
    private $prefix;


    private $proLink;

    /**
     * Constructor for the class.
     *
     * Initializes the prefix for the metaboxes and adds two actions to the 'cmb2_init' hook.
     *
     * @return void
     */
    public function __construct(){
        $this->prefix = 'dwl_team_';
        \add_action( 'cmb2_init', [$this, 'create_wp_team_manager_metaboxes'] );
        \add_action( 'cmb2_init', [$this, 'create_meta_for_dwl_team_generator_post_type'] );
        \add_action( 'cmb2_init', [$this, 'create_member_information_metabox'] );
        \add_action( 'admin_enqueue_scripts', [$this, 'enqueue_responsive_grid_assets'] );

        $this->proLink = '';
        if ( Helper::freemius_is_free_user() ) {
           
            $this->proLink = '<span class="wptm-pro-text">' . __( ' Pro', 'wp-team-manager' ) . '</span> <a class="wptm-pro-link" href="' . esc_url(tmwstm_fs()->get_upgrade_url()) . '">'  . __('Upgrade Now!', 'wp-team-manager') . '</a>';
        }

    }

    public function enqueue_responsive_grid_assets($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            global $post_type;
            if ('dwl_team_generator' === $post_type) {
                wp_enqueue_style('wtm-responsive-grid', TM_ADMIN_ASSETS . '/css/responsive-grid.css', [], '1.0.0');
            }
        }
    }

private function pagination_options( $box ) {

    $field_id = $this->prefix . 'pagination_type';


    $all_options = array(
        'none'     => esc_html__( 'None', 'wp-team-manager' ),
        'numbers'  => esc_html__( 'Numbers', 'wp-team-manager' ),
        'ajax'     => esc_html__( 'Ajax (Load More)', 'wp-team-manager' ),
        // 'infinite' => esc_html__( 'Infinite Scroll', 'wp-team-manager' ),
    );

    $is_locked   = Helper::freemius_is_free_user();
    $locked_keys = array( 'ajax', 'infinite' ); 

    $box->add_field( array(
        'name'        => __( 'Pagination Type', 'wp-team-manager' ),
        'id'          => $field_id,
        'type'        => 'select',
        'default'     => 'none',
        'options'     => $all_options,

       
        'escape_cb'   => function( $val ) use ( $is_locked, $locked_keys ) {
            if ( $is_locked && in_array( $val, $locked_keys, true ) ) {
                return 'none';
            }
            return $val;
        },

      
        'sanitization_cb' => function( $val ) use ( $is_locked, $locked_keys ) {
            if ( $is_locked && in_array( $val, $locked_keys, true ) ) {
                return 'none';
            }
            return $val;
        },

      
        'after_row'   => $is_locked ? sprintf(
            '<script>
                (function(){
                    var sel = document.getElementById(%s);
                    if (!sel) return;
                    var locked = %s;
                    for (var i=0; i<sel.options.length; i++) {
                        if (locked.indexOf(sel.options[i].value) !== -1) {
                            sel.options[i].disabled = true;
                            // Add a visual hint
                            sel.options[i].text = sel.options[i].text + " (Premium)";
                            sel.options[i].title = %s;
                        }
                    }
                 
                    if (locked.indexOf(sel.value) !== -1) {
                        sel.value = "none";
                    }
                })();
            </script>
            <p style="color:#d63638;margin-top:6px;">%s</p>',
            wp_json_encode( $field_id ),
            wp_json_encode( $locked_keys ),
            wp_json_encode( esc_js( __( 'Available in Premium', 'wp-team-manager' ) ) ),
            esc_html__( '', 'wp-team-manager' )
        ) : '',

        'desc' => $is_locked ? wp_kses_post( $this->proLink ) : '',
         // Hide when layout is slider
    'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
    ) );
    }


    function create_meta_for_dwl_team_generator_post_type(){

        $post_id = isset($_GET['post']) && is_string($_GET['post']) ? trim($_GET['post']) : "0";

        $shortcode = '[dwl_create_team id="' . esc_attr( $post_id ) . '"]';
        $title = sprintf(
            '<div class="wtm-sc-wrap">
                <p class="wtm-sc-help">%s</p>
                <div class="wtm-sc-row">
                    <span class="wtm-sc-input">
                        <input type="text" readonly value="%s" onclick="this.select();" />
                    </span>
                    <button type="button" class="button button-secondary" id="wtm-copy-shortcode">%s</button>
                </div>
                <div class="wtm-sc-note">%s</div>
            </div>
            <script>
                (function(){
                    var btn = document.getElementById("wtm-copy-shortcode");
                    if(!btn) return;
                    btn.addEventListener("click", function(){
                        var inp = btn.parentNode.querySelector("input");
                        if(!inp) return;
                        inp.select();
                        function done(){
                            btn.classList.add("button-primary");
                            btn.textContent = "%s";
                            setTimeout(function(){
                                btn.classList.remove("button-primary");
                                btn.textContent = "%s";
                            }, 1400);
                        }
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(inp.value).then(done).catch(function(){
                                try { document.execCommand("copy"); done(); } catch(e) {}
                            });
                        } else {
                            try { document.execCommand("copy"); done(); } catch(e) {}
                        }
                    });
                })();
            </script>',
            esc_html__( 'Copy and paste this shortcode into any page or post.', 'wp-team-manager' ),
            esc_attr( $shortcode ),
            esc_html__( 'Copy', 'wp-team-manager' ),
            esc_html__( 'Tip: You can also use this in a Shortcode block.', 'wp-team-manager' ),
            esc_html__( 'Copied!', 'wp-team-manager' ),
            esc_html__( 'Copy', 'wp-team-manager' )
        );
        $documentation = ''
        . '<div class="wtm-help">'
        . '<p class="wtm-help-desc">' . esc_html__( 'Browse step‑by‑step guides and videos.', 'wp-team-manager' ) . '</p>'
        . '<a class="wtm-btn" href="https://wpteammanager.com/documentation/?utm_source=wp-admin&utm_medium=metabox&utm_campaign=wtm" target="_blank" rel="noopener"><span class="dashicons dashicons-book-alt"></span> ' . esc_html__( 'Open Documentation', 'wp-team-manager' ) . '</a>'
        . '</div>';

        $support = '<div class="wtm-help">'
        . '<p class="wtm-help-desc">' . esc_html__( 'Need help from our team? Create a support ticket.', 'wp-team-manager' ) . '</p>'
        . '<a class="wtm-btn" href="https://dynamicweblab.com/submit-a-request/?utm_source=wp-admin&utm_medium=metabox&utm_campaign=wtm" target="_blank" rel="noopener"><span class="dashicons dashicons-sos"></span> ' . esc_html__( 'Get Support', 'wp-team-manager' ) . '</a>'
        . '</div>';
        $dwl_instructions = new_cmb2_box( 
            array(
                'id'            => 'dwl_team_help',
                'title'         =>  esc_html__( 'Team Shortcode', 'wp-team-manager' ),
                'object_types'  => ['dwl_team_generator'], 
                'context'       => 'side',
                'priority'      => 'low',
                'show_names'    => true, 
            ) 
        );

        $dwl_instructions->add_field( array(
            'name' => __( 'Instructions', 'wp-team-manager' ),
            'desc' => $title,
            'type' => 'title',
            'id'   => $this->prefix.'dwl_team_settings_title'
        ) );

        $dwl_instructions = new_cmb2_box( 
            array(
                'id'            => 'dwl_team_documentation',
                'title'         =>  esc_html__( 'Need Help ?', 'wp-team-manager' ),
                'object_types'  => ['dwl_team_generator', 'team_manager'], 
                'context'       => 'side',
                'priority'      => 'low',
                'show_names'    => true, 
            ) 
        );

        $dwl_instructions->add_field( array(
            'name' => __( 'Documentation', 'wp-team-manager' ),
            'desc' => $documentation,
            'type' => 'title',
            'id'   => $this->prefix.'dwl_team_settings_docomentation'
        ) );

        $dwl_instructions->add_field( array(
            'name' => __( 'Support ', 'wp-team-manager' ),
            'desc' => $support,
            'type' => 'title',
            'id'   => $this->prefix.'dwl_team_settings_support'
        ) );

        $dwl_layout = new_cmb2_box( 
            array(
                'id'            => 'dwl_team_layout',
                'title'         =>  esc_html__( 'Layout', 'wp-team-manager' ),
                'object_types'  => ['dwl_team_generator'], 
                'context'       => 'normal',
                'priority'      => 'high',
                'show_names'    => true, 
                'classes' => 'dwl-metabox-grid',
            ) 
        );

        $dwl_layout->add_field( 
			array(
				'name'           => __( 'Layout Type', 'wp-team-manager' ),
				'desc'           => __( 'Choose how your team members will be displayed. Each layout offers different visual styles and information density.', 'wp-team-manager' ),
				'id'             => $this->prefix . 'layout_option',
				'type'           => 'radio_image',
				'options'        => array(
					'grid'        => __('Grid - Card-based layout perfect for showcasing team photos and key info', 'wp-team-manager'),
					'list'        => __('List - Detailed horizontal layout ideal for comprehensive member profiles', 'wp-team-manager'),
					'slider'      => __('Slider - Interactive carousel great for highlighting featured team members', 'wp-team-manager'),
                    'table'      => __('Table - Structured format excellent for comparing member details', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'grid'     => 'icons/grid.svg',
					'list'     => 'icons/list.svg',
					'slider'  => 'icons/slider.svg',
                    'table'  => 'icons/Table.svg',
				),
				'default'        => 'grid',
                'classes'        => 'col-12 wtm-layout-selector',
                'after_row'      => '<div class="wtm-layout-tips">
                    <div class="wtm-tip" data-layout="grid">
                        <span class="dashicons dashicons-info"></span>
                        <strong>Grid Layout:</strong> Best for teams of 6+ members. Responsive and mobile-friendly.
                    </div>
                    <div class="wtm-tip" data-layout="list">
                        <span class="dashicons dashicons-info"></span>
                        <strong>List Layout:</strong> Perfect for detailed bios and contact information. Great for small teams.
                    </div>
                    <div class="wtm-tip" data-layout="slider">
                        <span class="dashicons dashicons-info"></span>
                        <strong>Slider Layout:</strong> Engaging for featured members or leadership teams. Auto-play available.
                    </div>
                    <div class="wtm-tip" data-layout="table">
                        <span class="dashicons dashicons-info"></span>
                        <strong>Table Layout:</strong> Ideal for comparing skills, experience, or contact details side-by-side.
                    </div>
                </div>',
        	) 
		);


        $dwl_layout->add_field( 
			array(
				'name'           => __( 'Style Type', 'wp-team-manager' ),
				'desc'           => __( 'Select Style Layout Type', 'wp-team-manager' ),
				'id'             => $this->prefix . 'grid_style_option',
				'type'           => 'radio_image',
				'options'        => array(
					'style-1'        => __('Style One', 'wp-team-manager'),
					'style-2'        => __('Style Two', 'wp-team-manager'),
                    'style-3'        => __('Style Three', 'wp-team-manager'),
                    'style-4'        => __('Style Three', 'wp-team-manager'),
                    'style-5'        => __('Style Five', 'wp-team-manager'),
                    // 'style-6'        => __('Style Six', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Grid-1.svg',
					'style-2'     => 'icons/short-code-layout/Grid-2.svg',
					'style-3'     => 'icons/short-code-layout/grid-3.svg',
                    'style-4'     => 'icons/short-code-layout/grid-4.svg',
                    'style-5'     => 'icons/short-code-layout/grid-5.svg',
                    // 'style-6'     => 'icons/short-code-layout/grid-5.svg',
				),
				'default'        => 'style-1',
                'classes'        => Helper::freemius_is_free_user() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'grid') ),
                ),
          'sanitization_cb' => function( $val ) {
            
            if ( Helper::freemius_is_free_user() ) {
                $blocked_styles = array( 'style-4');
                return in_array( $val, $blocked_styles, true ) ? 'style-1' : $val;
            }
            return $val;
        },
        	) 
		);


        $dwl_layout->add_field( 
			array(
				'name'           => __( 'Style Type', 'wp-team-manager' ),
				'desc'           => __( 'Select Style Layout Type', 'wp-team-manager' ),
				'id'             => $this->prefix . 'list_style_option',
				'type'           => 'radio_image',
				'options'        => array(
					'style-1'        => __('Style One', 'wp-team-manager'),
                    'style-2'        => __('Style Two', 'wp-team-manager'),
                    'style-3'        => __('Style Three', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/List-1.svg',
                    'style-2'     => 'icons/short-code-layout/List-2.svg',
                    'style-3'     => 'icons/short-code-layout/List-3.svg',
				),
				'default'        => 'style-1',
                'classes'        => Helper::freemius_is_free_user() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'list' ) ),
                ),
                       'sanitization_cb' => function( $val ) {
              
                if ( Helper::freemius_is_free_user() ) {
                    return $val === 'style-3' ? 'style-1' : $val; 
                }
                return $val;
        },
        	) 
		);

        $dwl_layout->add_field( 
			array(
				'name'           => __( 'Style Type', 'wp-team-manager' ),
				'desc'           => __( 'Select Style Layout Type', 'wp-team-manager' ),
				'id'             => $this->prefix . 'slider_style_option',
				'type'           => 'radio_image',
				'options'        => array(
					'style-1'        => __('Style One', 'wp-team-manager'),
                    'style-2'        => __('Style Two', 'wp-team-manager'),
                    'style-3'        => __('Style Three', 'wp-team-manager'),
                    'style-4'        => __('Style Four', 'wp-team-manager'),
                    'style-5'        => __('Style Five', 'wp-team-manager'),
                    'style-6'        => __('Style Six', 'wp-team-manager'),
                    // 'style-7'        => __('Style Seven', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Slider-1.svg',
                    'style-2'     => 'icons/short-code-layout/Slider-2.svg',
                    'style-3'     => 'icons/short-code-layout/Slider-3.svg',
                    'style-4'     => 'icons/short-code-layout/Slider-4.svg',
                    'style-5'     => 'icons/short-code-layout/Slider-5.svg',
                    'style-6'     => 'icons/short-code-layout/Slider-6.svg',
                    // 'style-7'     => 'icons/short-code-layout/Slider-5.svg',
				),
				'default'        => 'style-1',
                'classes'        => Helper::freemius_is_free_user() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'slider' ) ),
                ),
          'sanitization_cb' => function( $val ) {
            
            if ( Helper::freemius_is_free_user() ) {
                $blocked_styles = array( 'style-3', 'style-4', 'style-5', 'style-6' );
                return in_array( $val, $blocked_styles, true ) ? 'style-1' : $val;
            }
            return $val;
        },
        	) 
		);

        $dwl_layout->add_field( 
			array(
				'name'           => __( 'Style Type', 'wp-team-manager' ),
				'desc'           => __( 'Select Style Layout Type', 'wp-team-manager' ),
				'id'             => $this->prefix . 'table_style_option',
				'type'           => 'radio_image',
				'options'        => array(
					'style-1'        => __('Style One', 'wp-team-manager'),
                    'style-2'        => __('Style two', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Table-1.svg',
                    'style-2'     => 'icons/short-code-layout/Table-2.svg',
				),
				'default'        => 'style-1',
                'classes'        => 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'table' ) ),
                ),
        	) 
		);

        // Responsive Grid Settings - Mobile
        $dwl_layout->add_field( array(
            'name'    => __( 'Mobile Columns', 'wp-team-manager' ),
            'desc'    => __( 'Number of columns on mobile devices (< 768px)', 'wp-team-manager' ),
            'id'      => $this->prefix . 'mobile_columns',
            'type'    => 'select',
            'default' => '1',
            'options' => array(
                '1' => __( '1 Column', 'wp-team-manager' ),
                '2' => __( '2 Columns', 'wp-team-manager' ),
            ),
            'attributes' => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
                'data-conditional-value' => wp_json_encode( array( 'grid', 'list', 'slider' ) ),
            ),
            'classes' => 'col-md-4',
        ) );

        // Responsive Grid Settings - Tablet
        $dwl_layout->add_field( array(
            'name'    => __( 'Tablet Columns', 'wp-team-manager' ),
            'desc'    => __( 'Number of columns on tablet devices (768px - 1024px)', 'wp-team-manager' ),
            'id'      => $this->prefix . 'tablet_columns',
            'type'    => 'select',
            'default' => '2',
            'options' => array(
                '1' => __( '1 Column', 'wp-team-manager' ),
                '2' => __( '2 Columns', 'wp-team-manager' ),
                '3' => __( '3 Columns', 'wp-team-manager' ),
            ),
            'attributes' => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
                'data-conditional-value' => wp_json_encode( array( 'grid', 'list', 'slider' ) ),
            ),
            'classes' => 'col-md-4',
        ) );

        // Responsive Grid Settings - Desktop
        $dwl_layout->add_field( array(
            'name'    => __( 'Desktop Columns', 'wp-team-manager' ),
            'desc'    => __( 'Number of columns on desktop devices (> 1024px)', 'wp-team-manager' ),
            'id'      => $this->prefix . 'desktop_columns',
            'type'    => 'select',
            'default' => '3',
            'options' => array(
                '1' => __( '1 Column', 'wp-team-manager' ),
                '2' => __( '2 Columns', 'wp-team-manager' ),
                '3' => __( '3 Columns', 'wp-team-manager' ),
                '4' => __( '4 Columns', 'wp-team-manager' ),
            ),
            'attributes' => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
                'data-conditional-value' => wp_json_encode( array( 'grid', 'list', 'slider' ) ),
            ),
            'classes' => 'col-md-4',
        ) );



        $dwl_layout->add_field( 
            array(
                'name'                       => __( 'Enable Autoplay', 'wp-team-manager' ),
                'desc'                       => __( 'Enables Autoplay on the slider', 'wp-team-manager' ),
                'id'                         => $this->prefix . 'autoplay',
                'classes'                    => '',
                'type'                       => 'select',
                'show_option_none'           => false,
                'default'                    => 'yes',
                'options'                    => array(
                    'yes'                    => __( 'Yes', 'wp-team-manager' ),
                    'no'                     => __( 'No', 'wp-team-manager' ),
                ),
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                    'data-conditional-value' => 'slider',
                ),
                'classes'                    => 'dwl-meta-item col-md-3',
            )
        );

        $dwl_layout->add_field( 
            array(
                'name'                       => __( 'Show Arrow', 'wp-team-manager' ),
                'desc'                       => __( 'Show hide next previous button', 'wp-team-manager' ),
                'id'                         => $this->prefix . 'show_arrow',
                'classes'                    => '',
                'type'                       => 'select',
                'show_option_none'           => false,
                'default'                    => 'yes',
                'options'                    => array(
                    'yes'                    => __( 'Yes', 'wp-team-manager' ),
                    'no'                     => __( 'No', 'wp-team-manager' ),
                ),
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                    'data-conditional-value' => 'slider',
                ),
                'classes'                    => 'dwl-meta-item col-md-3',
            )
        );
    
        $dwl_layout->add_field(
            array(
				'name'                       => __( 'Arrow Position', 'wp-team-manager' ),
				//'desc'                     => 'Show hide next previous button',
				'id'                         => $this->prefix . 'arrow_position',
				'classes'                    => '',
				'type'                       => 'select',
				'show_option_none'           => false,
				'default'                    => 'side',
				'options'                    => array(
                    'top-right'              => __( 'Top Right', 'wp-team-manager' ),
					'side'                   => __( 'Side', 'wp-team-manager' ),
				),
				'attributes'                 => array(
					'data-conditional-id'    => $this->prefix . 'layout_option',
					'data-conditional-value' => 'slider',
				),
                'classes'                    => 'dwl-meta-item col-md-3',
        	)
    	);


		$dwl_layout->add_field( 
			array(
				'name'             => __( 'Show Dot navigation', 'wp-team-manager' ),
				'desc'             => __( 'Show hide dot navigation', 'wp-team-manager' ),
				'id'               => $this->prefix . 'dot_nav',
				'classes'          => '',
				'type'             => 'select',
				'show_option_none' => false,
				'default'          => 'yes',
				'options'          => array(
					'yes' => __( 'Yes', 'wp-team-manager' ),
					'no'   => __( 'No', 'wp-team-manager' ),
				),
				'attributes'    => array(
					'data-conditional-id'     => $this->prefix . 'layout_option',
					'data-conditional-value'  => 'slider',
				),
                'classes'          => 'dwl-meta-item col-md-3',
			)
		);

        $dwl_team_metabox = new_cmb2_box( 
            array(
                'id'            => 'dwl_team_metabox',
                'title'         =>  esc_html__( 'Manage your Team', 'wp-team-manager' ),
                'object_types'  => ['dwl_team_generator'], 
                'context'       => 'normal',
                'priority'      => 'high',
                'show_names'    => true,
                'vertical_tabs' => false,
                'tabs' => array(
                    array(
                        'id'    => 'dwl_content_query',
                        'icon' => 'dashicons-admin-site',
                        'title' => __( 'Content & Query', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix . 'group_featured_cats',
                            $this->prefix. 'show_total_members',
                            $this->prefix. 'team_order_by',
                            $this->prefix. 'team_order',
                            $this->prefix.'show_team_member_by_ids',
                            $this->prefix.'remove_team_members_by_ids',
                        ),
                    ),
                    array(
                        'id'    => 'dwl_advanced_filters',
                        'icon' => 'dashicons-filter',
                        'title' => __( 'Advanced Filters', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix.'tax_relation',
                            $this->prefix.'tax_include',
                            $this->prefix.'tax_exclude',
                            $this->prefix.'keyword',
                            $this->prefix.'date_from',
                            $this->prefix.'date_to',
                        ),
                    ),


                    array(
                        'id'    => 'dwl_display_setting',
                        'icon' => 'dashicons-visibility',
                        'title' => __( 'Display Options', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix . 'team_show_position',
                            $this->prefix . 'hide_short_bio',
                            $this->prefix . 'team_show_other_info',
                            $this->prefix . 'team_show_social',
                            $this->prefix . 'show_progress_bar',
                            $this->prefix . 'team_show_read_more',
                            $this->prefix . 'team_link_mobile_phone',
                        ),
                    ),
                    array(
                        'id'    => 'dwl_pagination_filters',
                        'icon' => 'dashicons-admin-page',
                        'title' => __( 'Pagination & Filters', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix . 'pagination_type',
                            $this->prefix . 'filter_enable',
                        ),
                    ),

                    array(
                        'id'    => 'dwl_image_setting',
                        'icon' => 'dashicons-format-image',
                        'title' => __( 'Images', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix . 'select_image_size',
                            $this->prefix . 'image_style',
                        ),
                    ),
                    array(
                        'id'    => 'dwl_styling_theming',
                        'icon'  => 'dashicons-art',
                        'title' => __( 'Styling & Theming', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix . 'social_icon_color',
                            $this->prefix . 'team_background_color',
                            $this->prefix . 'theme_preset',
                            $this->prefix . 'theme_primary_color',
                            $this->prefix . 'theme_card_bg',
                            $this->prefix . 'theme_title_color',
                            $this->prefix . 'theme_text_color',
                            $this->prefix . 'theme_border_radius',
                            $this->prefix . 'theme_border_radius_custom',
                            $this->prefix . 'theme_gap',
                            $this->prefix . 'theme_gap_custom',
                            $this->prefix . 'theme_shadow',
                            $this->prefix . 'theme_dark_mode',
                            $this->prefix . 'theme_custom_css',
                        ),
                    ),                    
                    
                ),
                
            ) 
        );

        // === Content & Query ===
        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Team Groups', 'wp-team-manager' ),
            'desc'       => __( 'Select specific team groups to display. Leave empty to show all groups.', 'wp-team-manager' ),
            'id'         => $this->prefix . 'group_featured_cats',
            'type'       => 'multicheck',
            'options_cb' => 'wptm_get_taxonomy_terms',
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Total Members', 'wp-team-manager' ),
            'desc'       => __( 'Maximum number of team members to display. Use -1 for unlimited.', 'wp-team-manager' ),
            'id'         => $this->prefix . 'show_total_members',
            'type'       => 'text',
            'default'    => '-1',
            'attributes' => array(
                'type'        => 'number',
                'min'         => '-1',
                'step'        => '1',
                'placeholder' => '-1',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Sort By', 'wp-team-manager' ),
            'desc'    => __( 'Choose how to order the team members.', 'wp-team-manager' ),
            'id'      => $this->prefix . 'team_order_by',
            'type'    => 'select',
            'default' => 'title',
            'options' => array(
                'title'    => __( 'Name (Alphabetical)', 'wp-team-manager' ),
                'date'     => __( 'Date Added', 'wp-team-manager' ),
                'modified' => __( 'Last Modified', 'wp-team-manager' ),
                'rand'     => __( 'Random Order', 'wp-team-manager' ),
                'menu_order' => __( 'Custom Order (Drag & Drop)', 'wp-team-manager' ),
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Sort Direction', 'wp-team-manager' ),
            'desc'    => __( 'Choose ascending or descending order.', 'wp-team-manager' ),
            'id'      => $this->prefix . 'team_order',
            'type'    => 'select',
            'default' => 'ASC',
            'options' => array(
                'ASC'  => __( 'Ascending (A-Z, Oldest First)', 'wp-team-manager' ),
                'DESC' => __( 'Descending (Z-A, Newest First)', 'wp-team-manager' ),
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Include Specific IDs', 'wp-team-manager' ),
            'desc'       => __( 'Show only these team members by their post IDs (comma-separated).', 'wp-team-manager' ),
            'id'         => $this->prefix . 'show_team_member_by_ids',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '1, 2, 3',
                'pattern'     => '^[0-9,\\s]*$',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Exclude Specific IDs', 'wp-team-manager' ),
            'desc'       => __( 'Hide these team members by their post IDs (comma-separated).', 'wp-team-manager' ),
            'id'         => $this->prefix . 'remove_team_members_by_ids',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '4, 5, 6',
                'pattern'     => '^[0-9,\\s]*$',
            ),
        ) );

        // === Query & Filtering ===
        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Taxonomy Relation', 'wp-team-manager' ),
            'id'      => $this->prefix . 'tax_relation',
            'type'    => 'select',
            'desc'    => __( 'Match all selected terms (AND) or any (OR).', 'wp-team-manager' ),
            'default' => 'OR',
            'options' => array(
                'AND' => __( 'AND (all terms must match)', 'wp-team-manager' ),
                'OR'  => __( 'OR (any term can match)', 'wp-team-manager' ),
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Include Terms (IDs)', 'wp-team-manager' ),
            'desc'       => __( 'Comma-separated term IDs to include (e.g., 12, 15, 18).', 'wp-team-manager' ),
            'id'         => $this->prefix . 'tax_include',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '12, 15, 18',
                'pattern'     => '^[0-9,\\s]*$',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Exclude Terms (IDs)', 'wp-team-manager' ),
            'desc'       => __( 'Comma-separated term IDs to exclude (e.g., 7, 9).', 'wp-team-manager' ),
            'id'         => $this->prefix . 'tax_exclude',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '7, 9',
                'pattern'     => '^[0-9,\\s]*$',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Keyword', 'wp-team-manager' ),
            'desc'       => __( 'Search by title or content.', 'wp-team-manager' ),
            'id'         => $this->prefix . 'keyword',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => __( 'Search term…', 'wp-team-manager' ),
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'        => __( 'Date From', 'wp-team-manager' ),
            'desc'        => __( 'Start date (YYYY-MM-DD).', 'wp-team-manager' ),
            'id'          => $this->prefix . 'date_from',
            'type'        => 'text_date',
            'date_format' => 'Y-m-d',
            'attributes'  => array(
                'placeholder' => '2025-01-01',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'        => __( 'Date To', 'wp-team-manager' ),
            'desc'        => __( 'End date (YYYY-MM-DD).', 'wp-team-manager' ),
            'id'          => $this->prefix . 'date_to',
            'type'        => 'text_date',
            'date_format' => 'Y-m-d',
            'attributes'  => array(
                'placeholder' => '2025-12-31',
            ),
        ) );



        


        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Show Social icon', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'team_social_icon',
				'type'    => 'multicheck',
                'options' => array(
                    'twitter'         => __( 'Twitter', 'wp-team-manager' ),
                    'linkedin'        => __( 'LinkedIn', 'wp-team-manager' ),
                    'googleplus'      => __( 'Google Plus', 'wp-team-manager' ),
                    'dribbble'        => __( 'Dribbble', 'wp-team-manager' ),
                    'youtube'         => __( 'Youtube', 'wp-team-manager' ),
                    'vimeo'           => __( 'Vimeo', 'wp-team-manager' ),
                    'email'           => __( 'Email', 'wp-team-manager' ),
                    'instagram'       => __( 'Instagram', 'wp-team-manager' ),
                    'discord'         => __( 'Discord', 'wp-team-manager' ),
                    'tiktok'          => __( 'Tiktok', 'wp-team-manager' ),
                    'github'          => __( 'Github', 'wp-team-manager' ),
                    'stack-overflow'  => __( 'stack overflow', 'wp-team-manager' ),
                    'medium'          => __( 'Medium', 'wp-team-manager' ),
                    'telegram'        => __( 'Telegram', 'wp-team-manager' ),
                    'pinterest'       => __( 'Pinterest', 'wp-team-manager' ),
                    'square-reddit'   => __( 'Square Reddit', 'wp-team-manager' ),
                    'tumblr'          => __( 'Tumblr', 'wp-team-manager' ),
                    'quora'           => __( 'Quora', 'wp-team-manager' ),
                    'snapchat'        => __( 'Snapchat', 'wp-team-manager' ),
                    'goodreads'       => __( 'Goodreads', 'wp-team-manager' ),
                    'twitch'          => __( 'Twitch', 'wp-team-manager' ),
                    
                ),
			)
		);

        $dwl_team_metabox->add_field( 
            array(
                'name'    => __( 'Hide Job Title', 'wp-team-manager' ),
                'desc' => 'Show/hide',
                'id'      => $this->prefix . 'team_show_position',
                'type'    => 'checkbox',
            )
        );

        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Hide Other Info', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'team_show_other_info',
				'type'    => 'checkbox',
			)
		);


        $link_mobile_phone =  array(
            'name'    => __( 'Links Mobile & Telephone', 'wp-team-manager' ) .  wp_kses_post( $this->proLink ),
            'desc' => 'Show/hide',
            'id'      => $this->prefix . 'team_link_mobile_phone',
            'type'    => 'checkbox',
        );

        if( Helper::freemius_is_free_user() ){

            $link_mobile_phone['attributes'] =   array(
                'disabled' => true
            );

        }

        $dwl_team_metabox->add_field( $link_mobile_phone );

        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Hide Read More', 'wp-team-manager' ),
                'desc' =>  __( 'Show/Hide', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_show_read_more',
				'type'    => 'checkbox',
			)
		);

        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Hide Social', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'team_show_social',
				'type'    => 'checkbox',
			)
		);

        $show_progress_bar =  array(
            'name'    => __( 'Hide Progress Bar', 'wp-team-manager' ) .  wp_kses_post( $this->proLink ),
            'desc' => 'Show/hide',
            'id'      => $this->prefix . 'show_progress_bar',
            'type'    => 'checkbox',
        );

        if( Helper::freemius_is_free_user() ){

            $show_progress_bar['attributes'] =   array(
                'disabled' => true
            );

        }

        $dwl_team_metabox->add_field( $show_progress_bar );

        $hide_short_bio =  array(
            'name'    => __( 'Hide Short Bio', 'wp-team-manager' ),
            'desc' => 'Show/hide',
            'id'      => $this->prefix . 'hide_short_bio',
            'type'    => 'checkbox',
        );


        $dwl_team_metabox->add_field( $hide_short_bio );


        // $dwl_team_metabox->add_field( 
		// 	array(
		// 		'name'    => __( 'Show Pagination', 'wp-team-manager' ),
        //         'desc' => 'Show/hide',
		// 		'id'      => $this->prefix . 'show_pagination',
		// 		'type'    => 'checkbox',
        //         'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
		// 	)
		// );
        
        // NEW: add Pagination & Loading (CMB2) fields
        $this->pagination_options( $dwl_team_metabox );

        $dwl_team_metabox->add_field(
        array(
            'name'  => __( 'Enable Filter', 'wp-team-manager' ).  wp_kses_post( $this->proLink ),
            'desc'  => __( 'Show/hide', 'wp-team-manager' ),
            'id'    => $this->prefix . 'filter_enable',
            'type'  => 'checkbox',
    
            'sanitization_cb' => function( $val ) {
                if ( Helper::freemius_is_free_user() ) {
                    return '';
                }
                return $val;
            },

            'after_row' => ( Helper::freemius_is_free_user() )
                ? '<script>
                    (function(){
                        var id = ' . wp_json_encode( $this->prefix . 'filter_enable' ) . ';
                        var cb = document.getElementById(id);
                        if (cb) {
                            cb.disabled = true;
                            cb.title = "' . esc_js( __( 'Available in Premium', 'wp-team-manager' ) ) . '";
                        }
                    })();
                </script>
                '
                : '',
        'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
        ) );


  // Image Setting
        $dwl_team_metabox->add_field( 
            array(
                'name'       =>  __( 'Select image size:', 'wp-team-manager' ),
                'desc'       =>  __( 'Change image size.', 'wp-team-manager' ),
                'id'         =>  $this->prefix . 'select_image_size',
                'type'       => 'checkbox',
                'type'    => 'select',
                'options' => array(
                    'thumbnail'                     => __( 'Thumbnail', 'wp-team-manager' ),
                    'medium'                        => __( 'Medium', 'wp-team-manager' ),
                    'large'                         => __( 'Large', 'wp-team-manager' ),
                    'full'                          => __( 'Full', 'wp-team-manager' ),
                ),
            )
        );

        $dwl_team_metabox->add_field( 
            array(
                'name'       =>  __( 'Image style', 'wp-team-manager' ),
                'id'         =>  $this->prefix . 'image_style',
                'type'       => 'checkbox',
                'type'    => 'select',
                'options' => array(
                    'thumbnail' => __( 'Rounded', 'wp-team-manager' ),
                    'circle'    => __( 'Circle', 'wp-team-manager' ),
                    'boxed'     => __( 'Boxed', 'wp-team-manager' ),
                ),
                'show_on_cb' => array( $this, 'hide_on_slider_layout' ), // hide when layout is slider
            )
        );

        // === Styling & Theming ===
        $preset_options = array(
            'default'     => __( 'Default - Standard card styling', 'wp-team-manager' ),
            'minimal'     => __( 'Minimal - Clean borders, subtle shadows', 'wp-team-manager' ),
            'soft-card'   => __( 'Soft Card - Elevated with soft shadows', 'wp-team-manager' ),
            'glass'       => __( 'Glass - Modern glassmorphism effect', 'wp-team-manager' ),
            'modern'      => __( 'Modern - Bold shadows and rounded corners', 'wp-team-manager' ),
            'flat'        => __( 'Flat - No shadows, clean design', 'wp-team-manager' ),
        );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Theme Preset', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_preset',
            'type'    => 'select',
            'default' => 'default',
            'options' => $preset_options,
            'desc'    => __( 'Choose a preset style that will be applied to all team cards.', 'wp-team-manager' ),
        ) );
        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Card Shadow', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_shadow',
            'type'       => 'select',
            'default'    => 'none',
            'options'    => array(
                'none' => __( 'None - Flat design', 'wp-team-manager' ),
                'sm'   => __( 'Small - Subtle elevation', 'wp-team-manager' ),
                'md'   => __( 'Medium - Moderate depth', 'wp-team-manager' ),
                'lg'   => __( 'Large - Strong elevation', 'wp-team-manager' ),
                'xl'   => __( 'Extra Large - Maximum depth', 'wp-team-manager' ),
            ),
            'desc'       => __( 'Add depth and elevation to team cards.', 'wp-team-manager' ),
            'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
        ) );
        // Color Scheme
        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Primary Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_primary_color',
            'type'    => 'colorpicker',
            // 'default' => '#4258d6',
            'desc'    => __( 'Main brand color for buttons, links, and accents.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Background Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'team_background_color',
            'type'    => 'colorpicker',
            'default' => '',
            'desc'    => __( 'Overall background color for the team section.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Card Background', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_card_bg',
            'type'    => 'colorpicker',
            // 'default' => '#ffffff',
            'desc'    => __( 'Background color for individual team member cards.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Title Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_title_color',
            'type'    => 'colorpicker',
            // 'default' => '#1a1a1a',
            'desc'    => __( 'Color for team member names and titles.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Text Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_text_color',
            'type'    => 'colorpicker',
            // 'default' => '#666666',
            'desc'    => __( 'Color for descriptions, bio text, and other content.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Social Icon Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'social_icon_color',
            'type'    => 'colorpicker',
            // 'default' => '#fff',
            'desc'    => __( 'Color for social media icons and links.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Card Border Radius', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_border_radius',
            'type'       => 'select',
            'default'    => '',
            'options'    => array(
                ''    => __( 'Default', 'wp-team-manager' ),
                '0px'    => __( 'None (0px)', 'wp-team-manager' ),
                '4px'    => __( 'Small (4px)', 'wp-team-manager' ),
                '8px'    => __( 'Medium (8px)', 'wp-team-manager' ),
                '12px'   => __( 'Large (12px)', 'wp-team-manager' ),
                '16px'   => __( 'Extra Large (16px)', 'wp-team-manager' ),
                '50%'    => __( 'Rounded (50%)', 'wp-team-manager' ),
                'custom' => __( 'Custom Value', 'wp-team-manager' ),
            ),
            'desc'       => __( 'Choose preset or custom CSS value.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Custom Border Radius', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_border_radius_custom',
            'type'       => 'text_small',
            'attributes' => array(
                'placeholder' => '20px',
                'pattern'     => '^\\d+(?:\\.\\d+)?(?:px|rem|em|%)$',
                'data-conditional-id' => $this->prefix . 'theme_border_radius',
                'data-conditional-value' => 'custom',
            ),
            'desc'       => __( 'Enter custom value (e.g., 20px, 1.5rem).', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Grid Gap', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_gap',
            'type'       => 'select',
            'default'    => '16px',
            'options'    => array(
                '0px'    => __( 'None (0px)', 'wp-team-manager' ),
                '8px'    => __( 'Small (8px)', 'wp-team-manager' ),
                '16px'   => __( 'Medium (16px)', 'wp-team-manager' ),
                '24px'   => __( 'Large (24px)', 'wp-team-manager' ),
                '32px'   => __( 'Extra Large (32px)', 'wp-team-manager' ),
                'custom' => __( 'Custom Value', 'wp-team-manager' ),
            ),
            'desc'       => __( 'Space between team cards.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Custom Grid Gap', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_gap_custom',
            'type'       => 'text_small',
            'attributes' => array(
                'placeholder' => '20px',
                'pattern'     => '^\\d+(?:\\.\\d+)?(?:px|rem|em)$',
                'data-conditional-id' => $this->prefix . 'theme_gap',
                'data-conditional-value' => 'custom',
            ),
            'desc'       => __( 'Enter custom gap value (e.g., 20px, 1.5rem).', 'wp-team-manager' ),
        ) );

        // $dwl_team_metabox->add_field( array(
        //     'name' => __( 'Dark Mode', 'wp-team-manager' ),
        //     'id'   => $this->prefix . 'theme_dark_mode',
        //     'type' => 'checkbox',
        //     'desc' => __( 'Enable dark theme styling.', 'wp-team-manager' ),
        // ) );



        $custom_css_field = array(
            'name' => __( 'Custom CSS (scoped)', 'wp-team-manager' ),
            'id'   => $this->prefix . 'theme_custom_css',
            'type' => 'textarea_code',
            'desc' => __( 'CSS will be scoped to this team block wrapper on the front‑end.', 'wp-team-manager' ),
        );

        if ( Helper::freemius_is_free_user() ) {
            $custom_css_field['desc'] .= ' ' . wp_kses_post( $this->proLink );
            $custom_css_field['attributes'] = array( 'disabled' => true );
            $custom_css_field['sanitization_cb'] = function( $val ) {
                return ''; // Always return empty for free users
            };
        }

        $dwl_team_metabox->add_field( $custom_css_field );

    }
    // The conditional callback function
        public function hide_on_slider_layout( $field ) {
            $layout = get_post_meta( get_the_ID(), $this->prefix . 'layout_option', true );
            return ( $layout !== 'slider' ); // show only if NOT slider
        }



    function create_wp_team_manager_metaboxes() {

        // General information begin
        $dwl_team_general = new_cmb2_box( 
            array(
                'id'            => 'wptm_cm2_metabox_general',
                'title'         =>  esc_html__( 'Memeber Information', 'wp-team-manager' ),
                'object_types'  => ['team_manager'], // post type 
                'context'       => 'normal',
                'priority'      => 'high',
                'show_names'    => true
            ) 
        );

        /**
         * Short Bio
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_short_bio' ) ),
            'desc'       => esc_html__( 'Brief description or stats for this member', 'wp-team-manager' ),
            'id'         => 'tm_short_bio',
            'type'       => 'textarea',
            'classes'    => 'col-12',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'A brief description of the team member (1-2 sentences). This appears in grid and list layouts.', 'wp-team-manager' ) . '">?</div>',
        ) );

        /**
         * Long Bio
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_long_bio' ) ),
            'desc'       => esc_html__( 'Detailed biography for this member', 'wp-team-manager' ),
            'id'         => 'tm_long_bio',
            'type'       => 'wysiwyg',
            'classes'    => 'col-12',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Detailed biography with rich text formatting. Displayed on single member pages and detailed views.', 'wp-team-manager' ) . '">?</div>',
        ) );

        /**
         * Job Title / Position
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_jtitle' ) ),
            'desc'       => esc_html__( 'Title or position of this member', 'wp-team-manager' ),
            'id'         => 'tm_jtitle',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Professional title or role (e.g., Senior Developer, Marketing Manager).', 'wp-team-manager' ) . '">?</div>',
        ) );
        
        
        /**
         * Email
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_email' ) ),
            'desc'       => esc_html__( 'Provide the official email address of this member.', 'wp-team-manager' ),
            'id'         => 'tm_email',
            'type'       => 'text_email',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Business email address. Will be displayed as a clickable mailto link.', 'wp-team-manager' ) . '">?</div>',
        ) );


       /**
         * Telephone
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_telephone' ) ),
            'desc'       => esc_html__( 'Enter the telephone number.', 'wp-team-manager' ),
            'id'         => 'tm_telephone',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Office phone number with area code (e.g., +1-555-123-4567).', 'wp-team-manager' ) . '">?</div>',
        ) );

         /**
         * Mobile
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_mobile' ) ),
            'desc'       => esc_html__( 'Enter the mobile phone number.', 'wp-team-manager' ),
            'id'         => 'tm_mobile',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Personal mobile number. Can be made clickable in Pro version.', 'wp-team-manager' ) . '">?</div>',
        ) );
       

        /**
         * Location / Jersey Number
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_location' ) ),
            'desc'       => esc_html__( 'Location or jersey number', 'wp-team-manager' ),
            'id'         => 'tm_location',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'City, state/country or office location (e.g., New York, NY or Remote).', 'wp-team-manager' ) . '">?</div>',
        ) );

        /**
         * Years of Experience / Seasons
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_year_experience' ) ),
            'desc'       => esc_html__( 'Experience or seasons played', 'wp-team-manager' ),
            'id'         => 'tm_year_experience',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Number of years in their profession (e.g., 5+ years or 10 years).', 'wp-team-manager' ) . '">?</div>',
        ) );


        /**
         * Web URL
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html( Helper::get_field_label( 'tm_web_url' ) ),
            'desc'       => esc_html__( 'Official website URL.', 'wp-team-manager' ),
            'id'         => 'tm_web_url',
            'type'       => 'text',
            'classes'    => 'col-md-4',
            'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Personal website, portfolio, or company page URL (include https://).', 'wp-team-manager' ) . '">?</div>',
        ) );

        /**
         * Custom Detail URL (Pro)
         */
        $custom_detail_url = array(
            'name'       => esc_html__( 'Custom Detail URL', 'wp-team-manager' ) . wp_kses_post( $this->proLink ),
            'desc'       => esc_html__( 'Provide a custom link to replace the default detail page.', 'wp-team-manager' ),
            'id'         => 'tm_custom_detail_url',
            'type'       => 'text_url',
            'classes'    => 'col-md-4',
        );

        if ( Helper::freemius_is_free_user() ) {
            $custom_detail_url['attributes'] = array( 'disabled' => true );
        }

        $dwl_team_general->add_field( $custom_detail_url );


        /**
         * Resume URL (Pro) - Hidden in Sports Mode
         */
        if ( ! Helper::is_field_hidden( 'tm_resume_url' ) ) {
            $resume_url = array(
                'name'       => esc_html( Helper::get_field_label( 'tm_resume_url' ) ) . wp_kses_post( $this->proLink ),
                'desc'       => esc_html__( 'Link to the member\'s resume or CV (PDF or web page).', 'wp-team-manager' ),
                'id'         => 'tm_resume_url',
                'type'       => 'text_url',
                'classes'    => 'col-md-4',
            );

            if ( Helper::freemius_is_free_user() ) {
                $resume_url['attributes'] = array( 'disabled' => true );
            }

            $dwl_team_general->add_field( $resume_url );
        }

        /**
         * Hire Me URL (Pro)
         */
        $hire_me_url = array(
            'name'       => esc_html__( 'Hire Me URL', 'wp-team-manager' ) . wp_kses_post( $this->proLink ),
            'desc'       => esc_html__( 'Link to the member\'s Hire Me page or booking form.', 'wp-team-manager' ),
            'id'         => 'tm_hire_me_url',
            'type'       => 'text_url',
            'classes'    => 'col-md-4',
        );

        if ( Helper::freemius_is_free_user() ) {
            $hire_me_url['attributes'] = array( 'disabled' => true );
        }

        $dwl_team_general->add_field( $hire_me_url );

        /**
         * vCard File - Hidden in Sports Mode
         */
        if ( ! Helper::is_field_hidden( 'tm_vcard' ) ) {
            $dwl_team_general->add_field( array(
                'name'    => esc_html( Helper::get_field_label( 'tm_vcard' ) ),
                'desc'    => esc_html__( 'Upload a vCard (.vcf) file containing contact information.', 'wp-team-manager' ),
                'id'      => 'tm_vcard',
                'type'    => 'file',
                'classes'    => 'col-md-4',
                'options' => array(
                    'url' => true, // Hide the text input for the url
                ),
                'text'    => array(
                    'add_upload_file_text' => esc_html__( 'Add File', 'wp-team-manager' ) // Change upload button text
                ),
                'after'      => '<div class="cmb2-tooltip" title="' . esc_attr__( 'Upload a .vcf file that visitors can download to add contact info to their address book.', 'wp-team-manager' ) . '">?</div>',
            ) );
        }

        // General information end

        // Social profile begin
        $dwl_team_social = new_cmb2_box( 
            array(
                'id'            => 'wptm_cm2_metabox_social',
                'title'         =>  esc_html__( 'Social Profile', 'wp-team-manager' ),
                'object_types'  => ['team_manager'], // post type 
                'context'       => 'normal',
                'priority'      => 'high',
                'show_names'    => true,
                'classes'    => 'col-4',
            ) 
        );

        $dwl_team_social_id = $dwl_team_social->add_field( array(
            'id'          => 'wptm_social_group',
            'type'        => 'group',
            'repeatable'  => true,
            'options'     => array(
                'add_button'        => __( 'Add Another Profile', 'wp-team-manager' ),
                'remove_button'     => __( 'Remove Profile', 'wp-team-manager' ),
                'sortable'          => true,
                'remove_confirm' => esc_html__( 'Are you sure you want to remove?', 'wp-team-manager' ), // Performs confirmation before removing group.
            ),
            'classes'    => 'col-12',
        ) );

        $social_options = array(
            'select_type' => __( 'Select Icon', 'wp-team-manager' ),
            'facebook'       => __( 'Facebook', 'wp-team-manager' ),
            'twitter'        => __( 'Twitter', 'wp-team-manager' ),
            'linkedin'       => __( 'LinkedIn', 'wp-team-manager' ),
            'googleplus'     => __( 'Google Plus', 'wp-team-manager' ),
            'dribbble'       => __( 'Dribbble', 'wp-team-manager' ),
            'youtube'        => __( 'Youtube', 'wp-team-manager' ),
            'vimeo'          => __( 'Vimeo', 'wp-team-manager' ),
            'email'          => __( 'Email', 'wp-team-manager' ),
            'instagram'      => __( 'Instagram', 'wp-team-manager' ),
            'discord'        => __( 'Discord', 'wp-team-manager' ),
            'tiktok'         => __( 'Tiktok', 'wp-team-manager' ),
            'github'         => __( 'Github', 'wp-team-manager' ),
            'stack-overflow' => __( 'Stack Overflow', 'wp-team-manager' ),
            'medium'         => __( 'Medium', 'wp-team-manager' ),
            'telegram'       => __( 'Telegram', 'wp-team-manager' ),
            'pinterest'      => __( 'Pinterest', 'wp-team-manager' ),
            'square-reddit'  => __( 'Square Reddit', 'wp-team-manager' ),
            'tumblr'         => __( 'Tumblr', 'wp-team-manager' ),
            'quora'          => __( 'Quora', 'wp-team-manager' ),
            'snapchat'       => __( 'Snapchat', 'wp-team-manager' ),
            'goodreads'      => __( 'Goodreads', 'wp-team-manager' ),
            'twitch'         => __( 'Twitch', 'wp-team-manager' ),
            'phone'          => __( 'Phone', 'wp-team-manager' ),
            'address'        => __( 'Business Card', 'wp-team-manager' ),
            'xing'         => __( 'Xing', 'wp-team-manager' ),
        );
        
        // Allow developers to add custom social media options
        $social_options = apply_filters( 'wp_team_manager_social_options', $social_options );

        $dwl_team_social->add_group_field( $dwl_team_social_id, array(
            'name'    => __( 'Type', 'wp-team-manager' ),
            'id'      => 'type',
            'type'    => 'select',
            'options' => $social_options,
        ) );

        $dwl_team_social->add_group_field( $dwl_team_social_id, array(
            'name'    => __( 'URL', 'wp-team-manager' ),
            'id'      => 'url',
            'type'    => 'text_url',
        ) );
        
        // Social profile end

        // Member Profile image gallery 
        $dwl_image_gallery = new_cmb2_box( 
            array(
                'id'            => 'wptm_cm2_image_gallery_metabox',
                'title'         =>  esc_html__( 'Member Image Gallery', 'wp-team-manager' ),
                'object_types'  => ['team_manager'], // post type 
                'context'       => 'normal',
                'priority'      => 'high',
                'show_names'    => true
            ) 
        );

        $dwl_image_gallery->add_field(  array(
            'name'    => __( 'Upload images.', 'wp-team-manager' ),
            'id'      => 'wptm_cm2_gallery_image',
            'type'    => 'file_list',
            'options' => array(
                'url' => false,
            ),
            'classes' => 'col-12',
            'query_args' => array( 'type' => 'image' ),
            'text' => array(
                'add_upload_files_text' => __( 'Add Images', 'wp-team-manager' ), 
            ),
            'preview_size' => 'large',
            'repeatable' => false,
        ) );

        // End Member Profile image gallery 
    }


    /**
     * Add a metabox for member information pro. This metabox contains a text field for skill.
     * 
     * @since 1.0.0
     */
    function create_member_information_metabox() {

            $dwl_team_skills = new_cmb2_box( 
                array(
                    'id'            => 'wptm_cm2_member_skills_pro',
                    'title'         => esc_html__( 'Member Skills', 'wp-team-manager' ) . wp_kses_post( $this->proLink ),
                    'object_types'  => ['team_manager'],
                    'context'       => 'normal',
                    'priority'      => 'high',
                    'show_names'    => true
                ) 
            );
        
            $group_field_id = $dwl_team_skills->add_field( array(
                'id'   => 'wptm_skills_group',
                'type' => 'group',
                'desc' => 'Add skill labels and their proficiency percentage.',
                'options' => array(
                    'group_title'   => __( 'Skill {#}', 'wp-team-manager' ),
                    'add_button'    => __( 'Add Another Skill', 'wp-team-manager' ),
                    'remove_button' => __( 'Remove Skill', 'wp-team-manager' ),
                    'sortable'      => true,
                ),
            ) );
          
            $show_team_skills = array(
                'name' => __( 'Skill Label', 'wp-team-manager' ),
                'id'   => 'tm_skill_label', // Static ID instead of wp_rand()
                'type' => 'text',
            );
            
            if( Helper::freemius_is_free_user() ){
                $show_team_skills['attributes'] = array(
                    'disabled' => true
                );   
            }
            
            $dwl_team_skills->add_group_field( $group_field_id, $show_team_skills );
            
          
            $show_team_skills_percentage = array( 
                'name'       => __( 'Skill Percentage', 'wp-team-manager' ),
                'id'         => 'tm_skill_percentage', // Static ID
                'type'       => 'text',
                'attributes' => array(
                    'type' => 'number',
                    'min'  => '0',
                    'max'  => '100',
                    'step' => '5',
                ),
                'desc' => __( 'Enter a number between 0 and 100.', 'wp-team-manager' ),
            );
            
            if( Helper::freemius_is_free_user()){
                $show_team_skills_percentage['attributes']['disabled'] = true;
            }
            
            $dwl_team_skills->add_group_field( $group_field_id, $show_team_skills_percentage );
            
         
    
    }

    function wtm_eam_layout_to_add_classes($field_args, $field) {
        $classes = array(
            'row',
        );
    
        return $classes;
    }
    
}