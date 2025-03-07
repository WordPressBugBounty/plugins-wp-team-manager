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

      slider.not('.slick-initialized').each( function( index, element ) {
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


    WTMObj.slider();

})(jQuery);