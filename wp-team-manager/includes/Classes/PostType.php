<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * metabox Class
 */
class PostType {

    use \DWL\Wtm\Traits\Singleton;

    /**
	 * Post Type Slug.
	 *
	 * @var string
	 */
	private $team_slug;

    protected function init(){

        $tm_slug = get_option( 'tm_slug' );

        $this->team_slug = isset( $tm_slug ) ? ( $tm_slug ? sanitize_title_with_dashes( $tm_slug ) : 'team-details' ) : 'team-details';

        $this->register_team_manager();
    }

    /**
     * register the custom post type for the team manager
     */
    public function register_team_manager() {

        $labels = array( 
            'name' => __( 'Team', 'wp-team-manager' ),
            'singular_name' => __( 'Team Member', 'wp-team-manager' ),
            'add_new' => __( 'Add New Team Member', 'wp-team-manager' ),
            'add_new_item' => __( 'Add New Team Member ', 'wp-team-manager' ),
            'edit_item' => __( 'Edit Team Member ', 'wp-team-manager' ),
            'new_item' => __( 'New Team Member', 'wp-team-manager' ),
            'view_item' => __( 'View Team Member', 'wp-team-manager' ),
            'search_items' => __( 'Search Team Members', 'wp-team-manager' ),
            'not_found' => __( 'Not found any Team Member', 'wp-team-manager' ),
            'not_found_in_trash' => __( 'No Team Member found in Trash', 'wp-team-manager' ),
            'parent_item_colon' => __( 'Parent Team Member:', 'wp-team-manager' ),
            'menu_name' => __( 'Team', 'wp-team-manager' ),
        );
        
        $args = array( 
            'labels' => $labels,
            'hierarchical' => false,        
            'supports' => array( 'title', 'thumbnail','editor','page-attributes'),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,       
            'show_in_nav_menus' => true,
            'publicly_queryable' => true,
            'exclude_from_search' => false,
            'has_archive' => true,
            'show_in_rest' => true, // To use Gutenberg editor.
            'query_var' => true,
            'can_export' => true,
            'rewrite' => true,
            'capability_type' => 'post',
            'menu_icon' => 'dashicons-groups',
            'rewrite' => array( 'slug' => $this->team_slug )

        );

        register_post_type( 'team_manager', $args );

        unset( $args );
        unset( $labels );

        /**
         * register Group Taxonomy for the team manager
         */
        $labels = array(
            'name'                       => __( 'Groups', 'wp-team-manager' ),
            'singular_name'              => __( 'Group', 'wp-team-manager' ),
            'search_items'               => __( 'Search Groups', 'wp-team-manager' ),
            'popular_items'              => __( 'Popular Groups', 'wp-team-manager' ),
            'all_items'                  => __( 'All Groups', 'wp-team-manager' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Group', 'wp-team-manager' ),
            'update_item'                => __( 'Update Group', 'wp-team-manager' ),
            'add_new_item'               => __( 'Add New Group', 'wp-team-manager' ),
            'new_item_name'              => __( 'New Group Name', 'wp-team-manager' ),
            'separate_items_with_commas' => __( 'Separate Groups with commas', 'wp-team-manager' ),
            'add_or_remove_items'        => __( 'Add or remove Groups', 'wp-team-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used Groups', 'wp-team-manager' ),
            'not_found'                  => __( 'No Groups found.', 'wp-team-manager' ),
            'menu_name'                  => __( 'Groups', 'wp-team-manager' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_rest'          => true, // To use Gutenberg editor.
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'team_groups' ),
        );

        register_taxonomy( 'team_groups', 'team_manager', $args );


        unset( $args );
        unset( $labels );

        /**
         * register Designation Taxonomy for the team manager
         */
        $labels = array(
            'name'                       => __( 'Designations', 'wp-team-manager' ),
            'singular_name'              => __( 'Designation', 'wp-team-manager' ),
            'search_items'               => __( 'Search Designation', 'wp-team-manager' ),
            'popular_items'              => __( 'Popular Designation', 'wp-team-manager' ),
            'all_items'                  => __( 'All Designations', 'wp-team-manager' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Designation', 'wp-team-manager' ),
            'update_item'                => __( 'Update Designation', 'wp-team-manager' ),
            'add_new_item'               => __( 'Add New Designation', 'wp-team-manager' ),
            'new_item_name'              => __( 'New Designation', 'wp-team-manager' ),
            'separate_items_with_commas' => __( 'Separate Designations with commas', 'wp-team-manager' ),
            'add_or_remove_items'        => __( 'Add or remove Designation', 'wp-team-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used Designation', 'wp-team-manager' ),
            'not_found'                  => __( 'No Designation found.', 'wp-team-manager' ),
            'menu_name'                  => __( 'Designations', 'wp-team-manager' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_rest'          => true, // To use Gutenberg editor.
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'team_designation' ),
        );

        register_taxonomy( 'team_designation', 'team_manager', $args );

        unset( $args );
        unset( $labels );

        /**
         * register Department Taxonomy for the team manager
         */
        $labels = array(
            'name'                       => __( 'Departments', 'wp-team-manager' ),
            'singular_name'              => __( 'Department', 'wp-team-manager' ),
            'search_items'               => __( 'Search Department', 'wp-team-manager' ),
            'popular_items'              => __( 'Popular Department', 'wp-team-manager' ),
            'all_items'                  => __( 'All Departments', 'wp-team-manager' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Department', 'wp-team-manager' ),
            'update_item'                => __( 'Update Department', 'wp-team-manager' ),
            'add_new_item'               => __( 'Add New Department', 'wp-team-manager' ),
            'new_item_name'              => __( 'New Department', 'wp-team-manager' ),
            'separate_items_with_commas' => __( 'Separate Departments with commas', 'wp-team-manager' ),
            'add_or_remove_items'        => __( 'Add or remove Department', 'wp-team-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used Department', 'wp-team-manager' ),
            'not_found'                  => __( 'No Department found.', 'wp-team-manager' ),
            'menu_name'                  => __( 'Departments', 'wp-team-manager' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_rest'          => true, // To use Gutenberg editor.
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'team_department' ),
        );

        register_taxonomy( 'team_department', 'team_manager', $args );

        unset( $args );
        unset( $labels );

        /**
         * register Gender Taxonomy for the team manager
         */
        $labels = array(
            'name'                       => __( 'Genders', 'wp-team-manager' ),
            'singular_name'              => __( 'Genders', 'wp-team-manager' ),
            'search_items'               => __( 'Search Genders', 'wp-team-manager' ),
            'popular_items'              => __( 'Popular Genders', 'wp-team-manager' ),
            'all_items'                  => __( 'All Genders', 'wp-team-manager' ),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __( 'Edit Gender', 'wp-team-manager' ),
            'update_item'                => __( 'Update Gender', 'wp-team-manager' ),
            'add_new_item'               => __( 'Add New Gender', 'wp-team-manager' ),
            'new_item_name'              => __( 'New Gender', 'wp-team-manager' ),
            'separate_items_with_commas' => __( 'Separate Gender with commas', 'wp-team-manager' ),
            'add_or_remove_items'        => __( 'Add or remove Gender', 'wp-team-manager' ),
            'choose_from_most_used'      => __( 'Choose from the most used Gender', 'wp-team-manager' ),
            'not_found'                  => __( 'No Gender found.', 'wp-team-manager' ),
            'menu_name'                  => __( 'Genders', 'wp-team-manager' ),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'show_in_rest'          => true, // To use Gutenberg editor.
            'query_var'             => true,
            'rewrite'               => array( 'slug' => 'team_genders' ),
        );

        register_taxonomy( 'team_genders', 'team_manager', $args );

        unset( $args );
        unset( $labels );
 

        $labels = array( 
            'name' => __( 'All Team Generator', 'wp-team-manager' ),
            'singular_name' => __( 'Team Generator', 'wp-team-manager' ),
            'add_new' => __( 'Add New Team Generator', 'wp-team-manager' ),
            'add_new_item' => __( 'Add New Team Generator', 'wp-team-manager' ),
            'edit_item' => __( 'Edit Team Generator ', 'wp-team-manager' ),
            'new_item' => __( 'New Team Generator', 'wp-team-manager' ),
            'view_item' => __( 'View Team Generator', 'wp-team-manager' ),
            'search_items' => __( 'Search Team Generator', 'wp-team-manager' ),
            'not_found' => __( 'Not found any Team Generator', 'wp-team-manager' ),
            'not_found_in_trash' => __( 'No Team Generator found in Trash', 'wp-team-manager' ),
            'parent_item_colon' => __( 'Parent Team Generator:', 'wp-team-manager' ),
            'menu_name' => __( 'Team Generator', 'wp-team-manager' ),
        );
        
        $args = array( 
            'labels'             => $labels,
            'supports'            => [ 'title' ],
			'public'              => false,
			'rewrite'             => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=team_manager',
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'page',
        );

        register_post_type('dwl_team_generator', $args );
    }
}
