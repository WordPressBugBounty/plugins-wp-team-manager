/**
 * WP Team Manager - Enhanced Search & Filtering
 */
(function($) {
    'use strict';

    class WTMEnhancedSearch {
        constructor() {
            this.searchTimeout = null;
            this.currentFilters = {};
            this.currentPreset = null;
            this.init();
        }

        init() {
            this.bindEvents();
            this.initAutocomplete();
            this.loadSavedFilters();
        }

        bindEvents() {
            // Live search
            $(document).on('input', '.wtm-search-input', (e) => {
                this.handleLiveSearch(e.target.value);
            });

            // Filter changes
            $(document).on('change', '.wtm-filter-select, .wtm-filter-checkbox', () => {
                this.handleFilterChange();
            });

            // Date range filters
            $(document).on('change', '.wtm-date-from, .wtm-date-to', () => {
                this.handleFilterChange();
            });

            // Clear filters
            $(document).on('click', '.wtm-clear-filters', () => {
                this.clearAllFilters();
            });

            // Save preset
            $(document).on('click', '.wtm-save-preset', () => {
                this.showSavePresetDialog();
            });

            // Load preset
            $(document).on('change', '.wtm-preset-select', (e) => {
                this.loadPreset(e.target.value);
            });

            // Advanced filter toggle
            $(document).on('click', '.wtm-toggle-advanced', () => {
                $('.wtm-advanced-filters').slideToggle();
            });
        }

        initAutocomplete() {
            $('.wtm-search-input').autocomplete({
                source: (request, response) => {
                    $.ajax({
                        url: wtmSearch.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'wtm_get_autocomplete',
                            term: request.term,
                            nonce: wtmSearch.nonce
                        },
                        success: (data) => {
                            if (data.success) {
                                response(data.data);
                            }
                        }
                    });
                },
                minLength: 2,
                delay: 300,
                select: (event, ui) => {
                    $(event.target).val(ui.item.value);
                    this.handleLiveSearch(ui.item.value);
                    return false;
                }
            });
        }

        handleLiveSearch(searchTerm) {
            clearTimeout(this.searchTimeout);
            
            this.searchTimeout = setTimeout(() => {
                this.performSearch(searchTerm);
            }, 500);
        }

        handleFilterChange() {
            this.currentFilters = this.collectFilters();
            this.performSearch($('.wtm-search-input').val());
        }

        performSearch(searchTerm = '') {
            const $container = $('.wtm-search-results');
            const $loading = $('.wtm-search-loading');

            $loading.show();
            $container.addClass('wtm-searching');

            $.ajax({
                url: wtmSearch.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wtm_enhanced_search',
                    search: searchTerm,
                    filters: this.currentFilters,
                    settings: this.getSearchSettings(),
                    nonce: wtmSearch.nonce
                },
                success: (response) => {
                    if (response.success) {
                        $container.html(response.data.html);
                        this.updateResultsCount(response.data.count);
                        this.updateSuggestions(response.data.suggestions);
                    }
                },
                error: () => {
                    $container.html('<div class="wtm-error">' + wtmSearch.strings.noResults + '</div>');
                },
                complete: () => {
                    $loading.hide();
                    $container.removeClass('wtm-searching');
                }
            });
        }

        collectFilters() {
            const filters = {
                taxonomies: {},
                meta: {},
                date_range: {}
            };

            // Collect taxonomy filters
            $('.wtm-taxonomy-filter').each(function() {
                const taxonomy = $(this).data('taxonomy');
                const selected = [];
                
                $(this).find('input:checked').each(function() {
                    selected.push($(this).val());
                });
                
                if (selected.length > 0) {
                    filters.taxonomies[taxonomy] = selected;
                }
            });

            // Collect meta filters
            $('.wtm-meta-filter').each(function() {
                const key = $(this).data('meta-key');
                const value = $(this).val();
                
                if (value) {
                    filters.meta[key] = value;
                }
            });

            // Collect date range
            const dateFrom = $('.wtm-date-from').val();
            const dateTo = $('.wtm-date-to').val();
            
            if (dateFrom || dateTo) {
                filters.date_range = {
                    from: dateFrom,
                    to: dateTo
                };
            }

            return filters;
        }

        getSearchSettings() {
            return {
                layout: $('.wtm-layout-selector').val() || 'grid',
                style: $('.wtm-style-selector').val() || 'style-1',
                per_page: parseInt($('.wtm-per-page').val()) || 12,
                page: 1
            };
        }

        clearAllFilters() {
            $('.wtm-filter-checkbox').prop('checked', false);
            $('.wtm-filter-select').val('');
            $('.wtm-date-from, .wtm-date-to').val('');
            $('.wtm-search-input').val('');
            
            this.currentFilters = {};
            this.performSearch();
        }

        showSavePresetDialog() {
            const name = prompt(wtmSearch.strings.savePreset + ':');
            if (name) {
                this.savePreset(name);
            }
        }

        savePreset(name) {
            $.ajax({
                url: wtmSearch.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wtm_save_preset',
                    name: name,
                    filters: this.currentFilters,
                    nonce: wtmSearch.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.addPresetToSelect(name);
                        alert(response.data);
                    } else {
                        alert(response.data);
                    }
                }
            });
        }

        loadPreset(name) {
            if (!name) return;

            $.ajax({
                url: wtmSearch.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'wtm_load_preset',
                    name: name,
                    nonce: wtmSearch.nonce
                },
                success: (response) => {
                    if (response.success) {
                        this.applyFilters(response.data);
                        this.currentPreset = name;
                    }
                }
            });
        }

        applyFilters(filters) {
            // Clear existing filters
            this.clearAllFilters();

            // Apply taxonomy filters
            if (filters.taxonomies) {
                Object.keys(filters.taxonomies).forEach(taxonomy => {
                    const terms = filters.taxonomies[taxonomy];
                    terms.forEach(termId => {
                        $(`.wtm-taxonomy-filter[data-taxonomy="${taxonomy}"] input[value="${termId}"]`)
                            .prop('checked', true);
                    });
                });
            }

            // Apply meta filters
            if (filters.meta) {
                Object.keys(filters.meta).forEach(key => {
                    $(`.wtm-meta-filter[data-meta-key="${key}"]`).val(filters.meta[key]);
                });
            }

            // Apply date range
            if (filters.date_range) {
                $('.wtm-date-from').val(filters.date_range.from || '');
                $('.wtm-date-to').val(filters.date_range.to || '');
            }

            this.currentFilters = filters;
            this.performSearch();
        }

        addPresetToSelect(name) {
            const $select = $('.wtm-preset-select');
            if ($select.find(`option[value="${name}"]`).length === 0) {
                $select.append(`<option value="${name}">${name}</option>`);
            }
        }

        updateResultsCount(count) {
            $('.wtm-results-count').text(count);
        }

        updateSuggestions(suggestions) {
            const $container = $('.wtm-search-suggestions');
            $container.empty();

            if (suggestions && suggestions.length > 0) {
                suggestions.forEach(suggestion => {
                    $container.append(
                        `<button class="wtm-suggestion" data-term="${suggestion}">${suggestion}</button>`
                    );
                });
                $container.show();
            } else {
                $container.hide();
            }
        }

        loadSavedFilters() {
            // Load any saved filter state from localStorage
            const saved = localStorage.getItem('wtm_search_state');
            if (saved) {
                try {
                    const state = JSON.parse(saved);
                    if (state.filters) {
                        this.applyFilters(state.filters);
                    }
                } catch (e) {
                    // Invalid saved state, ignore
                }
            }
        }

        saveCurrentState() {
            // Save current search state to localStorage
            const state = {
                filters: this.currentFilters,
                search: $('.wtm-search-input').val()
            };
            localStorage.setItem('wtm_search_state', JSON.stringify(state));
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        if ($('.wtm-enhanced-search').length > 0) {
            new WTMEnhancedSearch();
        }
    });

    // Handle suggestion clicks
    $(document).on('click', '.wtm-suggestion', function() {
        const term = $(this).data('term');
        $('.wtm-search-input').val(term).trigger('input');
        $('.wtm-search-suggestions').hide();
    });

})(jQuery);