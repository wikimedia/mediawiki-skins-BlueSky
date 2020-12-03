<?php
/**
 * SkinTemplate class for the BlueSky skin
 *
 * @ingroup Skins
 */
class SkinBlueSky extends SkinTemplate {
	public $stylename = 'BlueSky',
		$template = 'BlueSkyTemplate';

	/**
	 * Add RL modules
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		$out->addModules( [
			'skins.bluesky.js'
		] );
	}
}
