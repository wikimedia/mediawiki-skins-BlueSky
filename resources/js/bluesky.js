var WH = WH || {};

var ua = navigator.userAgent.toLowerCase(); // get client browser info

// temp hack for +1 button not working on iphone/ipad
var isiPad = ua.indexOf( 'ipad' );
var isiPhone = ua.indexOf( 'iphone' );

var bShrunk = false;

WH.onLoadChineseSpecific = function () {
	// Handler for variant change to Chinese site language
	$( '#header #wpUserVariant' ).change( function () {
		var variant = $( '#wpUserVariant' ).val();
		setCookie( 'wiki_sharedvariant', variant, 0 );

		var hs = location.href.split( '?' );
		var loc = "";
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
	var expireDays = typeof expires == 'undefined' ? 7 : expireDays;
	var daysMs = expireDays * 24 * 60 * 60 * 1000
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

// Do a scrolling reveal
function scroll_open( id, height, max_height ) {
	document.getElementById( id ).style.top = height + 'px';
	document.getElementById( id ).style.display = 'block';
	document.getElementById( id ).style.position = 'relative';
	height += 1;
	if ( height < max_height ) {
		window.setTimeout( "scroll_open('" + id + "'," + height + "," + max_height + ")", 15 );
	}
}

var mainPageFAToggleFlag = false;
function mainPageFAToggle() {
	var firstChild = jQuery('#toggle');
	if ( mainPageFAToggleFlag == false ) {
		jQuery( '#hiddenFA' ).slideDown( 'slow' ).show( function () {
			firstChild.html( wfMsg( 'mainpage_fewer_featured_articles' ) );
			jQuery( '#moreOrLess' ).attr( 'src', wgCDNbase + '/skins/WikiHow/images/arrowLess.png' );
			jQuery( '#featuredNav' ).hide(); //need to do this for IE7
			jQuery( '#featuredNav' ).show();
		} );

		mainPageFAToggleFlag = true;
	} else {
		jQuery( '#hiddenFA' ).slideUp( 'slow' ).hide( function () {
			firstChild.html( wfMsg( 'mainpage_more_featured_articles' ) );
			jQuery( '#moreOrLess').attr( 'src', wgCDNbase + '/skins/WikiHow/images/arrowMore.png' );
			jQuery( '#featuredNav' ).hide(); //need to do this for IE7
			jQuery( '#featuredNav' ).show();
		} );
		mainPageFAToggleFlag = false;
	}
}

function setStyle( obj, style ) {
	if ( obj ) {
		if ( navigator.userAgent.indexOf( 'MSIE' ) > 0) {
			obj.style.setAttribute( 'csstext', style, 0 );
		} else {
			obj.setAttribute( 'style', style );
		}
	}
}

/**
 * Translates a MW message (ie, 'new-link') into the correct language text. Eg:
 * wfMsg('new-link', 'http://mylink.com/');
 *
 * - loads all messages from WH.lang
 * - added by Reuben
 */
function wfMsg( key ) {
	if ( typeof WH.lang[key] === 'undefined' ) {
		return '[' + key + ']';
	} else {
		var msg = WH.lang[key];
		if ( arguments.length > 1 ) {
			// matches symbols like $1, $2, etc
			var syntax = /(^|.|\r|\n)(\$([1-9]))/g;
			var replArgs = arguments;
			msg = msg.replace( syntax, function ( match, p1, p2, p3 ) {
				return p1 + replArgs[p3];
			} );
			// This was the old prototype.js Template syntax
			//var template = new Template(msg, syntax);
			//var args = $A(arguments); // this has { 1: '$1', ... }
			//msg = template.evaluate(args);
		}
		return msg;
	}
}

/**
 * Templates html etc. Use as follows:
 *
 * var html = wfTemplate('<a href="$1">$2</a>', mylink, mytext);
 */
function wfTemplate( tmpl ) {
	var syntax = /(^|.|\r|\n)(\$([1-9]))/g; // matches symbols like $1, $2, etc
	var replArgs = arguments;
	var out = tmpl.replace( syntax, function ( match, p1, p2, p3 ) {
		return p1 + replArgs[p3];
	} );
	return out;
}

/**
 * A simple pad function. Note that it won't match up with the output of
 * the php.
 */
function wfGetPad( url ) {
	if ( url.search( /^http:\/\// ) >= 0 ) {
		return url;
	} else {
		return wgCDNbase + url;
	}
}

var sh_links = Array( 'showads' );

function sethideadscookie( val ) {
	var date = new Date();
	if ( val == 1 ) {
		date.setTime( date.getTime() + ( 1 * 24 * 60 * 60 * 1000 ) );
	} else {
		date.setTime( date.getTime() - ( 30 * 24 * 60 * 60 * 1000 ) );
	}
	var expires = '; expires=' + date.toGMTString();
	document.cookie = 'wiki_hideads=' + val + expires + '; path=/';
}

function showorhideads( hide ) {
	var style = 'display: inline;';
	if ( hide ) {
		style = 'display: none;';
	}
	$( '.wh_ad_inner' ).hide();
	for ( var i = 0; i < sh_links.length; i++ ) {
		var e = document.getElementById( sh_links[i] );
		if ( !e ) {
			continue;
		}
		if ( hide ) {
			style = 'display: inline;';
		} else {
			style = 'display: none;';
		}
		setStyle( e, style );
	}
	$( '.show_ads' ).show();
}

function hideads() {
	sethideadscookie( 1 );
	showorhideads( true );
	clickshare( 20 );
}

function showads() {
	sethideadscookie( 0 );
	showorhideads( false );
	window.location.reload();
}

var gHideAds = gHideAds || false;
var gchans = gchans || false;
var google_analytics_domain_name = ".wikihow.com"

function updateWidget( id, x ) {
	var url = '/Special:Standings/' + x;
	$.get( url,
		function ( data ) {
			$( id ).fadeOut();
			$( id ).html( data['html'] );
			$( id ).fadeIn();
		},
		'json'
	);
}
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

function parseIntWH( num ) {
	if ( !num ) {
		return 0;
	}
	return parseInt( num.replace( /,/, '' ), 10 );
}

function addCommas( nStr ) {
	nStr += '';
	x = nStr.split( '.' );
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while ( rgx.test( x1 ) ) {
		x1 = x1.replace( rgx, '$1' + ',' + '$2' );
	}
	return x1 + x2;
}

function setupEmailLinkForm() {
	$( '#emaillink' ).submit(function() {
		var params = { fromajax: true };
		$( '#emaillink input' ).each( function () {
			params[$( this ).attr( 'name' )] = $( this ).val();
		});
		$.post( '/Special:EmailLink', params, function ( data ) {
			$( '#dialog-box' ).html( data );
			setupEmailLinkForm();
		} );
		return false;
	} );
}

function emailLink() {
	var url = '/extensions/wikihow/common/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js';
	$.getScript( url, function() {
		var url2 = '/Special:EmailLink?target=' + mw.config.get( 'wgPageName' ) + '&fromajax=true';
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
	} );
	return false;
}

// Fancy Tool Tip thingy
function getToolTip( obj, on ) {
	if ( on ) {
		if ( jQuery.browser.msie && jQuery.browser.version == '6.0' ) {
			return;
		}

		var txt = jQuery( obj ).parent().find( 'span' ).html();
		if ( txt ) {
			var pos = jQuery( obj ).offset();
			var posTop = pos.top - 55;
			jQuery( '<div class="tooltip_text"><div>' + txt + '</div></div>' ).appendTo( 'body' );
			var imgWidth = pos.left + ( jQuery( '.tooltip' ).width() / 2 );
			var posLeft = imgWidth - 23;
			jQuery( '.tooltip_text' ).css( 'top', posTop ).css( 'left', posLeft );
		}
	} else {
		if ( jQuery.browser.msie && jQuery.browser.version == '6.0' ) {
			return;
		}

		jQuery( '.tooltip_text' ).remove();
	}
}

// record a push on the +1 button
function plusone_vote( obj ) {
	_gaq.push(['_trackEvent', 'plusone', obj.state]);
}

jQuery( '.lbg' ).live( 'click', function () {
	var id = jQuery( this ).attr( 'id' ).split( '_' );
	jQuery( '#lbgi_' + id[1] ).click();
} );

jQuery( '#tb_steps' ).live( 'click', function () {
	$( '#tb_steps' ).addClass( 'on' );
	$( '#tb_tips' ).removeClass( 'on' );
	$( '#tb_warnings' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#steps' ).offset().top - 70}, 'slow');
} );

jQuery( '#tb_tips' ).live( 'click', function () {
	$( '#tb_tips' ).addClass( 'on' );
	$( '#tb_steps' ).removeClass( 'on' );
	$( '#tb_warnings' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#tips' ).offset().top - 70}, 'slow');
} );

jQuery( '#tb_warnings' ).live( 'click', function () {
	$( '#tb_warnings' ).addClass( 'on' );
	$( '#tb_steps' ).removeClass( 'on' );
	$( '#tb_tips' ).removeClass( 'on' );
	$( 'html, body' ).animate({ scrollTop: $( '#warnings' ).offset().top - 70}, 'slow');
} );

/*
 * Code taken from:
 * http://code.google.com/apis/analytics/docs/tracking/gaTrackingSocial.html
 */
function extractParamFromUri( uri, paramName ) {
	if ( !uri ) {
		return;
	}
	var uri = uri.split( '#' )[0]; // Remove anchor.
	var parts = uri.split( '?' ); // Check for query params.
	if ( parts.length == 1 ) {
		return;
	}
	var query = decodeURI( parts[1] );

	// Find URL param.
	paramName += '=';
	var params = query.split( '&' );
	for ( var i = 0, param; param = params[i]; ++i ) {
		if ( param.indexOf( paramName ) === 0 ) {
			return unescape( param.split( '=' )[1] );
		}
	}
}

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
		$( this ).click( function () {
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

WH.isPageScrolledToArticleBottom = function () {
	var elem = '.article_bottom';
	var docViewTop = $( window ).scrollTop();
	var docViewBottom = docViewTop + $( window ).height();

	var elemTop = $( elem ).offset().top;
	elemTop += 125;
	var elemBottom = elemTop + $( elem ).height();

	return ( ( elemBottom >= docViewTop ) && ( elemTop <= docViewBottom ) );
};

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

WH.isPageScrolledToFollowTable = function () {
	var the_elem = '#follow_table';

	var docViewTop = $( window ).scrollTop();
	var docViewBottom = docViewTop + $( window ).height();

	var offset = $( the_elem ).offset();
	return offset ? offset.top <= docViewBottom : false;
};


// Snippet to prevent site search forms submitting empty queries
( function ( $ ) {
	$( '#search_site_bubble,#search_site_footer' ).live( 'click', function ( e ) {
		if ( $( this ).siblings( 'input[name="search"]' ).val().length == 0 ) {
			e.preventDefault();
			return false;
		}
	} );
} )( jQuery );

$( document ).ready( function () {

	// Slider -- not for browsers that don't render +1 buttons
	var oldMSIE = $.browser.msie && $.browser.version <= 7;
	if (
		$('#slideshowdetect').length &&
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
		//var url = '/Special:GallerySlide?show-slideshow=1&aid='+wgArticleId;
		//var url = '/Special:GallerySlide?show-slideshow=1&big-show=1&aid='+wgArticleId;
		var url = '/Special:GallerySlide?show-slideshow=1&big-show=1&article_layout=2&aid=' + mw.config.get( 'wgArticleId' );

		$.getJSON( url, function ( json ) {
			if ( json && json.content ) {
				document.getElementById( 'showslideshow' ).innerHTML = json.content;
				fireUpSlideShow( json.num_images);
				$( '#showslideshow' ).slideDown();
			}
		} );
	}

	// add checkbox click handlers
	$( '.step_checkbox' ).click( function () {
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
	$( '.css-checkbox' ).click( function() {
		$( this ).parent().find( '.checkbox-text' ).toggleClass( 'fading' );
	} );

	initTopMenu();
	initAdminMenu();

	// site notice close
	$( '#site_notice_x' ).click( function () {
		// 30 day cookie
		var exdate = new Date();
		var expiredays = 30;
		exdate.setDate( exdate.getDate() + expiredays );
		document.cookie = 'sitenoticebox=1;expires=' + exdate.toGMTString();

		// good-bye
		$( '#site_notice' ).hide();
	} );
} );

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
	jQuery.download( '/Special:WikitextDownloader', data );
} );

jQuery( '#authors' ).click( function ( e ) {
	$( '#originator_names' ).toggle();
	return false;
} );
jQuery( '#originator_names_close' ).click( function ( e ) {
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
				// @todo FIXME: i18n
				'<div class="transd-no" style="float:right;padding-right:10px;font-size:12px;"><a href="#">No thanks</a></div></div></span>'
			);
			$( '.transd-no' ).click( function () {
				$( '.message_box' ).hide();
				setCookie( 'trans', 1, 30 );

				return false;
			} );
		}
	}
};

$( document ).ready( WH.displayTranslationCTA );

/**
 *
 */
WH.maybeDisplayTopSocialCTA = function () {
	var referrer = document.referrer ? document.referrer : '';
	var testNetwork = extractParamFromUri( location.href, 'soc' );
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
		}
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
		var html = wfMsg( 'social-html-' + social );
		html = checkMsg( html ) ? html : '';

		// if not, check for the general HTML
		if ( !html ) {
			html = wfMsg( 'social-html' );
			html = checkMsg( html ) ? html : '';
		}

		// for efficiency, you can embed the social network stuff into the HTML
		// using social-html-facebook (for example) MW messages
		if ( html ) {
			insertHTMLcallback( html );
		} else {
			$.get( '/Special:WikihowShare?soc=' + social, insertHTMLcallback );
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
	$( '.th_close' ).click( function() {
		var revId = $( this ).attr( 'id' );
		var giverIds = $( '#th_msg_' + revId ).find( '.th_giver_ids' ).html();
		var url = '/Special:ThumbsNotifications?rev=' + revId + '&givers=' + giverIds;

		$.get( url, function ( data ) {} );
		$( '#th_msg_' + revId ).hide();

		// lower the notification count in the header by 1
		$( '#notification_count' ).html( $( '#notification_count' ).html() - 1 );
	} );

	// logged-in search click
	$( '#bubble_search .search_box, #cse_q' ).click( function () {
		$( this ).addClass( 'search_white' );
	} );

	//----- login stuff
	$( '.nav' ).click( function() {
		if ( $( this ).attr( 'href' ) == '#' ) {
			return false;
		}
	} );

	$( '.userlogin #wpName1, #wpName1_head' ).val( wfMsg( 'usernameoremail' ) )
		.css( 'color', '#ABABAB' )
		.click( function () {
			if ( $( this ).val() == wfMsg( 'usernameoremail' ) ) {
				$( this ).val( '' ); // clear field
				$( this ).css( 'color', '#333'); // change font color
			}
		} );

	// switch to text so we can display "Password"
	if ( !( $.browser.msie && $.browser.version <= 8.0 ) ) {
		if ( $( '.userlogin #wpPassword1' ).get( 0 ) ) {
			$('.userlogin #wpPassword1' ).get( 0 ).type = 'text';
		}
		if ( $( '#wpPassword1_head' ).get( 0 ) ) {
			$( '#wpPassword1_head' ).get( 0 ).type = 'text';
		}
	}

	$( '.userlogin #wpPassword1, #wpPassword1_head' ).val( wfMsg( 'password' ) )
		.css( 'color', '#ABABAB' )
		.focus( function() {
			if ( $( this ).val() == wfMsg( 'password' ) ) {
				$( this ).val( '' );
				$( this ).css( 'color', '#333' ); // change font color
				$( this ).get( 0 ).type = 'password'; // switch to dots
			}
		} );

	$( '#forgot_pwd' ).click( function () {
		if ( $( '#wpName1' ).val() == 'Username or Email' ) {
			$( '#wpName1' ).val( '' );
		}
		getPassword( escape( $( '#wpName1' ).val() ) );
		return false;
	} );

	$( '#forgot_pwd_head' ).click( function () {
		if ( $( '#wpName1_head' ).val() == 'Username or Email' ) {
			$( '#wpName1_head' ).val('');
		}
		getPassword( escape( $( '#wpName1_head' ).val() ) );
		return false;
	} );
}

$( '#method_toc_unhide' ).click( function () {
	$( '#method_toc a.excess' ).show();
	$( '#method_toc_hide' ).show();
	$( this ).hide();
	return false;
} );

$( '#method_toc_hide' ).click( function () {
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

// shrink/embiggen header
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
		// not so fast, IE7...
		if ( $.browser.msie && parseFloat( $.browser.version ) < 8 ) {
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
				.animate({ height: '72px' },150, function () {
					$( this ).removeClass( 'shrunk' );
					$( this ).css( 'overflow', 'visible' ); // jQuery forces overflow: hidden; this counters it
					$( '#main' ).removeClass( 'shrunk_header' );
					headerToggling = false;
				} );
			bShrunk = false;
		}
	}
} )( jQuery );

// - Make those section headings sticky
$( window ).scroll( function () {
	headerHeight = $( '#header' ).height();
	previousHeader = null;
	currentHeader = null;

	if ( $.browser.msie && parseFloat( $.browser.version ) < 8 ) {
		// IE7 doesn't like stickiness
	} else {
		$( '.section.sticky' ).each( function () {
			currentHeader = $( this ).find( 'h2' ); // need to get either h2 or h3
			if ( !$( currentHeader ).is( ':visible' ) ) {
				// likely means we're in a steps section with h3 headers
				currentHeader = $( this ).find( 'h3' );
			}
			if ( currentHeader.length == 0 ) {
				// if there's nothing to use, just skip this section.
				// Shouldn't really even end up in this case
				return;
			}
			makeSticky( $( this ), currentHeader );
		} );

		$( '.tool.sticky' ).each( function () {
			currentHeader = $( '.tool_header' );
			if ( currentHeader.length == 0 ) {
				// if there's nothing to use, just skip this section.
				// Shouldn't really even end up in this case
				return;
			}
			// Disable stickiness for the RC patrol guided tour
			if ( extractParamFromUri( document.location.search, 'gt_mode' ) != 1 ) {
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

// New checkmarks aren't compatible with IE 8 and earlier so rever to old style
$( document ).ready( function () {
	if ( $.browser.msie && $.browser.version <= 8 ) {
		$( '.css-checkbox' ).removeClass( 'css-checkbox' );
		$( '.css-checkbox-label' ).removeClass( 'css-checkbox-label' );
	}
} );

( function ( $ ) {
	$( document ).on('click', 'a[href^=#_note-], a[href^=#_ref-]', function ( e ) {
		e.preventDefault();
		$( 'html, body' ).animate( {
			scrollTop: $( $( this ).attr( 'href' ) ).offset().top - 100
		}, 0 );
	} );
} )( jQuery );

function initToolTitle() {
	$( '.firstHeading' ).before( '<h5>' + $( '.firstHeading' ).html() + '</h5>' );
}

function addOptions() {
	$( '.firstHeading' ).before( '<span class="tool_options_link">(<a href="#">Change Options</a>)</span>' ); // @todo FIXME: i18n
	$( '.firstHeading' ).after( '<div class="tool_options"></div>' );

	$( '.tool_options_link' ).click( function () {
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
	if ( $.browser.msie && parseFloat( $.browser.version ) < 9 ) {
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
		$( '#notification_count').hide();

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
				if ( unread.length == 0 ) {
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
	if ( $.browser.msie && parseFloat( $.browser.version ) < 9 ) {
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

// post-load taboola
 window._taboola = window._taboola || [];
_taboola.push({article: 'auto'});
_taboolaScriptElem = document.createElement( 'script' );
_taboolaBeforeElem = document.getElementsByTagName( 'script' )[0];
$( window ).load( function () {
	!function ( e, f, u ) {
		e.async = 1;
		e.src = u;
		f.parentNode.insertBefore( e, f );
	}( _taboolaScriptElem, _taboolaBeforeElem, 'http://cdn.taboola.com/libtrc/wikihow/loader.js' );
} );