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

    function create_meta_for_dwl_team_generator_post_type(){

        $post_id = isset($_GET['post']) && is_string($_GET['post']) ? trim($_GET['post']) : "0";

        $title = 'Copy and Past this on page or post<br/><br/><code>[dwl_create_team id="'.$post_id.'"]</code>';
        $documentation = '<a href="https://wpteammanager.com/docs/team-manager/getting-started/system-requirements/">Documentation</a>';
        $support = '<a href="https://dynamicweblab.com/submit-a-request/">Support</a>';

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
            'name' => __( 'Read Our Documentations', 'wp-team-manager' ),
            'desc' => $documentation,
            'type' => 'title',
            'id'   => $this->prefix.'dwl_team_settings_docomentation'
        ) );

        $dwl_instructions->add_field( array(
            'name' => __( 'Reach us for Support ', 'wp-team-manager' ),
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'grid'     => 'icons/grid.svg',
					'list'     => 'icons/list.svg',
					'slider'  => 'icons/slider.svg',
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Grid-1.svg',
					'style-2'     => 'icons/short-code-layout/Grid-2.svg',
				),
				'default'        => 'style-1',
                'classes'        => 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'grid') ),
                ),
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/List-1.svg',
				),
				'default'        => 'style-1',
                'classes'        => 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'list' ) ),
                ),
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
				),
				'images_path'    => TM_ADMIN_ASSETS,
				'images'         => array(
					'style-1'     => 'icons/short-code-layout/Slider-1.svg',
				),
				'default'        => 'style-1',
                'classes'        => 'col-12',
                'attributes'                 => array(
                    'data-conditional-id'    => $this->prefix . 'layout_option',
                   'data-conditional-value' => wp_json_encode( array( 'slider' ) ),
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
                            $this->prefix . 'team_background_color',
                            $this->prefix . 'team_show_other_info',
                            $this->prefix . 'team_show_social',
                            $this->prefix . 'show_progress_bar',
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
                            $this->prefix . 'social_icon_color',
                        ),
                    ),
                    
                    
                ),
                
            ) 
        );

        // General Setting
        $dwl_team_metabox->add_field( 
            array(
				'name'       =>  esc_html__(  'Select Team Group:', 'wp-team-manager' ),
				'id'         =>  $this->prefix . 'group_featured_cats',
				'type'       => 'multicheck',
				'options_cb' => 'wptm_get_taxonomy_terms'
            )
        );

        $dwl_team_metabox->add_field( 
            array(
				'name'    => __( 'Order By', 'wp-team-manager' ),
				'desc'    => __( 'Select an order by option.', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_order_by',
				'type'    => 'select',
				'default' => 'title',
				'options' => array(
					'title'    => __( 'Name ', 'wp-team-manager' ),
					'ID'       => __( 'ID', 'wp-team-manager' ),
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
                'type'   => 'number',
            ),
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Show this ids only (Example:1, 2, 3):', 'wp-team-manager' ),
            'desc'       => __( 'Only show specific team members.', 'wp-team-manager' ),
            'id'         => $this->prefix.'show_team_member_by_ids',
            'type'       => 'text',
        ) );

        $dwl_team_metabox->add_field( array(
            'name'       => __( 'Remove ids from list (Example:4, 5, 6):', 'wp-team-manager' ),
            'desc'       => __( 'Hide specific team members.', 'wp-team-manager' ),
            'id'         => $this->prefix.'remove_team_members_by_ids',
            'type'       => 'text',
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
				'name'    => __( 'Show Other Info', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'team_show_other_info',
				'type'    => 'checkbox',
			)
		);

        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Show Read More', 'wp-team-manager' ),
                'desc' =>  __( 'Hide/Show', 'wp-team-manager' ),
				'id'      => $this->prefix . 'team_show_read_more',
				'type'    => 'checkbox',
			)
		);

        $dwl_team_metabox->add_field( 
			array(
				'name'    => __( 'Show Social', 'wp-team-manager' ),
                'desc' => 'Show/hide',
				'id'      => $this->prefix . 'team_show_social',
				'type'    => 'checkbox',
			)
		);

        $show_progress_bar =  array(
            'name'    => __( 'Show Progress Bar', 'wp-team-manager' ) .  wp_kses_post( $this->proLink ),
            'desc' => 'Show/hide',
            'id'      => $this->prefix . 'show_progress_bar',
            'type'    => 'checkbox',
        );

        if( tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()){

            $show_progress_bar['attributes'] =   array(
                'disabled' => true
            );

        }

        $dwl_team_metabox->add_field( $show_progress_bar );
        
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
                    '1536x1536'                     => __( '1536x1536', 'wp-team-manager' ),
                    '2048x2048'                     => __( '2048x2048', 'wp-team-manager' ),
                    'woocommerce_thumbnail'         => __( 'Woocommerce Thumbnail', 'wp-team-manager' ),
                    'woocommerce_single'            => __( 'Woocommerce Single', 'wp-team-manager' ),
                    'woocommerce_gallery_thumbnail' => __( 'Woocommerce Gallery Thumbnail', 'wp-team-manager' ),
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
            )
        );

        $dwl_team_metabox->add_field( 
            array(
                'name'    => __( 'Social Icon Color', 'wp-team-manager' ),
                'desc'    => __( 'Set color for social icon.', 'wp-team-manager' ),
                'id'      => $this->prefix . 'social_icon_color',
                'type'    => 'colorpicker',
                'default' => '',
            )
        );

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