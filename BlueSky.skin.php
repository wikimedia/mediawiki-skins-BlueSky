<?php
/**
 * SkinTemplate class for the BlueSky skin
 *
 * @ingroup Skins
 */
class SkinBlueSky extends SkinTemplate {
	public $skinname = 'bluesky', $stylename = 'BlueSky',
		$template = 'BlueSkyTemplate', $useHeadElement = true;

	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param $out OutputPage
	 */
	public function initPage( OutputPage $out ) {

		$out->addMeta( 'viewport', 'width=device-width, initial-scale=1.0' );

		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			'skins.bluesky'
		] );
		$out->addModules( [
			'skins.bluesky.js'
		] );
	}

	/**
	 * @param $out OutputPage
	 */
	function setupSkinUserCss( OutputPage $out ) {
		parent::setupSkinUserCss( $out );
	}
}
