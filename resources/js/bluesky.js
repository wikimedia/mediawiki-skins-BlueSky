/* global mw */
var WH = WH || {};

var ua = navigator.userAgent.toLowerCase(); // get client browser info

// temp hack for +1 button not working on iPhone/iPad
var isiPad = ua.indexOf( 'ipad' );
var isiPhone = ua.indexOf( 'iphone' );

var bShrunk = false;

WH.onLoadChineseSpecific = function () {
	// Handler for variant change to Chinese site language
	$( '#header #wpUserVariant' ).change( function () {
		var variant = $( '#wpUserVariant' ).val();
		setCookie( 'wiki_sharedvariant', variant, 0 );

		var hs = location.href.split( '?' );
		var loc = '';
		if ( hs.length > 1 ) {
			var params = hs[1].replace( /^variant=[^&#]+/, '' ).replace( /&variant=[^&#]+/, '' );
			if ( params ) {
				loc = hs[0] + '?' + params;
			} else {
				loc = hs[0];
			}
		} else {
			loc = hs[0];
		}

		location.href = loc;
	} );
};
if ( mw.config.get( 'wgContentLanguage' ) == 'zh' ) {
	$( document ).ready( WH.onLoadChineseSpecific );
}

// Pass in expiresDays=0 for a session cookie
function setCookie( name, value, expireDays ) {
	expireDays = typeof expires == 'undefined' ? 7 : expireDays;
	var daysMs = expireDays * 24 * 60 * 60 * 1000;
	var expireDate = new Date();
	expireDate.setDate( expireDate.getDate() + daysMs );
	document.cookie = name + '=' + escape( value ) + ( !expireDays ? '' : ';expires=' + expireDate.toGMTString() ) + ';path=/';
}

function getCookie( c_name ) {
	var x, y,
		cookiesArr = document.cookie.split( ';' );
	for ( var i = 0; i < cookiesArr.length; i++ ) {
		x = cookiesArr[i].substr( 0, cookiesArr[i].indexOf( '=' ) );
		y = cookiesArr[i].substr( cookiesArr[i].indexOf( '=' ) + 1 );
		x = x.replace( /^\s+|\s+$/g, '' );
		if ( x == c_name ) {
			return unescape( y );
		}
	}
}

/**
 * This is not directly used by this skin, but there are like over ten different
 * wikiHow extensions that call this function, so I'll let it stay here
 * [[for now]] until I figure out what to do with it.
 *
 * @param {String} Element ID
 * @param {String} Subpage name
 */
function updateWidget( id, x ) {
	var url = mw.util.getUrl( 'Special:Standings/' + x );
	$.get( url,
		function ( data ) {
			$( id ).fadeOut();
			$( id ).html( data.html );
			$( id ).fadeIn();
		},
		'json'
	);
}

/**
 * This is not directly used by this skin, but there are like over ten different
 * wikiHow extensions that call this function, so I'll let it stay here
 * [[for now]] until I figure out what to do with it.
 *
 * @param {String} Element ID
 */
function updateTimer( id ) {
	var e = jQuery( '#' + id );
	var i = parseInt( e.html() );
	if ( i > 1 ) {
		e.fadeOut( 400, function() {
			i--;
			e.html( i );
			e.fadeIn();
		} );
	}
}

function setupEmailLinkForm() {
	$( '#emaillink' ).submit( function() {
		var params = { fromajax: true };
		$( '#emaillink input' ).each( function () {
			params[$( this ).attr( 'name' )] = $( this ).val();
		} );
		$.post( mw.util.getUrl( 'Special:EmailLink' ), params, function ( data ) {
			$( '#dialog-box' ).html( data );
			setupEmailLinkForm();
		} );
		return false;
	} );
}

function emailLink() {
	var params = {
		target: mw.config.get( 'wgPageName' ),
		fromajax: true
	};
	var url = mw.util.getUrl( 'Special:EmailLink', params );	
	$( '#dialog-box' ).load( url2, function() {
		$( '#dialog-box' ).dialog( {
			modal: true,
			title: 'E-mail This Page to a Friend',
			width: 650,
			height: 400,
			closeText: 'Close'
		} );
		setupEmailLinkForm();
	} );
	return false;
}

/**
 * Record a push on the +1 button to Google Analytics
 * This is called by /extensions/wikihow/WikihowShare.body.php.
 */
function plusone_vote( obj ) {
	if ( typeof _gaq !== undefined ) {
		_gaq.push(['_trackEvent', 'plusone', obj.state]);
	}
}

jQuery( document ).on( 'click', '.lbg', function () {
	var id = jQuery( this ).attr( 'id' ).split( '_' );
	jQuery( '#lbgi_' + id[1] ).click();
} );

jQuery( document ).on( 'click', '#tb_steps', function () {
	$( '#tb_steps' ).addClass( 'on' );
	$( '#tb_tips' ).removeClass( 'on' );
	$( '#tb_warnings' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#steps' ).offset().top - 70}, 'slow');
} );

jQuery( document ).on( 'click', '#tb_tips', function () {
	$( '#tb_tips' ).addClass( 'on' );
	$( '#tb_steps' ).removeClass( 'on' );
	$( '#tb_warnings' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#tips' ).offset().top - 70}, 'slow');
} );

jQuery( document ).on( 'click', '#tb_warnings', function () {
	$( '#tb_warnings' ).addClass( 'on' );
	$( '#tb_steps' ).removeClass( 'on' );
	$( '#tb_tips' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#warnings' ).offset().top - 70}, 'slow');
} );

WH.addScrollEffectToTOC = function () {
	// regular TOC
	if ( $( '#toc' ).length ) {
		WH.addScrollEffectToHashes( $( '#toc * a' ) );
	}
	// Alt Method TOC
	if ( $( '#method_toc' ).length ) {
		WH.addScrollEffectToHashes( $( '#method_toc a' ) );
	}
};

// add a scroll effect using jQuery.scrollTo
WH.addScrollEffectToHashes = function ( anchors ) {
	anchors.each( function () {
		// split off the part of the URL after the '#'
		var href = $( this ).attr( 'href' );
		var parts = href.split( /#/ );
		if ( parts.length <= 1 || !parts[1] ) {
			return;
		}

		// find the corresponding anchor tag to scroll to
		var hash = parts[1];
		var anchor = $( 'a[name="' + hash + '"]' );
		if ( anchor.length != 1 ) {
			return;
		}

		// add a new click handler
		$( this ).on( 'click', function () {
			$.scrollTo( anchor, 1000 );
			if ( history.pushState ) {
				history.pushState( null, null, '#' + hash );
			} else {
				location.hash = '#' + hash;
			}

			return false;
		} );
	} );
};

/**
 * Has the page been scrolled so that .article_bottom is visible?
 *
 * @todo FIXME: This method makes no sense because the skin doesn't natively
 *              provide such an element. Where does it come from?
 * @return bool
 */
WH.isPageScrolledToArticleBottom = function () {
	var elem = '.article_bottom';
	var docViewTop = $( window ).scrollTop();
	var docViewBottom = docViewTop + $( window ).height();

	var elemTop = $( elem ).offset().top;
	elemTop += 125;
	var elemBottom = elemTop + $( elem ).height();

	return ( ( elemBottom >= docViewTop ) && ( elemTop <= docViewBottom ) );
};

/**
 * Has the page been scrolled so that either #warnings or #article_info_header
 * is visible?
 *
 * @return bool
 */
WH.isPageScrolledToWarningsORArticleInfo = function () {
	var elem1 = '#warnings';
	var elem2 = '#article_info_header';
	var the_elem = '';

	if ( $( elem1 ).length ) {
		the_elem = elem1;
	} else {
		the_elem = elem2;
	}

	var docViewTop = $( window ).scrollTop();
	var docViewBottom = docViewTop + $( window ).height();

	var offset = $( the_elem ).offset();
	return offset ? offset.top <= docViewBottom : false;
};

/**
 * Has the page been scrolled so that #follow_table is visible?
 *
 * @return bool
 */
WH.isPageScrolledToFollowTable = function () {
	var the_elem = '#follow_table';

	var docViewTop = $( window ).scrollTop();
	var docViewBottom = docViewTop + $( window ).height();

	var offset = $( the_elem ).offset();
	return offset ? offset.top <= docViewBottom : false;
};

// Snippet to prevent site search forms submitting empty queries
( function ( $ ) {
	$( document ).on( 'click', '#search_site_bubble, #search_site_footer', function ( e ) {
		if ( $( this ).siblings( 'input[name="search"]' ).val().length === 0 ) {
			e.preventDefault();
			return false;
		}
	} );
} )( jQuery );

$( document ).ready( function () {

	var clientProfile = $.client.profile();
	// Slider -- not for browsers that don't render +1 buttons
	var oldMSIE = ( clientProfile['name'] === 'msie' ) && clientProfile['versionBase'] <= 7;
	if (
		$( '#slideshowdetect' ).length &&
		slider &&
		!getCookie( 'sliderbox' ) &&
		isiPhone < 0 &&
		isiPad < 0 &&
		!oldMSIE
	)
	{

		if ( $( '#slideshowdetect_mainpage' ).length ) {
			// homepage
			$( window ).bind( 'scroll', function () {
				if ( !getCookie( 'sliderbox' ) ) {
					if (
						WH.isPageScrolledToFollowTable() &&
						$( '#sliderbox' ).css( 'right' ) == '-500px' &&
						!$( '#sliderbox' ).is( ':animated' )
					)
					{
						slider.init();
					}
					if (
						!WH.isPageScrolledToFollowTable() &&
						$( '#sliderbox' ).css( 'right' ) == '0px' &&
						!$( '#sliderbox' ).is( ':animated' )
					)
					{
						slider.closeSlider();
					}
				}
			});
		} else {
			// article page
			$( window ).bind( 'scroll', function () {
				if ( !getCookie( 'sliderbox' ) ) {
					if (
						WH.isPageScrolledToWarningsORArticleInfo() &&
						$( '#sliderbox' ).css( 'right' ) == '-500px' &&
						!$( '#sliderbox' ).is( ':animated' )
					)
					{
						slider.init();
					}
					if (
						!WH.isPageScrolledToWarningsORArticleInfo() &&
						$( '#sliderbox' ).css( 'right' ) == '0px' &&
						!$( '#sliderbox' ).is( ':animated' )
					)
					{
						slider.closeSlider();
					}
				}
			} );
		}
	}

	// fire off slideshow if we need to
	if ( $( '#showslideshow' ).length ) {
		var url = mw.util.getUrl( 'Special:GallerySlide', {
			'show-slideshow': 1,
			'big-show': 1,
			'article-layout': 2,
			aid: mw.config.get( 'wgArticleId' )
		} );

		$.getJSON( url, function ( json ) {
			if ( json && json.content ) {
				document.getElementById( 'showslideshow' ).innerHTML = json.content;
				fireUpSlideShow( json.num_images );
				$( '#showslideshow' ).slideDown();
			}
		} );
	}

	// add checkbox click handlers
	$( '.step_checkbox' ).on( 'click', function () {
		if ( $( this ).hasClass( 'step_checked' ) ) {
			$( this ).removeClass( 'step_checked' );
		} else {
			$( this ).addClass( 'step_checked' );
			// track the clicks
			try {
				if ( _gaq ) {
					_gaq.push(['_trackEvent', 'checks', 'non-mobile', 'checked']);
				}
			} catch ( err ) {}
		}
		return false;
	} );
	$( '.css-checkbox' ).on( 'click', function() {
		$( this ).parent().find( '.checkbox-text' ).toggleClass( 'fading' );
	} );

	initTopMenu();
	initAdminMenu();

	// site notice close
	$( '#site_notice_x' ).on( 'click', function () {
		// 30 day cookie
		var exdate = new Date();
		var expiredays = 30;
		exdate.setDate( exdate.getDate() + expiredays );
		document.cookie = 'sitenoticebox=1;expires=' + exdate.toGMTString();

		// good-bye
		$( '#site_notice' ).hide();
	} );
} );

/**
 * Set the correct language code for Google+.
 *
 * Currently there's some special handling for Portuguese (pt) to make Google+
 * use Brazilian Portuguese instead of European Portuguese, and Google+ language
 * code is also set for Spanish (es) and German (de). All other languages
 * appear to be ignored? Strange.
 */
WH.setGooglePlusOneLangCode = function () {
	var langCode = '';
	if ( mw.config.get( 'wgUserLanguage' ) == 'pt' ) {
		langCode = 'pt-BR';
	}
	if (
		mw.config.get( 'wgUserLanguage' ) == 'es' ||
		mw.config.get( 'wgUserLanguage' ) == 'de'
	)
	{
		langCode = mw.config.get( 'wgUserLanguage' );
	}
	if ( langCode ) {
		window.___gcfg = {lang: langCode};
	}
};

jQuery( document ).on( 'click', 'a#wikitext_downloader', function ( e ) {
	e.preventDefault();
	var data = { 'pageid': mw.config.get( 'wgArticleId' ) };
	jQuery.download( mw.util.getUrl( 'Special:WikitextDownloader' ), data );
} );

jQuery( '#authors' ).on( 'click', function ( e ) {
	$( '#originator_names' ).toggle();
	return false;
} );
jQuery( '#originator_names_close' ).on( 'click', function ( e ) {
	$( '#originator_names' ).hide();
	return false;
} );

WH.displayTranslationCTA = function () {
	var userLang = ( navigator.language ) ? navigator.language : navigator.userLanguage;
	userLang = typeof userLang == 'string' ? userLang.substring( 0, 2 ).toLowerCase() : '';

	// Make it easier to test this feature
	if ( window.location.href.indexOf( 'testtranscta=' ) !== false ) {
		var matches = /testtranscta=([a-z]+)/.exec( window.location.href );
		if ( matches ) {
			userLang = matches[1];
		}
	}

	var transCookie = getCookie( 'trans' );
	if (
		transCookie != '1' &&
		userLang &&
		typeof WH.translationData != 'undefined' &&
		typeof WH.translationData[userLang] != 'undefined'
	)
	{
		var msg = WH.translationData[userLang]['msg'];
		if ( msg ) {
			$( '#main' ).prepend(
				'<span id="gatNewMessage"><div class="message_box">' + msg +
				'<div class="transd-no" style="float:right;padding-right:10px;font-size:12px;"><a href="#">' +
				mw.msg( 'bluesky-js-no-thanks' ) + '</a></div></div></span>'
			);
			$( '.transd-no' ).on( 'click', function () {
				$( '.message_box' ).hide();
				setCookie( 'trans', 1, 30 );

				return false;
			} );
		}
	}
};

$( document ).ready( WH.displayTranslationCTA );

/**
 * Consider displaying a Call To Action (CTA) for users who were referred to
 * the site from a social network (Facebook, Pinterest, Twitter or Google+).
 */
WH.maybeDisplayTopSocialCTA = function () {
	var referrer = document.referrer ? document.referrer : '';
	var testNetwork = mw.util.getParamValue( 'soc', location.href );
	if ( testNetwork ) {
		referrer = testNetwork;
	}

	var social = '';
	if ( /facebook\.com/.test( referrer ) ) {
		social = 'facebook';
	} else if ( /pinterest\.com/.test( referrer ) ) {
		social = 'pinterest';
	} else if ( /twitter\.com/.test( referrer ) ) {
		social = 'twitter';
	} else if ( /plus\.google\.com/.test( referrer ) ) {
		social = 'gplus';
	}

	if ( social ) {
		var checkMsg = function ( msg ) {
			return msg && $.trim( msg ).indexOf( '[' ) !== 0;
		};
		var insertHTMLcallback = function ( html ) {
			if ( !$.trim( html ) ) {
				return;
			}
			var node = $( html );
			node.css({height: '0px'});
			$( 'body' ).prepend( node );
			$( '#pin-head-cta' ).animate({height: '+230px'}, 700);
			$( '#main, #footer_shell' ).css({position: 'relative'});
			//$('#main, #header, #footer_shell').animate({top: '+100px'});
		};

		// check for HTML of a particular social network first
		var html = mw.msg( 'social-html-' + social );
		html = checkMsg( html ) ? html : '';

		// if not, check for the general HTML
		if ( !html ) {
			html = mw.msg( 'social-html' );
			html = checkMsg( html ) ? html : '';
		}

		// for efficiency, you can embed the social network stuff into the HTML
		// using social-html-facebook (for example) MW messages
		if ( html ) {
			insertHTMLcallback( html );
		} else {
			$.get( mw.util.getUrl( 'Special:WikihowShare', { soc: social } ), insertHTMLcallback );
		}
	}
};

$( document ).ready( WH.maybeDisplayTopSocialCTA );

function initAdminMenu() {
	$( 'ul#tabs li' ).hover( function () {
		var that = $( this );
		menu_show( that );
	}, function () {
		var that = $( this );
		menu_hide( that );
	} );
}

var on_menu = false;

function initTopMenu() {
	var on_menu = false;

	// intelligent delay so we don't have flickering menus
	$( '#header ul#actions li' ).hover( function () {
		var that = $( this );
		var wait = ( on_menu ) ? 150 : 0;
		clearTimeout( $( this ).data( 'timeout2' ) );
		that.data( 'timeout', setTimeout( function () {
			menu_show( that );
			on_menu = true;
		}, wait ) );
	}, function () {
		var that = $( this );
		var wait = 150;
		clearTimeout( that.data( 'timeout' ) );

		// Firefox fix: lengthen delay to make sure they aren't on an autocomplete menu
		if (
			( /Firefox/.test( navigator.userAgent ) ) &&
			(
				$( '#wpName1_head' ).is( ':focus' ) ||
				$( '#wpPassword1_head' ).is( ':focus' )
			)
		)
		{
			wait = 2000;
		}

		that.data( 'timeout2', setTimeout( function () {
			menu_hide( that );
			on_menu = false;
		}, wait ) );
	} );

	// thumbs up deletion
	$( '.th_close' ).on( 'click', function() {
		var revId = $( this ).attr( 'id' );
		var giverIds = $( '#th_msg_' + revId ).find( '.th_giver_ids' ).html();
		var url = mw.util.getUrl( 'Special:ThumbsNotifications', { rev: revId, givers: giverIds } );

		$.get( url, function ( data ) {} );
		$( '#th_msg_' + revId ).hide();

		// lower the notification count in the header by 1
		$( '#notification_count' ).html( $( '#notification_count' ).html() - 1 );
	} );

	// logged-in search click
	$( '#bubble_search .search_box, #cse_q' ).on( 'click', function () {
		$( this ).addClass( 'search_white' );
	} );

	// Login stuff
	$( '.nav' ).on( 'click', function() {
		if ( $( this ).attr( 'href' ) == '#' ) {
			return false;
		}
	} );

	$( '.userlogin #wpName1, #wpName1_head' ).val( mw.msg( 'userlogin-yourname-ph' ) )
		.css( 'color', '#ABABAB' )
		.on( 'click', function () {
			if ( $( this ).val() == mw.msg( 'userlogin-yourname-ph' ) ) {
				$( this ).val( '' ); // clear field
				$( this ).css( 'color', '#333' ); // change font color
			}
		} );

	var clientProfile = $.client.profile();
	// switch to text so we can display "Password"
	if ( !( clientProfile['name'] === 'msie' && clientProfile['versionBase'] <= 8 ) ) {
		if ( $( '.userlogin #wpPassword1' ).get( 0 ) ) {
			$('.userlogin #wpPassword1' ).get( 0 ).type = 'text';
		}
		if ( $( '#wpPassword1_head' ).get( 0 ) ) {
			$( '#wpPassword1_head' ).get( 0 ).type = 'text';
		}
	}

	$( '.userlogin #wpPassword1, #wpPassword1_head' ).val( mw.msg( 'userlogin-yourpassword-ph' ) )
		.css( 'color', '#ABABAB' )
		.focus( function () {
			if ( $( this ).val() == mw.msg( 'userlogin-yourpassword-ph' ) ) {
				$( this ).val( '' );
				$( this ).css( 'color', '#333' ); // change font color
				$( this ).get( 0 ).type = 'password'; // switch to dots
			}
		} );

	// @todo FIXME: getPassword() is defined in /extensions/wikihow/loginreminder/LoginReminder.js
	$( '#forgot_pwd' ).on( 'click', function () {
		if ( $( '#wpName1' ).val() == mw.msg( 'userlogin-yourname-ph' ) ) {
			$( '#wpName1' ).val( '' );
		}
		getPassword( escape( $( '#wpName1' ).val() ) );
		return false;
	} );

	$( '#forgot_pwd_head' ).on( 'click', function () {
		if ( $( '#wpName1_head' ).val() == mw.msg( 'userlogin-yourname-ph' ) ) {
			$( '#wpName1_head' ).val( '' );
		}
		getPassword( escape( $( '#wpName1_head' ).val() ) );
		return false;
	} );
}

$( '#method_toc_unhide' ).on( 'click', function () {
	$( '#method_toc a.excess' ).show();
	$( '#method_toc_hide' ).show();
	$( this ).hide();
	return false;
} );

$( '#method_toc_hide' ).on( 'click', function () {
	$( '#method_toc a.excess' ).hide();
	$( '#method_toc_unhide' ).show();
	$( this ).hide();
	return false;
} );

// Adjust padding for sections based on variable h3 height
$( document ).ready( function () {
	$( '.section' ).each( function () {
		var h3Height = $( 'h3:first', this ).height();
		$( this ).css( 'padding-top', h3Height + 'px' );
	} );
} );

/**
 * Shrink/embiggen the main header (div#header_outer) in all sorta-modern
 * browsers (which means that there's no support for IE7 and older)
 */
( function ( $ ) {
	var headerToggling = false;

	$( window ).scroll( function() {
		if ( !headerToggling ) {
			if ( $( window ).scrollTop() <= 0 ) {
				headerToggling = true;
				toggleHeader( false );
			} else {
				if ( !bShrunk ) {
					headerToggling = true;
					toggleHeader( true );
				}
			}
		}
	} );

	function toggleHeader( bShrink ) {
		var clientProfile = $.client.profile();
		// not so fast, IE7...
		if ( clientProfile['name'] === 'msie' && parseFloat( clientProfile['versionBase'] ) < 8 ) {
			return;
		}

		if ( bShrink ) {
			$( '#header' )
				.addClass( 'shrunk' )
				.animate({ height: '39px' }, 200, function () {
					$( this ).css( 'overflow', 'visible' ); // jQuery forces overflow: hidden; this counters it
					headerToggling = false;
				} );
			$( '#main' ).addClass( 'shrunk_header' );
			bShrunk = true;
		} else {
			$( '#header' )
				.animate({ height: '72px' }, 150, function () {
					$( this ).removeClass( 'shrunk' );
					$( this ).css( 'overflow', 'visible' ); // jQuery forces overflow: hidden; this counters it
					$( '#main' ).removeClass( 'shrunk_header' );
					headerToggling = false;
				} );
			bShrunk = false;
		}
	}
} )( jQuery );

// Make those section headings sticky
$( window ).scroll( function () {
	headerHeight = $( '#header' ).height();
	previousHeader = null;
	currentHeader = null;

	var clientProfile = $.client.profile();

	if ( clientProfile['name'] === 'msie' && parseFloat( clientProfile['versionBase'] ) < 8 ) {
		// IE7 doesn't like stickiness
	} else {
		$( '.section.sticky' ).each( function () {
			currentHeader = $( this ).find( 'h2' ); // need to get either h2 or h3
			if ( !$( currentHeader ).is( ':visible' ) ) {
				// likely means we're in a steps section with h3 headers
				currentHeader = $( this ).find( 'h3' );
			}
			if ( currentHeader.length === 0 ) {
				// if there's nothing to use, just skip this section.
				// Shouldn't really even end up in this case
				return;
			}
			makeSticky( $( this ), currentHeader );
		} );

		$( '.tool.sticky' ).each( function () {
			currentHeader = $( '.tool_header' );
			if ( currentHeader.length === 0 ) {
				// if there's nothing to use, just skip this section.
				// Shouldn't really even end up in this case
				return;
			}
			// Disable stickiness for the RC patrol guided tour
			if ( mw.util.getParamValue( 'gt_mode', document.location.search ) != 1 ) {
				makeSticky( $( this ), currentHeader );
			}
		} );
	}
} );

function makeSticky( container, element ) {
	scrollTop = $( document ).scrollTop();
	sectionHeight = container.height();
	offsetTop = container.offset().top;

	if ( scrollTop + headerHeight < offsetTop ) {
		$( element ).removeClass( 'sticking' );
	} else {
		if ( scrollTop - offsetTop - sectionHeight + headerHeight > 0 ) {
			$( element ).removeClass( 'sticking' );
		} else {
			$( element ).addClass( 'sticking' );
		}
	}
}

// New checkmarks aren't compatible with IE 8 and earlier so revert to old style
$( document ).ready( function () {
	var clientProfile = $.client.profile();
	if ( clientProfile['name'] === 'msie' && clientProfile['versionBase'] <= 8 ) {
		$( '.css-checkbox' ).removeClass( 'css-checkbox' );
		$( '.css-checkbox-label' ).removeClass( 'css-checkbox-label' );
	}
} );

( function ( $ ) {
	$( document ).on( 'click', 'a[href^=#_note-], a[href^=#_ref-]', function ( e ) {
		e.preventDefault();
		$( 'html, body' ).animate( {
			scrollTop: $( $( this ).attr( 'href' ) ).offset().top - 100
		}, 0 );
	} );
} )( jQuery );

/**
 * This is not directly used by the skin, but instead by various wikiHow extensions.
 */
function initToolTitle() {
	$( '.firstHeading' ).before( '<h5>' + $( '.firstHeading' ).html() + '</h5>' );
}

/**
 * The following things appear to call this function:
 * /extensions/wikihow/nfd/nfdGuardian.js
 * /extensions/wikihow/video/videoadder.js
 */
function addOptions() {
	$( '.firstHeading' ).before( '<span class="tool_options_link">(<a href="#">Change Options</a>)</span>' ); // @todo FIXME: i18n
	$( '.firstHeading' ).after( '<div class="tool_options"></div>' );

	$( '.tool_options_link' ).on( 'click', function () {
		if ( $( '.tool_options' ).css( 'display' ) == 'none' ) {
			// show it!
			$( '.tool_options' ).slideDown();
		} else {
			// hide it!
			$( '.tool_options' ).slideUp();
		}
	} );
}

function menu_show( choice ) {
	var clientProfile = $.client.profile();
	if ( clientProfile['name'] === 'msie' && parseFloat( clientProfile['versionBase'] ) < 9 ) {
		// IE7 and IE8 fix
		$( choice ).find( '.menu, .menu_login, .menu_messages' )
			.stop( true, true )
			.show();
	} else {
		// for every other browser
		$( choice ).find( '.menu, .menu_login, .menu_messages' )
			.stop( true, true )
			.slideDown( 300, 'easeInOutBack' );
	}

	// reset the notification count
	if (
		$( '.menu_message_morelink' ) &&
		$( choice ).attr( 'id' ) == 'nav_messages_li' &&
		$( '#notification_count' ).is( ':visible' )
	)
	{
		// first, let's just hide it
		$( '#notification_count' ).hide();

		if ( mw.echo ) {
			// now grab the unread messages and mark them as read
			var api = new mw.Api( { ajax: { cache: false } } ),
				notifications, data, unread = [], apiData;

			apiData = {
				'action': 'query',
				'meta': 'notifications',
				'notformat': 'flyout',
				'notprop': 'index|list|count',
				'notlimit': 5,
			};

			api.get( mw.echo.desktop.appendUseLang( apiData ) ).done( function ( result ) {
				notifications = result.query.notifications;
				unread = [];

				$.each( notifications.index, function ( index, id ) {
					data = notifications.list[id];

					if ( !data.read ) {
						unread.push( id );
					}
				} );

				// no unread ones? forget about it...
				if ( unread.length === 0 ) {
					return;
				}

				api.post( mw.echo.desktop.appendUseLang( {
					'action': 'echomarkread',
					'list': unread.join( '|' ),
					'token': mw.user.tokens.get( 'editToken' )
				} ) ).done( function ( result ) {
					// SUCCESS!
				} ).fail( function () {
					// FAIL
				} );
			} );
		}
	}
}

function menu_hide( choice ) {
	var clientProfile = $.client.profile();
	if ( clientProfile['name'] === 'msie' && parseFloat( clientProfile['versionBase'] ) < 9 ) {
		// IE7 and IE8 fix
		$( choice ).find( '.menu, .menu_login, .menu_messages' )
			.stop( true, true )
			.hide();
	} else {
		$( choice ).find( '.menu, .menu_login, .menu_messages' )
			.stop( true, true )
			.slideUp( 200 );
	}
}