var WH = WH || {};

var ua = navigator.userAgent.toLowerCase(); // get client browser info

//temp hack for +1 button not working on iphone/ipad
var isiPad = ua.indexOf('ipad');
var isiPhone = ua.indexOf('iphone');

// Global external objects used by this script.
/*extern ta, stylepath, skin */

// set defaults for these so that JS errors don't occur
if (typeof stylepath == 'undefined') stylepath = ''; // '/skins';
if (typeof wgContentLanguage == 'undefined') wgContentLanguage = ''; // 'en';

// add any onload functions in this hook (please don't hard-code any events in the xhtml source)
var doneOnloadHook;

var bShrunk = false;

/**
 * IN WIKIBITS.JS
if (!window.onloadFuncts) {
	var onloadFuncts = [];
}
*/

/**
 * IN WIKIBITS.JS
function addOnloadHook(hookFunct) {
	// Allows add-on scripts to add onload functions
	onloadFuncts.push(hookFunct);
}
*/

function hookEvent(hookName, hookFunct) {
	if (window.addEventListener) {
		window.addEventListener(hookName, hookFunct, false);
	} else if (window.attachEvent) {
		window.attachEvent("on" + hookName, hookFunct);
	}
}

if (typeof wgBreakFrames !== 'undefined' && wgBreakFrames) {
	// Un-trap us from framesets
	if (window.top != window) {
		window.top.location = window.location;
	}
}

// used for upload page
function toggle_element_activation(ida,idb) {
	if (!document.getElementById) {
		return;
	}
	document.getElementById(ida).disabled=true;
	document.getElementById(idb).disabled=false;
}

function toggle_element_check(ida,idb) {
	if (!document.getElementById) {
		return;
	}
	document.getElementById(ida).checked=true;
	document.getElementById(idb).checked=false;
}
/**
 * IN WIKIBITS.JS
function redirectToFragment(fragment) {
	var match = navigator.userAgent.match(/AppleWebKit\/(\d+)/);
	if (match) {
		var webKitVersion = parseInt(match[1]);
		if (webKitVersion < 420) {
			// Released Safari w/ WebKit 418.9.1 messes up horribly
			// Nightlies of 420+ are ok
			return;
		}
	}
	if (window.location.hash == "") {
		window.location.hash = fragment;
	}
}
*/

