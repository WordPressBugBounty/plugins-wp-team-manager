(function($){

  let WTMObj  = {};

  WTMObj.slider = function(){

      let slider = $('.dwl-team-layout-slider');
      //console.log(slider);
      if( slider.length == 0){
         return;
      }

      slider.each( function( index, element ) {

          $( element ).slick({
            dots: true,
            arrows: true,
            nextArrow: '<button class="dwl-slide-arrow dwl-slide-next"><i class="fas fa-chevron-left"></i></button>',
            prevArrow: '<button class="dwl-slide-arrow dwl-slide-prev"><i class="fas fa-chevron-right"></i></button>',
            infinite: false,
            speed: 300,
            slidesToShow: 4,
              responsive: [
                  {
                    breakpoint: 1024,
                    settings: {
                      slidesToShow: 3,
                      infinite: true,
                      dots: true
                    }
                  },
                  {
                    breakpoint: 767,
                    settings: {
                      slidesToShow: 2,
                    }
                  },
                  {
                    breakpoint: 480,
                    settings: {
                      slidesToShow: 1,
                    }
                  }
                ]
            });
          
      });

  };

  WTMObj.loadMore = function (){

    $('#load-more').click(function(){

      var button = $(this),
          data = {
              'action': 'load_more',
              'query': wptObj.posts,
              'page' : wptObj.current_page,
              'nonce': wptObj.nonce
          };

      $.ajax({
          url : wptObj.ajaxurl,
          data : data,
          type : 'POST',
          beforeSend : function ( xhr ) {
              button.text('Loading...');
          },
          success : function( data ){
              if( data ) {

                  //reset button text
                  button.text( 'Load More' );

                  //append new data
                  $('.load-more-target').append(data);

                  load_more.current_page++;
                  if ( load_more.current_page == load_more.max_page )
                      button.remove();

              } else {
                  button.remove();
              }
          }
      });
  });

  };

    // Slider for new shortcode

    WTMObj.slider2 = function(){

        let slider = $('.dwl-new-team-layout-slider');
       
        if( slider.length == 0){
            return;
        }

        slider.each( function( index, element ) {

            $( element ).slick({
            dots: Boolean(slider.data("dots")),
            arrows: Boolean(slider.data("arrows")),
            autoplay: Boolean(slider.data("autoplay")),
            nextArrow: '<button class="dwl-slide-arrow dwl-slide-next"><i class="fas fa-chevron-left"></i></button>',
            prevArrow: '<button class="dwl-slide-arrow dwl-slide-prev"><i class="fas fa-chevron-right"></i></button>',
            infinite: false,
            speed: 300,
            slidesToShow: Number( $( element ).data('desktop') ),
            slidesToScroll: 4,
                responsive: [
                    {
                      breakpoint: 1024,
                      settings: {
                          slidesToShow: Number( $( element ).data('tablet') ),
                          slidesToScroll: 3,
                          infinite: true,
                          dots: true
                      }
                    },
                    {
                      breakpoint: 767,
                      settings: {
                          slidesToShow: Number( $( element ).data('mobile') ),
                          slidesToScroll: 2
                      }
                    },
                    // You can unslick at a given breakpoint now by adding:
                    // settings: "unslick"
                    // instead of a settings object
                ]
            });
            
        });

    };

    WTMObj.slider2();

    WTMObj.slider();
    WTMObj.loadMore();
    
  
})(jQuery);