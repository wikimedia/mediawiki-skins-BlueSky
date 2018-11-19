<?php

class BlueSkyHooks {

	/**
	 * Shamelessly stolen from brickimedia's refreshed skin
	 * Currently: https://github.com/Brickimedia/Refreshed/blob/master/Refreshed.skin.php#L72
	 */
	public static function wfTOCCrap( OutputPage &$out, ParserOutput $parserOutput ) {
		global $wgBlueSkyTOC;
		$wgBlueSkyTOC = $parserOutput->mSections;

		return true;
	}
}
