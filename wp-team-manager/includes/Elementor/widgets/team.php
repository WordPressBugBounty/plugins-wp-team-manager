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

/**
 * Elementor WTM Posts Widget.
 *
 * Elementor widget that inserts an embbedable content into the page, from any given URL.
 *
 * @since 1.0.0
 */
class Team extends Widget_Base
{

	/**
	 * Get widget name.
	 *
	 * Retrieve oEmbed widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget name.
	 */
	public function get_name()
	{
		return 'wtm-team-manager';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve oEmbed widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget title.
	 */
	public function get_title()
	{
		return __('Team Layouts', 'wp-team-manager');
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve oEmbed widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Widget icon.
	 */
	public function get_icon()
	{
		return 'eicon-person';
	}

	public function get_keywords()
	{
		return ['team layout grid list table'];
	}
	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the oEmbed widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Widget categories.
	 */
	public function get_categories()
	{
		return ['dwl-items'];
	}

	public function get_style_depends()
	{
		return ['wp-team-font-awesome', 'wp-team-slick', 'wp-team-slick-theme', 'wp-team-style'];
	}

	public function get_script_depends()
	{
		return ['wp-team-slick', 'wp-team-el-slider', 'wp-team-script', 'wpteam-admin-js'];
	}

	private function get_all_taxonomy($taxonomy = 'category')
	{

		$options = array();

		if (!empty($taxonomy)) {
			// Get categories for post type.
			$terms = get_terms(
				array(
					'taxonomy' => $taxonomy,
					'hide_empty' => false,
				)
			);
			if (!empty($terms)) {
				foreach ($terms as $term) {
					if (isset($term)) {
						if (isset($term->term_id) && isset($term->name)) {
							$options[$term->term_id] = $term->name;
						}
					}
				}
			}
		}

		return $options;
	}
	/**
	 * Content Query Options.
	 */
	private function query_options()
	{

		$this->start_controls_section(
			'layout',
			[
				'label' => __('Layout', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		$this->add_control(
			'layout_type',
			[
				'label' => esc_html__('Layout Type', 'wp-team-manager'),
				'type' => Controls_Manager::SELECT,
				'default' => 'grid',
				'options' => [
					'grid' => esc_html__('Grid', 'wp-team-manager'),
					'list' => esc_html__('List', 'wp-team-manager'),
					'slider' => esc_html__('Slider', 'wp-team-manager'),
					'table' => esc_html__('Table', 'wp-team-manager'),

				]
			]
		);

		//Layout Style
		$this->add_control(
			'grid_style_type',
			[
				'label' => __('Layout Style', 'wp-team-manager'),
				'type' => 'wptm_image_selector',
				'options' => [
					'style-1' => [
						'title' => esc_html__('Style 1', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Grid-1.svg',
					],
					'style-2' => [
						'title' => esc_html__('Style 2', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Grid-2.svg',
					],
					'style-3' => [
						'title' => esc_html__('Style 3', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/grid-3.svg',
					],
					'style-4' => [
						'title' => esc_html__('Style 4', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/grid-4.svg',
					],
					'style-5' => [
						'title' => esc_html__('Style 5', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/grid-5.svg',
					],
					// 'style-6' => [
					// 	'title' => esc_html__('Style 6', 'wp-team-manager'),
					// 	'url' => TM_ADMIN_ASSETS . '/icons/layout/grid-6.svg',
					// ],
				],
				'default' => 'style-1',
				'condition' => [
					'layout_type' => 'grid',
				],
			]
		);

		$this->add_control(
			'list_style_type',
			[
				'label' => __('Layout Style', 'wp-team-manager'),
				'type' => 'wptm_image_selector',
				'options' => [
					'style-1' => [
						'title' => esc_html__('Style 1', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/List-1.svg',
					],
					'style-2' => [
						'title' => esc_html__('Style 2', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/List-style-2.svg',
					],
					'style-3' => [
						'title' => esc_html__('Style 3', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS .'/icons/layout/List-3.svg',
					],
					
				],
				'default' => 'style-1',
				'condition' => [
					'layout_type' => 'list',
				],
			]
		);

		$this->add_control(
			'slider_style_type',
			[
				'label' => __('Layout Style', 'wp-team-manager'),
				'type' => 'wptm_image_selector',
				'options' => [
					'style-1' => [
						'title' => esc_html__('Style 1', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-1.svg',
					],
					'style-2' => [
						'title' => esc_html__('Style 2', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-2.svg',
					],
					'style-3' => [
						'title' => esc_html__('Style 3', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-3.svg',
					],
					'style-4' => [
						'title' => esc_html__('Style 4', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-2.svg',
					],
					'style-5' => [
						'title' => esc_html__('Style 5', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-5.svg',
					],
					'style-6' => [
						'title' => esc_html__('Style 6', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Slider-6.svg',
					],

				],
				'default' => 'style-1',
				'condition' => [
					'layout_type' => 'slider',
				],
			]
		);

		$this->add_control(
			'table_style_type',
			[
				'label' => __('Layout Style', 'wp-team-manager'),
				'type' => 'wptm_image_selector',
				'options' => [
					'style-1' => [
						'title' => esc_html__('Style 1', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Table-1.svg',
					],

					'style-2' => [
						'title' => esc_html__('Style 2', 'wp-team-manager'),
						'url' => TM_ADMIN_ASSETS . '/icons/layout/Table-2.svg',
					],
				],
				'default' => 'style-1',
				'condition' => [
					'layout_type' => 'table',
				],
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
				'condition' => [
					'layout_type!' => ['list', 'table'],
				],
			]
		);

		$this->add_control(
			'Autoplay_Speed',
			[
				'label' => esc_html__('Autoplay Speed', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => esc_html__('4000', 'wp-team-manager'),
				'placeholder' => esc_html__('Type your Autoplay Speed Number', 'wp-team-manager'),
				'condition' => [
					'layout_type' => 'slider',
				],
			]
		);

		$this->add_control(
			'enable_autoplay',
			[
				'label' => esc_html__('Enable Autoplay', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('ON', 'wp-team-manager'),
				'label_off' => esc_html__('OFF', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'layout_type' => 'slider',
				],
			]
		);

		$this->add_control(
			'show_arrow',
			[
				'label' => esc_html__('Show Arrow', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'wp-team-manager'),
				'label_off' => esc_html__('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'layout_type' => 'slider',
				],
			]
		);

		$this->add_control(
			'team_arrow_position',
			[
				'label' => esc_html__('Arrow Position', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'top-right',
				'options' => [
					'top-right' => esc_html__('Top Right', 'wp-team-manager'),
					'side' => esc_html__('Side', 'wp-team-manager'),
				],
				'condition' => [
					'layout_type' => 'slider',
				],

			]
		);

		$this->add_control(
			'show_dot_navigation',
			[
				'label' => esc_html__('Show Dot navigation', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Show', 'wp-team-manager'),
				'label_off' => esc_html__('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'layout_type' => 'slider',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __('Query', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		//TODO: Common Filter
		$this->add_control(
			'common_filters_heading',
			[
				'label' => esc_html__('Common Filters:', 'wp-team-manager'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'classes' => '',
			]
		);

		$this->add_control(
			'include',
			[
				'label' => esc_html__('Include only', 'wp-team-manager'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Enter the post IDs separated by comma for include', 'wp-team-manager'),
				'placeholder' => 'Eg. 10, 15, 17',
			]
		);

		$this->add_control(
			'exclude',
			[
				'label' => esc_html__('Exclude', 'wp-team-manager'),
				'type' => Controls_Manager::TEXT,
				'description' => esc_html__('Enter the post IDs separated by comma for exclude', 'wp-team-manager'),
				'placeholder' => 'Eg. 12, 13',
			]
		);

		$this->add_control(
			'per_page',
			[
				'label' => esc_html__('Limit', 'wp-team-manager'),
				'type' => Controls_Manager::NUMBER,
				'description' => esc_html__('The number of posts to show. Enter -1 to show all found posts.', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'advanced_filters_heading',
			[
				'label' => esc_html__('Advanced Filters:', 'wp-team-manager'),
				'type' => Controls_Manager::HEADING,
				'separator' => 'before',
				'classes' => 'tpg-control-type-heading',
			]
		);

		$this->add_control(
			'relation',
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


		$this->add_control(
			'no_posts_found_text',
			[
				'label' => esc_html__('No post found Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('No posts found.', 'wp-team-manager'),
				'placeholder' => esc_html__('Enter No post found', 'wp-team-manager'),
				'separator' => 'before',
			]
		);



		// Post categories
		$this->add_control(
			'team_groups',
			[
				'label' => __('Group', 'wp-team-manager'),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $this->get_all_taxonomy('team_groups'),

			]
		);

		// Post Tags
		$this->add_control(
			'team_department',
			[
				'label' => __('Department', 'wp-team-manager'),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $this->get_all_taxonomy('team_department'),

			]
		);

		// Post Tags
		$this->add_control(
			'team_designation',
			[
				'label' => __('Designation', 'wp-team-manager'),
				'label_block' => true,
				'type' => Controls_Manager::SELECT2,
				'multiple' => true,
				'options' => $this->get_all_taxonomy('team_designation'),

			]
		);

			// Post Tags
			$this->add_control(
				'team_genders',
				[
					'label' => __('Genders', 'wp-team-manager'),
					'label_block' => true,
					'type' => Controls_Manager::SELECT2,
					'multiple' => true,
					'options' => $this->get_all_taxonomy('team_genders'),
	
				]
			);
	
		$this->add_control(
			'advanced',
			[
				'label' => __('Advanced', 'wp-team-manager'),
				'type' => Controls_Manager::HEADING,
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

		$this->end_controls_section();

	}

	private function style_options()
	{

		//Image
		$this->start_controls_section(
			'posts_image_section',
			[
				'label' => esc_html__('Image', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
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
				// 'default' => 'boxed',
				'options' => [
					'50%' => esc_html__('Circle', 'wp-team-manager'),
					'15px' => esc_html__('Rounded', 'wp-team-manager'),
					'0' => esc_html__('Boxed', 'wp-team-manager'),
				],

				'selectors' => [
					'{{WRAPPER}} .team-member-info-content header img' => 'border-radius: {{VALUE}}',
					'{{WRAPPER}} .dwl-team-overlay' => 'border-radius: {{VALUE}}',
					'{{WRAPPER}} .team-feature-img-round:has(img)' => 'border-radius: {{VALUE}}',				
					'{{WRAPPER}} .team-member-thumbnail img' => 'border-radius: {{VALUE}}',
					'{{WRAPPER}} .dwl-table-img-wraper a img' => 'border-radius: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'posts_image',
			[
				'label' => esc_html__('Padding', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-member-info-content header img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->end_controls_section();

		//Title
		$this->start_controls_section(
			'posts_title',
			[
				'label' => esc_html__('Name', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __('Name', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member name.', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'posts_title_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-title' => 'color: {{VALUE}};',
					'{{WRAPPER}} .team-member-title a' => 'color: {{VALUE}};',
				],
			]
		);
		$this->add_control(
			'posts_title_background_color',
			[
				'label' => esc_html__('Name Background Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-head' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .style-4 .team-member-title-info' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'grid_style_type' => ['style-2', 'style-4'],
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
			'team_post_sub_title',
			[
				'label' => esc_html__('Job Title', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'show_sub_title',
			[
				'label' => __('Title', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member job title.', 'wp-team-manager'),
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


		//Department
		$this->start_controls_section(
			'team_department_control',
			[
				'label' => esc_html__('Department', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type' => 'table',
					'table_style_type' => 'style-2',
				],
			],
		);

		$this->add_control(
			'show_department',
			[
				'label' => __('Show Text', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'team_department_color',
			[
				'label' => esc_html__('Text Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-team_department span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'team_department_typography',
				'selector' => '{{WRAPPER}} .team-team_department span',
			]
		);

		$this->add_responsive_control(
			'team_department_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-team_department span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Designation
		$this->start_controls_section(
			'team_designation',
			[
				'label' => esc_html__('Designation', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type' => 'table',
					'table_style_type' => 'style-2',
				],
			]
		);

		$this->add_control(
			'show_designation',
			[
				'label' => __('Show Text', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'team_designation_color',
			[
				'label' => esc_html__('Text Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-team_designation span' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'team_designation_typography',
				'selector' => '{{WRAPPER}} .team-team_designation span',
			]
		);

		$this->add_responsive_control(
			'team_designation_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-team_designation span' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		//Number
		$this->start_controls_section(
			'team_mobile_number',
			[
				'label' => esc_html__('Number', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'table_style_type' => 'style-2',
				],
			]
		);

		$this->add_control(
			'show_team_mobile_number',
			[
				'label' => __('Show Number', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		$this->add_control(
			'team_mobile_number_color',
			[
				'label' => esc_html__('Number Color', 'wp-team-manager'),
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-mobile-info a' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'team_mobile_number_typography',
				'selector' => '{{WRAPPER}} .team-member-mobile-info a',
			]
		);

		$this->add_responsive_control(
			'team_mobile_number_margin',
			[
				'label' => esc_html__('Margin', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .team-member-mobile-info a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
			'team_show_short_bio',
			[
				'label' => __('Short Bio', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member short biography.', 'wp-team-manager'),

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

		//Other info
		$this->start_controls_section(
			'other_info',
			[
				'label' => esc_html__('Other Info', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'show_other_info',
			[
				'label' => __('Show Other Info', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member other info(E-mail,Phone Number etc).', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'other_info_elements',
			[
				'label' => esc_html__( 'Show Elements', 'wp-team-manager' ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'multiple' => true,
				'options' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? [] : [
					'tm_mobile'           => esc_html__( 'Mobile', 'wp-team-manager' ),
					'tm_telephone'        => esc_html__( 'Telephone', 'wp-team-manager' ),
					'tm_year_experience'  => esc_html__( 'Year Experience', 'wp-team-manager' ),
					'tm_location'         => esc_html__( 'Location', 'wp-team-manager' ),
					'tm_email'            => esc_html__( 'Email', 'wp-team-manager' ),
					'tm_web_url'          => esc_html__( 'Web Url', 'wp-team-manager' ),
					'tm_vcard'            => esc_html__( 'Visit Card', 'wp-team-manager' ),
				],
				'description' => Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',
			]
		);



		$this->add_control(
			'social_info_icon_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .fas' => 'color: {{VALUE}}',
					'{{WRAPPER}} .style-3 .dwl-team-elementor-layout-grid .team-member-other-info .team-member-info i.fas' => 'color: {{VALUE}}',
					'{{WRAPPER}} .style-4 .team-member-other-info .team-member-info i.fas' => 'color: {{VALUE}}',
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
			'show_social',
			[
				'label' => __('Icon', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member social icon.', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'show_social_icon_color',
			[
				'label' => esc_html__('Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .team-member-socials a i' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'show_social_icon_background_color',
			[
				'label' => esc_html__('Background Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
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

		// Social info
		$this->start_controls_section(
			'biography_heading',
			[
				'label' => esc_html__('Full Biography', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'layout_type' => 'table',
				],
			]
		);


		$this->add_control(
			'full_biograph_text',
			[
				'label' => esc_html__('Biography Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Full Biography', 'wp-team-manager'),
				'placeholder' => esc_html__('Type your button Text', 'wp-team-manager'),
			]
		);

		$this->add_control(
			'show_full_biograph',
			[
				'label' => __('Full Biography', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('On', 'wp-team-manager'),
				'label_off' => __('Off', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Switch on to show team member full biography.', 'wp-team-manager'),
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'wtm_full_biograph_typography',
				'selector' => '{{WRAPPER}} .dwl-table-full-biograph a',
			]
		);

		$this->add_control(
			'full_biography_text_color',
			[
				'label' => esc_html__('Text Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-team-elementor-layout-table .team-table-style-2 .dwl-table-icon-wraper a' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_responsive_control(
			'wtm_rfull_biograph_padding',
			[
				'label' => esc_html__('Padding', 'wp-team-manager'),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%', 'em'],
				'selectors' => [
					'{{WRAPPER}} .dwl-table-full-biograph' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);


		$this->add_control(
			'full_biography_background_color',
			[
				'label' => esc_html__('Background Color', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-table-full-biograph' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'full_biography_hover_color',
			[
				'label' => esc_html__('Hover', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .dwl-table-full-biograph:hover' => 'background-color: {{VALUE}}',
				],
			]
		);

		$this->end_controls_section();


		//Details
		$this->start_controls_section(
			'wtm_read_more',
			[
				'label' => esc_html__('Read More', 'wp-team-manager'),
				'tab' => Controls_Manager::TAB_STYLE,
				// 'condition' => [
				// 	'grid_style_type' => ['style-1', 'style-2', 'style-3', 'style-4'],
				// 	'slider_style_type' => ['style-1', 'style-2', 'style-3', 'style-4', 'style-5', 'style-6'],
				// ],
				'condition' => [
					'table_style_type!' => 'style-2',
				],
			]
		);

		$this->add_control(
			'read_more_text',
			[
				'label' => esc_html__('Button Text', 'wp-team-manager'),
				'type' => \Elementor\Controls_Manager::TEXT,
				'default' => esc_html__('Read More', 'wp-team-manager'),
				'placeholder' => esc_html__('Type your button Text', 'wp-team-manager'),
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
				'description' => __('Switch on to show team member read more.', 'wp-team-manager'),
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
					// 'popup'  => __( 'Popup', 'wp-team-manager' ),
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
				'description' => __('Change team member read more text color.', 'wp-team-manager'),
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

	



	private function image_options()
	{
		$this->start_controls_section(
			'image_section',
			[
				'label' => esc_html__('Image', 'wp-team-manager'),
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

		$this->end_controls_section();
	}
	private function popup_options()
	{

		$this->start_controls_section(
			'popup_section',
			[
				'label' => esc_html__('Popup', 'wp-team-manager') . Helper::showProFeatureLabel(),
			]

		);

		$this->add_control(
			'popup_bar_show',
			[
				'label' => __('Popup Show/Hide', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',

			]
		);

		$this->end_controls_section();
	}

	private function disable_single_member_view()
	{

		$this->start_controls_section(
			'disable_single_member_view_section',
			[
				'label' => esc_html__('Disable single member', 'wp-team-manager') . Helper::showProFeatureLabel(),
			]

		);

		$this->add_control(
			'disable_single_member',
			[
				'label' => __('Single member view', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Yes', 'wp-team-manager'),
				'label_off' => __('No', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => __('Disable single team member view.', 'wp-team-manager') . Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',
			]
		);

		$this->add_control(
			'popup_bar_show',
			[
				'label' => __('Popup Show/Hide', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',
				'condition' => [
					'disable_single_member!' => 'yes', 
				],

			]
		);


		$this->end_controls_section();
	}
	private function skills_options()
	{

		$this->start_controls_section(
			'skills_section',
			[
				'label' => esc_html__('Skills', 'wp-team-manager') . Helper::showProFeatureLabel(),
				'condition' => [
               		'layout_type!' => ['slider'], 
            ],
			]

		);

		$this->add_control(
			'progress_bar_show',
			[
				'label' => __('Progress Bar', 'wp-team-manager'),
				'type' => Controls_Manager::SWITCHER,
				'label_on' => __('Show', 'wp-team-manager'),
				'label_off' => __('Hide', 'wp-team-manager'),
				'return_value' => 'yes',
				'default' => 'yes',
				'description' => Helper::showProFeatureLink('Pro Feature'),
				'classes' => tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial() ? 'is-pro-feature' : '',

			]
		);

		$this->add_control(
			'text_color',
			[
				'label' => esc_html__('Text Color', 'textdomain'),
				'type' => \Elementor\Controls_Manager::COLOR,

				'selectors' => [
					'{{WRAPPER}} .team-member-skill-title' => 'color: {{VALUE}}',
				],
				'condition' => [
					'progress_bar_show' => 'yes',
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
					'progress_bar_show' => 'yes',
				],
			]
		);


		$this->end_controls_section();
	}

	private function search_options() {
			// AJAX Search Section
			$this->start_controls_section(
				'section_ajax_search',
				[
					'label' => esc_html__('AJAX Search', 'wp-team-manager') . Helper::showProFeatureLabel(),
					'tab' => Controls_Manager::TAB_CONTENT,
					'condition' => [
               		'layout_type!' => ['slider'], 
            ],
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
	}

	private function pro_options()
	{

		if (tmwstm_fs()->is_not_paying() && !tmwstm_fs()->is_trial()) {

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
private function pagination_options() {

    $is_pro = tmwstm_fs()->is_paying_or_trial();

    $pagination_options = [
        'none'    => esc_html__('None', 'wp-team-manager'),
        'numbers' => esc_html__('Numbers', 'wp-team-manager'),
        'ajax'    => $is_pro
                        ? esc_html__('Ajax', 'wp-team-manager') . Helper::showProFeatureLabel()
                        : esc_html__('Ajax (Pro)', 'wp-team-manager'),
    ];

    $this->start_controls_section(
        'section_pagination',
        [
            'label' => esc_html__('Pagination', 'wp-team-manager'),
            'condition' => [
                'layout_type!' => ['slider'],
            ],
        ]
    );

    $this->add_control(
        'pagination_type',
        [
            'label' => __('Pagination Type', 'wp-team-manager'),
            'type' => Controls_Manager::SELECT,
            'default' => 'none',
            'options' => $pagination_options,
            'description' => !$is_pro ? Helper::showProFeatureLink('Ajax pagination is a Pro feature.') : '',
        ]
    );

    $this->end_controls_section();
}
	/**
	 * Register oEmbed widget controls.
	 *
	 * Adds different input fields to allow the user to change and customize the widget settings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls()
	{


		$this->query_options();
		$this->image_options();
		$this->search_options();
		$this->skills_options();
		// $this->popup_options();
		$this->disable_single_member_view();
		$this->pagination_options();
		$this->pro_options();
		$this->style_options();


	}

	/**
	 * Render oEmbed widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render()
	{
		$settings = $this->get_settings_for_display();
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$offset_posts = [];
		$excluded_ids = [];
		$wrapper_calss = '';
		$per_page = isset($settings['per_page']) && !empty($settings['per_page']) ? $settings['per_page'] : get_option('posts_per_page');
		$ajax_settings_all = [];

		if ($settings['layout_type'] != 'slider') {
			$wrapper_calss = 'wtm-row g-2 g-lg-3';
		}



		$query_args = array(
			'posts_per_page' => $per_page,
			'post_type' => 'team_manager',
			'paged' => $paged
		);

		// Order by.
		if (!empty($settings['orderby'])) {
			$query_args['orderby'] = $settings['orderby'];
		}

		// Order .
		if (!empty($settings['order'])) {
			$query_args['order'] = $settings['order'];
		}

		// Order .
		if (!empty($settings['offset'])) {
			$query_args['offset'] = absint($settings['offset']);
		}

		//relation
		if (!empty($settings['relation'])) {
			$query_args['tax_relation'] = $settings['relation'];
		}

		//category
		if (isset($settings['team_groups']) && !empty($settings['team_groups'])) {

			$query_args['tax_query'][] = [
				'taxonomy' => 'team_groups',
				'field' => 'term_id',
				'terms' => $settings['team_groups'],
			];
		}

		//Tags
		if (isset($settings['team_department']) && !empty($settings['team_department'])) {

			$query_args['tax_query'][] = [
				'taxonomy' => 'team_department',
				'field' => 'term_id',
				'terms' => $settings['team_department'],
			];
		}
		if (isset($settings['team_designation']) && !empty($settings['team_designation'])) {

			$query_args['tax_query'][] = [
				'taxonomy' => 'team_designation',
				'field' => 'term_id',
				'terms' => $settings['team_designation'],
			];
		}
		if (isset($settings['team_genders']) && !empty($settings['team_genders'])) {

			$query_args['tax_query'][] = [
				'taxonomy' => 'team_genders',
				'field' => 'term_id',
				'terms' => $settings['team_genders'],
			];
		}
		
		// Date query.
		if (isset($settings['date_range'])) {
			if (strpos($settings['date_range'], 'to')) {
				$date_range = explode('to', $settings['date_range']);
				$query_args['date_query'] = [
					[
						'after' => trim($date_range[0]),
						'before' => trim($date_range[1]),
						'inclusive' => true,
					],
				];
			}
		}

		//keyword
		if (isset($settings['post_keyword']) && !empty($settings['post_keyword'])) {
			$query_args['s'] = $settings['post_keyword'];
		}

		if ($settings['exclude']) {
			$excluded_ids = explode(',', $settings['exclude']);
			$excluded_ids = array_map('trim', $excluded_ids);
			$query_args['post__not_in'] = array_unique(!empty($excluded_ids) ? (array) $excluded_ids : []);
		}
		if (!empty($settings['include'])) {
			$included_ids = explode(',', $settings['include']);
			$included_ids = array_map('trim', $included_ids);
			$query_args['post__in'] = array_unique(array_filter($included_ids));
		}

		$style_type_name = $settings['layout_type'] . '_' . 'style_type';
		$style_type = !empty($settings[$style_type_name]) ? $settings[$style_type_name] : '';

		/**
		 * Slider settings
		 * 
		 */
		$slider_settings = [];
		$slider_settings['arrows'] = isset($settings['show_arrow']) && $settings['show_arrow'] ? true : false;
		$slider_settings['dot_nav'] = isset($settings['show_dot_navigation']) && $settings['show_dot_navigation'] ? true : false;
		$slider_settings['autoplay'] = isset($settings['enable_autoplay']) && $settings['enable_autoplay'] ? true : false;
		$slider_settings['arrow_position'] = isset($settings['team_arrow_position']) ? $settings['team_arrow_position'] : 'side';
		$slider_settings['desktop'] = isset($settings['columns']) ? $settings['columns'] : '4';
		$slider_settings['tablet'] = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : '3';
		$slider_settings['mobile'] = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : '1';
		$slider_settings['speed'] = isset($settings['Autoplay_Speed']) ? $settings['Autoplay_Speed'] : '4000';

		$settings['slider_settings'] = $slider_settings;


		$desktop_column = isset($settings['columns_desktop']) ? $settings['columns_desktop'] : (
			isset($settings['columns']) ? $settings['columns'] : '4');


		$tablet_column = isset($settings['columns_tablet']) ? $settings['columns_tablet'] : 3;

		$mobile_column = isset($settings['columns_mobile']) ? $settings['columns_mobile'] : 1;

		$settings['bootstrap_class'] = Helper::get_grid_layout_bootstrap_class($desktop_column, $tablet_column, $mobile_column);


		$ajax_settings = [
			'layout_type' => $settings['layout_type'],
			'posts_per_page' => $per_page,
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
			'image_size' => $settings['image_size'],
			'progress_bar_show' => $settings['progress_bar_show'],
			'team_show_short_bio' => $settings['team_show_short_bio'],
			'slider_style_type' => $settings['slider_style_type'],
			'grid_style_type' => $settings['grid_style_type'],
			'table_style_type' => $settings['table_style_type'],
			'disable_single_member'		=> $settings['disable_single_member'],
			'popup_bar_show'			=> $settings['popup_bar_show'],
			'list_style_type' => $settings['list_style_type'],
			'bootstrap_class' => $settings['bootstrap_class'],
		];


		$ajax_settings_all = array_merge($ajax_settings, $settings);

		

		// Replace all null values with empty strings
		foreach ( $ajax_settings_all as $key => $value ) {
			if ( is_null( $value ) ) {
				$ajax_settings_all[ $key ] = '';
			}
		}


		//skip unnecessary settings
		$ajax_settings_all = array_filter(
			$ajax_settings_all,
			function( $key ) {
				return strpos( $key, '_' ) !== 0 && strpos( $key, 'background' ) !== 0 && strpos( $key, 'button' ) !== 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		//remove slider settings

		if($settings['layout_type'] !== 'slider') {
			$ajax_settings_all = array_filter(
					$ajax_settings_all,
					function( $key ) {
						return strpos( $key, 'slider' ) !== 0;
					},
					ARRAY_FILTER_USE_KEY
				);
		}

		$team_data = Helper::get_team_data($query_args);


        if (!empty($team_data)) {
                $settings['pagination_data'] = [
                    'total_pages'  => $team_data['max_num_pages'],
                    'current_page' => $paged,
                ];
            }

		?>
		<div class="dwl-team-wrapper dwl-wp-team-wrapper <?php echo esc_attr($style_type) ?>"
			data-posts-per-page="<?php echo esc_attr($per_page) ?>" data-paged="1"
			data-settings="<?php echo esc_attr(wp_json_encode($ajax_settings_all)) ?>">
			<?php

			// Render AJAX search input if enabled
			if (!empty($settings['enable_ajax_search']) && 'yes' === $settings['enable_ajax_search']) {
				 echo '<div class="dwl-team-search-wrapper">';
				echo '<input type="text" class="dwl-team-search-input" placeholder="' . esc_attr__('Search team members...', 'wp-team-manager') . '" />';
				 echo '  <span class="dwl-search-icon"><i class="fas fa-search"></i></span>';
				echo '</div>';
			}
			?>
			<div
				class="dwl-team-wrapper--main dwl-team-elementor-layout-<?php echo esc_attr($settings['layout_type']) ?> <?php echo esc_attr($wrapper_calss); ?> dwl-team-layout-<?php echo esc_attr($settings['layout_type']) ?> dwl-content-container--<?php echo esc_attr($settings['layout_type']); ?>">
				<?php 
				Helper::renderElementorLayout($settings['layout_type'], $team_data['posts'], $settings); ?>
			</div><!--.wp-team-manager-widget-->
			<?php
				$pagination = isset($settings['pagination_type']) ? $settings['pagination_type'] : 'none';
				$load_more = isset($settings['load_more_button_text']) ? $settings['load_more_button_text'] : '';
				if ('numbers' === $pagination && 'none' !== $pagination):
					?>
					<div class="dwl-team-ajax-pagination-wrap wtm-pagination-wrap" data-total-pages="<?php echo esc_attr($settings['pagination_data']['total_pages']); ?>"
						data-current-page="<?php echo esc_attr($settings['pagination_data']['current_page']); ?>"
						data-type="pagination">
					</div>
					<?php
					echo wp_kses_post(Helper::get_pagination_markup(new \WP_Query($query_args), $per_page));
				elseif ('ajax' === $pagination && 'none' !== $pagination):
					if (tmwstm_fs()->is_paying_or_trial()) {
						?>
						<div class="dwl-team-load-more-wrap">
							<button class="dwl-team-load-more-btn"><?php echo esc_html($load_more); ?></button>
						</div>

						<?php
					}
				endif; 
			?>
		</div><!--.wp-team-manager-widget-->

		<?php
	}

}