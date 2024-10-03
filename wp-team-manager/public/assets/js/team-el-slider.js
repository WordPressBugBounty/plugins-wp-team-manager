(function($){
    "use strict";
    
        var WidgetDPCSliderHandler = function ($scope, $) {
    
            var slider_elem = $scope.find('.dwl-team-elementor-layout-slider');


            if ( slider_elem.length > 0) {

                slider_elem.each(function(){

                var $this = $(this);

                $this.slick({
                    dots: Boolean( $this.data('dots') ),
                    infinite: true,
                    speed: $this.data('speed'),
                    slidesToShow: $this.data('desktop'),
                    autoplay: Boolean( $this.data('autoplay') ),
                    autoplaySpeed: $this.data('speed'),
                    adaptiveHeight: true,
                    arrows: Boolean( $this.data('arrows') ),
                    margin: 10,
                    responsive: [
                        {
                          breakpoint: 1024,
                          settings: {
                            slidesToShow: $this.data('tablet'),
                          }
                        },
                        {
                          breakpoint: 767,
                          settings: {
                            slidesToShow: $this.data('mobile'),
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