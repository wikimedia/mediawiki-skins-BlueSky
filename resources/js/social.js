( function ( $ ) {
	$( document ).ready( function() {
		WH.addScrollEffectToTOC();
	} );

	$( window ).load( function() {
		var clientProfile = $.client.profile();
		
		if (
			$( '.twitter-share-button' ).length &&
			( !clientProfile['name'] === 'msie' || clientProfile['versionBase'] > 7 )
		)
		{
			$.getScript( 'https://platform.twitter.com/widgets.js', function() {
				twttr.events.bind( 'tweet', function ( event ) {
					if ( event ) {
						var targetUrl;
						if ( event.target && event.target.nodeName == 'IFRAME' ) {
							targetUrl = extractParamFromUri( event.target.src, 'url' );
						}
						if ( typeof _gaq !== 'undefined' ) {
							_gaq.push( ['_trackSocial', 'twitter', 'tweet', targetUrl] );
						}
					}
				} );
			} );
		}

		var ua = navigator.userAgent.toLowerCase(),
			isiPad = ua.indexOf( 'ipad' ),
			isiPhone = ua.indexOf( 'iphone' );

		if ( isiPhone < 0 && isiPad < 0 && $( '.gplus1_button' ).length ) {
			WH.setGooglePlusOneLangCode();
			var node2 = document.createElement( 'script' );
			node2.type = 'text/javascript';
			node2.async = true;
			node2.src = 'http://apis.google.com/js/plusone.js';
			$( 'body' ).append( node2 );
		}

		// Init Facebook components
		if ( typeof WH.FB != 'undefined' ) {
			WH.FB.init( 'new' );
		}

		// Init Google+ Sign In components
		if ( typeof WH.GP != 'undefined' ) {
			WH.GP.init();
		}

		if ( $( '#pinterest' ).length ) {
			var node3 = document.createElement( 'script' );
			node3.type = 'text/javascript';
			node3.async = true;
			node3.src = 'http://assets.pinterest.com/js/pinit.js';
			$( 'body' ).append( node3 );
		}

		if ( typeof WH.imageFeedback != 'undefined' ) {
			WH.imageFeedback();
		}
		if ( typeof WH.uciFeedback != 'undefined' ) {
			WH.uciFeedback();
		}
	} );
} )( jQuery );