WH.onLoadChineseSpecific = function () {
	// Handler for variant change to Chinese site language
	$('#header #wpUserVariant').change( function () {
		var variant = $('#wpUserVariant').val();
		setCookie('wiki_sharedvariant', variant, 0);

		var hs = location.href.split("?");
		var loc = "";
		if(hs.length > 1) {
			var params =  hs[1].replace(/^variant=[^&#]+/,"").replace(/&variant=[^&#]+/,"");
			if(params) {
				loc = hs[0] + "?" + params;
			}
			else {
				loc = hs[0];
			}
		}
		else {
			loc = hs[0];
		}

		location.href = loc;
	});
};
if (wgContentLanguage == 'zh') {
	$(document).ready(WH.onLoadChineseSpecific);
}

/**
 * IN WIKIBITS.JS
 * Inject a cute little progress spinner after the specified element
 *
 * @param element Element to inject after
 * @param id Identifier string (for use with removeSpinner(), below)
function injectSpinner( element, id ) {
	var spinner = document.createElement( "img" );
	spinner.id = "mw-spinner-" + id;
	spinner.src = wgCDNbase + "/skins/common/images/spinner.gif";
	spinner.alt = spinner.title = "...";
	if( element.nextSibling ) {
		element.parentNode.insertBefore( spinner, element.nextSibling );
	} else {
		element.parentNode.appendChild( spinner );
	}
}
 */

/**
 * IN WIKIBITS.JS
 * Remove a progress spinner added with injectSpinner()
 *
 * @param id Identifier string
function removeSpinner( id ) {
	var spinner = document.getElementById( "mw-spinner-" + id );
	if (spinner) {
		spinner.parentNode.removeChild( spinner );
	}
}
 */

/**
 * IN WIKIBITS.JS
function runOnloadHook() {
	// don't run anything below this for non-dom browsers
	if (doneOnloadHook || !(document.getElementById && document.getElementsByTagName)) {
		return;
	}

	// set this before running any hooks, since any errors below
	// might cause the function to terminate prematurely
	doneOnloadHook = true;
	//updateTooltipAccessKeys( null );

	// Run any added-on functions
	for (var i = 0; i < onloadFuncts.length; i++) {
		onloadFuncts[i]();
	}
}
*/

/**
 * IN WIKIBITS.JS
 * Add an event handler to an element
 *
 * @param Element element Element to add handler to
 * @param String attach Event to attach to
 * @param callable handler Event handler callback
function addHandler( element, attach, handler ) {
	if (window.addEventListener) {
		element.addEventListener( attach, handler, false );
	} else if (window.attachEvent) {
		element.attachEvent( 'on' + attach, handler );
	}
}
 */

/**
 * IN WIKIBITS.JS
 * Add a click event handler to an element
 *
 * @param Element element Element to add handler to
 * @param callable handler Event handler callback
function addClickHandler( element, handler ) {
	addHandler( element, 'click', handler );
}
//note: all skins should call runOnloadHook() at the end of html output,
//	  so the below should be redundant. It's there just in case.
hookEvent("load", runOnloadHook);
 */

var share_requester;
function handle_shareResponse() {
}

function clickshare(selection) {
	share_requester = null;
	try {
		share_requester = new XMLHttpRequest();
	} catch (error) {
		try {
			share_requester = new ActiveXObject('Microsoft.XMLHTTP');
		} catch (error) {
			 return false;
		}
	}
	share_requester.onreadystatechange =  handle_shareResponse;
	url = window.location.protocol + '//' + window.location.hostname + '/Special:CheckJS?selection=' + selection;
	share_requester.open('GET', url);
	share_requester.send(' ');
}

function shareTwitter(source) {
	//var title = encodeURIComponent(wgTitle);
	var title = wgTitle;
	var url = encodeURIComponent(location.href);

	if (title.search(/How to/) != 0) {
		title = 'How to '+title;
	}

	if (source == 'aen') {
		status = "I just wrote an article on @wikiHow - "+title+".";
	} else if (source == 'africa') {
		status = "wikiHow.com is sending a book to Africa when you write a new how-to article. Help out here: http://bit.ly/9qWKe";
		title = "";
		url = "";
	} else {
		status = "Reading @wikiHow on "+title+".";
	}

	window.open('https://twitter.com/intent/tweet?text='+ status +' '+url );

	return false;
}

function button_click(obj) {
	if ((navigator.appName == "Microsoft Internet Explorer") && (navigator.appVersion < 7)) {
		return false;
	}
	jobj = jQuery(obj);

	background = jobj.css('background-position');
	if(background == undefined || background == null)
		background_x_position = jobj.css('background-position-x');
	else
		background_x_position = background.split(" ")[0];

	//article tabs
	if (obj.id.indexOf("tab_") >= 0) {
		obj.style.color = "#514239";
		obj.style.backgroundPosition = background_x_position + " -111px";
	}

	if (obj.id == "play_pause_button") {
		if (jobj.hasClass("play")) {
			obj.style.backgroundPosition = "0 -130px";
		}
		else {
			obj.style.backgroundPosition = "0 -52px";
		}
	}


	if (jobj.hasClass("search_button")) {
		obj.style.backgroundPosition = "0 -29px";
	}
}

//do a scrolling reveal
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

// Pass in expiresDays=0 for a session cookie
function setCookie(name, value, expireDays) {
	var expireDays = typeof expires == 'undefined' ? 7 : expireDays;
	var daysMs = expireDays * 24 * 60 * 60 * 1000
	var expireDate = new Date();
	expireDate.setDate(expireDate.getDate() + daysMs);
	document.cookie = name + "=" + escape(value) + (!expireDays ? "" : ";expires=" + expireDate.toGMTString()) + ";path=/";
}

function getCookie(c_name) {
	var x, y,
		cookiesArr = document.cookie.split(";");
	for (var i = 0; i < cookiesArr.length; i++) {
		x = cookiesArr[i].substr(0, cookiesArr[i].indexOf("="));
		y = cookiesArr[i].substr(cookiesArr[i].indexOf("=") + 1);
		x = x.replace(/^\s+|\s+$/g,"");
		if (x == c_name) {
			return unescape(y);
		}
	}
}

// Do a scrolling reveal
function scroll_open(id,height,max_height) {
	document.getElementById(id).style.top = height + "px";
	document.getElementById(id).style.display = "block";
	document.getElementById(id).style.position = "relative";
	height += 1;
	if (height < max_height) {
		window.setTimeout("scroll_open('" + id + "'," + height + "," + max_height + ")",15);
	}
}

function share_article(who) {

	switch (who) {

		case 'email':
			clickshare(1);
			window.location='http://' + window.location.hostname + '/Special:EmailLink/' + window.location.pathname;
			break;
		case 'facebook':
			clickshare(4);
			var d=document,f='http://www.facebook.com/share',
				l=d.location,e=encodeURIComponent,p='.php?src=bm&v=4&i=1178291210&u='+e(l.href)+'&t='+e(d.title);1; try{ if(!/^(.*\.)?facebook\.[^.]*$/.test(l.host))throw(0);share_internal_bookmarklet(p)}catch(z){a=function(){if(!window.open(f+'r'+p,'sharer','toolbar=0,status=0,resizable=0,width=626,height=436'))l.href=f+p};if(/Firefox/.test(navigator.userAgent))setTimeout(a,0);else{a()}}void(0);
			break;
		case 'twitter':
			clickshare(8);
			shareTwitter();
			break;
		case 'delicious':
			clickshare(2);
			window.open('http://del.icio.us/post?v=4&partner=whw&noui&jump=close&url='+encodeURIComponent(location.href)+'&title='+encodeURIComponent(document.title),'delicious','toolbar=no,width=700,height=400');
			void(0);
			break;
		case 'stumbleupon':
			clickshare(9);
			window.open('http://www.stumbleupon.com/submit?url='+encodeURIComponent(location.href)); void(0);
			break;
		case 'digg':
			javascript:clickshare(3);
			window.open(' http://digg.com/submit?phase=2&url=' + encodeURIComponent(location.href) + '&title=' + encodeURIComponent(document.title) + '&bodytext=&topic=');
			break;
		case 'blogger':
			javascript:clickshare(7);
			window.open('http://www.blogger.com/blog-this.g?&u=' +encodeURIComponent(location.href)+ '&n=' +encodeURIComponent(document.title), 'blogger', 'toolbar=no,width=700,height=400');
			void(0);
			break;
		case 'google':
			javascript:clickshare(5);
			(function(){var a=window,b=document,c=encodeURIComponent,d=a.open("https://plus.google.com/share?url="+c(b.location),"bkmk_popup","left="+((a.screenX||a.screenLeft)+10)+",top="+((a.screenY||a.screenTop)+10)+",height=420px,width=550px,resizable=1,alwaysRaised=1");a.setTimeout(function(){d.focus()},300)})();
			break;
	}
}

// remote scripting library
// (c) copyright 2005 modernmethod, inc
var sajax_debug_mode = false;
var sajax_request_type = "GET";

/**
 * if sajax_debug_mode is true, this function outputs given the message into
 * the element with id = sajax_debug; if no such element exists in the document,
 * it is injected.
 */
function sajax_debug(text) {
	if (!sajax_debug_mode) return false;

	var e= document.getElementById('sajax_debug');

	if (!e) {
		e= document.createElement("p");
		e.className= 'sajax_debug';
		e.id= 'sajax_debug';

		var b= document.getElementsByTagName("body")[0];

		if (b.firstChild) b.insertBefore(e, b.firstChild);
		else b.appendChild(e);
	}

	var m= document.createElement("div");
	m.appendChild( document.createTextNode( text ) );

	e.appendChild( m );

	return true;
}

/**
* compatibility wrapper for creating a new XMLHttpRequest object.
*/
function sajax_init_object() {
	sajax_debug("sajax_init_object() called..")
	var A;
	try {
		// Try the new style before ActiveX so we don't
		// unnecessarily trigger warnings in IE 7 when
		// set to prompt about ActiveX usage
		A = new XMLHttpRequest();
	} catch (e) {
		try {
			A=new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try {
				A=new ActiveXObject("Microsoft.XMLHTTP");
			} catch (oc) {
				A=null;
			}
		}
	}
	if (!A)
		sajax_debug("Could not create connection object.");

	return A;
}

/**
* Perform an ajax call to mediawiki. Calls are handeled by AjaxDispatcher.php
*   func_name - the name of the function to call. Must be registered in $wgAjaxExportList
*   args - an array of arguments to that function
*   target - the target that will handle the result of the call. If this is a function,
*			if will be called with the XMLHttpRequest as a parameter; if it's an input
*			element, its value will be set to the resultText; if it's another type of
*			element, its innerHTML will be set to the resultText.
*
* Example:
*	sajax_do_call('doFoo', [1, 2, 3], document.getElementById("showFoo"));
*
* This will call the doFoo function via MediaWiki's AjaxDispatcher, with
* (1, 2, 3) as the parameter list, and will show the result in the element
* with id = showFoo
*/
function sajax_do_call(func_name, args, target) {
	var i, x, n;
	var uri;
	var post_data;
	uri = wgServer +
		((wgScript == null) ? (wgScriptPath + "/index.php") : wgScript) +
		"?action=ajax";
	if (sajax_request_type == "GET") {
		if (uri.indexOf("?") == -1)
			uri = uri + "?rs=" + encodeURIComponent(func_name);
		else
			uri = uri + "&rs=" + encodeURIComponent(func_name);
		for (i = 0; i < args.length; i++)
			uri = uri + "&rsargs[]=" + encodeURIComponent(args[i]);
		//uri = uri + "&rsrnd=" + new Date().getTime();
		post_data = null;
	} else {
		post_data = "rs=" + encodeURIComponent(func_name);
		for (i = 0; i < args.length; i++)
			post_data = post_data + "&rsargs[]=" + encodeURIComponent(args[i]);
	}
	x = sajax_init_object();
	if (!x) {
		alert("AJAX not supported");
		return false;
	}

	try {
		x.open(sajax_request_type, uri, true);
	} catch (e) {
		if (window.location.hostname == "localhost") {
			alert("Your browser blocks XMLHttpRequest to 'localhost', try using a real hostname for development/testing.");
		}
		throw e;
	}
	if (sajax_request_type == "POST") {
		x.setRequestHeader("Method", "POST " + uri + " HTTP/1.1");
		x.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	}
	x.setRequestHeader("Pragma", "cache=yes");
	x.setRequestHeader("Cache-Control", "no-transform");
	x.onreadystatechange = function() {
		if (x.readyState != 4)
			return;

		sajax_debug("received (" + x.status + " " + x.statusText + ") " + x.responseText);

		//if (x.status != 200)
		//	alert("Error: " + x.status + " " + x.statusText + ": " + x.responseText);
		//else

		if ( typeof( target ) == 'function' ) {
			target( x );
		}
		else if ( typeof( target ) == 'object' ) {
			if ( target.tagName == 'INPUT' ) {
				if (x.status == 200) target.value= x.responseText;
				//else alert("Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")");
			}
			else {
				if (x.status == 200) target.innerHTML = x.responseText;
				else target.innerHTML= "<div class='error'>Error: " + x.status + " " + x.statusText + " (" + x.responseText + ")</div>";
			}
		}
		else {
			alert("bad target for sajax_do_call: not a function or object: " + target);
		}

		return;
	}

	sajax_debug(func_name + " uri = " + uri + " / post = " + post_data);
	x.send(post_data);
	sajax_debug(func_name + " waiting..");
	delete x;

	return true;
}

var mainPageFAToggleFlag = false;
function mainPageFAToggle() {
	var firstChild = jQuery('#toggle');
	if (mainPageFAToggleFlag == false) {
		jQuery('#hiddenFA').slideDown('slow').show(function(){
			firstChild.html(wfMsg('mainpage_fewer_featured_articles'));
			jQuery('#moreOrLess').attr('src', wgCDNbase + '/skins/WikiHow/images/arrowLess.png');
			jQuery("#featuredNav").hide(); //need to do this for IE7
			jQuery("#featuredNav").show();
		});

		mainPageFAToggleFlag = true;
	} else {
		jQuery('#hiddenFA').slideUp('slow').hide(function(){
			firstChild.html(wfMsg('mainpage_more_featured_articles'));
			jQuery('#moreOrLess').attr('src', wgCDNbase + '/skins/WikiHow/images/arrowMore.png');
			jQuery("#featuredNav").hide(); //need to do this for IE7
			jQuery("#featuredNav").show();
		});
		mainPageFAToggleFlag = false;
	}
}

function setStyle(obj, style) {
	if (obj) {
		if (navigator.userAgent.indexOf('MSIE') > 0) {
			obj.style.setAttribute('csstext', style, 0);
		} else {
			obj.setAttribute('style', style);
		}
	}
}

/**
 * Translates a MW message (ie, 'new-link') into the correct language text.  Eg:
 * wfMsg('new-link', 'http://mylink.com/');
 *
 * - loads all messages from WH.lang
 * - added by Reuben
 */
function wfMsg(key) {
	if (typeof WH.lang[key] === 'undefined') {
		return '[' + key + ']';
	} else {
		var msg = WH.lang[key];
		if (arguments.length > 1) {
			// matches symbols like $1, $2, etc
			var syntax = /(^|.|\r|\n)(\$([1-9]))/g;
			var replArgs = arguments;
			msg = msg.replace(syntax, function(match, p1, p2, p3) {
				return p1 + replArgs[p3];
			});
			// This was the old prototype.js Template syntax
			//var template = new Template(msg, syntax);
			//var args = $A(arguments); // this has { 1: '$1', ... }
			//msg = template.evaluate(args);
		}
		return msg;
	}
}

/**
 * Templates html etc.  Use as follows:
 *
 * var html = wfTemplate('<a href="$1">$2</a>', mylink, mytext);
 */
function wfTemplate(tmpl) {
	var syntax = /(^|.|\r|\n)(\$([1-9]))/g; // matches symbols like $1, $2, etc
	var replArgs = arguments;
	var out = tmpl.replace(syntax, function(match, p1, p2, p3) {
		return p1 + replArgs[p3];
	});
	return out;
}

/**
 * A simple pad function.  Note that it won't match up with the output of
 * the php.
 */
function wfGetPad(url) {
	if (url.search(/^http:\/\//) >= 0) {
		return url;
	} else {
		return wgCDNbase + url;
	}
}

function getRequestObject() {
	var request;
	try {
		request = new XMLHttpRequest();
	} catch (error) {
		try {
			request = new ActiveXObject('Microsoft.XMLHTTP');
		} catch (error) {
			return false;
		}
	}
	return request;
}


var sh_links = Array("showads");

function sethideadscookie(val) {
	var date = new Date();
	if (val == 1)
		date.setTime(date.getTime()+(1*24*60*60*1000));
	else
		date.setTime(date.getTime()-(30*24*60*60*1000));
	var expires = "; expires="+date.toGMTString();
	document.cookie = "wiki_hideads="+val+expires+"; path=/";
}

function showorhideads(hide) {
	var style = 'display: inline;';
	if (hide) {
		style = 'display: none;';
	}
	$(".wh_ad_inner").hide();
	for (var i = 0; i < sh_links.length; i++) {
		var e = document.getElementById(sh_links[i]);
		if (!e) continue;
		if (hide) {
			style = 'display: inline;';
		} else {
			style = 'display: none;';
		}
		setStyle(e, style);
	}
	$(".show_ads").show();
}

function hideads() {
	sethideadscookie(1);
	showorhideads(true);
	clickshare(20);
}

function showads() {
	sethideadscookie(0);
	showorhideads(false);
	window.location.reload();
}

var cp_request;
var cp_request2;

function cp_finish() {
	gatTrack("Author_engagement","Click_done","Publishing_popup");

	if (document.getElementById('email_friend_cb') && document.getElementById('email_friend_cb').checked == true) {
		gatTrack("Author_engagement","Author_mail_friends","Publishing_popup");

		try {
			cp_request = new XMLHttpRequest();
		} catch (error) {
			try {
				cp_request = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (error) {
				return false;
			}
		}
		var params =  "friends=" + encodeURIComponent(document.getElementById('email_friends').value) + "&target=" + window.location.pathname.substring(1);
		var url = "http://" + window.location.hostname + "/Special:CreatepageEmailFriend";
		cp_request.open('POST', url);
		cp_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		cp_request.send(params);
	}

	if (document.getElementById('email_notification') && document.getElementById('email_notification').checked == true) {

		gatTrack("Author_engagement","Email_updates","Publishing_popup");
		try {
			cp_request2 = new XMLHttpRequest();
		} catch (error) {
			try {
				cp_request2 = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (error) {
				return false;
			}
		}

		var params = "";
		if (document.getElementById('email_address_flag').value == '1') {
			params =  "action=addNotification&target=" + window.location.pathname.substring(1);
		} else {
			params =  "action=addNotification&email=" + encodeURIComponent(document.getElementById('email_me').value) + "&target=" + window.location.pathname.substring(1);
		}


		var url = "http://" + window.location.hostname + "/Special:AuthorEmailNotification";
		cp_request2.open('POST', url);
		cp_request2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		cp_request2.send(params);
	}

	if (document.getElementById('dont_show_again') && document.getElementById('dont_show_again').checked == true) {

		gatTrack("Author_engagement","Reject_pub_pop","Reject_pub_pop");
		try {
			cp_request2 = new XMLHttpRequest();
		} catch (error) {
			try {
				cp_request2 = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (error) {
				return false;
			}
		}

		var params =  "action=updatePreferences&dontshow=1";

		var url = "http://" + window.location.hostname + "/Special:AuthorEmailNotification";
		cp_request2.open('POST', url);
		cp_request2.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		cp_request2.send(params);
	}

	jQuery("#dialog-box").dialog("close");
	//closeModal();
}

var gHideAds = gHideAds || false;
var gchans = gchans || false;
var google_analytics_domain_name = ".wikihow.com"

var gRated = false;

function ratingReason(reason, itemId, type) {
    if (!reason) {
        return;
    }

    $.ajax(
        { url: '/Special:RatingReason?item_id=' + itemId+ '&reason=' + reason + '&type=' + type
        }
    ).done(function(data) {
        $('#' + type + '_rating').html(data);
    });
}

function rateItem(r, itemId, type) {
	if (!gRated) {
		$.ajax(
			{ url: '/Special:RateItem?page_id=' + itemId+ '&rating=' + r + '&type=' + type
			}
		).done(function(data) {
            if (type=="sample") {
                $('#' + type + '_rating').css('height', '100px');
            }
			$('#' + type + '_rating').html(data);
		});
	}
	gRated = true;
}

function updateWidget(id, x) {
	var url = '/Special:Standings/' + x;
	$.get(url, function (data) {
		$(id).fadeOut();
		$(id).html(data['html']);
		$(id).fadeIn();
	},
	'json'
	);
}
function updateTimer(id) {
	var e = jQuery("#" + id);
	var i = parseInt(e.html());
	if (i > 1) {
	   e.fadeOut(400, function() {
		   i--;
		   e.html(i);
		   e.fadeIn();
		});
	}
}

function parseIntWH(num) {
	if (!num) {
		return 0;
	}
	return parseInt(num.replace(/,/, ''), 10);
}

function addCommas(nStr) {
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function setupEmailLinkForm() {
	$("#emaillink").submit(function() {
		var params = { fromajax: true };
		$("#emaillink input").each(function() {
			params[$(this).attr('name')] = $(this).val();
		});
		$.post('/Special:EmailLink', params, function(data) {
			$("#dialog-box").html(data);
			setupEmailLinkForm();
		});
		return false;
	});
}

function emailLink() {
	var url = "/extensions/wikihow/common/jquery-ui-1.9.2.custom/js/jquery-ui-1.9.2.custom.min.js";
	$.getScript(url, function() {
		var url2 = '/Special:EmailLink?target=' + wgPageName + '&fromajax=true';
		$("#dialog-box").load(url2, function() {
			$("#dialog-box").dialog( {
				modal: true,
				title: "E-mail This Page to a Friend",
				width: 650,
				height: 400,
				closeText: 'Close'
			}) ;
			setupEmailLinkForm();
		});
	});
	return false;
}

// Fancy Tool Tip thingy
function getToolTip(obj, on) {
	if (on) {
		if (jQuery.browser.msie && jQuery.browser.version == "6.0")
			return;

		var txt = jQuery(obj).parent().find('span').html();
		if (txt) {
			var pos = jQuery(obj).offset();
			var posTop = pos.top - 55;
			jQuery('<div class="tooltip_text"><div>' + txt + '</div></div>').appendTo('body');
			var imgWidth = pos.left + (jQuery('.tooltip').width() / 2);
			var posLeft = imgWidth - 23;
			jQuery('.tooltip_text').css('top', posTop).css('left', posLeft);
		}
	} else {
		if (jQuery.browser.msie && jQuery.browser.version == "6.0")
			return;

		jQuery('.tooltip_text').remove();
	}
}

//record a push on the +1 button
function plusone_vote( obj ) {
    _gaq.push(['_trackEvent','plusone',obj.state]);
}

jQuery(".lbg").live('click', function() {
	var id = jQuery(this).attr("id").split("_");
	jQuery('#lbgi_' + id[1]).click();
});

jQuery('#tb_steps').live('click', function() {
	$('#tb_steps').addClass('on');
	$('#tb_tips').removeClass('on');
	$('#tb_warnings').removeClass('on');
	$('html, body').animate({ scrollTop: $('#steps').offset().top - 70}, 'slow');
});

jQuery('#tb_tips').live('click', function() {
	$('#tb_tips').addClass('on');
	$('#tb_steps').removeClass('on');
	$('#tb_warnings').removeClass('on');
	$('html, body').animate({ scrollTop: $('#tips').offset().top - 70}, 'slow');
});

jQuery('#tb_warnings').live('click', function() {
	$('#tb_warnings').addClass('on');
	$('#tb_steps').removeClass('on');
	$('#tb_tips').removeClass('on');
	$('html, body').animate({ scrollTop: $('#warnings').offset().top - 70}, 'slow');
});

/*
 * Code taken from:
 * http://code.google.com/apis/analytics/docs/tracking/gaTrackingSocial.html
 */
function extractParamFromUri(uri, paramName) {
	if (!uri) {
		return;
	}
	var uri = uri.split('#')[0];  // Remove anchor.
	var parts = uri.split('?');  // Check for query params.
	if (parts.length == 1) {
		return;
	}
	var query = decodeURI(parts[1]);

	// Find url param.
	paramName += '=';
	var params = query.split('&');
	for (var i = 0, param; param = params[i]; ++i) {
		if (param.indexOf(paramName) === 0) {
			return unescape(param.split('=')[1]);
		}
	}
}

WH.addScrollEffectToTOC = function() {
	//regular TOC
	if ($('#toc').length) WH.addScrollEffectToHashes( $('#toc * a') );
	//Alt Method TOC
	if ($('#method_toc').length) WH.addScrollEffectToHashes( $('#method_toc a') );
};

// add a scroll effect using jQuery.scrollTo
WH.addScrollEffectToHashes = function(anchors) {
	anchors.each(function() {
		// split off the part of the URL after the '#'
		var href = $(this).attr('href');
		var parts = href.split(/#/);
		if (parts.length <= 1 || !parts[1]) {
			return;
		}

		// find the corresponding anchor tag to scroll to
		var hash = parts[1];
		var anchor = $('a[name="' + hash + '"]');
		if (anchor.length != 1) {
			return;
		}

		// add a new click handler
		$(this).click(function () {
			$.scrollTo(anchor, 1000);
			if (history.pushState) {
			    history.pushState(null, null, '#' + hash);
			} else {
				location.hash = '#' + hash;
			}

			return false;
		});
	});
};

WH.isPageScrolledToArticleBottom = function () {
	var elem = '.article_bottom';
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    var elemTop = $(elem).offset().top;
	elemTop += 125;
    var elemBottom = elemTop + $(elem).height();

    return ((elemBottom >= docViewTop) && (elemTop <= docViewBottom));
};

WH.isPageScrolledToWarningsORArticleInfo = function () {
	var elem1 = '#warnings';
	var elem2 = '#article_info_header';
	var the_elem = '';

	if ($(elem1).length) {
		the_elem = elem1;
	} else {
		the_elem = elem2;
	}

    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

	var offset = $(the_elem).offset();
	return offset ? offset.top <= docViewBottom : false;
};

WH.isPageScrolledToFollowTable = function () {
	var the_elem = '#follow_table';

    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

	var offset = $(the_elem).offset();
	return offset ? offset.top <= docViewBottom : false;
};


// Snippet to prevent site search forms submitting empty queries
(function ($) {
	$('#search_site_bubble,#search_site_footer').live('click', function(e) {
		if($(this).siblings('input[name="search"]').val().length == 0) {
			e.preventDefault();
			return false;
		}
	});
})(jQuery);

$(document).ready(function() {

	// Slider -- not for browsers that don't render +1 buttons
	var oldMSIE = $.browser.msie && $.browser.version <= 7;
	if ($('#slideshowdetect').length
		&& slider
		&& !getCookie('sliderbox')
		&& isiPhone < 0 && isiPad < 0
		&& !oldMSIE)
	{

		if ($('#slideshowdetect_mainpage').length) {
			//homepage
			$(window).bind('scroll', function(){
				if  (!getCookie('sliderbox')) {
					if (WH.isPageScrolledToFollowTable() && $('#sliderbox').css('right') == '-500px' && !$('#sliderbox').is(':animated')) {
						slider.init();
					}
					if (!WH.isPageScrolledToFollowTable() && $('#sliderbox').css('right') == '0px' && !$('#sliderbox').is(':animated')) {
						slider.closeSlider();
					}
				}
			});
		}
		else {
			//article page
			$(window).bind('scroll', function(){
				if  (!getCookie('sliderbox')) {
					if (WH.isPageScrolledToWarningsORArticleInfo() && $('#sliderbox').css('right') == '-500px' && !$('#sliderbox').is(':animated')) {
						slider.init();
					}
					if (!WH.isPageScrolledToWarningsORArticleInfo() && $('#sliderbox').css('right') == '0px' && !$('#sliderbox').is(':animated')) {
						slider.closeSlider();
					}
				}
			});
		}
	}

	//fire off slideshow if we need to
	if ($('#showslideshow').length) {
		//var url = '/Special:GallerySlide?show-slideshow=1&aid='+wgArticleId;
		//var url = '/Special:GallerySlide?show-slideshow=1&big-show=1&aid='+wgArticleId;
		var url = '/Special:GallerySlide?show-slideshow=1&big-show=1&article_layout=2&aid='+wgArticleId;

		$.getJSON(url, function(json) {
			if (json && json.content) {
				document.getElementById('showslideshow').innerHTML = json.content;
				fireUpSlideShow(json.num_images);
				$('#showslideshow').slideDown();
			}
		});
	}

	//add checkbox click handlers
	$('.step_checkbox').click(function() {
		if ($(this).hasClass('step_checked')) {
			$(this).removeClass('step_checked');
		}
		else {
			$(this).addClass('step_checked');
			// track the clicks
			try{
				if (_gaq) {
					_gaq.push(['_trackEvent', 'checks', 'non-mobile', 'checked']);
				}
			} catch(err) {}
		}
		return false;
	});
	$('.css-checkbox').click(function() {
		$(this).parent().find('.checkbox-text').toggleClass('fading');
	});

	initTopMenu();
	initAdminMenu();

	//site notice close
	$('#site_notice_x').click(function() {

		//30 day cookie
		var exdate = new Date();
		var expiredays = 30;
		exdate.setDate(exdate.getDate()+expiredays);
		document.cookie = "sitenoticebox=1;expires="+exdate.toGMTString();

		//good-bye
		$('#site_notice').hide();
	});
});

WH.setGooglePlusOneLangCode = function() {
	var langCode = '';
	if (wgUserLanguage == 'pt') {
		langCode = 'pt-BR';
	}
	if (wgUserLanguage == 'es' || wgUserLanguage  == 'de') {
		langCode = wgUserLanguage;
	}
	if (langCode) {
		window.___gcfg = {lang: langCode};
	}
};

jQuery(document).on('click', 'a#wikitext_downloader', function(e) {
	e.preventDefault();
	var data = { 'pageid' : wgArticleId };
	jQuery.download('/Special:WikitextDownloader', data);
});

jQuery("#authors").click(function(e){
	$("#originator_names").toggle();
	return false;
});
jQuery("#originator_names_close").click(function(e){
	$("#originator_names").hide();
	return false;
});

WH.displayTranslationCTA = function() {
	var userLang = (navigator.language) ? navigator.language : navigator.userLanguage;
	userLang = typeof userLang == 'string' ? userLang.substring(0, 2).toLowerCase() : '';

	// Make it easier to test this feature
	if (window.location.href.indexOf('testtranscta=') !== false) {
		var matches = /testtranscta=([a-z]+)/.exec(window.location.href);
		if (matches) {
			userLang = matches[1];
		}
	}

	var transCookie = getCookie('trans');
	if (transCookie != '1'
		&& userLang
		&& typeof WH.translationData != 'undefined'
		&& typeof WH.translationData[userLang] != 'undefined')
	{
		var msg = WH.translationData[userLang]['msg'];
		if (msg) {
			$('#main').prepend("<span id='gatNewMessage'><div class='message_box'>" + msg + "<div class='transd-no' style='float:right;padding-right:10px;font-size:12px;'><a href='#'>No thanks</a></div></div></span>");
			$('.transd-no').click(function () {
				$('.message_box').hide();
				setCookie('trans', 1, 30);

				return false;
			});
		}
	}
};

$(document).ready(WH.displayTranslationCTA);

/**
 *
 */
WH.maybeDisplayTopSocialCTA = function() {
	var referrer = document.referrer ? document.referrer : '';
	var testNetwork = extractParamFromUri(location.href, 'soc');
	if (testNetwork) {
		referrer = testNetwork;
	}

	var social = '';
	if (/facebook\.com/.test(referrer)) {
		social = 'facebook';
	} else if (/pinterest\.com/.test(referrer)) {
		social = 'pinterest';
	} else if (/twitter\.com/.test(referrer)) {
		social = 'twitter';
	} else if (/plus\.google\.com/.test(referrer)) {
		social = 'gplus';
	}

	if (social) {

		var checkMsg = function(msg) { return msg && $.trim(msg).indexOf('[') !== 0; }
		var insertHTMLcallback = function(html) {
			if (! $.trim(html) ) return;
			var node = $(html);
			node.css({height: '0px'});
			$('body').prepend(node);
			$('#pin-head-cta').animate({height: '+230px'}, 700);
			$('#main, #footer_shell').css({position: 'relative'});
			//$('#main, #header, #footer_shell').animate({top: '+100px'});
		};

		// check for html of a particular social network first
		var html = wfMsg('social-html-' + social);
		html = checkMsg(html) ? html : '';

		// if not, check for the general html
		if (!html) {
			html = wfMsg('social-html');
			html = checkMsg(html) ? html : '';
		}

		// for efficiency, you can embed the social network stuff into the html
		// using social-html-facebook (for example) MW messages
		if (html) {
			insertHTMLcallback(html);
		} else {
			$.get('/Special:WikihowShare?soc=' + social, insertHTMLcallback);
		}
	}
};

$(document).ready(WH.maybeDisplayTopSocialCTA);

WH.supplementAnimations = function() {
	//add our slick easing
	$.extend($.easing,
	{
		easeInOutQuad: function (x, t, b, c, d) {
			if ((t/=d/2) < 1) return c/2*t*t + b;
			return -c/2 * ((--t)*(t-2) - 1) + b;
		},
		easeInOutQuint: function (x, t, b, c, d) {
			if ((t/=d/2) < 1) return c/2*t*t*t*t*t + b;
			return c/2*((t-=2)*t*t*t*t + 2) + b;
		},
		easeInOutQuart: function (x, t, b, c, d) {
			if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
			return -c/2 * ((t-=2)*t*t*t - 2) + b;
		},
		easeInOutExpo: function (x, t, b, c, d) {
			if (t==0) return b;
			if (t==d) return b+c;
			if ((t/=d/2) < 1) return c/2 * Math.pow(2, 10 * (t - 1)) + b;
			return c/2 * (-Math.pow(2, -10 * --t) + 2) + b;
		},
		easeInOutBack: function (x, t, b, c, d, s) {
			if (s == undefined) s = 1.70158;
			if ((t/=d/2) < 1) return c/2*(t*t*(((s*=(1.525))+1)*t - s)) + b;
			return c/2*((t-=2)*t*(((s*=(1.525))+1)*t + s) + 2) + b;
		}
	});
}

function initAdminMenu() {
	$('ul#tabs li').hover(function(){
		var that = $(this);
		menu_show(that);
	}, function() {
		var that = $(this);
		menu_hide(that);
	});
}

var on_menu = false;

function initTopMenu() {

	WH.supplementAnimations();

	var on_menu = false;

	//intelligent delay so we don't have flickering menus
	$('#header ul#actions li').hover(function() {
		var that = $(this);
		var wait = (on_menu) ? 150 : 0;
		clearTimeout($(this).data('timeout2'));
		that.data('timeout', setTimeout( function () {
			menu_show(that);
			on_menu = true;
		  }, wait));
	}, function() {
		var that = $(this);
		var wait = 150;
		clearTimeout(that.data('timeout'));

		//Firefox fix: lengthen delay to make sure they aren't on an autocomplete menu
		if ((/Firefox/.test(navigator.userAgent)) &&
			($('#wpName1_head').is(":focus") || $('#wpPassword1_head').is(":focus"))) {
				wait = 2000;
		}

		that.data('timeout2', setTimeout( function () {
			menu_hide(that);
			on_menu = false;
		  }, wait));
	});

	//thumbs up deletion
	$('.th_close').click(function() {
		var revId = $(this).attr('id');
		var giverIds = $('#th_msg_' + revId).find('.th_giver_ids').html();
		var url = '/Special:ThumbsNotifications?rev=' + revId + '&givers=' + giverIds;

		$.get(url, function(data) {});
		$('#th_msg_' + revId).hide();

		//lower the notification count in the header by 1
		$('#notification_count').html($('#notification_count').html()-1);
	});

	//logged-in search click
	$('#bubble_search .search_box, #cse_q').click(function() {
		$(this).addClass('search_white');
	});

	//----- login stuff
	$(".nav").click(function() {
		if ($(this).attr('href')=='#') return false;
	});

	$('.userlogin #wpName1, #wpName1_head').val(wfMsg('usernameoremail'))
	.css('color','#ABABAB')
	.click(function() {
		if ($(this).val() == wfMsg('usernameoremail')) {
			$(this).val(''); //clear field
			$(this).css('color','#333'); //change font color
		}
	});

	//switch to text so we can display "Password"
	if (!($.browser.msie && $.browser.version <= 8.0)) {
		if ($('.userlogin #wpPassword1').get(0)) $('.userlogin #wpPassword1').get(0).type = 'text';
		if ($('#wpPassword1_head').get(0)) $('#wpPassword1_head').get(0).type = 'text';
	}

	$('.userlogin #wpPassword1, #wpPassword1_head').val(wfMsg('password'))
	.css('color','#ABABAB')
	.focus(function() {
		if ($(this).val() == wfMsg('password')) {
			$(this).val('');
			$(this).css('color','#333'); //change font color
			$(this).get(0).type = 'password'; //switch to dots
		}
	});

	$("#forgot_pwd").click(function() {
		if ($("#wpName1").val() == 'Username or Email') $("#wpName1").val('');
		getPassword(escape($("#wpName1").val()));
		return false;
	});

	$("#forgot_pwd_head").click(function() {
		if ($("#wpName1_head").val() == 'Username or Email') $("#wpName1_head").val('');
		getPassword(escape($("#wpName1_head").val()));
		return false;
	});
	//-----------------
}

$("#method_toc_unhide").click(function(){
	$("#method_toc a.excess").show();
	$("#method_toc_hide").show();
	$(this).hide();
	return false;
});

$("#method_toc_hide").click(function(){
	$("#method_toc a.excess").hide();
	$("#method_toc_unhide").show();
	$(this).hide();
	return false;
});

// Adjust padding for sections based on variable h3 height
$(document).ready(function() {
	$(".section").each(function() {
		var h3Height = $('h3:first', this).height();
		$(this).css('padding-top', h3Height + 'px');
	});
});

//shrink/embiggen header
(function($) {
	var headerToggling = false;

	$(window).scroll(function() {
		if (!headerToggling) {
			if ($(window).scrollTop() <= 0) {
				headerToggling = true;
				toggleHeader(false);
			}
			else {
				if (!bShrunk) {
					headerToggling = true;
					toggleHeader(true);
				}
			}
		}
	});

	function toggleHeader(bShrink) {
		//not so fast, ie7...
		if ($.browser.msie && parseFloat($.browser.version) < 8) return;

		if (bShrink) {
			$('#header')
				.addClass('shrunk')
				.animate({ height: '39px' },200,function() {
					$(this).css('overflow','visible'); //jquery forces overflow: hidden; this counters it
					headerToggling = false;
				});
			$('#main').addClass('shrunk_header');
			bShrunk = true;
		}
		else {
			$('#header')
				.animate({ height: '72px' },150, function() {
					$(this).removeClass('shrunk');
					$(this).css('overflow','visible'); //jquery forces overflow: hidden; this counters it
					$('#main').removeClass('shrunk_header');
					headerToggling = false;
				});
			bShrunk = false;
		}
	}
})(jQuery);

// - Make those section headings sticky
$(window).scroll(function() {
	headerHeight = $("#header").height();
	previousHeader = null;
	currentHeader = null;

	if ($.browser.msie && parseFloat($.browser.version) < 8) {
		//ie7 doesn't like stickiness
	}
	else {

		$(".section.sticky").each(function(){
			currentHeader = $(this).find("h2"); //need to get either h2 or h3
			if(!$(currentHeader).is(":visible")) //likely means we're in a steps section with h3 headers
				currentHeader = $(this).find("h3");
			if(currentHeader.length == 0) //if there's nothing to use, just skip this section. Shouldn't really even end up in this case
				return;
			makeSticky($(this),currentHeader);
		});

		$(".tool.sticky").each(function(){
			currentHeader = $('.tool_header');
			if(currentHeader.length == 0) //if there's nothing to use, just skip this section. Shouldn't really even end up in this case
				return;
			// Disable stickiness for the rc patrol guided tour
			if (extractParamFromUri(document.location.search, 'gt_mode') != 1) {
				makeSticky($(this),currentHeader);
			}
		});

	}
});

function makeSticky(container,element) {
	scrollTop = $(document).scrollTop();
	sectionHeight = container.height();
	offsetTop = container.offset().top;

	if(scrollTop + headerHeight < offsetTop ) {
		$(element).removeClass("sticking");
	}
	else {
		if( scrollTop - offsetTop - sectionHeight + headerHeight > 0)
			$(element).removeClass("sticking");
		else
			$(element).addClass("sticking");
	}
}

// New checkmarks aren't compatible with IE 8 and earlier so rever to old style
$(document).ready(function() {
	if ($.browser.msie && $.browser.version <= 8) {
		$('.css-checkbox').removeClass('css-checkbox');
		$('.css-checkbox-label').removeClass('css-checkbox-label');
	}
});

(function($) {
	$(document).on('click', "a[href^=#_note-], a[href^=#_ref-]", function(e) {
		e.preventDefault();
		$('html, body').animate({
			scrollTop: $($(this).attr('href')).offset().top - 100
		}, 0);
	});
})(jQuery);

function initToolTitle() {
	$(".firstHeading").before("<h5>" + $(".firstHeading").html() + "</h5>")
}

function addOptions() {
	$(".firstHeading").before('<span class="tool_options_link">(<a href="#">Change Options</a>)</span>');
	$(".firstHeading").after('<div class="tool_options"></div>');

	$(".tool_options_link").click(function(){
		if ($('.tool_options').css('display') == 'none') {
			//show it!
			$('.tool_options').slideDown();
		}
		else {
			//hide it!
			$('.tool_options').slideUp();
		}
	});
}

function menu_show(choice) {
	if ($.browser.msie && parseFloat($.browser.version) < 9) {
		//ie7 and ie8 fix
		$(choice).find('.menu, .menu_login, .menu_messages')
		.stop(true, true)
		.show();
	}
	else {
		//for every other browser
		$(choice).find('.menu, .menu_login, .menu_messages')
		.stop(true, true)
		.slideDown(300,'easeInOutBack');
	}

	//reset the notification count
	if ($('.menu_message_morelink') && $(choice).attr('id') == 'nav_messages_li' && $('#notification_count').is(":visible")) {
		//first, let's just hide it
		$('#notification_count').hide();

		if (mw.echo) {
			//now grab the unread messages and mark them as read
			var api = new mw.Api( { ajax: { cache: false } } ),
				notifications, data, unread = [], apiData;

			apiData = {
				'action' : 'query',
				'meta' : 'notifications',
				'notformat' : 'flyout',
				'notprop' : 'index|list|count',
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

				//no unread ones? forget about it...
				if (unread.length == 0) return;

				api.post( mw.echo.desktop.appendUseLang( {
					'action' : 'echomarkread',
					'list' : unread.join( '|' ),
					'token': mw.user.tokens.get( 'editToken' )
				} ) ).done( function ( result ) {
					//SUCCESS!
				} ).fail( function () {
					//FAIL
				} );
			});
		}
	}
}

function menu_hide(choice) {
	if ($.browser.msie && parseFloat($.browser.version) < 9) {
		//ie7 and ie8 fix
		$(choice).find('.menu, .menu_login, .menu_messages')
			.stop(true, true)
			.hide();
	}
	else {
		$(choice).find('.menu, .menu_login, .menu_messages')
			.stop(true, true)
			.slideUp(200);
	}
}

//post-load taboola
 window._taboola = window._taboola || [];
_taboola.push({article:'auto'});
_taboolaScriptElem = document.createElement('script');
_taboolaBeforeElem = document.getElementsByTagName('script')[0];
$(window).load( function() {
        !function (e, f, u) {
           e.async = 1;
           e.src = u;
           f.parentNode.insertBefore(e, f);
		}(_taboolaScriptElem, _taboolaBeforeElem, 'http://cdn.taboola.com/libtrc/wikihow/loader.js');
});
