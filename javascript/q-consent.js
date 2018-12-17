/*
 * $ Calls v0.9 ##
 * (c) 2012 Q Studio - qstudio.us
 */

// jQuery ##
if ( typeof jQuery !== 'undefined' ) {

    (function ($) {

    	$( window ).on( "load", function(){

    	$tabs = $('.q-tab-trigger');
    	//$tabs.removeClass('active');
    	$('q-tab-current').addClass('active');
    	});//First modal page load

		// setting options @viktor to improve UI and UX ##
		$( document.body).on( "click", ".q-consent-option > div", function(e){

			//e.preventDefault();
			var t = this;

			// reject disabled option ##
			if( $(t).hasClass('disabled') ) {

				q_snackbar({
					content:    q_consent.disabled, // msg ##
					timeout:    5000, // never timeout ##
					style: 		'error'
				});

				return false;

			}

			var $field = $(t).parent('div').data('q-consent-field'); // get field ##
			var $value = $(t).data('q-consent-value'); // get value ##

			// console.log( 'Clicked on option: '+$field+' with value: '+$value );

			// swap class ##
			if ( $value == '1' ) {

				// console.log( 'Click on..' );
				$(t).addClass('on').prev('div').removeClass('off');

			} else {

				// console.log( 'Click off..' );
				$(t).addClass('off').next('div').removeClass('on');

			}

			// update tracking values ##
			$btn = $('button.q-consent-set');
			$btn.prop( 'disabled', false );
			$btn.data( 'q-consent-'+$field, $value );
			$btn.attr( 'data-q-consent-'+$field, $value ) 

			// $('[data-q-consent-="'+$field+'"]').val( $value );
			// $( '.q-consent-set' ).data( 'q-consent-marketing', $value );

		});

		// bootstrap-js hack - Bolts on the active class for bootstrap tabs - nothing else ##
        $( document.body ).on( "click", ".q-tab-trigger", function(e){
        	//this doesn't work. the existing q-tabs JS writes over it. That global JS should be updated to accomodate Bootstrap tab styles
        	var t = e.target;
        	$('.q-tab-trigger').removeClass('active');
        	$(t).addClass('active');

        });

		// save settings ##
        $( document.body ).on( "click", ".q-consent-set", function(e){

			e.preventDefault();
			var t = this;
			
			// collect data for process ##
			var $marketing = $(t).data('q-consent-marketing'); // get marketing ##
			var $analytics = $(t).data('q-consent-analytics'); // get analytics ##

			//console.log( 'Marketing: '+$marketing );
			//console.log( 'Analytics: '+$analytics );

			// log ##
            // console.log( "Saving Consent settings..." );
			
			// clear progress ##
			if ( typeof NProgress !== 'undefined' ) { NProgress.done(); }

			$.ajax({
				url: q_consent.ajax_url,
				type: 'POST',
				data: {
						action: 				'consent_set'
					,	q_consent_marketing: 	$marketing
					,	q_consent_analytics: 	$analytics
					,   nonce: 					q_consent.ajax_nonce
				},
				dataType: 'json',
				beforeSend: function () {

					if ( typeof NProgress !== 'undefined' ) { NProgress.start(); }

				},
				success: function ( response ) {

					if ( response.status ) {

						q_snackbar({
							content:    response.message, // msg ##
							timeout:    5000, // never timeout ##
							style: 		'success'
						});

						if ( typeof NProgress !== 'undefined' ) { NProgress.done(); }

						// we should hide the consent bar, as this is not longer required - the user can "x" out of the modal to close the process ##
						$('.q-consent-bar').hide();

					} else {

						q_snackbar({
							content:    q_consent.error, // msg ##
							timeout:    5000, // never timeout ##
							style: 		'error'
						});

					}

				}

			});

        });


		// clear cookie callback - for debugging ##
		$( document.body ).on( "click", ".q-consent-reset", function(e){

			e.preventDefault();

            // console.log( "Resetting Consent settings..." );
			
			// clear progress ##
			if ( typeof NProgress !== 'undefined' ) { NProgress.done(); }

			$.ajax({
				url: q_consent.ajax_url,
				type: 'POST',
				data: {
						action: 'consent_reset'
					,   nonce: q_consent.ajax_nonce
				},
				dataType: 'json',
				beforeSend: function () {

					if ( typeof NProgress !== 'undefined' ) { NProgress.start(); }

				},
				success: function (response) {

					if ( response ) {

						q_snackbar({
							content:    response.message, // msg ##
							timeout:    5000, // never timeout ##
							style: 		'success'
						});

						if ( typeof NProgress !== 'undefined' ) { NProgress.done(); }

					} else {

						q_snackbar({
							content:    q_consent.error, // msg ##
							timeout:    5000, // never timeout ##
							style: 		'error'
						});

					}

				}

			});

        });


    })(jQuery);

} 