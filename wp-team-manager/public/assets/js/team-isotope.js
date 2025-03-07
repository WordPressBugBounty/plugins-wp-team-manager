(function($) {
"use strict";

/**
 * dwlTeamElementorIsotope
 *
 * Handles the isotope js for Team Manager elementor widget
 *
 * @param {object} $scope - the elementor widget scope
 * @param {object} $ - jQuery
 * @since 1.0.0
 */
let dwlTeamElementorIsotope = function ($scope, $) {

        let $grid = $scope.find('.dwl-team-isotope-container');


        if (  $grid.length > 0) {

            setTimeout(function () {
  
                let $istope = $grid.find('.wtm-isotope-grid');
         
                $istope.isotope({
                    filter: "*",
                    itemSelector: '.wtm-isotope-item',
                    layoutMode: 'fitRows',

                }
                );
                    
                $grid.find('.dwl-team-filter-button').on('click', function() {
                    let filterValue = $(this).attr('data-filter');
                    $istope.isotope({ filter: filterValue });
                });
    
                // change is-checked class on buttons
                $grid.find('.dwl-team-isotope-filter').each( function( i, buttonGroup ) {
                    let $buttonGroup = $( buttonGroup );
                    $buttonGroup.on( 'click', 'button', function() {
                    $buttonGroup.find('.active').removeClass('active');
                    $( this ).addClass('active');
                    });
                });


            },10);

       


        };
    };
    
    // Run this code under Elementor.
    $(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction( 'frontend/element_ready/wtm-team-isotope.default', dwlTeamElementorIsotope );
    });

})(jQuery);
