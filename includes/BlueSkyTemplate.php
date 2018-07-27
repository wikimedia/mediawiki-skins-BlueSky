<?php
/**
 * BaseTemplate class for the BlueSky skin
 *
 * @ingroup Skins
 */
class BlueSkyTemplate extends BaseTemplate {

	/**
	 * Array of all tool link data, sorted by menu (mostly)
	 * @var array
	 */
	protected $allTools;

	/**
	 * Is this the mainpage, or to be treated as such?
	 * @var bool
	 */
	protected $isMainPage;

	/**
	 * Outputs the entire contents of the page
	 */
	public function execute() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$request = $skin->getRequest();
		$action = $request->getVal( 'action', 'view' );
		if ( $request->getVal( 'diff' ) !== null ) {
			$action = 'diff';
		}
		$namespace = $title->getNamespace();
		$user = $skin->getUser();
		// get stupid tools pile; we'll dump these on the page throughout
		$this->allTools = $this->getPageTools();
		// We'll treat the mainpage like any other page if they're doing something besides looking at it
		$this->isMainPage = ( $title->isMainPage() && $action == 'view' );

		// Variables out of the way; open html, body elements, etc
		$html = $this->get( 'headelement' );
		$html .= Html::openElement( 'div', [ 'id' => 'mw-wrapper' ] );

		// Page header
		$html .= Html::rawElement( 'div', [ 'id' => 'header-outer' ],
			Html::rawElement( 'div', [ 'class' => 'wrapper-inner', 'id' => 'header-inner' ],
				// Logo block
				$this->getBanner() .
				$this->getSearch() .
				// funky tabs
				$this->getMiscNavigation( 'sidebar', 2, true ) .
				$this->getClear()
			)
		);

		$html .= Html::openElement( 'div', [ 'class' => 'wrapper-inner', 'id' => 'main-outer' ] );

		if ( $this->data['sitenotice'] ) {
			$html .= Html::rawElement(
				'div',
				[ 'id' => 'siteNotice' ],
				$this->get( 'sitenotice' )
			);
		}

		// Content header
		if ( $namespace != NS_SPECIAL && !$this->isMainPage ) {
			$html .= Html::rawElement( 'div', [ 'id' => 'content-nav' ],
				Html::rawElement(
					'div',
					[ 'id' => 'page-tools' ],
					$this->getPageLinks()
				) .
				Html::rawElement(
					'div',
					[ 'id' => 'page-categories' ],
					$this->getCategoryBreadcrumbs()
				)
			);
			$html .= $this->getClear();
			$html .= Html::openElement( 'div', [ 'id' => 'content-header' ] );
			$html .= $this->getIndicators();
			$html .= $this->getSpareEditLink();
			$html .= Html::rawElement(
				'h1',
				[
					'class' => 'firstHeading',
					'lang' => $this->get( 'pageLanguage' )
				],
				$this->get( 'title' )
			);

			// contentSub
			$html .= Html::openElement(
				'div',
				[ 'id' => 'contentSub' ]
			);
			if ( $this->data['subtitle'] ) {
				$html .= Html::rawelement(
					'p',
					[],
					$this->get( 'subtitle' )
				);
			}
			if ( $this->data['undelete'] ) {
				$html .= Html::rawelement(
					'p',
					[],
					$this->get( 'undelete' )
				);
			}
			$html .= Html::rawElement(
				'div',
				[ 'id' => 'last-edit' ],
				$this->getPageLastEdit()
			);
			$html .= Html::closeElement( 'div' );

			// ToC
			$toc = $this->getToc();
			if ( $toc != '' ) {
				$html .= Html::rawElement(
					'div',
					[ 'id' => 'header-toc-wrapper' ],
					Html::rawElement(
						'div',
						[ 'id' => 'header-toc' ],
						Html::element(
							'h2',
							[ 'id' => 'header-toc-header' ],
							$this->getMsg( 'bluesky-toc-sections' )->text()
						) . $toc
					)
				);
			}
			$html .= Html::closeElement( 'div' );
		}

		$html .= Html::openElement( 'div', [ 'id' => 'content-block' ] );

		// Alternate content header for special pages
		if ( $namespace == NS_SPECIAL ) {
			$html .= Html::openElement( 'div', [ 'id' => 'content-block-header' ] );

			$html .= $this->getIndicators();
			$html .= Html::rawElement(
				'h1',
				[
					'class' => 'firstHeading',
					'lang' => $this->get( 'pageLanguage' )
				],
				$this->get( 'title' )
			);
			$html .= Html::openElement(
				'div',
				[ 'id' => 'contentSub' ]
			);

			if ( $this->data['subtitle'] ) {
				$html .= Html::rawelement(
					'p',
					[],
					$this->get( 'subtitle' )
				);
			}
			$html .= Html::closeElement( 'div' );
			$html .= Html::closeElement( 'div' );
		}

		// Content
		$html .= Html::rawElement( 'div', [ 'class' => 'mw-body', 'role' => 'main' ],
			Html::rawElement( 'div', [ 'class' => 'mw-body-content', 'id' => 'bodyContent' ],
				$this->get( 'bodytext' ) .
				$this->getClear() .
				Html::rawElement(
					'div',
					[ 'class' => 'printfooter' ],
					$this->get( 'printfooter' )
				)
			)
		);

		// Footers and closing up
		$html .= Html::closeElement( 'div' );

		$html .= $this->getContentFooter();

		$html .= $this->get( 'dataAfterContent' );

		$html .= Html::closeElement( 'div' );

		$html .= $this->getPageFooter();

		$html .= Html::closeElement( 'div' );

		// Required for RL to run
		$html .= MWDebug::getDebugHTML( $this->getSkin()->getContext() );
		$html .= $this->get( 'bottomscripts' );
		$html .= $this->get( 'reporttime' );

