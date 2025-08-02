<?php

use MediaWiki\Languages\LanguageNameUtils;
use MediaWiki\Linker\LinkRenderer;
use MediaWiki\Permissions\PermissionManager;
use MediaWiki\Revision\RevisionLookup;
use MediaWiki\User\TalkPageNotificationManager;
use MediaWiki\Utils\UrlUtils;
use Wikimedia\Rdbms\IConnectionProvider;

class SkinBlueSky extends SkinTemplate {

	public function __construct(
		public readonly IConnectionProvider $connectionProvider,
		public readonly Language $contentLanguage,
		public readonly LanguageNameUtils $languageNameUtils,
		public readonly LinkRenderer $linkRenderer,
		public readonly PermissionManager $permissionManager,
		public readonly RevisionLookup $revisionLookup,
		public readonly TalkPageNotificationManager $talkPageNotificationManager,
		public readonly UrlUtils $urlUtils,
		array $options = [],
	) {
		parent::__construct( $options );
	}

}
