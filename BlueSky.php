<?php
/**
 * This PHP entry point is deprecated. Please use wfLoadSkin() and the skin.json file
 * instead. See https://www.mediawiki.org/wiki/Manual:Extension_registration for more details.
 */
if ( !function_exists( 'wfLoadSkin' ) ) {
	die( 'The BlueSky skin requires MediaWiki 1.36 or newer.' );
}
wfWarn(
	'Deprecated PHP entry point used for BlueSky skin. Please use wfLoadSkin instead, ' .
	'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
);

wfLoadSkin( 'BlueSky' );
