var $jwptm = jQuery.noConflict();
$jwptm(function(){

	const output_box = $jwptm('#shortcode_output_box');

	function modify_array(obj){

		let arr = [];
		Object.keys(obj).forEach(function(key) {

			if('value'== key){
				arr.push(`'${obj[key]}'`);
			}else{
				arr.push(obj[key]);
			}
			
		  });
		return arr;
	}

	function generate_shortcode(selector){

		const tm_short_code = $jwptm('#tm_short_code').serializeArray();

		const str = tm_short_code.map(a =>  `${modify_array(a).join("=")}`).join(" ");

		const short_code = "[team_manager "+str+"]";

		selector.empty().append(short_code);

		return short_code;
	}


	function get_preview(shortcode){

		const data = {
			'action': 'wtm_admin_preview',
			'nonce': wtm_ajax.nonce,
			'shortcode': shortcode
		};
		$jwptm.ajax({
			url: wtm_ajax.url, // this will point to admin-ajax.php
			type: 'POST',
			data: data, 
			success: function (response) {
				//console.log(response);
				$jwptm('#wtpm_short_code_preview').empty().append(response);
				let slider = $jwptm('#wtpm_short_code_preview').find('.dwl-team-layout-slider');
				//console.log(slider);
				if( slider.length == 0){
				return;
				}

				slider.each( function( index, element ) {

					$jwptm( element ).slick({
					dots: true,
					arrows: true,
					nextArrow: '<button class="dwl-slide-arrow dwl-slide-next"><i class="fas fa-chevron-left"></i></button>',
					prevArrow: '<button class="dwl-slide-arrow dwl-slide-prev"><i class="fas fa-chevron-right"></i></button>',
					infinite: false,
					speed: 300,
					slidesToShow: 4,
					slidesToScroll: 4,
						responsive: [
							{
							breakpoint: 1024,
							settings: {
								slidesToShow: 3,
								slidesToScroll: 3,
								infinite: true,
								dots: true
							}
							},
							{
							breakpoint: 600,
							settings: {
								slidesToShow: 2,
								slidesToScroll: 2
							}
							},
							{
							breakpoint: 480,
							settings: {
								slidesToShow: 1,
								slidesToScroll: 1
							}
							}
							// You can unslick at a given breakpoint now by adding:
							// settings: "unslick"
							// instead of a settings object
						]
					});
					
				});
			}
		});
	}

	// Debounce utility function
	function debounce(func, wait) {
		let timeout;
		return function(...args) {
			const context = this;
			clearTimeout(timeout);
			timeout = setTimeout(() => func.apply(context, args), wait);
		};
	}

	const debouncedUpdate = debounce(function() {
		const shortcodegenerated = generate_shortcode(output_box);
		get_preview(shortcodegenerated);
	}, 400);

	$jwptm('.wtm-color-picker').wpColorPicker({
		change: function(event, ui) {
			debouncedUpdate();
		}
	});

	const shortcodegenerated = generate_shortcode(output_box);
	get_preview(shortcodegenerated);

	// Debounced handler for input changes to avoid excessive AJAX calls
	$jwptm('#tm_short_code :input').on("keyup keydown change", debouncedUpdate);

});