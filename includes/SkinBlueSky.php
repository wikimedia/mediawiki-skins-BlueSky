<?php
/**
 * SkinTemplate class for the BlueSky skin
 *
 * @ingroup Skins
 */
class SkinBlueSky extends SkinTemplate {
	public $skinname = 'bluesky', $stylename = 'BlueSky',
		$template = 'BlueSkyTemplate';

	/**
	 * Add CSS via ResourceLoader
	 *
	 * @param $out OutputPage
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addMeta( 'viewport',
			'width=device-width, initial-scale=1.0, ' .
			'user-scalable=yes, minimum-scale=0.25, maximum-scale=5.0'
		);

		$out->addModuleStyles( [
			'mediawiki.skinning.content.externallinks',
			'skins.bluesky',
			// Ensure that something is output even when the Theme extension is
			// installed. It overrides this later on anyway.
			'themeloader.skins.bluesky.blue'
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
