<?php
/**
 * BlueSky skin -- a skin based on wikiHow's third redesign, introduced in
 * autumn 2013.
 *
 * @file
 * @ingroup Skins
 * @version 2014-05-15
 * @author Various wikiHow developers
 * @author Jack Phoenix <jack@countervandalism.net>
 * @date 27 May 2014
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 *
 * To install place the BlueSky folder (the folder containing this file!) into
 * skins/ and add this line to your wiki's LocalSettings.php:
 * require_once("$IP/skins/BlueSky/BlueSky.php");
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is not a valid entry point to MediaWiki.' );
}

// Skin credits that will show up on Special:Version
$wgExtensionCredits['skin'][] = array(
	'path' => __FILE__,
	'name' => 'BlueSky',
	'version' => '1.0',
	'author' => array( 'wikiHow', 'Jack Phoenix' ),
	// @todo To be moved into the i18n file eventually once i18n is stable enough.
	// No need to cause translators unnecessary extra work before I finalize
	// the description. (Suggestions for a better desc? Let me know!)
	'description' => 'Skin based on the current version (late 2013-) of wikiHow\'s skin',
	'url' => 'https://www.mediawiki.org/wiki/Skin:BlueSky',
);

// The first instance must be strtolower()ed so that useskin=bluesky works and
// so that it does *not* force an initial capital (i.e. we do NOT want
// useskin=Bluesky) and the second instance is used to determine the name of
// *this* file.
$wgValidSkinNames['bluesky'] = 'BlueSky';

// Autoload the skin class, make it a valid skin, set up i18n, set up CSS & JS
// (via ResourceLoader)
$wgAutoloadClasses['SkinBlueSky'] = __DIR__ . '/BlueSky.skin.php';
$wgExtensionMessagesFiles['SkinBlueSky'] = __DIR__ . '/BlueSky.i18n.php';
$wgMessagesDirs['SkinBlueSky'] = __DIR__ . '/i18n';

// Main CSS ResourceLoader module
$wgResourceModules['skins.bluesky'] = array(
	'styles' => array(
		// MonoBook also loads these
		#'skins/common/commonElements.css' => array( 'media' => 'screen' ),
		#'skins/common/commonContent.css' => array( 'media' => 'screen' ),
		'skins/common/commonInterface.css' => array( 'media' => 'screen' ),
		// Styles custom to this skin
		'skins/BlueSky/resources/css/nonarticle.css' => array( 'media' => 'screen' ),
		'skins/BlueSky/resources/css/searchresults.css' => array( 'media' => 'screen' ),
		'skins/BlueSky/resources/css/special.css' => array( 'media' => 'screen' ),
		'skins/BlueSky/resources/css/printable.css' => array( 'media' => 'print' ),
		#'skins/BlueSky/resources/css/iphone.css' => array( 'media' => 'only screen and (max-device-width: 480px)' ),
	),
	'position' => 'top'
);

// zzz prefix is a hack to ensure that this module is loaded after the main
// skins.bluesky module
$wgResourceModules['zzzskins.bluesky.mainpage'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/home.css' => array( 'media' => 'screen' ),
	),
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.externallinks.123'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/externallinks-123.css' => array( 'media' => 'screen' ),
	),
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.externallinks.124'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/externallinks-124.css' => array( 'media' => 'screen' ),
	),
	'position' => 'top'
);

// LESS versions of things that used to be PHP-side core hacks
$wgResourceModules['skins.bluesky.hacks.general'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/general.less',
	'position' => 'top'
);

// Action-specific LESS hacks
$wgResourceModules['skins.bluesky.hacks.action.delete'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.action.delete.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.action.edit'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.action.edit.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.action.history'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.action.history.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.action.protect'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.action.protect.less',
	'position' => 'top'
);

// Namespace-specific LESS hacks
$wgResourceModules['skins.bluesky.hacks.filepage'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.filepage.less',
	'position' => 'top'
);

// (Special) page-specific LESS hacks
$wgResourceModules['skins.bluesky.hacks.special.log'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.special.log.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.special.movepage'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.special.movepage.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.special.recentchanges'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.special.recentchanges.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.special.undelete'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.special.undelete.less',
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.hacks.special.watchlist'] = array(
	'styles' => 'skins/BlueSky/resources/css/hacks/mediawiki.special.watchlist.less',
	'position' => 'top'
);

// Themes
// The 'themeloader.' prefix is a hack around https://bugzilla.wikimedia.org/show_bug.cgi?id=66508
$wgResourceModules['themeloader.skins.bluesky.blue'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/theme/blue.less' => array( 'media' => 'screen' )
	),
	'position' => 'top'
);

$wgResourceModules['themeloader.skins.bluesky.green'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/theme/green.less' => array( 'media' => 'screen' )
	),
	'position' => 'top'
);

$wgResourceModules['themeloader.skins.bluesky.grey'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/theme/grey.less' => array( 'media' => 'screen' )
	),
	'position' => 'top'
);

$wgResourceModules['themeloader.skins.bluesky.red'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/theme/red.less' => array( 'media' => 'screen' )
	),
	'position' => 'top'
);

$wgResourceModules['themeloader.skins.bluesky.white'] = array(
	'styles' => array(
		'skins/BlueSky/resources/css/theme/white.less' => array( 'media' => 'screen' )
	),
	'position' => 'top'
);

$wgResourceModules['skins.bluesky.js.easing'] = array(
	'scripts' => 'skins/BlueSky/resources/js/jquery.easing.js',
	'position' => 'top'
);

// Main JS module for this skin
$wgResourceModules['skins.bluesky.js'] = array(
	'scripts' => array(
		// not ready yet
		#'skins/BlueSky/resources/js/social.js',
		'skins/BlueSky/resources/js/bluesky.js',
	),
	'messages' => array(
		'navlist_collapse', 'navlist_expand',
		'userlogin-yourname-ph', 'userlogin-yourpassword-ph',
		'bluesky-js-no-thanks'
	),
	'dependencies' => array(
		'jquery.client',
		'skins.bluesky.js.easing',
		// for the e-mail to a friend stuff (well, at least as long as it's
		// there...)
		'jquery.ui.dialog'
	)
);

/**
 * Additional junk for the page head element.
 */
