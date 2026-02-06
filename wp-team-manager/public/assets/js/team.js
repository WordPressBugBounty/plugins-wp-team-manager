(function($){

  let WTMObj  = {};

  /**
   * Slick slider for team layout
   *
   * @return {void}
   */
  WTMObj.slider = function(){

      let slider = $('.dwl-team-layout-slider:not(.dwl-team-elementor-layout-slider)');
      // console.log(slider);
      if( slider.length == 0){
         return;
      }

      slider.not('.slick-initialized').each( function( _index, element ) {
        let arrows = true;
        let autoplay = false;
        let dots = true;
        let desktop = 4;
        let tablet = 3;
        let mobile = 1;

        if( undefined != this.dataset.arrows ){
          arrows = this.dataset.arrows == '1'  ? true : false;
        }
        if( undefined != this.dataset.autoplay ){
          autoplay = this.dataset.autoplay == '1'  ? true : false;
        }
        if( undefined != this.dataset.dots ){
          dots = this.dataset.dots == '1'  ? true : false;
        }
        if( undefined != this.dataset.desktop ){
          desktop = Number(this.dataset.desktop);
        }
        if( undefined != this.dataset.tablet ){
          tablet = Number(this.dataset.tablet);
        }
        if( undefined != this.dataset.mobile ){
          mobile = Number(this.dataset.mobile);
        }

        $( element ).not('.slick-initialized').slick({
          dots: dots,
          arrows: arrows,
          nextArrow: '<button class="dwl-slide-arrow dwl-slide-next fas"><i class="fas fa-chevron-left"></i></button>',
          prevArrow: '<button class="dwl-slide-arrow dwl-slide-prev"><i class="fas fa-chevron-right"></i></button>',
          infinite: false,
          autoplay: autoplay,
          speed: 300,
          pauseOnHover: true,
          slidesToShow: desktop,
            responsive: [
              {
                breakpoint: 1024,
                settings: {
                  slidesToShow: tablet,
                  infinite: true,
                  dots: true
                }
              },
              {
                breakpoint: 767,
                settings: {
                  slidesToShow: mobile,
                }
              },
              {
                breakpoint: 480,
                settings: {
                  slidesToShow:1,
                }
              }
            ]
        });
      });
  };


  /**
   * Taxonomy filters functionality
   * Uses event delegation to handle dynamically loaded content
   *
   * @return {void}
   */
  WTMObj.taxonomyFilters = function(){
    // Use event delegation on document for filter buttons
    $(document).off('click.wtmFilter').on('click.wtmFilter', '.wtm-filter-btn', function(e) {
      e.preventDefault();

      const $btn = $(this);
      const $wrapper = $btn.closest('.dwl-team-wrapper');
      const filter = $btn.attr('data-filter');

      // Store active filter on wrapper for load more
      $wrapper.attr('data-active-filter', filter);

      // Remove active class from all buttons in this wrapper
      $wrapper.find('.wtm-filter-btn').removeClass('active');

      // Add active class to clicked button
      $btn.addClass('active');

      // Apply filter to all team members
      WTMObj.applyFilter($wrapper, filter);

      // Trigger resize event for sliders to recalculate
      $(window).trigger('resize');
    });

    // Listen for new content being added (after AJAX load more)
    $(document).on('wtm:content-loaded', '.dwl-team-wrapper', function() {
      const $wrapper = $(this);
      const activeFilter = $wrapper.attr('data-active-filter') || '*';
      WTMObj.applyFilter($wrapper, activeFilter);
    });
  };

  /**
   * Apply filter to team members in a wrapper
   *
   * @param {jQuery} $wrapper The team wrapper element
   * @param {string} filter The filter value (e.g., '*' or '.team_groups-photographer')
   */
  WTMObj.applyFilter = function($wrapper, filter) {
    const $teamMembers = $wrapper.find('.team-member-info-wrap');

    $teamMembers.each(function() {
      const $member = $(this);
      if (filter === '*') {
        $member.removeClass('hidden');
      } else {
        const filterClass = filter.substring(1); // Remove the dot
        if ($member.hasClass(filterClass)) {
          $member.removeClass('hidden');
        } else {
          $member.addClass('hidden');
        }
      }
    });
  };

  // Initialize functions when DOM is ready
  $(document).ready(function() {
    //console.log('[WTM Debug] Document ready - initializing');
    WTMObj.slider();
    WTMObj.taxonomyFilters();
  });

  // Also try on window load (for late-loaded content)
  $(window).on('load', function() {
    //console.log('[WTM Debug] Window load - re-initializing');
    WTMObj.taxonomyFilters();
  });

})(jQuery);