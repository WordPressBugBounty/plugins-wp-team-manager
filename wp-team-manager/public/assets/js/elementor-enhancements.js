(function($) {
    'use strict';
    
    // Enhanced Elementor Widget Functionality
    const WTMElementorEnhancements = {
        
        init: function() {
            this.initHoverEffects();
            this.initLazyLoading();
            this.initPerformanceOptimizations();
            this.initAccessibilityFeatures();
        },
        
        initHoverEffects: function() {
            $('.dwl-team-wrapper').each(function() {
                const $wrapper = $(this);
                const settings = $wrapper.data('settings');
                
                if (settings && settings.enable_hover_effects === 'yes') {
                    const animationType = settings.hover_animation_type || 'fade';
                    $wrapper.addClass('wtm-hover-' + animationType);
                }
            });
        },
        
        initLazyLoading: function() {
            $('.slick-slider').each(function() {
                const $slider = $(this);
                const settings = $slider.closest('.dwl-team-wrapper').data('settings');
                
                if (settings && settings.slider_lazy_loading === 'yes') {
                    $slider.addClass('wtm-lazy-loading');
                    
                    // Initialize lazy loading for slick slider
                    $slider.on('init', function() {
                        $slider.find('img').each(function() {
                            const $img = $(this);
                            const src = $img.attr('src');
                            
                            if (src) {
                                $img.addClass('slick-loading');
                                
                                const img = new Image();
                                img.onload = function() {
                                    $img.removeClass('slick-loading').addClass('slick-loaded');
                                };
                                img.src = src;
                            }
                        });
                    });
                }
            });
        },
        
        initPerformanceOptimizations: function() {
            // Add performance class for CSS optimizations
            $('.dwl-team-wrapper').addClass('wtm-performance-optimized');
            
            // Intersection Observer for lazy loading
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    });
                });
                
                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },
        
        initAccessibilityFeatures: function() {
            // Enhanced keyboard navigation
            $('.team-member-info-content').on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    const $link = $(this).find('a').first();
                    if ($link.length) {
                        e.preventDefault();
                        $link[0].click();
                    }
                }
            });
            
            // ARIA labels for better screen reader support
            $('.team-member-socials a').each(function() {
                const $link = $(this);
                const platform = $link.find('i').attr('class').match(/fa-(\w+)/);
                
                if (platform && platform[1]) {
                    $link.attr('aria-label', 'Visit ' + platform[1] + ' profile');
                }
            });
            
            // Focus management for sliders
            $('.slick-slider').on('afterChange', function(event, slick, currentSlide) {
                const $currentSlide = $(slick.$slides[currentSlide]);
                $currentSlide.find('.team-member-info-content').focus();
            });
        },
        
        // Dynamic content loading for theme builder
        loadDynamicContent: function() {
            $('.wtm-theme-builder-archive').each(function() {
                const $container = $(this);
                const settings = $container.data('settings');
                
                if (settings && settings.dynamic_loading === 'yes') {
                    // Implement AJAX loading for archive pages
                    $container.on('scroll', function() {
                        if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                            // Load more content
                            WTMElementorEnhancements.loadMoreContent($container);
                        }
                    });
                }
            });
        },
        
        loadMoreContent: function($container) {
            const settings = $container.data('settings');
            const currentPage = parseInt($container.data('current-page') || 1);
            const maxPages = parseInt($container.data('max-pages') || 1);
            
            if (currentPage >= maxPages) {
                return;
            }
            
            $.ajax({
                url: wtm_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'wtm_load_more_archive',
                    page: currentPage + 1,
                    settings: settings,
                    nonce: wtm_ajax.nonce
                },
                beforeSend: function() {
                    $container.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        $container.append(response.data.content);
                        $container.data('current-page', currentPage + 1);
                    }
                },
                complete: function() {
                    $container.removeClass('loading');
                }
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        WTMElementorEnhancements.init();
    });
    
    // Re-initialize on Elementor frontend init
    $(window).on('elementor/frontend/init', function() {
        WTMElementorEnhancements.init();
    });
    
})(jQuery);