$wgHooks['OutputPageParserOutput'][] = 'wfTOCCrap';

$wgHooks['BeforePageDisplay'][] = function( OutputPage &$out, &$skin ) {
	//global $wgRequest, $wgUser;

	// Hooks are global, but we want these things *only* for this skin.
	if ( get_class( $skin ) !== 'SkinBlueSky' ) {
		return true;
	}

	/*
	$action = $wgRequest->getVal( 'action', 'view' );
	$isMainPage = $out->getTitle()->isMainPage();
	$isArticlePage = $out->getTitle() &&
			!$isMainPage &&
			$out->getTitle()->getNamespace() == NS_MAIN &&
			$action == 'view';
	*/

	$out->addMeta( 'http:content-type', 'text/html; charset=UTF-8' );

	/*
	if ( $isArticlePage || $isMainPage ) {
		global $wgLanguageCode;

		if ( $wgLanguageCode != 'en' ) {
			$mobileLang = $wgLanguageCode . '.';
		} else {
			$mobileLang = '';
		}

		$out->addLink( array(
			'rel' => 'alternate',
			'media' => 'only screen and (max-width: 640px)',
			'href' => 'http://' . $mobileLang . 'm.wikihow.com/' . $out->getTitle()->getPartialURL()
		) );
	}

	$out->setCanonicalUrl( $out->getTitle()->getFullURL() );
	$out->addLink( array(
		'href' => 'https://plus.google.com/102818024478962731382',
		'rel' => 'publisher'
	) );

	$out->addLink( array(
		'rel' => 'alternate',
		'type' => 'application/rss+xml',
		'title' => 'wikiHow: How-to of the Day',
		'href' => 'http://www.wikihow.com/feed.rss'
	) );

	$out->addLink( array(
		'rel' => 'apple-touch-icon',
		'href' => $wgStylePath . '/BlueSky/images/safari-large-icon.png'
	) );

	echo $out->getHeadItems();
	*/

	return true;
};

/**
 * TOC processing
 * Shamelessly stolen from brickimedia's refreshed skin
 * Currently: https://github.com/Brickimedia/Refreshed/blob/master/Refreshed.skin.php#L72
 */
$blueSkyTOC = '';

function wfTOCCrap( OutputPage &$out, ParserOutput $parseroutput ) {
	global $blueSkyTOC;
	$blueSkyTOC = $parseroutput->mSections;

	return true;
}
