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
		return 'eicon-container-grid';
	}

	public function get_categories()
	{
		return ['dwl-items'];
	}

	public function get_keywords()
	{
		return ['team layout isotope'];
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
					],
					'style-2' => [
						'title' => esc_html__('Style 2', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/isotope-2.svg',
					],
					'style-3' => [
						'title' => esc_html__('Style 3', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Table-1.svg',
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

		if (class_exists('DWL_Wtm_Pro_Helper')) {

			$this->add_control(
				'include_team_groups',
				[
					'label' => __('Include Groups', 'wp-team-manager'),
					'label_block' => true,
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => \DWL_Wtm_Pro_Helper::get_all_taxonomy('team_groups'),
					'condition' => [
						'isotope_taxonomy' => 'team_groups',
					],
				]
			);

			$this->add_control(
				'include_team_department',
				[
					'label' => __('Include Departments', 'wp-team-manager'),
					'label_block' => true,
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => \DWL_Wtm_Pro_Helper::get_all_taxonomy('team_department'),
					'condition' => [
						'isotope_taxonomy' => 'team_department',
					],
				]
			);
	
			$this->add_control(
				'include_team_genders',
				[
					'label' => __('Include Genders', 'wp-team-manager'),
					'label_block' => true,
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => \DWL_Wtm_Pro_Helper::get_all_taxonomy('team_genders'),
					'condition' => [
						'isotope_taxonomy' => 'team_genders',
					],
				]
			);
	
			$this->add_control(
				'include_team_designation',
				[
					'label' => __('Include Designation', 'wp-team-manager'),
					'label_block' => true,
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => \DWL_Wtm_Pro_Helper::get_all_taxonomy('team_designation'),
					'condition' => [
						'isotope_taxonomy' => 'team_designation',
					],
				]
			);

		}


		$this->add_control(
			'include',
			[
				'label' => esc_html__('Include Team Members', 'wp-team-manager'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Enter the post IDs separated by comma for include', 'wp-team-manager'),
				'placeholder' => 'Eg. 10, 15, 17',
			]
		);

		$this->add_control(
			'exclude',
			[
				'label' => esc_html__('Exclude Team Members', 'the-post-grid'),
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
		$this->add_control(
			'orderby',
			[
				'label' => __('Order By', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'date',
				'options' => Helper::getOrderBy(),
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __('Order', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __('ASC', 'wp-team-manager'),
					'desc' => __('DESC', 'wp-team-manager'),
				],
			]
		);

		$this->add_control(
			'post_keyword',
			[
				'label' => esc_html__('By Keyword', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'label_block' => true,
				'placeholder' => esc_html__('Search by keyword', 'wp-team-manager'),
				'description' => esc_html__('Search by post title or content keyword', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'date_range',
			[
				'label' => esc_html__('Date Range (Start to End)', 'wp-team-manager'),
				'type' => Controls_Manager::DATE_TIME,
				'placeholder' => 'Choose date...',
				'description' => esc_html__('NB: Enter DEL button for delete date range', 'wp-team-manager'),
				'picker_options' => [
					'enableTime' => false,
					'mode' => 'range',
					'dateFormat' => 'M j, Y',
				],
			]
		);

		$this->add_control(
			'ignore_sticky_posts',
			[
				'label' => esc_html__('Ignore sticky posts at the top', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-team-manager'),
				'label_off' => esc_html__('No', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'no',
				'disabled' => true,
			]
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'isotope_ajax_search',
			[
				'label' => esc_html__('AJAX Search', 'wp-team-manager') . Helper::showProFeatureLabel(),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'enable_ajax_search',
			[
				'label' => esc_html__('Enable AJAX Search', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Yes', 'wp-team-manager'),
				'label_off' => esc_html__('No', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => '',
				'description' => Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',
			]
		);



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
			'isotope_name_title_text',
			[
				'label' => esc_html__('Name Title', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Name', 'wp-team-manager'),
				'placeholder' => esc_html__('Enter Team Name', 'wp-team-manager'),
				'condition' => [
					'isotope_name_switcher' => 'yes',
					'isotope_style_type' => 'style-3',
				],
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
			'isotope_sub_title_text',
			[
				'label' => esc_html__('Job Title Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Designation', 'wp-team-manager'),
				'placeholder' => esc_html__('Enter job title', 'wp-team-manager'),
				'condition' => [
					'isotope_sub_title' => 'yes',
					'isotope_style_type' => 'style-3',
				],
			]
		);
		
		
		$this->add_control(
			'isotope_designation_switcher_separator',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);


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
		$this->add_control(
			'isotope_bio_switcher_title_text',
			[
				'label' => esc_html__('Head Title', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Short Biography', 'wp-team-manager'),
				'placeholder' => esc_html__('Enter head title', 'wp-team-manager'),
				'condition' => [
					'isotope_bio_switcher' => 'yes',
					'isotope_style_type' => 'style-3',
				],
			]
		);



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
			'other_info_elements',
			[
				'label' => esc_html__( 'Show Elements', 'wp-team-manager' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => [
					'tm_mobile'  => esc_html__( 'Mobile', 'wp-team-manager' ),
					'tm_telephone' => esc_html__( 'Telephone', 'wp-team-manager' ),
					'tm_year_experience' => esc_html__( 'Year Experience', 'wp-team-manager' ),
					'tm_location' => esc_html__( 'Location', 'wp-team-manager' ),
					'tm_email' => esc_html__( 'Email', 'wp-team-manager' ),
					'tm_web_url' => esc_html__( 'Web Url', 'wp-team-manager' ),
					'tm_vcard' => esc_html__( 'Visit Card', 'wp-team-manager' ),
				],

			]
		);

		$this->add_control(
			'isotope_show_other_info_title_text',
			[
				'label' => esc_html__('Head Title', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Email', 'wp-team-manager'),
				'placeholder' => esc_html__('Enter head title', 'wp-team-manager'),
				'condition' => [
					'show_other_info' => 'yes',
					'isotope_style_type' => 'style-3',
				],
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

		$this->start_controls_section(
			'isotope_img_section',
			[
				'label' => esc_html__('Image Settings', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'show_image',
			[
				'label' => __('Show Image', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
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
					'{{WRAPPER}} .dwl-team-wrapper .team-isotope-feature-img-round' => 'border-radius: {{VALUE}}',
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

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'image_border',
				'label' => esc_html__('Image Border', 'wp-team-manager'),
				'selector' => '{{WRAPPER}} .team-member-info-content header img', // Change class based on your image wrapper
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

		//others info
		$this->start_controls_section(
			'others_info_icon',
			[
				'label' => esc_html__('Others Info', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);
		$this->add_control(
			'others_info_icon_color',
			[
				'label' => esc_html__('Icon Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .fas' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .team-member-other-info a, {{WRAPPER}} .team-member-other-info span',
				'label_block' => true,
			]
		);
		$this->end_controls_section();

		//skills options
		$this->start_controls_section(
			'skills_section_isotope',
			[
				'label' => esc_html__( 'Skills', 'wp-team-manager' ) . Helper::showProFeatureLabel(),
				'tab' => Controls_Manager::TAB_STYLE
			]
			
		);

		$this->add_control(
			'progress_bar_show_isotope',
			[
				'label' => __( 'Progress Bar', 'wp-team-manager' ),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __( 'On', 'wp-team-manager' ),
				'label_off' => __( 'Off', 'wp-team-manager' ),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => Helper::showProFeatureLink( 'Pro Feature' ),
			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__( 'Text Color', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::COLOR,
				
				'selectors' => [
					'{{WRAPPER}} .team-member-skill-title' => 'color: {{VALUE}}',
				],
				'condition' => [
					'progress_bar_show_isotope' => 'yes',  
				],
				
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			[
				'name' => 'content_typography',
				'selector' => '{{WRAPPER}} .team-member-skill-title',
				'label_block' => true,
				'condition' => [
					'progress_bar_show_isotope' => 'yes',  
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
			'social_icon_color',
			[
				'label' => esc_html__('Icon Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a i' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'show_social_icon_color',
			[
				'label' => esc_html__('Background Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					// '{{WRAPPER}} .fas' => 'color: {{VALUE}}',
					'{{WRAPPER}} .team-member-socials a' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'social_icon_hover_color',
			[
				'label' => esc_html__('Icon Hover Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a:hover i' => 'color: {{VALUE}};',
				],
			]
		);
		
		$this->add_control(
			'social_icon_bg_hover_color',
			[
				'label' => esc_html__('Background Hover Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a:hover' => 'background-color: {{VALUE}};',
				],
			]
		);



		$this->add_control(
			'button_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'default' => [
					'top' => '5',
					'right' => '5',
					'bottom' => '5',
					'left' => '5',
					'unit' => 'px',
				],
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			'button_text',
			[
				'label' => esc_html__('Button Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Read More', 'wp-team-manager'),
				'label_block' => true,
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

		$this->add_control(
			'wtm_read_more_background_color',
			[
				'label' => esc_html__('Background Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a.wtm-read-more' => 'background-color: {{VALUE}};',
				],
				'description' => __( 'Change background color of the read more button.', 'wp-team-manager' ),
			]
		);
		
		$this->add_control(
			'wtm_read_more_padding',
			[
				'label' => esc_html__('Padding', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} a.wtm-read-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'description' => __( 'Adjust padding of the read more button.', 'wp-team-manager' ),
			]
		);
		
		$this->add_control(
			'wtm_read_more_border_radius',
			[
				'label' => esc_html__('Border Radius', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} a.wtm-read-more' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'description' => __( 'Adjust border radius of the read more button.', 'wp-team-manager' ),
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
	//Load More

		// Load More
		$this->start_controls_section(
			'load_more_button',
			[
				'label' => esc_html__('Load More', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'pagination_type' => 'ajax',
				],
			]
		);

		$this->add_control(
			'load_more_button_text',
			[
				'label' => esc_html__('Button Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Load More', 'wp-team-manager'),
				'placeholder' => esc_html__('Type your button Text', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'load_more_button_heading',
			[
				'label' => __('Button', 'wp-team-manager'),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->start_controls_tabs('tabs_button_style');

		$this->start_controls_tab(
			'tab_button_normal',
			[
				'label' => __('Normal', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'button_text_color',
			[
				'label' => __('Text Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'default' => '',
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-btn' => 'fill: {{VALUE}}; color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'background',
				'label' => __('Background', 'wp-team-manager'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .dwl-team-load-more-btn',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_button_hover',
			[
				'label' => __('Hover', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'hover_color',
			[
				'label' => __('Text Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-btn:hover, {{WRAPPER}} .dwl-team-load-more-btn:focus' => 'color: {{VALUE}};',
					'{{WRAPPER}} .dwl-team-load-more-btn:hover svg, {{WRAPPER}} .dwl-team-load-more-btn:focus svg' => 'fill: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name' => 'button_background_hover',
				'label' => __('Background', 'wp-team-manager'),
				'types' => ['classic', 'gradient'],
				'exclude' => ['image'],
				'selector' => '{{WRAPPER}} .dwl-team-load-more-btn:hover, {{WRAPPER}} .dwl-team-load-more-btn:focus',
				'fields_options' => [
					'background' => [
						'default' => 'classic',
					],
				],
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label' => __('Border Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'condition' => [
					'border_border!' => '',
				],
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-btn:hover, {{WRAPPER}} .dwl-team-load-more-btn:focus' => 'border-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => __('Hover Animation', 'wp-team-manager'),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name' => 'border',
				'selector' => '{{WRAPPER}} .dwl-team-load-more-btn',
				'separator' => 'before',
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __('Border Radius', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name' => 'button_box_shadow',
				'selector' => '{{WRAPPER}} .dwl-team-load-more-btn',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'button_typography',
				'selector' => '{{WRAPPER}} .dwl-team-load-more-btn',
			]
		);

		$this->add_responsive_control(
			'text_padding',
			[
				'label' => __('Padding', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', 'em', '%'],
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'text_align',
			[
				'label' => esc_html__('Button Alignment', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__('Left', 'wp-team-manager'),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => esc_html__('Center', 'wp-team-manager'),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => esc_html__('Right', 'wp-team-manager'),
						'icon' => 'eicon-text-align-right',
					],
				],
				'default' => 'center',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-load-more-wrap' => 'text-align: {{VALUE}};',
				],
			]
		);


		$this->end_controls_section();


		// Color, Typography & Spacing
		$this->start_controls_section(
			'posts_article_settings',
			[
				'label' => esc_html__('Container Settings', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type' => ['slider', 'grid', 'list'],
				],
			]
		);

		$this->add_control(
			'card_background_color',
			[
				'label' => esc_html__('Card Background Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'posts_article_margin',
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
			'posts_article_padding',
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
				'name' => 'posts_article_border',
				'selector' => '{{WRAPPER}} .team-member-info-content',
			]
		);

	$this->end_controls_section();
	}

	private function pagination_options()
	{

		$pagination_options = [
			'none' => esc_html__('None', 'wp-team-manager'),
			'numbers' => esc_html__('Numbers', 'wp-team-manager'),
		];

		// Conditionally add 'Ajax' option if the user is not paying
		if (tmwstm_fs()->is_paying_or_trial()) {
			$pagination_options['ajax'] = esc_html__('Ajax', 'wp-team-manager') . Helper::showProFeatureLabel();
		}

		$this->start_controls_section(
			'section_pagination',
			[
				'label' => esc_html__('Pagination', 'wp-team-manager'),
			
			]
		);

		$this->add_control(
			'pagination_type',
			[
				'label' => __('Pagination Type', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => $pagination_options,
				'description' => Helper::showProFeatureLink('Pro Feature'),
			]
		);

		$this->end_controls_section();
	}
	protected function _register_controls()
	{
		$this->register_controls();
		$this->settings_controls();
		$this->style_options();
		$this->pagination_options();
	}


	protected function render()
	{
		$settings = $this->get_settings_for_display();

		if (tmwstm_fs()->is_paying_or_trial()) {

			if (class_exists('DWL_Wtm_Pro_Helper')) {
				$settings = $this->get_settings_for_display();
				\DWL_Wtm_Pro_Helper::IsotopeOptions($settings);
			}

			
		} else {
			return false;
		}

	}

}
