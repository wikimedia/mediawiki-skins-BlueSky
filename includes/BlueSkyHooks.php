<?php

use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;

class BlueSkyHooks implements
	\MediaWiki\Output\Hook\OutputPageParserOutputHook
{

	/**
	 * Shamelessly stolen from brickimedia's refreshed skin
	 * Currently: https://github.com/Brickimedia/Refreshed/blob/master/Refreshed.skin.php#L72
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 * @return void
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		global $wgBlueSkyTOC;
		$wgBlueSkyTOC = $parserOutput->getSections();
	}
}
