<?php

class BlueSkyHooks {

	/**
	 * Shamelessly stolen from brickimedia's refreshed skin
	 * Currently: https://github.com/Brickimedia/Refreshed/blob/master/Refreshed.skin.php#L72
	 * @param OutputPage &$out
	 * @param ParserOutput $parserOutput
	 * @return bool
	 */
	public static function wfTOCCrap( OutputPage &$out, ParserOutput $parserOutput ) {
		global $wgBlueSkyTOC;
		$wgBlueSkyTOC = $parserOutput->mSections;

		return true;
	}
}
