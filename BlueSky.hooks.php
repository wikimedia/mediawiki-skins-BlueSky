<?php

/**
 * Additional junk for the page head element.
 */
class SkinBlueSkyHooks {
	/**
	* TOC processing
	* Shamelessly stolen from brickimedia's refreshed skin
	* Currently: https://github.com/Brickimedia/Refreshed/blob/master/Refreshed.skin.php#L72
	*/

	public static function wfTOCCrap( OutputPage &$out, ParserOutput $parseroutput ) {
		global $blueSkyTOC;
		$blueSkyTOC = $parseroutput->mSections;

		return true;
	}

	public static function onBeforePageDisplayCrap( OutputPage &$out, &$skin ) {
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
	}
}
