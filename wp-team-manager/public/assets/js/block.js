const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor || wp.editor;
const { PanelBody, SelectControl, ToggleControl, RangeControl } = wp.components;
const { __ } = wp.i18n;
const { createElement, useCallback } = wp.element;

wp.domReady(() => {
    try {
        registerBlockType('wp-team-manager/team-block', {
        title: __('Team Manager', 'wp-team-manager'),
        icon: 'groups',
        category: 'widgets',
        attributes: {
            orderby: { type: 'string', default: 'menu_order' },
            layout: { type: 'string', default: 'grid' },
            style: { type: 'string', default: 'style-1' },
            postsPerPage: { type: 'number', default: -1 },
            category: { type: 'string', default: '0' },
            showSocial: { type: 'boolean', default: true },
            showOtherInfo: { type: 'boolean', default: true },
            showReadMore: { type: 'boolean', default: true },
            imageSize: { type: 'string', default: 'medium' },
            columns: { type: 'number', default: 3 },
            gap: { type: 'string', default: 'medium' },
        },
        edit: (props) => {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            const {
                orderby = 'menu_order',
                layout = 'grid',
                style = 'style-1',
                postsPerPage = -1,
                category = '0',
                showSocial = true,
                showOtherInfo = true,
                showReadMore = true,
                imageSize = 'medium',
                columns = 3,
                gap = 'medium',
            } = attributes;

            const categories = wp.data.select('core').getEntityRecords('taxonomy', 'team_groups', { per_page: -1 }) || [];

            return createElement(
                'div',
                blockProps,
                createElement(
                    InspectorControls,
                    null,
                    createElement(
                        PanelBody,
                        { title: __('Settings', 'wp-team-manager') },
                        createElement(SelectControl, {
                            label: __('Layout', 'wp-team-manager'),
                            value: layout,
                            options: [
                                { label: __('Grid', 'wp-team-manager'), value: 'grid' },
                                { label: __('List', 'wp-team-manager'), value: 'list' },
                                { label: __('Slider', 'wp-team-manager'), value: 'slider' },
                            ],
                            onChange: (val) => setAttributes({ layout: val })
                        }),
                        createElement(SelectControl, {
                            label: __('Style', 'wp-team-manager'),
                            value: style,
                            options: [
                                { label: __('Style 1', 'wp-team-manager'), value: 'style-1' },
                                { label: __('Style 2', 'wp-team-manager'), value: 'style-2' },
                            ],
                            onChange: (val) => setAttributes({ style: val })
                        }),
                        createElement(RangeControl, {
                            label: __('Columns', 'wp-team-manager'),
                            value: columns,
                            onChange: (val) => setAttributes({ columns: val }),
                            min: 1,
                            max: 6
                        }),
                        createElement(SelectControl, {
                            label: __('Groups', 'wp-team-manager'),
                            value: category,
                            options: [
                                { label: __('All Groups', 'wp-team-manager'), value: '0' },
                                ...categories.map(cat => ({ label: cat.name, value: cat.slug }))
                            ],
                            onChange: (val) => setAttributes({ category: val })
                        }),
                        createElement(SelectControl, {
                            label: __('Order By', 'wp-team-manager'),
                            value: orderby,
                            options: [
                                { label: __('Menu Order', 'wp-team-manager'), value: 'menu_order' },
                                { label: __('Title', 'wp-team-manager'), value: 'title' },
                                { label: __('Date', 'wp-team-manager'), value: 'date' },
                                { label: __('Random', 'wp-team-manager'), value: 'rand' },
                            ],
                            onChange: (val) => setAttributes({ orderby: val })
                        }),
                        createElement(RangeControl, {
                            label: __('Posts Per Page', 'wp-team-manager'),
                            value: postsPerPage === -1 ? 20 : postsPerPage,
                            onChange: (val) => setAttributes({ postsPerPage: val === 20 ? -1 : val }),
                            min: 1,
                            max: 20
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Social Links', 'wp-team-manager'),
                            checked: showSocial,
                            onChange: (val) => setAttributes({ showSocial: val })
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Other Info', 'wp-team-manager'),
                            checked: showOtherInfo,
                            onChange: (val) => setAttributes({ showOtherInfo: val })
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Read More', 'wp-team-manager'),
                            checked: showReadMore,
                            onChange: (val) => setAttributes({ showReadMore: val })
                        })
                    )
                ),
                createElement('div', { 
                    style: { 
                        padding: '20px', 
                        border: '2px dashed #ddd', 
                        textAlign: 'center',
                        backgroundColor: '#f9f9f9'
                    } 
                },
                    createElement('div', { style: { fontSize: '18px', marginBottom: '10px' } }, '👥'),
                    createElement('strong', null, __('Team Manager Block', 'wp-team-manager')),
                    createElement('div', { style: { fontSize: '12px', color: '#666', marginTop: '5px' } }, 
                        `${layout} • ${style} • ${columns} columns`
                    )
                )
            );
        },
        save: () => {
            return null;
        },
        });
        console.log('WP Team Manager block registered successfully');
    } catch (error) {
        console.error('WP Team Manager block registration failed:', error);
    }
});