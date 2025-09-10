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

        $this->proLink = '';
        if ( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ) {
           
            $this->proLink = '<span class="wptm-pro-text">' . __( ' Pro', 'wp-team-manager' ) . '</span> <a class="wptm-pro-link" href="' . esc_url(tmwstm_fs()->get_upgrade_url()) . '">'  . __('Upgrade Now!', 'wp-team-manager') . '</a>';
        }

    }

private function pagination_options( $box ) {

    $field_id = $this->prefix . 'pagination_type';


    $all_options = array(
        'none'     => esc_html__( 'None', 'wp-team-manager' ),
        'numbers'  => esc_html__( 'Numbers', 'wp-team-manager' ),
        'ajax'     => esc_html__( 'Ajax (Load More)', 'wp-team-manager' ),
        'infinite' => esc_html__( 'Infinite Scroll', 'wp-team-manager' ),
    );

    $is_locked   = function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial();
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
            '<style>
                .wtm-sc-wrap{margin-top:6px}
                .wtm-sc-help{margin:2px 0 8px;color:#555}
                .wtm-sc-row{display:flex;gap:6px}
                .wtm-sc-input{flex:1}
                .wtm-sc-input input{width:100%%;font-family:monospace;background:#f7f7f7}
                .wtm-sc-note{margin-top:6px;color:#777;font-size:12px}
            </style>
            <div class="wtm-sc-wrap">
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
        $documentation = '<style>
        .wtm-help{margin-top:4px}
        .wtm-help .wtm-help-desc{color:#666;margin:0 0 8px;font-size:12px}
        .wtm-help .wtm-btn{display:inline-flex;align-items:center;gap:6px;margin-top:6px;padding:6px 12px;border-radius:4px;border:2px solid #A259FF;background:#fff;color:#A259FF;text-decoration:none;font-weight:600}
        .wtm-help .wtm-btn:hover{background:#A259FF;color:#fff}
        .wtm-help .dashicons{font-size:16px;line-height:1}
        </style>'
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
				'desc'           => __( 'Select Layout type', 'wp-team-manager' ),
				'id'             => $this->prefix . 'layout_option',
				'type'           => 'radio_image',
				'options'        => array(
					'grid'        => __('Grid', 'wp-team-manager'),
					'list'        => __('List', 'wp-team-manager'),
					'slider'      => __('Slider', 'wp-team-manager'),
                    'table'      => __('Table', 'wp-team-manager'),
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'grid'     => 'icons/grid.svg',
					'list'     => 'icons/list.svg',
					'slider'  => 'icons/slider.svg',
                    'table'  => 'icons/Table.svg',
				),
				'default'        => 'grid',
                'classes'        => 'col-12',
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Grid-1.svg',
					'style-2'     => 'icons/short-code-layout/Grid-2.svg',
					'style-3'     => 'icons/short-code-layout/grid-3.svg',
                    'style-4'     => 'icons/short-code-layout/grid-4.svg',
                    'style-5'     => 'icons/short-code-layout/grid-5.svg',
				),
				'default'        => 'style-1',
                'classes'        => function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'grid') ),
                ),
          'sanitization_cb' => function( $val ) {
            
            if ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) {
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
                'classes'        => function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'list' ) ),
                ),
                       'sanitization_cb' => function( $val ) {
              
                if ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) {
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Slider-1.svg',
                    'style-2'     => 'icons/short-code-layout/Slider-2.svg',
                    'style-3'     => 'icons/short-code-layout/Slider-3.svg',
                    'style-4'     => 'icons/short-code-layout/Slider-4.svg',
                    'style-5'     => 'icons/short-code-layout/Slider-5.svg',
                    'style-6'     => 'icons/short-code-layout/Slider-6.svg',
				),
				'default'        => 'style-1',
                'classes'        => function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ? 'col-12 pro-locked' : 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'slider' ) ),
                ),
          'sanitization_cb' => function( $val ) {
            
            if ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) {
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

        

        // $dwl_layout->add_field( 
		// 	array(
		// 		'name'           => __( 'Layout Style', 'wp-team-manager' ),
		// 		'desc'           => __( 'Select Layout Style', 'wp-team-manager' ),
		// 		'id'             => $this->prefix . 'style_type',
		// 		'type'           => 'radio_image',
		// 		'options'        => array(
		// 			'style-1'        => __('Style 1', 'wp-team-manager'),
		// 			'style-2'        => __('Style 2', 'wp-team-manager'),
		// 		),
		// 		'images_path'    => TM_ADMIN_ASSETS,
		// 		'images'         => array(
		// 			'style-1'     => 'icons/style-1.png',
		// 			'style-2'     => 'icons/style-2.png',
		// 		),
		// 		'default'        => 'style-1',
        //         'classes'        => 'col-12',
        // 	) 
		// );

        $dwl_layout->add_field( array(
            'name'    => __( 'Mobile', 'wp-team-manager' ),
            'id'      =>  $this->prefix . 'mobile',
            'desc' => __( 'Columns per row on phones.', 'wp-team-manager' ),
            'type'    => 'select',
            'default' => '1',
            'options' => array(
                '1'          => __( '1 Column', 'wp-team-manager' ),
                '2'          => __( '2 Column', 'wp-team-manager' ),
                '3'          => __( '3 Column', 'wp-team-manager' ),
                '4'          => __( '4 Column', 'wp-team-manager' ),
            ),
            'attributes'                 => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
                'data-conditional-value' => wp_json_encode( array( 'grid','slider' ) ),
            ),
            'classes'        => 'dwl-meta-item col-md-4',
        ) );

        $dwl_layout->add_field( array(
            'name'    => __( 'Tablet', 'wp-team-manager' ),
            'id'      =>  $this->prefix . 'tablet',
            'desc' => __( 'Columns per row on tablets.', 'wp-team-manager' ),
            'default' => '2',
            'type'    => 'select',
            'options' => array(
                '1'       => __( '1 Column', 'wp-team-manager' ),
                '2'       => __( '2 Column', 'wp-team-manager' ),
                '3'       => __( '3 Column', 'wp-team-manager' ),
                '4'       => __( '4 Column', 'wp-team-manager' ),
            ),
            'attributes'                 => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
                'data-conditional-value' => wp_json_encode( array( 'grid','slider' ) ),
            ),
            'classes'        => 'dwl-meta-item col-md-4',
        ) );

        $dwl_layout->add_field( array(
            'name'    => __( 'Desktop', 'wp-team-manager' ),
            'id'      =>  $this->prefix . 'desktop',
            'desc' => __( 'Columns per row on desktop screens.', 'wp-team-manager' ),
            'type'    => 'select',
            'default' => '3',
            'options' => array(
                '1'       => __( '1 Column', 'wp-team-manager' ),
                '2'       => __( '2 Column', 'wp-team-manager' ),
                '3'       => __( '3 Column', 'wp-team-manager' ),
                '4'       => __( '4 Column', 'wp-team-manager' ),
            ),
            'attributes'                 => array(
                'data-conditional-id'    => $this->prefix . 'layout_option',
               'data-conditional-value' => wp_json_encode( array( 'grid','slider' ) ),
            ),
            'classes'        => 'dwl-meta-item col-md-4',
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
                        'id'    => 'dwl_general_setting',
                        'icon' => 'dashicons-admin-site',
                        'title' => __( 'General Settings', 'wp-team-manager' ),
                        'fields' => array(
                            $this->prefix. 'show_total_members',
                            $this->prefix. 'team_order',
                            $this->prefix . 'team_order_by',
                            $this->prefix . 'group_featured_cats',
                             // NEW: Query & Filtering
                            $this->prefix.'tax_relation',
                            $this->prefix.'tax_include',
                            $this->prefix.'tax_exclude',
                            $this->prefix.'keyword',
                            $this->prefix.'date_from',
                            $this->prefix.'date_to',
                            $this->prefix . 'layout_option',
                            $this->prefix.'show_team_member_by_ids',
                            $this->prefix.'remove_team_members_by_ids',
                            $this->prefix.'desktop',
                            $this->prefix.'tablet',
                            $this->prefix.'mobile',
                        ),
                    ),

                    array(
                        'id'    => 'dwl_display_setting',
                        'icon' => 'dashicons-align-left',
                        'title' => __( 'Display Options', 'wp-team-manager' ),
                        'fields' => array(
                         
                            $this->prefix . 'team_show_other_info',
                            $this->prefix . 'team_show_social',
                            $this->prefix . 'show_progress_bar',
                            $this->prefix . 'hide_short_bio',
                            $this->prefix . 'show_pagination',
                            $this->prefix . 'pagination_type',
                            $this->prefix . 'filter_enable',
                            $this->prefix . 'team_show_position',
                            $this->prefix . 'team_show_read_more',
                        ),
                    ),

                    array(
                        'id'    => 'dwl_image_setting',
                        'icon' => 'dashicons-format-image',
                        'title' => __( 'Image Settings', 'wp-team-manager' ),
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
                            $this->prefix . 'theme_gap',
                            $this->prefix . 'theme_shadow',
                            $this->prefix . 'theme_dark_mode',
                            $this->prefix . 'theme_custom_css',
                        ),
                    ),                    
                    
                ),
                
            ) 
        );

        // General Setting
        $dwl_team_metabox->add_field( 
            array(
				'name'       =>  esc_html__( 'Select Team Group(s)', 'wp-team-manager' ),
				'id'         =>  $this->prefix . 'group_featured_cats',
				'type'       => 'multicheck',
				'options_cb' => 'wptm_get_taxonomy_terms'
            )
        );

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
				'name'    => __( 'Order By', 'wp-team-manager' ),
				'desc'    => __( 'Select an order by option.', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_order_by',
				'type'    => 'select',
				'default' => 'title',
				'options' => array(
					'title'    => __( 'Name ', 'wp-team-manager' ),
					'date'     => __( 'Date', 'wp-team-manager' ),
					'modified' => __( 'Modified', 'wp-team-manager' ),
					'rand'     => __( 'Random', 'wp-team-manager' ), 
				),
            )
        );

        $dwl_team_metabox->add_field( 
            array(
				'name'    => __( 'Order', 'wp-team-manager' ),
				'desc'    => __( 'Select an order option.', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_order',
				'type'    => 'select',
				'default' => 'ASC',
				'options' => array(
					'ASC'    => __( 'ASC ', 'wp-team-manager' ),
					'DESC'   => __( 'DESC', 'wp-team-manager' ),
				),				
            )
        );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Total Member(s) To Display', 'wp-team-manager' ),
            'desc'       => __( 'Number of total members to show.', 'wp-team-manager' ),
            'id'         => $this->prefix.'show_total_members',
            'type'       => 'text',
            'attributes' => array(
                'type'   => 'text',
            ),
                'attributes' => array(
                'type'        => 'number',
                'min'         => '-1',
                'step'        => '1',
                'placeholder' => '-1',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Show this ids only (Example:1, 2, 3):', 'wp-team-manager' ),
            'desc'       => __( 'Only show specific team members.', 'wp-team-manager' ),
            'id'         => $this->prefix.'show_team_member_by_ids',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '1, 2, 3',
                'pattern'     => '^[0-9,\\s]*$'
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Remove ids from list (Example:4, 5, 6):', 'wp-team-manager' ),
            'desc'       => __( 'Hide specific team members.', 'wp-team-manager' ),
            'id'         => $this->prefix.'remove_team_members_by_ids',
            'type'       => 'text',
            'attributes' => array(
                'placeholder' => '4, 5, 6',
                'pattern'     => '^[0-9,\\s]*$'
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

        if( !tmwstm_fs()->is_paying_or_trial() ){

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


        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Show Pagination', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'show_pagination',
				'type'    => 'checkbox',
                'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
			)
		);
        
        // NEW: add Pagination & Loading (CMB2) fields
        $this->pagination_options( $dwl_team_metabox );

        $dwl_team_metabox->add_field(
        array(
            'name'  => __( 'Enable Filter', 'wp-team-manager' ).  wp_kses_post( $this->proLink ),
            'desc'  => __( 'Show/hide', 'wp-team-manager' ),
            'id'    => $this->prefix . 'filter_enable',
            'type'  => 'checkbox',
    
            'sanitization_cb' => function( $val ) {
                if ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() ) {
                    return ''; // force unchecked (CMB2 checkbox খালি রাখলে false ধরা হয়)
                }
                return $val;
            },

            'after_row' => ( function_exists( 'tmwstm_fs' ) && tmwstm_fs()->is_not_paying() && ! tmwstm_fs()->is_trial() )
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
            'default'     => __( 'Default', 'wp-team-manager' ),
            'minimal'     => __( 'Minimal', 'wp-team-manager' ),
            'soft-card'   => __( 'Soft Card', 'wp-team-manager' ),
            'glass'       => __( 'Glassmorphism', 'wp-team-manager' ),
        );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Theme Preset', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_preset',
            'type'    => 'select',
            'default' => 'default',
            'options' => $preset_options,
            'desc'    => __( 'Quickly apply a preset style to cards.', 'wp-team-manager' ),
        ) );
    $dwl_team_metabox->add_field( array(
            'name'       => __( 'Card Shadow', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_shadow',
            'type'       => 'select',
            'default'    => 'sm',
            'options'    => array(
                'none' => __( 'None', 'wp-team-manager' ),
                'sm'   => __( 'Small', 'wp-team-manager' ),
                'md'   => __( 'Medium', 'wp-team-manager' ),
                'lg'   => __( 'Large', 'wp-team-manager' ),
            ),
            'show_on_cb' => array( $this, 'hide_on_slider_layout' ),
        ) );
        // Display Option
        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Background Color', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_background_color',
				'type'    => 'colorpicker',
                'default' => '',
			)
		);
        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Primary Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_primary_color',
            'type'    => 'colorpicker',
            'default' => '',
            'desc'    => __( 'Links, buttons and accents.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Card Background', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_card_bg',
            'type'    => 'colorpicker',
            'default' => '',
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Title Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_title_color',
            'type'    => 'colorpicker',
            'default' => '',
        ) );

        $dwl_team_metabox->add_field( array(
            'name'    => __( 'Body Text Color', 'wp-team-manager' ),
            'id'      => $this->prefix . 'theme_text_color',
            'type'    => 'colorpicker',
            'default' => '',
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Card Border Radius', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_border_radius',
            'type'       => 'text_small',
            'attributes' => array(
                'placeholder' => '12px',
            ),
            'desc'       => __( 'Accepts CSS units (e.g., 8px, 0.5rem).', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Grid Gap', 'wp-team-manager' ),
            'id'         => $this->prefix . 'theme_gap',
            'type'       => 'text_small',
            'attributes' => array(
                'placeholder' => '16px',
            ),
            'desc'       => __( 'Space between team cards.', 'wp-team-manager' ),
        ) );

        $dwl_team_metabox->add_field( 
            array(
                'name'    => __( 'Social Icon Color', 'wp-team-manager' ),
                'desc'    => __( 'Set color for social icon.', 'wp-team-manager' ),
                'id'      => $this->prefix . 'social_icon_color',
                'type'    => 'colorpicker',
                'default' => '',
            )
        );

        // $dwl_team_metabox->add_field( array(
        //     'name'    => __( 'Dark Mode', 'wp-team-manager' ),
        //     'id'      => $this->prefix . 'theme_dark_mode',
        //     'type'    => 'checkbox',
        //     'desc'    => __( 'Invert colors for dark backgrounds.', 'wp-team-manager' ),
        // ) );

        $custom_css_field = array(
            'name' => __( 'Custom CSS (scoped)', 'wp-team-manager' ),
            'id'   => $this->prefix . 'theme_custom_css',
            'type' => 'textarea_code',
            'desc' => __( 'CSS will be scoped to this team block wrapper on the front‑end.', 'wp-team-manager' ),
        );

        $custom_css_field['desc'] .= ' ' . wp_kses_post( $this->proLink );
        $custom_css_field['attributes'] = array( 'disabled' => true );

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
            'name'       => esc_html__( 'Short Bio', 'wp-team-manager' ),
            'desc'       => esc_html__( 'Short Bio of this team member', 'wp-team-manager' ),
            'id'         => 'tm_short_bio',
            'type'       => 'textarea',
            'classes'    => 'col-12',
        ) );

        /**
         * Long Bio
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Long Bio', 'wp-team-manager' ),
            'desc'       => esc_html__( 'Long Bio of this team member', 'wp-team-manager' ),
            'id'         => 'tm_long_bio',
            'type'       => 'wysiwyg',
            'classes'    => 'col-12',
        ) );

        /**
         * Job Title
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Job Title', 'wp-team-manager' ),
            'desc'       => esc_html__( 'Job title of this team member', 'wp-team-manager' ),
            'id'         => 'tm_jtitle',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );
        
        
        /**
         * Email
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Email Address', 'wp-team-manager' ),
            //'desc'       => esc_html__( 'Telephone number of this team member', 'wp-team-manager' ),
            'id'         => 'tm_email',
            'type'       => 'text_email',
            'classes'    => 'col-md-4',
        ) );


       /**
         * Telephone
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Telephone (Office)', 'wp-team-manager' ),
            //'desc'       => esc_html__( 'Telephone number of this team member', 'wp-team-manager' ),
            'id'         => 'tm_telephone',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );

         /**
         * Mobile
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Mobile (Personal)', 'wp-team-manager' ),
            //'desc'       => esc_html__( 'Telephone number of this team member', 'wp-team-manager' ),
            'id'         => 'tm_mobile',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );
       

        /**
         * Location
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Location', 'wp-team-manager' ),
            'desc'       => esc_html__( 'Location of this team member', 'wp-team-manager' ),
            'id'         => 'tm_location',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );

        /**
         * Years of Experience
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Years of Experience', 'wp-team-manager' ),
            'id'         => 'tm_year_experience',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );


        /**
         * Web URL
         */
        $dwl_team_general->add_field( array(
            'name'       => esc_html__( 'Web URL', 'wp-team-manager' ),
            'desc'       => esc_html__( 'Website url of this team member', 'wp-team-manager' ),
            'id'         => 'tm_web_url',
            'type'       => 'text',
            'classes'    => 'col-md-4',
        ) );

        /**
         * Image
         */
        $dwl_team_general->add_field( array(
            'name'    => 'Add vCard File',
            'desc'    => 'Upload a File',
            'id'      => 'tm_vcard',
            'type'    => 'file',
            'classes'    => 'col-md-4',
            // Optional:
            'options' => array(
                //'url' => false, // Hide the text input for the url
                'url' => true, // Hide the text input for the url
            ),
            'text'    => array(
                'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
            ),
            // query_args are passed to wp.media's library query.
            'preview_size' => 'medium', // Image size to use when previewing in the admin.
        ), 
        );

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
            
            if( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ){
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
            
            if( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()){
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