(function($){
    "use strict";
    
    let WidgetDPCSliderHandler = function ($scope, $) {
    
          let slider_elem = $scope.find('.team-member-slider-wrap');

            if ( slider_elem.length > 0) {
              
                slider_elem.not('.slick-initialized').each(function(){
                  let $this = $(this);
                  let slider_settings = $this.data('slider_settings');
                $this.slick({
                    dots: Boolean( slider_settings.dot_nav ),
                    infinite: true,
                    speed: slider_settings.speed,
                    slidesToShow: slider_settings.desktop,
                    autoplay: Boolean( slider_settings.autoplay ),
                    autoplaySpeed: slider_settings.speed,
                    adaptiveHeight: true,
                    arrows: Boolean( slider_settings.arrows ),
                    nextArrow: '<button class="dwl-slide-arrow dwl-slide-next"><i class="fas fa-chevron-left"></i></button>',
                    prevArrow: '<button class="dwl-slide-arrow dwl-slide-prev"><i class="fas fa-chevron-right"></i></button>',
                    margin: 10,
                    responsive: [
                        {
                          breakpoint: 1024,
                          settings: {
                            slidesToShow: slider_settings.tablet,
                          }
                        },
                        {
                          breakpoint: 767,
                          settings: {
                            slidesToShow: slider_settings.mobile,
                          }
                        }
                      ]
                });

                });
    
            };
        };
        
        // Run this code under Elementor.
        $(window).on('elementor/frontend/init', function () {
            elementorFrontend.hooks.addAction( 'frontend/element_ready/wtm-team-manager.default', WidgetDPCSliderHandler);
        });
    })(jQuery);