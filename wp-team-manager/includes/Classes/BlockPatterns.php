<?php
namespace DWL\Wtm\Classes;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BlockPatterns {

    public function __construct() {
        add_action('init', [$this, 'register_patterns']);
        add_action('init', [$this, 'register_pattern_categories']);
    }

    /**
     * Register block pattern categories
     */
    public function register_pattern_categories() {
        register_block_pattern_category('team-manager', [
            'label' => __('Team Manager', 'wp-team-manager'),
            'description' => __('Professional team display patterns', 'wp-team-manager'),
        ]);

        register_block_pattern_category('team-layouts', [
            'label' => __('Team Layouts', 'wp-team-manager'),
            'description' => __('Various team member layout options', 'wp-team-manager'),
        ]);
    }

    /**
     * Register all block patterns
     */
    public function register_patterns() {
        $this->register_corporate_patterns();
        $this->register_creative_patterns();
        $this->register_executive_patterns();
        $this->register_department_patterns();
    }

    /**
     * Corporate team patterns
     */
    private function register_corporate_patterns() {
        // Corporate Team Grid
        register_block_pattern('wp-team-manager/corporate-grid', [
            'title'       => __('Corporate Team Grid', 'wp-team-manager'),
            'description' => __('Professional 3-column team grid with social links', 'wp-team-manager'),
            'content'     => '<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . __('Our Team', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . __('Meet the professionals behind our success', 'wp-team-manager') . '</p>
<!-- /wp:paragraph -->

<!-- wp:wp-team-manager/team-block {"layout":"grid","style":"style-1","columns":3,"showSocial":true,"showOtherInfo":true,"showReadMore":false,"postsPerPage":6} /-->',
            'categories'  => ['team-manager', 'team-layouts'],
            'keywords'    => ['team', 'corporate', 'grid', 'professional'],
            'viewportWidth' => 1200,
        ]);

        // Corporate Leadership
        register_block_pattern('wp-team-manager/corporate-leadership', [
            'title'       => __('Corporate Leadership', 'wp-team-manager'),
            'description' => __('Executive team with detailed information', 'wp-team-manager'),
            'content'     => '<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-group alignwide has-light-gray-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . __('Leadership Team', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:wp-team-manager/team-block {"layout":"grid","style":"style-2","columns":2,"gap":"large","showSocial":true,"showOtherInfo":true,"showReadMore":true,"postsPerPage":4,"orderby":"menu_order"} /-->

</div>
<!-- /wp:group -->',
            'categories'  => ['team-manager'],
            'keywords'    => ['leadership', 'executive', 'corporate'],
            'viewportWidth' => 1200,
        ]);
    }

    /**
     * Creative team patterns
     */
    private function register_creative_patterns() {
        // Creative Team Showcase
        register_block_pattern('wp-team-manager/creative-showcase', [
            'title'       => __('Creative Team Showcase', 'wp-team-manager'),
            'description' => __('Modern slider layout for creative teams', 'wp-team-manager'),
            'content'     => '<!-- wp:cover {"url":"","dimRatio":20,"overlayColor":"black","minHeight":400,"contentPosition":"center center","isDark":false} -->
<div class="wp-block-cover is-light" style="min-height:400px"><span aria-hidden="true" class="wp-block-cover__background has-black-background-color has-background-dim-20"></span><div class="wp-block-cover__inner-container">

<!-- wp:heading {"textAlign":"center","level":2,"textColor":"white"} -->
<h2 class="wp-block-heading has-text-align-center has-white-color has-text-color">' . __('Creative Minds', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color">' . __('Innovative professionals driving creativity', 'wp-team-manager') . '</p>
<!-- /wp:paragraph -->

</div></div>
<!-- /wp:cover -->

<!-- wp:wp-team-manager/team-block {"layout":"slider","style":"style-1","columns":3,"showSocial":true,"showOtherInfo":false,"showReadMore":false,"postsPerPage":8} /-->',
            'categories'  => ['team-manager'],
            'keywords'    => ['creative', 'slider', 'showcase', 'modern'],
            'viewportWidth' => 1200,
        ]);

        // Agency Team Grid
        register_block_pattern('wp-team-manager/agency-grid', [
            'title'       => __('Agency Team Grid', 'wp-team-manager'),
            'description' => __('4-column grid perfect for agencies', 'wp-team-manager'),
            'content'     => '<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . __('Meet Our Agency', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:wp-team-manager/team-block {"layout":"grid","style":"style-2","columns":4,"gap":"medium","showSocial":false,"showOtherInfo":true,"showReadMore":false,"postsPerPage":8} /-->',
            'categories'  => ['team-layouts'],
            'keywords'    => ['agency', 'grid', '4-column'],
            'viewportWidth' => 1200,
        ]);
    }