		$html .= Html::closeElement( 'body' );
		$html .= Html::closeElement( 'html' );

		// The unholy echo
		echo $html;
	}

	/**
	 * Page content footer
	 *
	 * @return string html
	 */
	private function getContentFooter() {
		$skin = $this->getSkin();
		$user = $skin->getUser();
		$title = $skin->getTitle();
		$namespace = $title->getNamespace();

		// Add main page
		$target = Skin::makeInternalOrExternalUrl( $this->getMsg( 'mainpage' )->inContentLanguage()->text() );
		$link = Html::element(
			'a',
			[ 'href' => $target ],
			$this->getMsg( 'mainpage-description' )->text()
		);
		$this->set( 'mainpage', $link );
		$this->data['footerlinks']['places'][] = 'mainpage';

		// Reverse order to put mainpage link at the beginning
		$this->data['footerlinks']['places'] = array_reverse( $this->data['footerlinks']['places'] );

		// Add login link if not logged in
		if ( !$user->isLoggedIn() ) {
			$link = Linker::link(
				SpecialPage::getTitleFor( 'Userlogin' ),
				$this->getMsg( 'login' )->text()
			);
			$this->set( 'login', $link );
			$this->data['footerlinks']['places'][] = 'login';
		}

		$html = '';

		// Footer info
		$defaultFooter = $this->getFooterLinks();

		$languages = $this->getInterLanguageLinks();
		$catLinks = $skin->getCategoryLinks();
		// normalise into string
		$catLinks = $catLinks ? $catLinks : '';

		$footerInfo = $catLinks . $languages;

		if (
			( $namespace != NS_SPECIAL ) &&
			( $footerInfo !== '' || count( $this->allTools['page-tertiary'] ) > 0 )
		) {
			$html .= Html::openElement( 'div', [ 'id' => 'content-footer' ] );

			if ( $namespace != NS_MAIN || $this->isMainPage ) {
				$infoHeader = 'bluesky-page-info';
			} else {
				$infoHeader = 'bluesky-article-info';
			}
			$html .= Html::element(
				'h2',
				[],
				$this->getMsg( $infoHeader )->text()
			);

			$footerTools = '';
			$footerTools .= $this->getPortlet( [
				'id' => 'p-page-footer-tools',
				'class' => 'info',
				'headerMessage' => 'bluesky-footer-tools',
				'content' => $this->allTools['page-tertiary']
			] );
			if ( $this->isMainPage ) {
				// Output the page edit/etc tools since we didn't above
				$footerTools .= Html::rawElement(
					'div',
					[ 'id' => 'page-tools' ],
					$this->getPageLinks()
				);
			}

			$html .= Html::rawElement(
				'div',
				[ 'id' => 'content-footer-main' ],
				$footerInfo . $footerTools
			);

			if ( isset( $defaultFooter['info'] ) ) {
				$html .= Html::openElement( 'ul', [ 'id' => 'footer-info' ] );
				foreach ( $defaultFooter['info'] as $key ) {
					$html .= Html::rawElement(
						'li',
						[
							'id' => 'footer-' . Sanitizer::escapeId( 'info-' . $key )
						],
						$this->get( $key )
					);
				}
				$html .= Html::closeElement( '' );
			}

			$html .= Html::closeElement( 'div' );
		}

		return $html;
	}

	protected function getPageFooter() {
		$skin = $this->getSkin();

		$html = Html::openElement( 'div', [ 'id' => 'mw-footer' ] );
		$html .= Html::openElement( 'div', [ 'id' => 'footer-inner', 'class' => 'wrapper-inner' ] );

		$defaultFooter = $this->getFooterLinks();
		foreach ( $defaultFooter as $category => $links ) {
			if ( $category == 'info' ) {
				continue;
			}
			$html .= Html::openElement(
				'ul',
				[
					'id' => 'footer-' . Sanitizer::escapeId( $category ),
					'role' => 'contentinfo'
				]
			);
			foreach ( $links as $key ) {
				$html .= Html::rawElement(
					'li',
					[
						'id' => 'footer-' . Sanitizer::escapeId( $category . '-' . $key )
					],
					$this->get( $key )
				);
			}
			$html .= Html::closeElement( 'ul' );
		}

		// Icon stuff - powered by, copyright, etc
		$html .= Html::openElement(
			'ul',
			[
				'id' => 'footer-icons',
				'role' => 'contentinfo',
			]
		);
		foreach ( $this->getFooterIcons( 'icononly' ) as $blockName => $footerIcons ) {
			$html .= Html::openElement(
				'li',
				[
					'id' => 'footer-' . Sanitizer::escapeId( $blockName ) . 'ico'
				]
			);
			foreach ( $footerIcons as $icon ) {
				$html .= $skin->makeFooterIcon( $icon );
			}
			$html .= Html::closeElement( 'li' );
		}
		$html .= Html::closeElement( 'ul' );
		$html .= $this->getClear();

		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	/**
	 * Generates pile of all the tools
	 * WHAT THE FUCK IS THIS
	 *
	 * @return array of arrays of each kind of tool
	 */
	private function getPageTools() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$request = $skin->getRequest();
		$action = $request->getVal( 'action', 'view' );
		$namespace = $title->getNamespace();

		$sortedPileOfTools = [
			'edit-menu' => [], // sticky header menu
			'page-primary' => [], // nestled at the top of the page
			'page-admin' => [], // dropdown in the page-primary list
			'page-tertiary' => [], // footer menu
			'general' => [] // another footer menu for gemeral site stuff
		];
		// also possible: languages

		$pileOfTools = [];

		// Get page tools (tabs on top in vector/monobook)
		foreach ( $this->data['content_navigation'] as $navKey => $navBlock ) {
			// Just use namespaces items as they are, plus stuff
			if ( $navKey == 'namespaces' ) {
				$sortedPileOfTools['page-primary'] = $navBlock;
				// Add talk link to bottom too
				if ( isset( $sortedPileOfTools['page-primary']['talk'] ) ) {
					$sortedPileOfTools['page-tertiary']['talk'] = $sortedPileOfTools['page-primary']['talk'];
				}
				if ( $namespace != NS_SPECIAL ) {
					// Because some moron though it'd be a good idea to use the (arbitrary) namespace name as the array key for the page tab, we have no idea what the key is here
					$key = array_keys( $navBlock )[0];

					if ( $namespace == NS_MAIN ) {
						$sortedPileOfTools['page-primary'][$key]['text'] = $this->getMsg( 'article' )->text();
					}

					if ( $action != 'view' ) {
						// Remove extra selected from page-main tab if something else is
						unset( $sortedPileOfTools['page-primary'][$key]['class'] );
					}
				}
			} else {
				$pileOfTools = array_merge( $pileOfTools, $navBlock );
			}
		}

		// Get other tools (toolbox in sidebar in vector/monobook)
		$pileOfTools = array_merge( $pileOfTools, $this->getToolbox() );
		if ( $namespace != NS_SPECIAL ) {
			$pileOfTools['pagelog'] = [
				'text' => $this->getMsg( 'bluesky-pagelog' )->escaped(),
				'href' => SpecialPage::getTitleFor( 'Log', $title->getPrefixedText() )->getLocalURL(),
				'id' => 't-pagelog'
			];
		}

		// Not needed in this skin
		unset( $pileOfTools['view'] );

		// This is really dumb, but there is no sane way to do this. So we'll just go through all of them and pick out the ones we know we want for any given block. Note that this completely screws over any extensions that add new ones.
		foreach ( $pileOfTools as $navKey => $navBlock ) {
			if ( in_array( $navKey, [
				'edit',
				'viewsource'
			] ) ) {
				// Add these to a couple of extras:
				$sortedPileOfTools['edit-menu'][$navKey] = $navBlock;
				$sortedPileOfTools['page-tertiary'][$navKey] = $navBlock;
			}

			$currentSet = null;

			if ( in_array( $navKey, [
				// 'edit',
				// 'viewsource',
				'info',
				'whatlinkshere',
				'addsection'
			] ) ) {
				$currentSet = 'edit-menu';
			} elseif ( in_array( $navKey, [
				// ns
				// talk
				'edit',
				'viewsource',
				'history',
				'contributions'
			] ) ) {
				$currentSet = 'page-primary';
			} elseif ( in_array( $navKey, [
				'delete',
				'rename',
				'protect',
				'unprotect',
				'move',
				'blockip',
				'userrights',
				'log'
			] ) ) {
				$currentSet = 'page-admin';
			} elseif ( in_array( $navKey, [
				// 'edit',
				// 'viewsource',
				'watch',
				'unwatch',
				'print',
				'permalink',
				'pagelog',
				'recentchangeslinked',
			] ) ) {
				$currentSet = 'page-tertiary';
			} else {
				$currentSet = 'general';
			}
			$sortedPileOfTools[$currentSet][$navKey] = $navBlock;
		}

		// Use different edit msg in header menu
		if ( isset( $sortedPileOfTools['edit-menu']['edit'] ) ) {
			$sortedPileOfTools['edit-menu']['edit']['text'] =  $this->getMsg( 'bluesky-editthis' )->text();
		}

		return $sortedPileOfTools;
	}

	/**
	 * Assembles a single sidebar portlet of any kind (monobook style)
	 *
	 * @return string portlet
	 */
	private function getPortlet( $box ) {
		if ( !isset( $box['content'] ) || !$box['content'] ) {
			return;
		}

		$class = isset( $box['class'] ) ? 'mw-portlet ' . $box['class'] : 'mw-portlet';
		$content = Html::openElement(
			'div',
			[
				'role' => 'navigation',
				'class' => $class,
				'id' => Sanitizer::escapeId( $box['id'] )
			] + Linker::tooltipAndAccesskeyAttribs( $box['id'] )
		);
		$content .= Html::element(
			'h3',
			[],
			isset( $box['headerMessage'] ) ? $this->getMsg( $box['headerMessage'] )->escaped() : htmlspecialchars( $box['header'] )
		);
		if ( is_array( $box['content'] ) ) {
			$content .= Html::openElement( 'ul', [ 'class' => 'menu-block' ] );
			foreach ( $box['content'] as $key => $item ) {
				$content .= $this->makeListItem( $key, $item );
			}
			$content .= Html::closeElement( 'ul' );
		} else {
			$content .= Html::rawElement(
				'div',
				[ 'class' => 'menu-block' ],
				$box['content']
			);
		}
		$content .= Html::closeElement( 'div' );

		return $content;
	}

	/**
	 * Assembles the logo banner thing
	 *
	 * @return string portlets
	 */
	private function getBanner() {
		$html = Html::openElement(
			'a',
			[
				'href' => $this->data['nav_urls']['mainpage']['href'],
				'id' => 'p-banner',
				'class' => 'mw-portlet',
				'role' => 'banner'
			] + Linker::tooltipAndAccesskeyAttribs( 'p-logo' )
		);
		// Logo image
		$html .= Html::element(
			'div',
			[
				'class' => 'mw-wiki-logo',
				'id' => 'p-logo'
			]
		);
		// Site title and subtitle
		$html .= Html::rawElement(
			'div',
			[ 'id' => 'mw-wiki-bannertext' ],
			Html::element(
				'div',
				[ 'id' => 'p-wiki-title' ],
				$this->getMsg( 'sitetitle' )->escaped()
			) . Html::element(
				'div',
				[ 'id' => 'p-sitesubtitle' ],
				wfMessage( 'sitesubtitle' )->escaped()
			)
		);
		$html .= Html::closeElement( 'a' );

		return $html;
	}

	/**
	 * Essentially a nestedmenuparser; replaces normal sidebar generation with something more widely-capable
	 * Does not support extensions adding things to the sidebar (usually extra portlets)
	 * For styling menu headers: css classes 'navbar-edit', 'navbar-grow', 'navbar-view', 'navbar-explore',
	 * 'navbar-user', 'navbar-community', 'navbar-messages', 'navbar-burger'
	 *
	 * @param string $menu MediaWiki menu message from which to assemble
	 * @param int $maxDepth Maximum depth to allow; subsequent levels will all be treated as the same level
	 * @param bool $appendJunk Whether or not to include extra edit, profile, and messages dropdowns
	 * @return string HTML
	 */
	private function getMiscNavigation( $menu, $maxDepth = 3, $appendJunk = false ) {
		$html = Html::openElement( 'div', [ 'id' => Sanitizer::escapeId( 'mw-' . $menu ) ] );

		if ( $appendJunk ) {
			$html .= $this->getEditMenu();
		}

		$message = trim( wfMessage( $menu )->text() );
		$previousLevel = 0;

		// parse lines into an array: strings of target, display, class
		$lines = explode( "\n", $message );
		$links = [];
		foreach ( $lines as $line ) {
			$item = $this->parseLine( $line );
			// Cap max depth
			$item['depth'] = $item['depth'] > $maxDepth ? $maxDepth : $item['depth'];

			// Deal with nesting
			if ( $item['depth'] == 0 ) {
				continue; // empty
			}
			if ( $item['depth'] == 1 ) {
				// It's a top level; make div and an h3 instead of nesting uls
				// Mostly just because this is what outputPortlet etc do
				if ( $previousLevel > 0 ) {
					if ( $previousLevel > 1 ) {
						$html .= Html::closeElement( 'ul' );
					}
					$html .= Html::closeElement( 'div' );
				}
				if ( isset( $item['class'] ) ) {
					$class =  'mw-portlet ' . $item['class'];
				} else {
					$class = 'mw-portlet ' . $this->getRandomTabClass( $item['html'] );
				}

				$html .= Html::openElement(
					'div',
					[
						'role' => 'navigation',
						'class' => $class,
						'id' => 'p-' . $item['id']
					]
				);
				$html .= Html::rawElement(
					'h3',
					[],
					$item['html']
				);
			} else {
				// Actual nesting; shut up I know this looks stupid
				if ( $item['depth'] > $previousLevel ) {
					$html .= Html::openElement( 'ul', [ 'class' => 'menu-block' ] );

				} elseif ( $item['depth'] < $previousLevel ) {
					$html .= Html::closeElement( 'li' );
					$html .= Html::closeElement( 'ul' );
					$html .= Html::closeElement( 'li' );
				} else {
					$html .= Html::closeElement( 'li' );
				}

				// Set class and id, if any
				$params = [ 'id' => 'n-' . $item['id'] ];
				if ( isset( $item['class'] ) ) {
					$params['class'] = $item['class'];
				}
				$html .= Html::openElement( 'li', $params );

				// Displayed content
				$html .= $item['html'];
			}
			$previousLevel = $item['depth'];
		}
		$html .= Html::closeElement( 'div' );

		if ( $appendJunk ) {
			$html .= $this->getProfile();
			$html .= $this->getMessages();
		}

		$html .= Html::closeElement( 'div' );
		return $html;
	}

	/**
	 * Helper function for getMiscNavigation: get a random icon for icon-less tabs
	 *
	 * Icons are pretty arbitrary anyway, so most of these should make sense for most
	 * menu sections no matter what they are...
	 *
	 * @param string $seed
	 * @return string
	 */
	private function getRandomTabClass( $seed ) {
		switch ( hexdec( sha1( $seed )[0] ) % 4 ) {
			 case 0:
				return 'navbar-grow';
			 case 1:
				return 'navbar-view';
			 case 2:
				return 'navbar-explore';
			 case 3:
				return 'navbar-community';
		}
	}

	/**
	 * Helper function for getMiscNavigation
	 *
	 * Lines are supposed to look like the following:
	 * * link target|link display text|optional link class
	 * * not a link
	 * * -|also not a link, but needs a '-' because of the class on the end|link class
	 *
	 * They're all parsed based on total number of items.
	 *
	 * @param string $line
	 * @return array
	 */
	private function parseLine( $line ) {
		$depth = 0;
		while ( $depth < strlen( $line ) && $line[$depth] == '*' ) {
			$depth++;
		}
		if ( $depth == 0 ) {
			// Not a valid menu item, probably a comment or blank line
			return [ 'depth' => 0 ];
		}
		$item['depth'] = $depth;

		// [ link target, link display, css class ]
		// Parse this junk
		$text = explode( "|", trim( $line, '*' ) );
		foreach ( $text as $key => $value ) {
			$text[$key] = trim( $value );
		}

		$specialCases = [
			'SEARCH',
			'TOOLBOX',
			'LANGUAGES'
		];
		if ( in_array( $text[0], $specialCases ) ) {
			// fuck off, we don't care
			return [ 'depth' => 0 ];
		}

		// Special case: '-' for empty targets
		if ( $text[0] == '-' ) {
			$textContent = $this->getMsgOrDump( $text[1] );
			$item['id'] = Sanitizer::escapeId( $text[1] );
		} else {
			if ( isset( $text[1] ) ) {
				// has both target and display text
				$target = $this->getMsgOrDump( $text[0] );
				if ( preg_match( '/^(?i:' . wfUrlProtocols() . ')/', $target ) ) {
					$textContent = Linker::makeExternalLink(
						$target,
						$this->getMsgOrDump( $text[1] )
					);
				} else {
					$textContent = Linker::link(
						Title::newFromText( $target ),
						$this->getMsgOrDump( $text[1] )
					);
				}
				$item['id'] = Sanitizer::escapeId( $text[1] );
			} else {
				// only display; no target
				$textContent = $this->getMsgOrDump( $text[0] );
				$item['id'] = Sanitizer::escapeId( $text[0] );
			}
		}
		if ( isset( $text[2] ) ) {
			// extra class to apply
			$item['class'] = Sanitizer::escapeClass( $text[2] );
		}

		$item['html'] = Html::rawElement(
			'span',
			[ 'class' => 'menu-item' ],
			$textContent
		);
		return $item;
	}

	/**
	 * Helper function for getMiscNavigation
	 * Gets the mw message for the string if exists and parses, or just dumps the string
	 *
	 * @param string $text
	 * @return string
	 */
	private function getMsgOrDump( $text ) {
		if ( $this->getMsg( $text )->isDisabled() ) {
			// not the name of a MediaWiki message
			return htmlspecialchars( $text );
		} else {
			return $this->getMsg( $text )->escaped();
		}
	}

	/**
	 * Get the edit menu if editable: edit page, whatlinkshere, page stats, relatedpages, wikidata item etc
	 *
	 * @return string portlet
	 */
	private function getEditMenu() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();

		if ( !$title->userCan( 'edit' ) ) {
			return '';
		}

		return $this->getPortlet( [
			'id' => 'p-editmenu',
			'class' => 'navbar-edit',
			'headerMessage' => 'bluesky-navbar-edit',
			'content' => $this->allTools['edit-menu']
		] );
	}

	/**
	 * Get the user links menu
	 *
	 * @return string portlet
	 */
	private function getProfile() {
		return $this->getPortlet( [
			'id' => 'p-personal',
			'class' => 'navbar-user',
			'headerMessage' => 'bluesky-personaltools',
			'content' => $this->getPersonalTools(),
		] );
	}

	/**
	 * Get messages display stuff
	 *
	 * @return string portlets
	 */
	private function getMessages() {
		$html = '';
		$user = $this->getSkin()->getUser();

		if ( is_callable( [ ApiEchoNotifications::class, 'getNotifications' ] )
			&& $user->isLoggedIn()
		) {
			$maxNotesShown = 5;
			// FIXME update this to the newer Echo API
			$notif = ApiEchoNotifications::getNotifications( $user, 'html', $maxNotesShown );

			if ( $notif ) {
				$notificationsPage = SpecialPage::getTitleFor( 'Notifications' );

				// show those notifications
				foreach ( $notif as $note ) {
					$this_note = $note['*'];
					// unread?
					if ( !isset( $note['read'] ) ) {
						$this_note = str_replace(
							'mw-echo-state',
							'mw-echo-state mw-echo-unread',
							$this_note
						);
					}
					$html .= $this_note;
				}

				// get the unread count
				$notifUser = MWEchoNotifUser::newFromUser( $user );
				$this->notifications_count = $notifUser->getNotificationCount();

				if ( $this->notifications_count > $maxNotesShown ) {
					$unshown = '<br />' . Linker::link(
						$notificationsPage,
						$this->msg( 'parentheses',
							$this->msg( 'bluesky-unread-notifications' )->numParams(
								( $this->notifications_count - $maxNotesShown )
							)->parse()
						)->text()
					);
				} else {
					$unshown = '';
				}

				// add view all link
				$html .= '<div class="menu_message_morelink">';
				$html .= Linker::link( $notificationsPage, $this->msg( 'more-notifications-link' )->plain() );
				$html .= $unshown . '</div>';
			} else {
				// no notifications
				$html .= '<div class="menu_message_morelink">' . $this->msg( 'no-notifications' )->parse() . '</div>';
			}

		} else {
			// old school
			if ( class_exists( 'Notifications' ) ) {
				$ret = Notifications::loadNotifications();
				if ( is_array( $ret ) ) {
					list( $html, $this->notifications_count ) = $ret;
				}
			} else {
				// the wikiHow notifications ext. isn't installed either,
				// so we essentially reimplement its logic here
				list( $notes, $count, $newTalk ) = $this->getNotifications();
				$html = $this->formatNotifications( $notes, $newTalk );
				$this->notifications_count = $count;
			}
		}
		return $this->getPortlet( [
			'id' => 'p-messages',
			'class' => 'navbar-messages',
			'headerMessage' => 'bluesky-messages',
			'content' => Html::rawElement(
				'div',
				[ 'id' => 'messages-block' ],
				$html
			)
		] );
	}

	/**
	 * Get all notifications for the current user.
	 *
	 * @return array [ HTML output, total amount of all notifications, has new User_talk messages? ]
	 */
	private function getNotifications() {
		global $wgMemc;

		$user = $this->getSkin()->getUser();
		$memKey = wfMemcKey( 'notification_box_' . $user->getId() );
		$box = $wgMemc->get( $memKey );

		if ( !is_array( $box ) ) {
			$notes = [];

			// Talk messages
			$talkCount = 0;
			if ( $user->getNewtalk() ) {
				$talkCount = $this->getCount( 'user_newtalk' );
				$msg = '<div class="note_row"><div class="note_icon_talk"></div>' .
					Linker::link(
						$user->getTalkPage(),
						$this->getMsg( 'bluesky-notifications-new-talk' )->numParams( $talkCount )->parse()
					) . '</div>';
				$notes[] = $msg;
				$newTalk = true;
			} else {
				$newTalk = false;
			}

			// Kudos (fan mail) and Thumbs Up removed for the time being due to
			// being rather wikiHow-specific and generally the way how it was
			// done was ugly. Hooks, people; use hooks instead of hard-coding
			// things!

			$totalCount = $talkCount;

			$box = [ $notes, $totalCount, $newTalk ];
		}

		return $box;
	}

	/**
	 * @param array $notes Notification HTML for each notification in an array
	 * @param bool $newTalk Does the current user have new talk page messages?
	 *
	 * @return string HTML output
	 */
	private function formatNotifications( $notes, $newTalk ) {
		$html = '';
		$talkPage = $this->getSkin()->getUser()->getTalkPage()->getPrefixedText();

		foreach ( $notes as $note ) {
			$html .= $note;
		}

		if ( $html ) {
			// no line at the top
			$html = preg_replace( '/note_row/', 'note_row first_note_row', $html, 1 );
			if ( !$newTalk ) {
				$html .= '<div class="note_row note_empty">' .
					$this->getMsg( 'bluesky-notifications-no-talk', $talkPage )->parse() .
					'</div>';
			}
		} else {
			$html = '<div class="note_row note_empty">' .
				$this->getMsg( 'bluesky-notifications-none', $talkPage )->parse() .
				'</div>';
		}

		return $html;
	}

	/**
	 * Assembles the search form
	 *
	 * @return string portlet
	 */
	private function getSearch() {
		$html = '';
		$html .= Html::openElement(
			'form',
			[
				'action' => htmlspecialchars( $this->get( 'wgScript' ) ),
				'role' => 'search',
				'class' => 'mw-portlet',
				'id' => 'p-search'
			]
		);
		$html .= Html::openElement( 'div', [ 'id' => 'search-inner' ] );
		$html .= Html::hidden( 'title', htmlspecialchars( $this->get( 'searchtitle' ) ) );
		$html .= Html::rawelement(
			'h3',
			[],
			Html::label( $this->getMsg( 'search' )->escaped(), 'searchInput' )
		);
		$html .= $this->makeSearchInput( [ 'id' => 'searchInput' ] );
		$html .= $this->makeSearchButton( 'go', [ 'id' => 'searchGoButton', 'class' => 'searchButton' ] );

		$html .= Html::closeElement( 'div' );
		$html .= Html::closeElement( 'form' );

		return $html;
	}

	/**
	 * Assembles page-related tools/links
	 *
	 * @return string portlets
	 */
	private function getPageLinks() {
		$html = $this->getPortlet( [
			'id' => 'p-page-main',
			'headerMessage' => 'bluesky-page',
			'content' => $this->allTools['page-primary']
		] );
		$html .= $this->getPortlet( [
			'id' => 'p-page-admin',
			'headerMessage' => 'bluesky-admin',
			'content' => $this->allTools['page-admin']
		] );

		return $html;
	}

	/**
	 * Output the "Page last edited X days Y hours ago" string for pages in content namespaces.
	 *
	 * @param Title $title
	 * @return string "edited X ago" string on success, empty string on failure
	 */
	private function getPageLastEdit() {
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$html = '';

		if ( $title->exists() ) {
			$revision = Revision::newFromTitle( $title );
			if ( $revision instanceof Revision ) {

				$timestamp = wfTimestamp( TS_UNIX, $revision->getTimestamp() );
				// Normally we'd just call Language::formatTimePeriod or something at this point, but none of the relevant functions in core seem to actually work. So we'll just reimplement our own by getting the number of seconds difference between the UNIX timestamps.
				$timediff = time() - $timestamp;

				// numbers of seconds in each span
				$spans = [
					'years' => 60 * 60 * 24 * 365.25, // Not entirely accurate after a few decades/centuries, but who cares
					'months' => 60 * 60 * 24 * 30.5, // 30.5 days is not technically a month, but who cares
					'weeks' => 60 * 60 * 24 * 7,
					'days' => 60 * 60 * 24,
					'hours' => 60 * 60,
					'minutes' => 60
				];

				$formattedTS = null;

				foreach ( $spans as $span => $amount ) {
					if ( $amount < $timediff ) {
						// Number of blah ago is rounded down
						$number = floor( $timediff / $amount );
						$formattedTS = $this->getMsg( 'duration-' . $span )->params( [ $number ] );

						break;
					}
				}

				$author = $revision->getUserText();
				// Check if IP

				// Pick the correct message depending on if the current user can access the revision's last author's name or not (hey, it could be RevisionDeleted, as Revision::getUserText()'s documentation states)
				if ( $author ) {
					$userLink = Linker::userLink( $revision->getUser(), $revision->getUserText() );
					$html = $this->getMsg( 'bluesky-page-edited-user' )->params( $formattedTS )->rawParams( $userLink );
				} else {
					$html = $this->getMsg( 'bluesky-page-edited' )->params( [ $formattedTS ] );
				}
			}
		}

		return $html;
	}

	/**
	 * Get languages string, if any interlanguages present
	 * (As usual, $this->data['language_urls'] is dumb and hardcodes too many assumptions)
	 *
	 * @return string
	 */
	private function getInterlanguageLinks() {
		global $wgContLang, $wgHideInterlanguageLinks;

		$skin = $this->getSkin();

		$languages = [];
		if ( !$wgHideInterlanguageLinks ) {
			foreach ( $skin->getOutput()->getLanguageLinks() as $blob ) {
				$tmp = explode( ':', $blob, 2 );
				$class = 'interwiki-' . $tmp[0];
				$code = $tmp[0];
				$iwTitleName = $tmp[1];
				$iwTitle = Title::newFromText( $blob );
				$inLanguage = $wgContLang->getCode();
				$interwiki = $iwTitle->getInterwiki();
				if ( Language::fetchLanguageName( $interwiki, $inLanguage ) != '' ) {
					$language = Language::fetchLanguageName( $interwiki, $inLanguage );
				} else {
					$language = $blob;
				}
				$languages[] = [
					'code' => $code,
					'href' => $iwTitle->getFullURL(),
					'text' => $iwTitleName,
					'class' => $class,
					'language' => $language
				];
			}
		}

		$html = '';

		if ( count( $languages ) > 0 ) {
			$html .= Html::openElement( 'div', [ 'id' => 'mw-languages' ] );
			$html .= Html::element(
				'span',
				[ 'id' => 'otherlanguages-label' ],
				$this->getMsg( 'otherlanguages' )->text()
			);

			$html .= Html::openElement( 'ul', [] );
			foreach ( $languages as $langlink ) {
				$html .= Html::rawElement(
					'li',
					[ 'class' => Sanitizer::escapeClass( $langlink['code'] ) ],
					htmlspecialchars( trim( $langlink['language'] ) ) .
					Html::element(
						'a',
						[
							'href' => htmlspecialchars( $langlink['href'] ),
							'class' => $langlink['class'] . ' interwiki'
						],
						$langlink['text']
					)
				);
			}
			$html .= Html::closeElement( 'ul' );
			$html .= Html::closeElement( 'div' );
		}

		return $html;
	}

	/**
	 * Make category breadcrumbs
	 *
	 * @return string portlet
	 */
	private function getCategoryBreadcrumbs() {
		global $wgContLang;

		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$namespace = $title->getNamespace();

		// home
		$html = Html::openElement(
			'div',
			[
				'class' => 'p-portlet',
				'id' => 'breadcrumbs'
			]
		);
		$html .= Html::element(
			'a',
			[
				'href' => $this->data['nav_urls']['mainpage']['href'],
				'id' => 'bc-home'
			],
			$this->getMsg( 'bluesky-home' )->text()
		);

		$html .= $this->getBreadcrumbsPointer();

		// figure out categories
		// Get list from output if in view/edit/preview; otherwise get list from title
		if ( in_array( $skin->getRequest()->getVal( 'action' ), [ 'submit', 'view', 'edit' ] ) ) {
			$allCats = [];
			$allCats2 = $skin->getOutput()->GetCategories();
			foreach ( $allCats2 as $displayName ) {
				$safeTitle = Title::makeTitleSafe( NS_CATEGORY, $displayName );
				$allCats[] = $safeTitle->getDBkey();
			}
		} else {
			$allCats = array_keys( $title->getParentCategories() );
			// Horrible backwards parsing
			foreach ( $allCats as $i => $catName ) {
				$len = strlen( $wgContLang->getNsText( NS_CATEGORY ) . ':' );
				$allCats[$i] = substr( $catName, $len );
			}
		}

		// namespaces
		$crumbs = Html::openElement( 'ul', [ 'id' => 'catlist-top-ns' ] );
		switch ( $namespace ) {
			case NS_MAIN:
				if ( count( $allCats ) > 0 ) {
					$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse() );
				} else {
					$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nsmain' )->parse() );
				}
				break;
			case NS_PROJECT:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nsproject' )->parse() );
				break;
			case NS_PROJECT_TALK:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nsproject-talk' )->parse() );
				break;
			case NS_FILE:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nsfile' )->parse() );
				break;
			case NS_TEMPLATE:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nstemplate' )->parse() );
				break;
			case NS_CATEGORY:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse() );
				break;
			default:
				$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nsdefault' )->parse() );
		}
		if ( $namespace > NS_MAIN && count( $allCats ) > 0 ) {
			$crumbs .= Html::rawElement( 'li', [], $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse() );
		}

		$crumbs .= Html::closeElement( 'ul' );
		$html .= $crumbs;

		// Do categories
		if ( count( $allCats ) > 0 ) {
			$html .= $this->getBreadcrumbsPointer();

			// SQL provided by your friendly neighbourhood Skizzers
			// I honestly don't remember what this was for, but it was apparently needed to get the actually relevant ones
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
				[ 'page', 'page_props', 'category' ],
				[ 'cat_title' ],
				[
					'cat_title' => $allCats,
					'pp_propname' => null
				],
				__METHOD__,
				[
					'ORDER BY' => 'cat_pages DESC',
					'LIMIT' => 2
				],
				[
					'page' => [ 'LEFT OUTER JOIN', [
						'cat_title = page_title',
						'page_namespace' => NS_CATEGORY
					] ],
					'page_props' => [ 'LEFT OUTER JOIN', [
						'pp_propname' => 'hiddencat',
						'pp_page = page_id'
					] ]
				]
			);
			$normalCats = [];
			foreach ( $res as $row ) {
				if ( strlen( $row->cat_title ) < 25 ) {
					$normalCats[] = $row->cat_title;
				}
			}

			$catList = '';
			if ( count( $normalCats ) > 0 ) {
				$catList = '<ul id="catlist-top">';
				foreach ( $normalCats as $category ) {
					$titleSafe = Title::makeTitleSafe( NS_CATEGORY, $category );
					if ( !$titleSafe ) {
						continue;
					}
					$category = Linker::link( $titleSafe, $titleSafe->getText() );
					$catList .=  '<li>' . $category . '</li>';
				}
				$catList .= '</ul>';
			}
			$html .= $catList;

			// $html .= $this->getBreadcrumbsPointer();
		}

		// page
		// $html .= Linker::link( $title, $title->getSubpageText() );
		$html .= Html::closeElement( 'div' );

		return $html;
	}

	private function getBreadcrumbsPointer() {
		return Html::element(
			'span',
			[ 'class' => 'breadcrumbs-separator' ],
			$this->getMsg( 'bluesky-breadcrumb-pointer' )->text()
		);
	}

	/**
	 * Get all the category info
	 *
	 * @return array of parsed normal and hidden catlink html
	 */
	private function getCategoryLinks() {
		global $wgContLang;

		$namespace = $this->getSkin()->getTitle()->getNamespace();
		$page = Linker::link( $this->getSkin()->getTitle(), $this->getSkin()->getTitle()->getSubpageText() );
		$categoryOutput = '';
		$normalCats = [];
		$count = 0;

		if ( $namespace == NS_SPECIAL ) {
			$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsspecial' )->parse();
			$categoryOutput .= substr( $crumbs, 0, -6 );
		} else {
			/* It's categorisable; get lists of categories and hidden categories */
			/* Get list from output if in preview; otherwise get list from title */
			if ( in_array( $this->getSkin()->getRequest()->getVal( 'action' ), [ 'submit', 'view', 'edit' ] ) ) {
				$allCats = [];
				$allCats2 = $this->getSkin()->getOutput()->GetCategories();
				foreach ( $allCats2 as $displayName ) {
					$title = Title::makeTitleSafe( NS_CATEGORY, $displayName );
					$allCats[] = $title->getDBkey();
				}
			} else {
				$allCats = array_keys( $this->getSkin()->getTitle()->getParentCategories() );

				foreach ( $allCats as $i => $catName ) {
					$len = strlen( $wgContLang->getNsText( NS_CATEGORY ) . ':' );
					$allCats[$i] = substr( $catName, $len );
				}
			}

			if ( count( $allCats ) > 0 ) {
				$dbr = wfGetDB( DB_REPLICA );
				$res = $dbr->select(
					[ 'page', 'page_props', 'category' ],
					[ 'cat_title' ],
					[
						'cat_title' => $allCats,
						'pp_propname' => null
					],
					__METHOD__,
					[
						'ORDER BY' => 'cat_pages DESC',
						'LIMIT' => 2
					],
					[
						'page' => [ 'LEFT OUTER JOIN', [
							'cat_title = page_title',
							'page_namespace' => NS_CATEGORY
						] ],
						'page_props' => [ 'LEFT OUTER JOIN', [
							'pp_propname' => 'hiddencat',
							'pp_page = page_id'
						] ]
					]
				);
				foreach ( $res as $row ) {
					if ( strlen( $row->cat_title ) < 25 ) {
						$normalCats[] = $row->cat_title;
					}
					$count++;
				}

				if ( count( $normalCats ) > 0 ) {
					$catList = '<li><ul id="catlist-top">';
					foreach ( $normalCats as $category ) {
						$title = Title::makeTitleSafe( NS_CATEGORY, $category );
						if ( !$title ) {
							continue;
						}
						$category = Linker::link( $title, $title->getText() );
						$catList .=  '<li>' . $category . '</li>';
					}
					$catList .= '</ul></li>';
				}
			}
			if ( $namespace == 0 ) {
				if ( count( $normalCats ) > 0 ) {
					$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse();
				} else {
					$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsmain' )->parse();
				}
			} elseif ( $namespace == 4 ) {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsproject' )->parse();
			} elseif ( $namespace == 5 ) {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsproject-talk' )->parse();
			} elseif ( $namespace == 6 ) {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsfile' )->parse();
			} elseif ( $namespace == 10 ) {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nstemplate' )->parse();
			} elseif ( $namespace == 14 ) {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse();
			} else {
				$crumbs = $this->getMsg( 'bluesky-breadcrumbs-nsdefault' )->parse();
			}
			if ( $namespace !== NS_MAIN && $namespace !== NS_CATEGORY && count( $normalCats ) > 0 ) {
				$crumbs .= ' ⚬ ';
				$crumbs .= $this->getMsg( 'bluesky-breadcrumbs-nscategory' )->parse();
			}
			$categoryOutput .= $crumbs;
			if ( count( $normalCats ) > 0 ) {
				$categoryOutput .= $catList;
			}
		}
		$categoryOutput .= '<li id="bc-pagetitle">' . $page . '</li></ul>';

		return [ 'categories' => $categoryOutput, 'count' => $count ];
	}

	/**
	 * Make an extra edit link for the page header, or a refresh link for special pages
	 * TODO implement?
	 *
	 * @return string HTML
	 */
	private function getSpareEditLink() {
		return '';
	}

	/**
	 * Hideous hack using the stuff from the hook to make a new ToC
	 * Still needs to be made to work on preview/whatever; should be querying something less dumb
	 *
	 * @return string HTML
	 */
	private function getToC() {
		global $wgBlueSkyTOC;

		$tocHTML = '';
		if ( is_array( $wgBlueSkyTOC ) && count( $wgBlueSkyTOC ) > 0 ) {
			if ( count( $wgBlueSkyTOC ) > 6 ) {
				$tocHTML .= Html::openElement(
					'div',
					[ 'class' => 'toc-long' ]
				) ;
			} else {
				$tocHTML .= Html::openElement(
					'div',
					[ 'class' => 'toc-short' ]
				);
			}
			$i = 0;
			foreach ( $wgBlueSkyTOC as $tocpart ) {
				$class = "toclevel-{$tocpart['toclevel']}";
				$href = "#{$tocpart['anchor']}";
				$tocHTML .= Html::rawElement(
					'span',
					[ 'class' => $class ],
					Html::rawElement(
						'a',
						[
							'href' => $href,
							'data-to' => $href,
							'data-numid' => $i
						],
						Html::element(
							'span',
							[ 'class' => 'toc-square' ]
						) . $tocpart['line']
					)
				);
				$i++;
			}
			$tocHTML .= Html::closeElement( 'div' );
		}

		return $tocHTML;
	}
}
