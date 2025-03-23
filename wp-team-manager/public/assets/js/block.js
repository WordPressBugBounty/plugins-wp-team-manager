const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, ToggleControl, TextControl } = wp.components;
const { __ } = wp.i18n;
const { createElement, useCallback } = wp.element;

wp.domReady(() => {
    registerBlockType('wp-team-manager/team-block', {
        title: __('Team Manager', 'wp-team-manager'),
        icon: 'groups',
        category: 'widgets',
        attributes: {
            orderby: { type: 'string', default: 'menu_order' },
            layout: { type: 'string', default: 'grid' },
            postsPerPage: { type: 'number', default: -1 },
            category: { type: 'string', default: '0' },
            showSocial: { type: 'boolean', default: true },
            showOtherInfo: { type: 'boolean', default: true },
            showReadMore: { type: 'boolean', default: true },
            imageSize: { type: 'string', default: 'medium' },
        },
        edit: (props) => {
            const { attributes, setAttributes } = props;
            const blockProps = useBlockProps();

            const {
                orderby = 'menu_order',
                layout = 'grid',
                postsPerPage = -1,
                category = '0',
                showSocial = true,
                showOtherInfo = true,
                showReadMore = true,
                imageSize = 'medium',
            } = attributes;

            const onChangeOrderBy = useCallback((val) => setAttributes({ orderby: val }), [setAttributes]);
            const onChangeLayout = useCallback((val) => setAttributes({ layout: val }), [setAttributes]);
            const onChangeCategory = useCallback((val) => setAttributes({ category: val }), [setAttributes]);
            const onToggleShowSocial = useCallback((val) => setAttributes({ showSocial: val }), [setAttributes]);
            const onToggleShowOtherInfo = useCallback((val) => setAttributes({ showOtherInfo: val }), [setAttributes]);
            const onToggleShowReadMore = useCallback((val) => setAttributes({ showReadMore: val }), [setAttributes]);
            const onChangeImageSize = useCallback((val) => setAttributes({ imageSize: val }), [setAttributes]);

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
                            label: __('Order By', 'wp-team-manager'),
                            value: orderby,
                            options: [
                                { label: 'Menu Order', value: 'menu_order' },
                                { label: 'Title', value: 'title' },
                                { label: 'ID', value: 'ID' },
                                { label: 'Date', value: 'date' },
                                { label: 'Modified Date', value: 'modified' },
                                { label: 'Random', value: 'rand' },
                            ],
                            onChange: onChangeOrderBy
                        }),
                        createElement(SelectControl, {
                            label: __('Layout', 'wp-team-manager'),
                            value: layout,
                            options: [
                                { label: 'Grid', value: 'grid' },
                                { label: 'List', value: 'list' },
                                { label: 'Slider', value: 'slider' },
                            ],
                            onChange: onChangeLayout
                        }),
                        createElement(SelectControl, {
                            label: __('Groups', 'wp-team-manager'),
                            value: category,
                            options: [
                                { label: __('Select Group', 'wp-team-manager'), value: '0' },
                                ...categories.map(cat => ({ label: cat.name, value: cat.slug }))
                            ],
                            onChange: onChangeCategory
                        }),
                        createElement(TextControl, {
                            label: __('Posts Per Page', 'wp-team-manager'),
                            value: postsPerPage,
                            type: 'text',
                            onChange: (val) => setAttributes({ postsPerPage: parseInt(val) || -1 })
                        }),
                        createElement(SelectControl, {
                            label: __('Image Size', 'wp-team-manager'),
                            value: imageSize,
                            options: (wp.data.select('core/block-editor').getSettings()?.imageSizes || []).map(size => ({
                                label: size.name,
                                value: size.slug
                            })),
                            onChange: onChangeImageSize
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Social Links', 'wp-team-manager'),
                            checked: showSocial,
                            onChange: onToggleShowSocial
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Other Info?', 'wp-team-manager'),
                            checked: showOtherInfo,
                            onChange: onToggleShowOtherInfo
                        }),
                        createElement(ToggleControl, {
                            label: __('Show Read More?', 'wp-team-manager'),
                            checked: showReadMore,
                            onChange: onToggleShowReadMore
                        }),
                    )
                ),
                createElement('div', { 
                    style: { padding: '10px', border: '1px solid #ddd' } 
                },
                    createElement('strong', null, __('Team Manager Block', 'wp-team-manager')),
                )
            );
        },
        save: () => {
            return createElement('p', null, __('This block is dynamically rendered.', 'wp-team-manager'));
        },
    });
});