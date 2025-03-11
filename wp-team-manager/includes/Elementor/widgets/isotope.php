<?php
namespace DWL\Wtm\Elementor\Widgets;

if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

use DWL\Wtm\Classes\Helper;
use \Elementor\Controls_Manager;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use \Elementor\Group_Control_Background;
use \Elementor\Plugin;
use \Elementor\Utils;
use \Elementor\Widget_Base;

class Isotope extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'wtm-team-isotope';
	}

	public function get_title()
	{
		return __('Isotope Layout', 'wp-team-manager');
	}

	public function get_icon()
	{
		return 'eicon-user-circle-o';
	}

	public function get_categories()
	{
		return ['dwl-items'];
	}

	public function get_keywords()
	{
		return ['team layout'];
	}
	public function get_style_depends()
	{
		return ['wp-team-font-awesome', 'wp-team-style', 'wp-team-isotope'];
	}

	public function get_script_depends()
	{
		return ['wtm-isotope-js', 'wp-team-isotope'];
	}

	protected function register_controls()
	{
		$this->start_controls_section(
			'isotope',
			[
				'label' => __('Isotope', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'isotope_style_type',
			[
				'label' => __('Layout Styles', 'wp-team-manager'),
				'type' => 'wptm_image_selector',
				'options' => [
					'style-1' => [
						'title' => esc_html__('Style 1', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/isotope-1.svg',
					]
				],
				'default' => 'style-1',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'isotope-columns',
			[
				'label' => __('Columns', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __('Columns', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
				],

			]
		);

		// $this->add_control(
		// 	'isotope_column_style_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_grid_style',
		// 	[
		// 		'label' => esc_html__( 'Grid Styles', 'wp-team-manager' ),
		// 		'type' => Controls_Manager::SELECT,
		// 		'default' => 'masonry', 
		// 		'options' => [
		// 			'masonry' => esc_html__( 'Masonry', 'wp-team-manager' ),
		// 			'grid_2' => esc_html__( 'Grid 2', 'wp-team-manager' ),
		// 			'grid_3' => esc_html__( 'Grid 3', 'wp-team-manager' ),
		// 			'grid_4' => esc_html__( 'Grid 4', 'wp-team-manager' ),
		// 		]
		// 	]
		// );

		$this->end_controls_section();

		$this->start_controls_section(
			'isotope_section_query',
			[
				'label' => __('Query', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);


		$this->add_control(
			'isotope_taxonomy',
			[
				'label' => esc_html__('Taxonomies', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'team_groups',
				'options' => [
					'team_groups' => __('Group', 'wp-team-manager'),
					'team_department' => __('Department', 'wp-team-manager'),
					'team_genders' => __('Gender', 'wp-team-manager'),
					'team_designation' => __('Designation', 'wp-team-manager'),
				],
			]
		);

		$this->add_control(
			'isotope_include',
			[
				'label' => esc_html__('Include only', 'wp-team-manager'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Enter the post IDs separated by comma for include', 'wp-team-manager'),
				'placeholder' => 'Eg. 10, 15, 17',
			]
		);

		$this->add_control(
			'isotope_exclude',
			[
				'label' => esc_html__('Exclude', 'the-post-grid'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Enter the post IDs separated by comma for exclude', 'wp-team-manager'),
				'placeholder' => 'Eg. 12, 13',
			]
		);

		$this->add_control(
			'isotope_per_page',
			[
				'label' => esc_html__('Limit', 'wp-team-manager'),
				'type' => Controls_Manager::NUMBER,
				'description' => esc_html__('The number of posts to show. Enter -1 to show all found posts.', 'the-post-grid'),
			]
		);

		$this->add_control(
			'isotope_advanced_filters_heading',
			[
				'label' => esc_html__('Advanced Filters:', 'wp-team-manager'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'classes' => 'tpg-control-type-heading',
			]
		);

		$this->add_control(
			'isotope_relation',
			[
				'label' => esc_html__('Taxonomies Relation', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'OR',
				'options' => [
					'OR' => __('OR', 'wp-team-manager'),
					'AND' => __('AND', 'wp-team-manager'),
				],
			]
		);

		$this->end_controls_section();


		$this->start_controls_section(
			'isotope_img_section',
			[
				'label' => esc_html__('Image Settings', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => __('Show Image', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);



		$this->add_control(
			'image_style',
			[
				'label' => esc_html__('Border Style', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => '0',
				'options' => [
					'50%' => esc_html__('Circle', 'wp-team-manager'),
					'15px' => esc_html__('Rounded', 'wp-team-manager'),
					'0' => esc_html__('Boxed', 'wp-team-manager'),
				],
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content header img' => 'border-radius: {{VALUE}}',
					'{{WRAPPER}} .dwl-table-img-wraper a img' => 'border-radius: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'image_size',
			[
				'label' => esc_html__('Image Size', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'medium',
				'options' => [
					'thumbnail' => esc_html__('Thumbnail', 'wp-team-manager'),
					'medium' => esc_html__('Medium', 'wp-team-manager'),
					'large' => esc_html__('Large', 'wp-team-manager'),
					'full' => esc_html__('Full', 'wp-team-manager'),
				],
			]
		);

		// $this->add_control(
		// 	'isotope_show_feature_img',
		// 	[
		// 		'label' => esc_html__( 'Show Feature Image', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Show', 'wp-team-manager' ),
		// 		'label_off' => esc_html__( 'Hide', 'wp-team-manager' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	],
		// );

		// $this->add_control(
		// 	'isotope_show_feature_img_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_img_width',
		// 	[
		// 		'label' => esc_html__( 'Image Width', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SELECT,
		// 		'default' => 'medium',
		// 		'options' => [
		// 			'thumbnail' => esc_html__( 'Thumbnail (150 x 150)', 'wp-team-manager' ),
		// 			'medium' => esc_html__( 'Small (300 x 300)', 'wp-team-manager' ),
		// 			'large' => esc_html__( 'Large (1024 x 1024)', 'wp-team-manager' ),
		// 			'full' => esc_html__( 'Full', 'wp-team-manager' ),
		// 		],
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_img_width_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_grayscale_img',
		// 	[
		// 		'label' => esc_html__( 'Grayscale Image', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Show', 'wp-team-manager' ),
		// 		'label_off' => esc_html__( 'Hide', 'wp-team-manager' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	],
		// );

		$this->end_controls_section();

		$this->start_controls_section(
			'isotope_pagination',
			[
				'label' => esc_html__('Pagination', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label' => __('Pagination Type', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => esc_html__('None', 'wp-team-manager'),
					'numbers' => esc_html__('Numbers', 'wp-team-manager'),
				],
			]
		);

		// $this->add_control(
		// 	'isotope_show_pagination',
		// 	[
		// 		'label' => esc_html__( 'Show Ajax Pagination', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Show', 'textdomain' ),
		// 		'label_off' => esc_html__( 'Hide', 'textdomain' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		$this->end_controls_section();

		if(tmwstm_fs()-> is_not_paying() && !tmwstm_fs()->is_trial()){

			//Pro tab info
			$this->start_controls_section(
				'wtm_pro_info',
				[
					'label' => esc_html__('Go Premium for More Features', 'wp-team-manager'),
					'tab' => Controls_Manager::TAB_CONTENT,
	
				]
			);

			$this->add_control(
				'pro_notice',
				[
					'type' => \Elementor\Controls_Manager::RAW_HTML,
					'raw' => '<div class="team-pro-notice">
								<h3>Unlock more possibilities</h3>
								<p>Get the <strong style="color: #ff4a4a;">PRO VERSION</strong> for more stunning layouts and customization options.</p>
								<a class="team-go-pro" href="' . esc_url(tmwstm_fs()->get_upgrade_url()) . '">Get Pro</a>
							</div>',
				]
			);

			$this->end_controls_section();
		}

	}

	private function settings_controls()
	{
		$this->start_controls_section(
			'isotope_content_visibility',
			[
				'label' => esc_html__('Content Visibility', 'wp-team-manager'),
				'tab' => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->start_controls_tabs('style_tabs');

		$this->start_controls_tab(
			'style_details_tab',
			[
				'label' => esc_html__('Details', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'isotope_filter_action',
			[
				'label' => esc_html__('Enable Isotope Filters Button', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('On', 'wp-team-manager'),
				'label_off' => esc_html__('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'no',
				'description' => __( 'Switch on to show team member isotope filters button.', 'wp-team-manager' ),
			],
		);

		$this->add_control(
			'isotope_name_switcher',
			[
				'label' => esc_html__('Name', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('On', 'wp-team-manager'),
				'label_off' => esc_html__('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Switch on to show team member name.', 'wp-team-manager' ),
			]
		);
		$this->add_control(
			'isotope_name_switcher_separator',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);
		$this->add_control(
			'isotope_sub_title',
			[
				'label' => esc_html__('Job Title', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('On', 'wp-team-manager'),
				'label_off' => esc_html__('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Switch on to show team job title.', 'wp-team-manager' ),
			]
		);
		$this->add_control(
			'isotope_designation_switcher_separator',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

		// $this->add_control(
		// 	'isotope_department_switcher',
		// 	[
		// 		'label' => esc_html__('Show Department', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_department_switcher_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		$this->add_control(
			'isotope_bio_switcher',
			[
				'label' => esc_html__('Short Biography', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('On', 'wp-team-manager'),
				'label_off' => esc_html__('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Switch on to show team short biography.', 'wp-team-manager' ),
			]
		);

		// $this->add_control(
		// 	'isotope_bio_switcher_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_skill_switch',
		// 	[
		// 		'label' => esc_html__('Show Team Member Skill', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		$this->end_controls_tab();

		// Contact Tab
		$this->start_controls_tab(
			'style_contact_tab',
			[
				'label' => esc_html__('Contact', 'wp-team-manager'),
			]
		);
		$this->add_control(
			'show_other_info',
			[
				'label' => __('Other Info', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Switch on to show team member other info(E-mail,Phone Number etc).', 'wp-team-manager' ),

			]
		);

		$this->add_control(
			'show_read_more',
			[
				'label' => __('Read More', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __( 'Switch on to show team member read more.', 'wp-team-manager' ),

			]
		);

		// $this->add_control(
		// 	'isotope_team_member_email_switch',
		// 	[
		// 		'label' => esc_html__('Show Email Address', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_email_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_telephone_switch',
		// 	[
		// 		'label' => esc_html__('Show Telephone Number', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_telephone_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_mobile_switch',
		// 	[
		// 		'label' => esc_html__('Show Mobile Number', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_mobile_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_fax_switch',
		// 	[
		// 		'label' => esc_html__('Show Fax', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_fax_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_location_switch',
		// 	[
		// 		'label' => esc_html__('Show Location', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_location_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_team_member_website_switch',
		// 	[
		// 		'label' => esc_html__('Show Website URL', 'wp-team-manager'),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__('Show', 'wp-team-manager'),
		// 		'label_off' => esc_html__('Hide', 'wp-team-manager'),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	]
		// );

		$this->add_control(
			'isotope_website_separator',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

		$this->end_controls_tab();

		// Social Tab
		$this->start_controls_tab(
			'style_social_tab',
			[
				'label' => esc_html__('Social', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'isotope_social_media_switch',
			[
				'label' => esc_html__('Social Media', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('On', 'wp-team-manager'),
				'label_off' => esc_html__('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'no',
				'description' => __( 'Switch on to show team member social media.', 'wp-team-manager' ),
			]
		);

		// $this->add_control(
		// 	'isotope_social_separator',
		// 	[
		// 		'type' => \Elementor\Controls_Manager::DIVIDER,
		// 	]
		// );

		// $this->add_control(
		// 	'icons',
		// 	[
		// 		'label' => esc_html__( 'Which icons to show', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SELECT2,
		// 		'label_block' => true,
		// 		'multiple' => true,
		// 		'options' => [
		// 			'facebook'  => esc_html__( 'Facebook', 'wp-team-manager' ),
		// 			'twitter' => esc_html__( 'Twitter', 'wp-team-manager' ),
		// 			'linkedin' => esc_html__( 'Linkedin', 'wp-team-manager' ),
		// 			'pinterest' => esc_html__('Pinterest', 'wp-team-manager'),
		// 		],
		// 		'default' => [ 'title', 'description' ],
		// 	]
		// );



		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();


		$this->start_controls_section(
			'isotope_filters',
			[
				'label' => __('Isotope Filters Button', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_SETTINGS,
			]
		);

		$this->add_control(
			'isotope_filter_button_text_color',
			[
				'label' => esc_html__('Text color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-isotope-container button' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'isotope_filter_typography',
				'selector' => '{{WRAPPER}} .dwl-team-isotope-container button',
			]
		);

		$this->add_control(
			'isotope_filter_background_color',
			[
				'label' => esc_html__('Background color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-isotope-container button' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'isotope_filter_hover_background_color',
			[
				'label' => esc_html__('Hover background color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-isotope-container button:hover' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'isotope_filter_active_background_color',
			[
				'label' => esc_html__('Active background color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-isotope-container .dwl-team-filter-button.active' => 'background-color: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();


		// $this->start_controls_section(
		// 	'isotope_details_page',
		// 	[
		// 		'label' => __( 'Details Page', 'wp-team-manager' ),
		// 		'tab' => Controls_Manager::TAB_SETTINGS,
		// 	]
		// );

		// $this->add_control(
		// 	'isotope_link_to_details_page',
		// 	[
		// 		'label' => esc_html__( 'Link to Details Page', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::SWITCHER,
		// 		'label_on' => esc_html__( 'Show', 'wp-team-manager' ),
		// 		'label_off' => esc_html__( 'Hide', 'wp-team-manager' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'no',
		// 	],
		// );

		// $this->end_controls_section();


		// $this->start_controls_section(
		// 	'isotope_content_limit',
		// 	[
		// 		'label' => __( 'Content Limit', 'wp-team-manager' ),
		// 		'tab' => Controls_Manager::TAB_SETTINGS,
		// 	]
		// );

		// $this->add_control(
		// 	'short_bio_limit',
		// 	[
		// 		'label' => esc_html__( 'Short Biography Limit', 'wp-team-manager' ),
		// 		'type' => \Elementor\Controls_Manager::NUMBER,
		// 		'min' => 10,
		// 		'max' => 50,
		// 		'step' => 1,
		// 		'default' => 10,
		// 	]
		// );

		// $this->end_controls_section();

	}

	private function style_options()
	{
		$this->start_controls_section(
			'isotope_container_settings',
			[
				'label' => esc_html__('Container Settings', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'isotope_background_color',
			[
				'label' => esc_html__('Card Background Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'isotope_container_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'isotope_container_padding',
			[
				'label' => esc_html__('Padding', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'isotope_container_border',
				'selector' => '{{WRAPPER}} .team-member-info-content',
			]
		);

		$this->end_controls_section();

		//Title
		$this->start_controls_section(
			'posts_title',
			[
				'label' => esc_html__('Title', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'posts_title_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-title' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'posts_title_typography',
				'selector' => '{{WRAPPER}} .team-member-title',
			]
		);

		$this->add_responsive_control(
			'posts_title_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-member-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Sub Title
		$this->start_controls_section(
			'posts_sub_title',
			[
				'label' => esc_html__('Job Title', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		// $this->add_control(
		// 	'show_sub_title',
		// 	[
		// 		'label' => __( 'Show Job Title', 'wp-team-manager' ),
		// 		'type' => Controls_Manager::SWITCHER,
		// 		'label_on' => __( 'Show', 'wp-team-manager' ),
		// 		'label_off' => __( 'Hide', 'wp-team-manager' ),
		// 		'return_value' => 'yes',
		// 		'default' => 'yes',
		// 	]
		// );

		$this->add_control(
			'posts_sub_title_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-position' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'posts_sub_title_typography',
				'selector' => '{{WRAPPER}} .team-position',
			]
		);

		$this->add_responsive_control(
			'posts_sub_title_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-position' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Team Short Bio
		$this->start_controls_section(
			'team_short_bio',
			[
				'label' => esc_html__('Short Biography', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);



		$this->add_control(
			'team_short_bio_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-short-bio' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'posts_excerpt_typography',
				'selector' => '{{WRAPPER}} .team-short-bio',
			]
		);

		$this->add_responsive_control(
			'posts_excerpt_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-short-bio' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Social info
		$this->start_controls_section(
			'social_icon_heading',
			[
				'label' => esc_html__('Social Icon', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'show_full_biograph',
			[
				'label' => __('Show Full Biograph', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'table_style_type' => 'style-2',
				],
				'description' => __( 'Switch on to show team member full biography.', 'wp-team-manager' ),
			]
		);


		$this->add_control(
			'show_social_icon_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .fas' => 'color: {{VALUE}}',
					'{{WRAPPER}} .team-member-socials a' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'show_social_icon_hover_color',
			[
				'label' => esc_html__('Hover', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();

		//Details
		$this->start_controls_section(
			'wtm_read_more',
			[
				'label' => esc_html__('Read More', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);


		$this->add_control(
			'read_more_type',
			[
				'label' => esc_html__('Link Type', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'link',
				'options' => [
					'link' => __('Link', 'wp-team-manager'),
					//'popup'  => __( 'Popup', 'wp-team-manager' ),
				],
			]
		);


		$this->add_control(
			'wtm_read_more_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .wtm-read-more' => 'color: {{VALUE}};',
				],
				'description' => __( 'Change team member read more text color.', 'wp-team-manager' ),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'wtm_read_more_typography',
				'selector' => '{{WRAPPER}} .wtm-read-more',
			]
		);

		$this->add_responsive_control(
			'wtm_read_more_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .wtm-read-more' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();



	}


	protected function _register_controls()
	{
		$this->register_controls();
		$this->settings_controls();
		$this->style_options();
	}


	protected function render()
	{


		if (tmwstm_fs()->is_paying_or_trial()) {

			
			
			if (class_exists('DWL_Wtm_Pro')) {
				$settings = $this->get_settings_for_display();
				$obj_isotop = new \DWL_Wtm_Pro();
				$obj_isotop->IsotopeOptions($settings);

			}

		} else {
			return false;
		}

	}

}