    /**
     * Executive team patterns
     */
    private function register_executive_patterns() {
        // Executive Board
        register_block_pattern('wp-team-manager/executive-board', [
            'title'       => __('Executive Board', 'wp-team-manager'),
            'description' => __('Formal executive team presentation', 'wp-team-manager'),
            'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}},"backgroundColor":"white"} -->
<div class="wp-block-group alignfull has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80)">

<!-- wp:group {"align":"wide"} -->
<div class="wp-block-group alignwide">

<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . __('Board of Directors', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:separator {"align":"center","className":"is-style-wide"} -->
<hr class="wp-block-separator aligncenter has-alpha-channel-opacity is-style-wide"/>
<!-- /wp:separator -->

<!-- wp:wp-team-manager/team-block {"layout":"list","style":"style-1","showSocial":true,"showOtherInfo":true,"showReadMore":true,"postsPerPage":5,"orderby":"menu_order"} /-->

</div>
<!-- /wp:group -->

</div>
<!-- /wp:group -->',
            'categories'  => ['team-manager'],
            'keywords'    => ['executive', 'board', 'directors', 'formal'],
            'viewportWidth' => 1200,
        ]);
    }

    /**
     * Department team patterns
     */
    private function register_department_patterns() {
        // Department Overview
        register_block_pattern('wp-team-manager/department-overview', [
            'title'       => __('Department Overview', 'wp-team-manager'),
            'description' => __('Compact team display for departments', 'wp-team-manager'),
            'content'     => '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%">

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">' . __('Our Department', 'wp-team-manager') . '</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . __('Dedicated professionals working together to deliver exceptional results for our clients and organization.', 'wp-team-manager') . '</p>
<!-- /wp:paragraph -->

</div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%">

<!-- wp:wp-team-manager/team-block {"layout":"grid","style":"style-2","columns":3,"gap":"small","showSocial":false,"showOtherInfo":true,"showReadMore":false,"postsPerPage":6} /-->

</div>
<!-- /wp:column -->

</div>
<!-- /wp:columns -->',
            'categories'  => ['team-layouts'],
            'keywords'    => ['department', 'compact', 'overview'],
            'viewportWidth' => 1200,
        ]);

        // Team Table View
        register_block_pattern('wp-team-manager/team-table', [
            'title'       => __('Team Directory Table', 'wp-team-manager'),
            'description' => __('Comprehensive team directory in table format', 'wp-team-manager'),
            'content'     => '<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="wp-block-heading has-text-align-center">' . __('Team Directory', 'wp-team-manager') . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . __('Complete team member directory with contact information', 'wp-team-manager') . '</p>
<!-- /wp:paragraph -->

<!-- wp:wp-team-manager/team-block {"layout":"table","style":"style-1","showSocial":true,"showOtherInfo":true,"showReadMore":false,"postsPerPage":15,"orderby":"title"} /-->',
            'categories'  => ['team-layouts'],
            'keywords'    => ['table', 'directory', 'contact', 'comprehensive'],
            'viewportWidth' => 1200,
        ]);
    }
}

new BlockPatterns();