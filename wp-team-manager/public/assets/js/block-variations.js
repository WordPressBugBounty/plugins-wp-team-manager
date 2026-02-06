const { registerBlockVariation } = wp.blocks;
const { __ } = wp.i18n;

wp.domReady(() => {
    // Grid Variations
    registerBlockVariation('wp-team-manager/team-block', {
        name: 'grid-classic',
        title: __('Classic Grid', 'wp-team-manager'),
        description: __('Traditional grid layout with basic styling', 'wp-team-manager'),
        icon: 'grid-view',
        attributes: {
            layout: 'grid',
            style: 'style-1',
            columns: 3,
            showSocial: true,
            showOtherInfo: true,
            showReadMore: false
        },
        scope: ['inserter']
    });

    registerBlockVariation('wp-team-manager/team-block', {
        name: 'grid-modern',
        title: __('Modern Grid', 'wp-team-manager'),
        description: __('Modern grid with enhanced styling', 'wp-team-manager'),
        icon: 'grid-view',
        attributes: {
            layout: 'grid',
            style: 'style-2',
            columns: 4,
            gap: 'large',
            showSocial: true,
            showOtherInfo: false,
            showReadMore: true
        },
        scope: ['inserter']
    });

    // List Variations
    registerBlockVariation('wp-team-manager/team-block', {
        name: 'list-detailed',
        title: __('Detailed List', 'wp-team-manager'),
        description: __('Comprehensive list with full information', 'wp-team-manager'),
        icon: 'list-view',
        attributes: {
            layout: 'list',
            style: 'style-1',
            showSocial: true,
            showOtherInfo: true,
            showReadMore: true,
            postsPerPage: 5
        },
        scope: ['inserter']
    });

    registerBlockVariation('wp-team-manager/team-block', {
        name: 'list-compact',
        title: __('Compact List', 'wp-team-manager'),
        description: __('Minimal list layout for space efficiency', 'wp-team-manager'),
        icon: 'list-view',
        attributes: {
            layout: 'list',
            style: 'style-2',
            showSocial: false,
            showOtherInfo: false,
            showReadMore: false,
            postsPerPage: 10
        },
        scope: ['inserter']
    });

    // Slider Variations
    registerBlockVariation('wp-team-manager/team-block', {
        name: 'slider-showcase',
        title: __('Team Showcase', 'wp-team-manager'),
        description: __('Dynamic slider for team presentation', 'wp-team-manager'),
        icon: 'slides',
        attributes: {
            layout: 'slider',
            style: 'style-1',
            columns: 3,
            showSocial: true,
            showOtherInfo: false,
            showReadMore: false,
            postsPerPage: 8
        },
        scope: ['inserter']
    });

    // Executive Team Variation
    registerBlockVariation('wp-team-manager/team-block', {
        name: 'executive-team',
        title: __('Executive Team', 'wp-team-manager'),
        description: __('Leadership team with professional styling', 'wp-team-manager'),
        icon: 'businessperson',
        attributes: {
            layout: 'grid',
            style: 'style-1',
            columns: 2,
            gap: 'large',
            showSocial: true,
            showOtherInfo: true,
            showReadMore: true,
            postsPerPage: 4,
            orderby: 'menu_order'
        },
        scope: ['inserter']
    });

    // Department Team Variation
    registerBlockVariation('wp-team-manager/team-block', {
        name: 'department-team',
        title: __('Department Team', 'wp-team-manager'),
        description: __('Department-specific team display', 'wp-team-manager'),
        icon: 'groups',
        attributes: {
            layout: 'grid',
            style: 'style-2',
            columns: 4,
            gap: 'medium',
            showSocial: false,
            showOtherInfo: true,
            showReadMore: false,
            postsPerPage: 12
        },
        scope: ['inserter']
    });
});