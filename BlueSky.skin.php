<?php
/**
 * BlueSky skin
 *
 * @file
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

/**
 * Inherit main code from SkinTemplate, set the CSS and template filter.
 *
 * @ingroup Skins
 */
class SkinBlueSky extends SkinTemplate {

	public $skinname = 'bluesky', $stylename = 'bluesky',
		$template = 'BlueSkyTemplate', $useHeadElement = true;

	public $mSidebarWidgets = array();
	public $mSidebarTopWidgets = array();

	/**
	 * Basically just loads the skin's JavaScript via ResourceLoader.
	 *
	 * @param OutputPage $out
	 */
	public function initPage( OutputPage $out ) {
		parent::initPage( $out );

		global $wgHooks;
		// Add a required class to the <body> element
		$wgHooks['OutputPageBodyAttributes'][] = function( $out, $sk, &$bodyAttrs ) {
			if ( $sk->getUser()->isLoggedIn() ) {
				$bodyAttrs['class'] .= ' loggedin';
			} else {
				$bodyAttrs['class'] .= ' loggedout';
			}
			return true;
		};

		// Load JavaScript via ResourceLoader
		$out->addModules( 'skins.bluesky.js' );
	}

	/**
	 * Load the skin's CSS via ResourceLoader.
	 *
	 * @param OutputPage $out
	 */
	function setupSkinUserCss( OutputPage $out ) {
		global $wgVersion, $wgResourceModules;

		parent::setupSkinUserCss( $out );

		$baseCSSmodules = array( 'skins.bluesky' );
		// Pick the correct external links module...the difference between
		// 1.23 and 1.24 versions is in the capitalization of the "MonoBook" name
		// (lowercase for 1.23 and older, CamelCase for 1.24 and newer)
		if ( version_compare( $wgVersion, '1.23c', '<' ) ) {
			$baseCSSmodules[] = 'skins.bluesky.externallinks.123';
		} elseif (
			isset( $wgResourceModules['mediawiki.skinning.content.externallinks'] ) &&
			$wgResourceModules['mediawiki.skinning.content.externallinks']
		)
		{
			// Let's hope that https://gerrit.wikimedia.org/r/#/c/143173/ makes
			// it to the 1.24 release, but in case if not, there's a final fallback
			// in this if-else loop for such a case (1.24 w/o this skinning module)
			$baseCSSmodules[] = 'mediawiki.skinning.content.externallinks';
		} else {
			$baseCSSmodules[] = 'skins.bluesky.externallinks.124';
		}

		$modules = array();
		$title = $this->getTitle();
		$request = $this->getRequest();
		$action = $request->getVal( 'action', 'view' );

		if ( $title->isMainPage() ) {
			$baseCSSmodules[] = 'zzzskins.bluesky.mainpage';
		}

		// Add base CSS (i.e. no themes or ugly hacks) via ResourceLoader
		$out->addModuleStyles( $baseCSSmodules );

		// Page action specific hacks
		switch ( $action ) {
			case 'delete':
				$modules[] = 'skins.bluesky.hacks.action.delete';
				break;
			case 'edit':
			case 'submit': // action=edit after previewing etc.
				$modules[] = 'skins.bluesky.hacks.action.edit';
				break;
			case 'history':
				$modules[] = 'skins.bluesky.hacks.action.history';
				break;
			case 'protect':
				$modules[] = 'skins.bluesky.hacks.action.protect';
				break;
			default:
				break;
		}

		// Namespace (and/or page) specific hacks
		if ( $title->inNamespace( NS_FILE ) ) {
			$modules[] = 'skins.bluesky.hacks.filepage';
		} elseif ( $title->inNamespace( NS_SPECIAL ) ) {
			switch ( strtolower( $title->getDBkey() ) ) {
				case 'log':
					$modules[] = 'skins.bluesky.hacks.special.log';
					break;
				case 'movepage':
					$modules[] = 'skins.bluesky.hacks.special.movepage';
					break;
				case 'recentchanges':
					$modules[] = 'skins.bluesky.hacks.special.recentchanges';
					break;
				case 'undelete':
					$modules[] = 'skins.bluesky.hacks.special.undelete';
					break;
				case 'watchlist':
					$modules[] = 'skins.bluesky.hacks.special.watchlist';
					break;
				default:
					break;
			}
		}

		// Finally output all the modules!
		$out->addModuleStyles( $modules );

		// Load the CSS for a theme if there is usetheme parameter in the URL
		// or if $wgDefaultTheme is something else than the global default when
		// [[mw:Extension:Theme]] isn't installed; otherwise let the Theme ext.
		// handle this
		if ( !function_exists( 'wfDisplayTheme' ) ) {
			global $wgDefaultTheme, $wgResourceModules;

			$theme = $out->getRequest()->getVal( 'usetheme', false );

			$themeModule = 'themeloader.skins.bluesky.blue';
			// The 'themeloader.' prefix is a hack around
			// https://bugzilla.wikimedia.org/show_bug.cgi?id=66508
			if ( $theme && isset( $wgResourceModules['themeloader.skins.bluesky.' . $theme] ) ) {
				$themeModule = 'themeloader.skins.bluesky.' . $theme;
			} elseif ( isset( $wgDefaultTheme ) && $wgDefaultTheme != 'default' ) {
				$themeModule = 'themeloader.skins.bluesky.' . $wgDefaultTheme;
			} elseif (
				isset( $wgDefaultTheme ) && in_array( $wgDefaultTheme, array( 'blue', 'default' ) ) ||
				!isset( $wgDefaultTheme )
			)
			{
				$themeModule = 'themeloader.skins.bluesky.blue';
			}
			$out->addModuleStyles( $themeModule );
		} else {
			// Ensure that something is output even when the Theme extension is
			// installed. It overrides this later on anyway.
			$out->addModuleStyles( 'themeloader.skins.bluesky.blue' );
		}
	}

	/**
	 * Add a "widget" to the output, or more accurately, wrap given HTML in
	 * a div that has class="sidebox" and any and all other specific classes
	 * and store the result in the class member variable mSidebarWidgets.
	 *
	 * @param string $html HTML to output
	 * @param string $class Additional HTML class(es) as a string, if any
	 */
	function addWidget( $html, $class = '' ) {
		$class = htmlspecialchars( $class ); // Healthy paranoia, just in case.
		$display = Html::rawElement( 'div',
			array( 'class' => array( 'sidebox', $class ) ), $html );

		array_push( $this->mSidebarWidgets, $display );
		return;
	}

	/**
	 * If page counters are *not* disabled, this function gets and returns the
	 * internationalized string stating how many times a page has been viewed.
	 * If page counters are disabled or the current thing isn't a valid article,
	 * or if we're viewing a diff, this function returns an empty string.
	 *
	 * @return string
	 */
	function pageStats() {
		global $wgDisableCounters;

		$context = $this->getSkin()->getContext();
		$request = $this->getRequest();
		$oldid = $request->getVal( 'oldid' );
		$diff = $request->getVal( 'diff' );

		if ( !$this->getOutput()->isArticle() ) {
			return '';
		}

		if ( isset( $oldid ) || isset( $diff ) ) {
			return '';
		}

		if ( !$context->canUseWikiPage() ) {
			return '';
		}

		$s = '';
		if ( !$wgDisableCounters ) {
			$count = $this->getLanguage()->formatNum( $context->getWikiPage()->getCount() );
			if ( $count ) {
				if ( $this->getTitle()->inNamespace( NS_USER ) ) {
					$s = $this->msg( 'viewcountuser', $count )->text();
				} else {
					$s = $this->msg( 'viewcount', $count )->text();
				}
			}
		}

		return $s;
	}

	/**
	 * User links feature: users can get a list of their own links by specifying
	 * a list in User:username/Mylinks
	 *
	 * @return string
	 */
	function getUserLinks() {
		global $wgParser;

		$ret = '';
		$user = $this->getUser();

		// This feature is available only for registered users.
		if ( !$user->isLoggedIn() ) {
			return $ret;
		}

		$t = Title::makeTitle( NS_USER, $user->getName() . '/Mylinks' );
		if ( $t->exists() ) {
			$r = Revision::newFromTitle( $t );
			$text = $r->getText();

			if ( $text != '' ) {
				$ret = '<h3>' . $this->msg( 'bluesky-mylinks' )->escaped() . '</h3>';
				$ret .= '<div id="my_links_list">';
				$options = new ParserOptions();
				$output = $wgParser->parse( $text, $this->getTitle(), $options );
				$ret .= $output->getText();
				$ret .= '</div>';
			}
		}

		return $ret;
	}

	static function getGalleryImage( $title, $width, $height, $skipParser = false ) {
		global $wgMemc, $wgLanguageCode;

		$cachekey = wfMemcKey( 'gallery1', $title->getArticleID(), $width, $height );
		$val = $wgMemc->get( $cachekey );
		if ( $val ) {
			return $val;
		}

		if ( ( $title->getNamespace() == NS_MAIN ) || ( $title->getNamespace() == NS_CATEGORY ) ) {
			if ( $title->getNamespace() == NS_MAIN ) {
				$file = Wikitext::getTitleImage( $title, $skipParser );

				if ( $file && isset( $file ) ) {
					// need to figure out what size it will actually be able to create
					// and put in that info. ImageMagick gives prefence to width, so
					// we need to see if it's a landscape image and adjust the sizes
					// accordingly
					$sourceWidth = $file->getWidth();
					$sourceHeight = $file->getHeight();
					$heightPreference = false;
					if ( $width / $height < $sourceWidth / $sourceHeight ) {
						// desired image is portrait
						$heightPreference = true;
					}
					$thumb = $file->getThumbnail( $width, $height, true, true, $heightPreference );
					if ( $thumb instanceof MediaTransformError ) {
						// we got problems!
						$thumbDump = print_r( $thumb, true );
						wfDebug( "problem getting thumb for article '{$title->getText()}' of size {$width}x{$height}, image file: {$file->getTitle()->getText()}, path: {$file->getPath()}, thumb: {$thumbDump}\n" );
					} else {
						$wgMemc->set( $cachekey, $thumb->getUrl(), 2 * 3600 ); // 2 hours
						return $thumb->getUrl();
					}
				}
			}

			$catmap = Categoryhelper::getIconMap();

			// if page is a top category itself otherwise get top
			if ( isset( $catmap[urldecode( $title->getPartialURL() )] ) ) {
				$cat = urldecode( $title->getPartialURL() );
			} else {
				$cat = Categoryhelper::getTopCategory( $title );

				// INTL: Get the partial URL for the top category if it exists
				// For some reason only the english site returns the partial
				// URL for getTopCategory
				if ( isset( $cat ) && $wgLanguageCode != 'en' ) {
					$title = Title::newFromText( $cat );
					if ( $title ) {
						$cat = $title->getPartialURL();
					}
				}
			}

			if ( isset( $catmap[$cat] ) ) {
				$image = Title::newFromText( $catmap[$cat] );
				$file = wfFindFile( $image, false );
				if ( $file ) {
					$sourceWidth = $file->getWidth();
					$sourceHeight = $file->getHeight();
					$heightPreference = false;
					if ( $width / $height < $sourceWidth / $sourceHeight ) {
						// desired image is portrait
						$heightPreference = true;
					}
					$thumb = $file->getThumbnail( $width, $height, true, true, $heightPreference );
					if ( $thumb ) {
						$wgMemc->set( $cachekey, $thumb->getUrl(), 2 * 3600 ); // 2 hours
						return $thumb->getUrl();
					}
				}
			} else {
				$image = Title::makeTitle( NS_FILE, 'Book_266.png' );
				$file = wfFindFile( $image, false );
				if ( !$file ) {
					$file = wfFindFile( 'Book_266.png' );
				}
				$sourceWidth = $file->getWidth();
				$sourceHeight = $file->getHeight();
				$heightPreference = false;
				if ( $width / $height < $sourceWidth / $sourceHeight ) {
					// desired image is portrait
					$heightPreference = true;
				}
				$thumb = $file->getThumbnail( $width, $height, true, true, $heightPreference );
				if ( $thumb ) {
					$wgMemc->set( $cachekey, $thumb->getUrl(), 2 * 3600 ); // 2 hours
					return $thumb->getUrl();
				}
			}
		}
	}

	function featuredArticlesLineWide( $t ) {
		$data = self::featuredArticlesAttrs( $t, $t->getText(), 103, 80 );
		$html = "<td>
				<div>
					<a href='{$data['url']}' class='rounders2 rounders2_tl rounders2_white'>
						<img src='{$data['img']}' alt='' width='103' height='80' class='rounders2_img' />
					</a>
					{$data['link']}
				</div>
			</td>";

		return $html;
	}

	static function getArticleThumb( &$t, $width, $height ) {
		global $wgContLang, $wgLanguageCode;
		$html = '';
		$data = self::featuredArticlesAttrs( $t, $t->getText(), $width, $height );
		$articleName = $t->getText();
		if ( $wgLanguageCode == 'zh' ) {
			$articleName = $wgContLang->convert( $articleName );
		}
		$html .= "<div class='thumbnail' style='width:{$width}px; height:{$height}px;'><a href='{$data['url']}'><img src='{$data['img']}' alt='' /><div class='text'><p>" . wfMessage( 'Howto', '' )->text() . "<br /><span>{$articleName}</span></p></div></a></div>";

		return $html;
	}

	private static function featuredArticlesAttrs( $title, $msg, $dimx = 44, $dimy = 33 ) {
		$link = Linker::linkKnown( $title, $msg );
		$img = self::getGalleryImage( $title, $dimx, $dimy );
		return array(
			'url' => $title->getLocalURL(),
			'img' => $img,
			'link' => $link,
			'text' => $msg,
		);
	}

	/**
	 * @param Title|array $data
	 * @return string HTML
	 */
	function featuredArticlesRow( $data ) {
		if ( !is_array( $data ) ) { // $data is actually a Title obj
			$data = self::featuredArticlesAttrs( $data, $data->getText() );
		}
		$html = "<tr>
					<td class='thumb'>
						<a href='{$data['url']}'><img alt='' src='{$data['img']}' /></a>
					</td>
					<td>{$data['link']}</td>
				</tr>\n";
		return $html;
	}

	/**
	 * Render the array as a series of links.
	 * Overloaded from the Skin class.
	 *
	 * @param array $tree Categories tree returned by Title::getParentCategoryTree
	 * @return string Separated by &gt;, terminate with "\n"
	 */
	function drawCategoryBrowser( $tree ) {
		$return = '';
		//$viewMode = WikihowCategoryViewer::getViewModeArray( $this->getContext() );
		foreach ( $tree as $element => $parent ) {
			$start = ' ' . self::BREADCRUMB_SEPARATOR;

			$eltitle = Title::newFromText( $element );
			if ( empty( $parent ) ) {
				# element start a new list
				$return .= "\n";
			} else {
				# grab the others elements
				$return .= $this->drawCategoryBrowser( $parent );
			}
			# add our current element to the list
			$return .= "<li>$start " . Skin::link(
				$eltitle,
				$eltitle->getText()/*,
				array(),
				$viewMode*/
			) . '</li>';
		}
		return $return;
	}

	const BREADCRUMB_SEPARATOR = '&raquo;';

	/**
	 * Copied from /extensions/wikihow/Categoryhelper.body.php, 2014-05-22 release
	 * and made non-static to remove dependency on a very ugly global
	 */
	private function getCurrentParentCategoryTree() {
		global $wgMemc;

		$title = $this->getTitle();

		$cachekey = wfMemcKey( 'parentcattree', $title->getArticleId() );
		$cats = $wgMemc->get( $cachekey );

		if ( $cats ) {
			return $cats;
		}

		$cats = $title->getParentCategoryTree();

		$wgMemc->set( $cachekey, $cats );

		return $cats;
	}

	function getCategoryLinks(/* $usebrowser */) {
		global $wgContLang;

		$out = $this->getOutput();
		$categoryLinks = $out->getCategoryLinks();
		$usebrowser = false; // @todo FIXME

		if ( !$usebrowser && empty( $categoryLinks['normal'] ) ) {
			return '';
		}

		// Use Unicode bidi embedding override characters,
		// to make sure links don't smash each other up in ugly ways.
		$dir = $wgContLang->isRTL() ? 'rtl' : 'ltr';
		$embed = "<span dir='$dir'>";
		$pop = '</span>';

		if ( empty( $categoryLinks['normal'] ) ) {
			$t = $embed . '' . $pop;
		} else {
			$t = $embed . implode( "{$pop}, {$embed}", $categoryLinks['normal'] ) . $pop;
		}
		if ( !$usebrowser ) {
			return $t;
		}

		$mainPageObj = Title::newMainPage();

		$sep = self::BREADCRUMB_SEPARATOR;

		//$viewMode = WikihowCategoryViewer::getViewModeArray( $this->getContext() );
		$categories = Linker::link(
			SpecialPage::getTitleFor( 'Categories' ),
			$this->msg( 'categories' )->text()/*,
			array(),
			$viewMode*/
		);
		$s = '<li class="home">' . Linker::link(
			$mainPageObj,
			$this->msg( 'bluesky-home' )->text()
		) . "</li> <li>$sep $categories</li>";

		# optional 'dmoz-like' category browser. Will be shown under the list
		# of categories an article belong to
		if ( $usebrowser ) {
			$s .= ' ';

			# get a big array of the parents tree
			$parentTree = $this->getCurrentParentCategoryTree();
			if ( is_array( $parentTree ) ) {
				$parentTree = array_reverse( $parentTree );
			} else {
				return $s;
			}
			# Skin object passed by reference cause it can not be
			# accessed under the method subfunction drawCategoryBrowser
			$tempout = explode( "\n", $this->drawCategoryBrowser( $parentTree, $this ) );
			$newarray = array();
			foreach ( $tempout as $t ) {
				if ( trim( $t ) != '' ) {
					$newarray[] = $t;
				}
			}
			$tempout = $newarray;

			asort( $tempout );
			$olds = $s;
			if ( $tempout ) {
				$s .= $tempout[0]; // this usually works
			}

			if ( strpos( $s, "/Category:WikiHow" ) !== false
				|| strpos( $s, "/Category:Featured" ) !== false
				|| strpos( $s, "/Category:Nomination" ) !== false
			) {
				for ( $i = 1; $i <= sizeof( $tempout ); $i++ ) {
					if ( strpos( $tempout[$i], "/Category:WikiHow" ) === false
					&& strpos( $tempout[$i], "/Category:Featured" ) == false
					&& strpos( $tempout[$i], "/Category:Nomination" ) == false
					) {
						$s = $olds;
						$s .= $tempout[$i];
						break;
					}
				}
			}
		}

		return $s;
	}

	/**
	 * Should the <h1> tag be suppressed or not?
	 *
	 * @todo This should support social tools' $wgSupressPageTitle [sic], but
	 * since core doesn't define that, it's *technically* a register_globals
	 * vuln...as of 1.24+ we can safely /ignore register_globals, but until
	 * then, this will have to do.
	 *
	 * @return bool
	 */
	function suppressH1Tag() {
		$title = $this->getTitle();

		if ( $title->isMainPage() ) {
			return true;
		}

		if ( $title->isSpecial( 'Userlogin' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if user has editing or login cookies set
	 *
	 * Copied from wikiHow's /includes/User.php, 2014-05-22 release; originally
	 * called just "hasCookies"; modified to take $wgCookiePrefix into account.
	 *
	 * @return bool
	 */
	private function userHasCookies() {
		global $wgCookiePrefix;
		foreach ( $_COOKIE as $cookie => $val ) {
			if ( strpos( $cookie, $wgCookiePrefix ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Fetches the site notice and the surrounding div element, depending on
	 * various factors, such as:
	 * -whether the site's database has been locked or not
	 * -whether the user has cookies enabled or not
	 * -whether the user is logged in or not, and
	 * -whether there is a site notice or not
	 *
	 * @return string A div with id="site_notice" if there's a site notice,
	 *                otherwise returns an empty string
	 */
	function getSiteNotice() {
		global $wgReadOnly;

		$hasCookies = $this->userHasCookies();
		if ( $hasCookies && $wgReadOnly ) {
			$siteNotice = $wgReadOnly;
		} elseif ( !$hasCookies && $this->getRequest()->getVal( 'c' ) == 't' ) {
			$siteNotice = $this->msg( 'sitenotice_cachedpage' )->parse();
		} elseif ( !$this->getUser()->isAnon() ) {
			if ( $this->msg( 'sitenotice_loggedin' )->isDisabled() ) {
				return '';
			}
			$siteNotice = $this->msg( 'sitenotice_loggedin' )->parse();
		} else {
			if ( $this->msg( 'sitenotice' )->isDisabled() ) {
				return '';
			}
			$siteNotice = $this->msg( 'sitenotice' )->parse();
		}

		$x = '<a href="#" id="site_notice_x"></a>';

		// format here so there's no logic later
		$count = 0;
		$siteNotice = preg_replace( '@^\s*(<p>\s*)?important\s+@i', '', $siteNotice, 1, $count );
		$colorClassName = $count == 0 ? 'notice_bgcolor' : 'notice_bgcolor_important';

		$siteNotice = "<div id='site_notice' class='sidebox $colorClassName'>$x$siteNotice</div>";

		return $siteNotice;
	}

	/**
	 * Calls the MobileWikihow class to determine whether or
	 * not a browser's User-Agent string is that of a mobile browser.
	 *
	 * @return bool
	 */
	static function isUserAgentMobile() {
		if ( class_exists( 'MobileWikihow' ) ) {
			return MobileWikihow::isUserAgentMobile();
		} else {
			return false;
		}
	}

	/**
	 * Calls the WikihowCSSDisplay class to determine whether or
	 * not to display a "special" background.
	 *
	 * @return bool
	 */
	static function isSpecialBackground() {
		if ( class_exists( 'WikihowCSSDisplay' ) ) {
			return WikihowCSSDisplay::isSpecialBackground();
		} else {
			return false;
		}
	}

	/**
	 * Calls any hooks in place to see if a module has requested that the
	 * right rail on the site shouldn't be displayed.
	 *
	 * @return bool
	 */
	static function showSideBar() {
		$result = true;
		wfRunHooks( 'ShowSideBar', array( &$result ) );
		return $result;
	}

	/**
	 * Calls any hooks in place to see if a module has requested that the
	 * bread crumb (category) links at the top of the article shouldn't
	 * be displayed.
	 *
	 * @todo This is *very* dirty since it uses The Global That Shall Not Be Named
	 *
	 * @return bool
	 */
	static function showBreadCrumbs() {
		global $wgTitle, $wgRequest;
		$result = true;
		wfRunHooks( 'ShowBreadCrumbs', array( &$result ) );
		if ( $result ) {
			$namespace = $wgTitle ? $wgTitle->getNamespace() : NS_MAIN;
			$action = $wgRequest ? $wgRequest->getVal( 'action' ) : '';
			$goodAction = empty( $action ) || $action == 'view';
			if ( !in_array( $namespace, array( NS_CATEGORY, NS_MAIN, NS_SPECIAL ) ) || !$goodAction ) {
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Calls any hooks in place to see if a module has requested that the
	 * right rail on the site shouldn't be displayed.
	 *
	 * @todo This is *very* dirty since it uses The Global That Shall Not Be Named
	 *
	 * @return bool
	 */
	static function showGrayContainer() {
		global $wgTitle, $wgRequest;

		$result = true;
		wfRunHooks( 'ShowGrayContainer', array( &$result ) );

		$action = $wgRequest ? $wgRequest->getVal( 'action' ) : '';
		$namespace = $wgTitle->getNamespace();

		if ( $wgTitle->exists() || $namespace == NS_USER ) {
			if (
				in_array( $namespace, array( NS_USER, NS_FILE, NS_CATEGORY ) ) ||
				( $namespace == NS_MAIN ) && ( $action == 'edit' || $action == 'submit2' )
			)
			{
				$result = false;
			}
		}
		return $result;
	}

	/**
	 * Get and return an array of content action tabs (i.e. "page", "edit", etc.).
	 *
	 * @param bool $showArticleTabs Should we render the content generated by
	 *                              this function or just leave it up to the
	 *                              pageTabs hook?
	 * @return array
	 */
	function getTabsArray( $showArticleTabs ) {
		$request = $this->getRequest();
		$skin = $this->getSkin();
		$title = $skin->getTitle();
		$user = $this->getUser();

		$action = $request->getVal( 'action', 'view' );
		if ( $request->getVal( 'diff' ) ) {
			$action = 'diff';
		}

		$tabs = array();

		wfRunHooks( 'pageTabs', array( &$tabs ) );

		if ( count( $tabs ) > 0 ) {
			return $tabs;
		}

		if ( !$showArticleTabs ) {
			return;
		}

		$articleTab = new stdClass;
		$editTab = new stdClass;
		$talkTab = new stdClass;
		$historyTab = new stdClass;
		$adminTab = new stdClass;
		$admin1 = new stdClass;
		$admin2 = new stdClass;
		$admin3 = new stdClass;

		// article
		if ( $title->getNamespace() != NS_CATEGORY ) {
			$articleTab->href = $title->isTalkPage() ? $title->getSubjectPage()->getFullURL() : $title->getFullURL();
			$articleTab->text = $title->getSubjectPage()->getNamespace() == NS_USER ? $this->msg( 'user' )->plain() : $this->msg( 'article' )->plain();
			$articleTab->class = ( !MWNamespace::isTalk( $title->getNamespace() ) && $action != 'edit' && $action != 'history' ) ? 'on' : '';
			$articleTab->id = 'tab_article';
			$tabs[] = $articleTab;
		}

		// edit
		if (
			$title->getNamespace() != NS_CATEGORY &&
			(
				!in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK, NS_FILE ) ) ||
				$action == 'edit' || $user->getId() > 0
			)
		)
		{
			$editTab->href = $title->getLocalURL( $skin->editUrlOptions() );
			$editTab->text = $this->msg( 'edit' )->plain();
			$editTab->class = ( $action == 'edit' ) ? 'on' : '';
			$editTab->id = 'tab_edit';
			$tabs[] = $editTab;
		}

		// talk
		if ( $title->getNamespace() != NS_CATEGORY ) {
			if ( $action == 'view' && MWNamespace::isTalk( $title->getNamespace() ) ) {
				$talklink = '#postcomment';
			} else {
				$talklink = $title->getTalkPage()->getLocalURL();
			}
			if ( in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK ) ) ) {
				$msg = $this->msg( 'talk' )->plain();
			} else {
				$msg = $this->msg( 'bluesky-discuss' )->plain();
			}
			$talkTab->href = $talklink;
			$talkTab->text = $msg;
			$talkTab->class = ( $title->isTalkPage() && $action != 'edit' && $action != 'history' ) ? 'on' : '';
			$talkTab->id = 'tab_discuss';
			$tabs[] = $talkTab;
		}

		// history
		if ( !$user->isAnon() && $title->getNamespace() != NS_CATEGORY ) {
			$historyTab->href = $title->getLocalURL( 'action=history' );
			$historyTab->text = $this->msg( 'history' )->plain();
			$historyTab->class = ( $action == 'history' ) ? 'on' : '';
			$historyTab->id = 'tab_history';
			$tabs[] = $historyTab;
		}

		// admin
		if ( $user->isAllowedAny( 'delete', 'protect', 'unprotect', 'move' ) && $title->userCan( 'delete' ) ) {
			$adminTab->href = '#';
			$adminTab->text = $this->msg( 'bluesky-admin-menu' )->plain();
			$adminTab->class = '';
			$adminTab->id = 'tab_admin';
			$adminTab->hasSubMenu = true;
			$adminTab->subMenuName = 'AdminOptions';

			$adminTab->subMenu = array();
			$admin1->href = $title->getLocalURL( 'action=protect' );
			$admin1->text = !$title->isProtected() ? $this->msg( 'protect' )->plain() : $this->msg( 'unprotect' )->plain();
			$adminTab->subMenu[] = $admin1;
			if ( $title->getNamespace() != NS_FILE ) {
				$admin2->href = SpecialPage::getTitleFor( 'Movepage', $title )->getLocalURL();
			} else {
				$admin2->href = SpecialPage::getTitleFor( 'Movepage' )->getLocalURL( array(
					'target' => $title->getPrefixedURL(),
					'_' => time()
				) );
			}
			$admin2->text = $this->msg( 'move' )->plain();
			$adminTab->subMenu[] = $admin2;
			$admin3->href = $title->getLocalURL( 'action=delete' );
			$admin3->text = $this->msg( 'delete' )->plain();
			$adminTab->subMenu[] = $admin3;

			$tabs[] = $adminTab;
		}

		return $tabs;
	}

	/**
	 * Turns an array of content action tabs (generated by getTabsArray()) into
	 * HTML.
	 *
	 * @return string HTML
	 */
	function getTabsHtml( $tabs ) {
		$html = '';

		if ( count( $tabs ) > 0 ) {
			$html .= '<div id="article_tabs">';
			$html .= '<ul id="tabs">';

			foreach ( $tabs as $tab ) {
				$attributes = '';

				foreach ( $tab as $attribute => $value ) {
					if ( $attribute != 'text' && !is_array( $value ) ) {
						$attributes .= " {$attribute}='{$value}'";
					}
				}

				$html .= "<li><a {$attributes}>{$tab->text}</a>";
				if ( isset( $tab->hasSubMenu ) && $tab->hasSubMenu ) {
					$html .= '<span class="admin_arrow"></span>';
					$html .= "<div id=\"{$tab->subMenuName}\" class=\"menu\">";

					foreach ( $tab->subMenu as $subTab ) {
						$html .= "<a href=\"{$subTab->href}\">{$subTab->text}</a>";
					}
					$html .= '</div>';
				}

				$html .= '</li>';
			}

			$html .= '</ul></div>';
		}

		return $html;
	}

	/**
	 * Generates the four navigation tabs, which are shown on the fixed header.
	 * Currently the tabs and their contents are basically hard-coded, but maybe
	 * eventually those are editable without touching this file.
	 *
	 * @return array
	 */
	function genNavigationTabs() {
		$sk = $this->getSkin();
		$title = $sk->getTitle();
		$user = $this->getUser();

		$isLoggedIn = $user->getID() > 0;

		$navTabs = array(
			'nav_messages' => array(
				'menu' => $sk->getHeaderMenu( 'messages' ),
				'link' => '#',
				'text' => $this->msg( 'bluesky-navbar-messages' )->plain()
			),
			'nav_profile' => array(
				'menu' => $sk->getHeaderMenu( 'profile' ),
				'link' => $isLoggedIn ? $user->getUserPage()->getLocalURL() : '#',
				'text' => $isLoggedIn ? strtoupper( $this->msg( 'bluesky-navbar-profile' )->plain() ) : strtoupper( $this->msg( 'login' )->plain() )
			),
			'nav_explore' => array(
				'menu' => $sk->getHeaderMenu( 'explore' ),
				'link' => '#',
				'text' => $this->msg( 'bluesky-navbar-explore' )->plain()
			),
			'nav_help' => array(
				'menu' => $sk->getHeaderMenu( 'help' ),
				'link' => '#',
				'text' => $this->msg( 'bluesky-navbar-help' )->plain()
			)
		);

		if ( $title->userCan( 'edit' ) ) {
			$editPage = $title->getLocalURL( $sk->editUrlOptions() );
			$navTabs['nav_edit'] = array(
				'menu' => $sk->getHeaderMenu( 'edit' ),
				'link' => htmlspecialchars( $editPage ),
				'text' => strtoupper( $this->msg( 'edit' )->plain() )
			);
		}

		return $navTabs;
	}

	/**
	 * Get the header menu items (links), which depend on things like the user's
	 * login state, language, etc.
	 *
	 * @param string $menu What part to get? Valid values are 'edit', 'explore',
	 *                      'help', 'messages' and 'profile'
	 * @return string HTML
	 */
	function getHeaderMenu( $menu ) {
		global $wgForumLink;

		$html = '';
		$menu_css = 'menu';
		$sk = $this->getSkin();
		$title = $this->getTitle();
		$user = $this->getUser();
		$isLoggedIn = $user->getID() > 0;

		switch ( $menu ) {
			case 'edit':
				$html = Linker::link(
					$title,
					$this->msg( 'bluesky-edit-this-article' )->plain(),
					array(),
					$sk->editUrlOptions()
				);
				if ( !$isLoggedIn ) {
					break;
				}
				$html .= Linker::link(
					SpecialPage::getTitleFor( 'Whatlinkshere', $title->getPrefixedURL() ),
					$this->msg( 'whatlinkshere' )->plain(),
					array(
						'title' => Linker::titleAttrib( 't-whatlinkshere', 'withaccess' ),
						'accesskey' => Linker::accesskey( 't-whatlinkshere' )
					)
				);
				break;
			case 'profile':
				// @todo Theoretically this should call $this->getPersonalToolsList(),
				// but since that generates <a>s inside <li>s, it won't work for
				// our particular use case :-( As a result of not using that
				// method, the PersonalUrls hook never gets called for this skin
				if ( $isLoggedIn ) {
					$myNotifications = '';
					if ( class_exists( 'EchoEvent' ) ) {
						$myNotifications = Linker::link(
							SpecialPage::getTitleFor( 'Notifications' ),
							$this->msg( 'bluesky-my-notifications' )->plain()
						);
					}
					$html = Linker::link( Title::makeTitle( NS_SPECIAL, 'Mytalk', 'post' ), $this->msg( 'mytalk' )->plain() ) .
							Linker::link( SpecialPage::getTitleFor( 'Mypage' ), $this->msg( 'bluesky-my-page' )->plain() ) .
							$myNotifications .
							Linker::link( SpecialPage::getTitleFor( 'Watchlist' ), $this->msg( 'watchlist' )->plain() ) .
							#Linker::link( Title::makeTitle( NS_SPECIAL, 'Drafts' ), $this->msg( 'mydrafts' )->text() ) .
							Linker::link( SpecialPage::getTitleFor( 'Mycontributions' ), $this->msg( 'mycontris' )->plain() ) .
							Linker::link( SpecialPage::getTitleFor( 'Preferences' ), $this->msg( 'mypreferences' )->plain() ) .
							Linker::link( SpecialPage::getTitleFor( 'Userlogout' ), $this->msg( 'logout' )->plain() );
				} else {
					$html = $this->getUserLoginBox();
					$menu_css = 'menu_login';
				}
				break;
			case 'explore':
				$html = Linker::link(
					Title::newFromText( $this->msg( 'portal-url' )->inContentLanguage()->text() ),
					$this->msg( 'portal' )->plain()
				);
				if ( $isLoggedIn && isset( $wgForumLink ) ) {
					$html .= "<a href=\"$wgForumLink\">" . $this->msg( 'forums' )->text() . '</a>';
				}
				$html .= Linker::link(
					SpecialPage::getTitleFor( 'Randompage' ),
					$this->msg( 'randompage' )->plain()
				);
				if ( !$isLoggedIn ) {
					$html .= Linker::link(
						Title::newFromText( $this->msg( 'aboutpage' )->text() ),
						$this->msg( 'bluesky-navmenu-aboutus' )->text()
					);
				}
				$html .= Linker::link( SpecialPage::getTitleFor( 'Categories' ), $this->msg( 'bluesky-navmenu-categories' )->text() ) .
						Linker::link( SpecialPage::getTitleFor( 'Recentchanges' ), $this->msg( 'recentchanges' )->text() );
				if ( $isLoggedIn ) {
					$html .= Linker::link(
						SpecialPage::getTitleFor( 'Specialpages' ),
						$this->msg( 'specialpages' )->text()
					);
					$html .= Linker::link(
						Title::newFromText( $this->msg( 'helppage' )->text() ),
						$this->msg( 'help' )->text()
					);
				}
				break;
			case 'help':
				$editHelpMsgContents = $this->msg( 'edithelppage' )->text();
				// By default it's an (external) URL, hence not a valid Title.
				// But because MediaWiki is by nature very customizable, someone
				// might've changed it to point to a local page. Tricky!
				if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $editHelpMsgContents ) ) {
					$html = Linker::makeExternalLink(
						$this->msg( 'edithelppage' )->text(),
						$this->msg( 'edithelp' )->plain()
					);
				} else {
					$html = Linker::link(
						Title::newFromText( $this->msg( 'edithelppage' )->text() ),
						$this->msg( 'edithelp' )->plain()
					);
				}

				$html .= Linker::link(
					SpecialPage::getTitleFor( 'Uncategorizedpages' ),
					$this->msg( 'bluesky-categorize' )->plain()
				);
				$html .= Linker::link(
					Title::newFromText( $this->msg( 'bluesky-more-ideas-url' )->text() ),
					$this->msg( 'bluesky-more-ideas' )->plain()
				);
				break;
			case 'messages':
				if ( class_exists( 'EchoEvent' ) && $this->userHasCookies() ) {
					$maxNotesShown = 5;
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

				$menu_css = 'menu_messages';
				break;
		}

		if ( $html ) {
			$html = '<div class="' . $menu_css . '">' . $html . '</div>';
		}

		return $html;
	}

	/**
	 * Get all notifications for the current user.
	 *
	 * @return array array( HTML output, total amount of all notifications, has new User_talk messages? )
	 */
	private function getNotifications() {
		global $wgMemc;

		$user = $this->getUser();
		$memKey = wfMemcKey( 'notification_box_' . $user->getId() );
		$box = $wgMemc->get( $memKey );

		if ( !is_array( $box ) ) {
			$notes = array();

			// Talk messages
			$talkCount = 0;
			if ( $user->getNewtalk() ) {
				$talkCount = $this->getCount( 'user_newtalk' );
				$msg = '<div class="note_row"><div class="note_icon_talk"></div>' .
					Linker::link(
						$user->getTalkPage(),
						$this->msg( 'bluesky-notifications-new-talk' )->numParams( $talkCount )->parse()
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

			$box = array( $notes, $totalCount, $newTalk );
		}

		return $box;
	}

	/**
	 * Fetch the COUNT() of some entries in the given $table.
	 *
	 * @param string $table Database table name
	 * @return int Amount of entries
	 */
	private function getCount( $table ) {
		$user = $this->getUser();

		if ( $user->getId() > 0 ) {
			$field = 'user_id';
			$id = $user->getId();
		} else {
			$field = 'user_ip';
			$id = $user->getName();
		}

		$db = wfGetDB( DB_MASTER );
		$count = $db->selectField(
			$table,
			'COUNT(' . $field . ')',
			array( $field => $id ),
			__METHOD__
		);

		return $count;
	}

	/**
	 * @param array $notes Notification HTML for each notification in an array
	 * @param bool $newTalk Does the current user have new talk page messages?
	 * @return string HTML output
	 */
	private function formatNotifications( $notes, $newTalk ) {
		$html = '';
		$talkPage = $this->getUser()->getTalkPage()->getPrefixedText();

		foreach ( $notes as $note ) {
			$html .= $note;
		}

		if ( $html ) {
			// no line at the top
			$html = preg_replace( '/note_row/', 'note_row first_note_row', $html, 1 );
			if ( !$newTalk ) {
				$html .= '<div class="note_row note_empty">' .
					$this->msg( 'bluesky-notifications-no-talk', $talkPage )->parse() .
					'</div>';
			}
		} else {
			$html = '<div class="note_row note_empty">' .
				$this->msg( 'bluesky-notifications-none', $talkPage )->parse() .
				'</div>';
		}

		return $html;
	}

	/**
	 * This is only available in MediaWiki 1.23+, but...
	 *
	 * @param OutputPage|null $out
	 * @return QuickTemplate
	 */
	protected function prepareQuickTemplate( OutputPage $out = null ) {
		global $wgContLang, $wgHideInterlanguageLinks;
		wfProfileIn( __METHOD__ );

		$tpl = parent::prepareQuickTemplate( $out );
		if ( !$out instanceof OutputPage ) {
			$out = $this->getOutput();
		}

		// Add various meta properties if the ArticleMetaInfo extension is
		// available
		if ( class_exists( 'ArticleMetaInfo' ) ) {
			$description = ArticleMetaInfo::getCurrentTitleMetaDescription();
			if ( $description ) {
				$out->addMeta( 'description', $description );
			}
			$keywords = ArticleMetaInfo::getCurrentTitleMetaKeywords();
			if ( $keywords ) {
				$out->mKeywords = array();
				$out->addMeta( 'keywords', $keywords );
			}

			ArticleMetaInfo::addFacebookMetaProperties( $tpl->data['title'] );
			ArticleMetaInfo::addTwitterMetaProperties();

			ArticleMetaInfo::addFacebookMetaProperties( $tpl->data['title'] );
			ArticleMetaInfo::addTwitterMetaProperties();
		}

		// If the UserPagePolicy extension is installed and we're trying to
		// view a User: page that doesn't match the criteria of a "good user page"
		// (in other words, it's likely spam) and we're not logged in, force the
		// article text to be a generic "Sorry, no such page" and force the
		// correct HTTP headers
		if (
			$this->getTitle()->getNamespace() == NS_USER &&
			$this->getUser()->getId() == 0 &&
			class_exists( 'UserPagePolicy' ) &&
			!UserPagePolicy::isGoodUserPage( $this->getTitle()->getDBkey() )
		)
		{
			$txt = wfMessage( 'noarticletext_user' )->parse();
			$tpl->setRef( 'bodytext', $txt );
			header( 'HTTP/1.1 404 Not Found' );
		}

		// <copypasta type="awful" absolutely="true">
		// The following is copypasted from core /includes/SkinTemplate.php
		// in order to add the section class to #mw-content-text, because that's
		// what it's "supposed" to look like
		$title = $this->getTitle();
		# An ID that includes the actual body text; without categories, contentSub, ...
		$realBodyAttribs = array( 'id' => 'mw-content-text' );

		# Add a mw-content-ltr/rtl class to be able to style based on text direction
		# when the content is different from the UI language, i.e.:
		# not for special pages or file pages AND only when viewing AND if the page exists
		# (or is in MW namespace, because that has default content)
		if ( !in_array( $title->getNamespace(), array( NS_SPECIAL, NS_FILE ) ) &&
			Action::getActionName( $this ) === 'view' &&
			( $title->exists() || $title->getNamespace() == NS_MEDIAWIKI ) ) {
			$pageLang = $title->getPageViewLanguage();
			$realBodyAttribs['lang'] = $pageLang->getHtmlCode();
			$realBodyAttribs['dir'] = $pageLang->getDir();
			$realBodyAttribs['class'] = 'section mw-content-' . $pageLang->getDir();
		}

		$out->mBodytext = Html::rawElement( 'div', $realBodyAttribs, $out->mBodytext );
		$tpl->setRef( 'bodytext', $out->mBodytext );
		// </copypasta>

		// Interlanguage links
		$language_urls = array();
		if ( !$wgHideInterlanguageLinks ) {
			foreach ( $out->getLanguageLinks() as $l ) {
				$tmp = explode( ':', $l, 2 );
				$class = 'interwiki-' . $tmp[0];
				$code = $tmp[0];
				$lTitle = $tmp[1];
				unset( $tmp );
				$nt = Title::newFromText( $l );
				if ( $wgContLang->getLanguageName( $nt->getInterwiki() ) != '' ) {
					$language = $wgContLang->getLanguageName( $nt->getInterwiki() );
				} else {
					$language = $l;
				}
				$language_urls[] = array(
					'code' => $code,
					'href' => $nt->getFullURL(),
					'text' => $lTitle,
					'class' => $class,
					'language' => $language . ': '
				);
			}
		}

		if ( count( $language_urls ) ) {
			$tpl->setRef( 'language_urls', $language_urls );
		} else {
			$tpl->set( 'language_urls', false );
		}

		wfProfileOut( __METHOD__ );

		return $tpl;
	}

	/**
	 * If it's holiday season, get a festive logo instead of the standard one!
	 *
	 * @return string Path to the logo image (during a festive season) or an empty string
	 */
	static function getHolidayLogo() {
		global $wgStylePath;

		// Note 1: you should take into account 24h Varnish page caching when
		//   considering these dates!
		// Note 2: we use full dates for safety rather than figuring out what year
		//   we're in! We just need to change these once a year.
		$holidayLogos = array(
			array(
				'logo' => $wgStylePath . '/BlueSky/resources/images/wikihow_logo_halloween.png',
				'start' => strtotime( 'October 25, 2013 PST' ),
				'end' => strtotime( 'November 1, 2013 PST' ),
			),
		);

		$now = time();

		foreach ( $holidayLogos as $hl ) {
			if ( $hl['start'] <= $now && $now <= $hl['end'] ) {
				return $hl['logo'];
			}
		}

		return '';
	}

	public function getHTMLTitle( $defaultHTMLTitle, $title, $isMainPage ) {
		global $wgSitename;

		$theRealTitle = $this->getTitle();
		$namespace = $theRealTitle->getNamespace();
		$action = $this->getRequest()->getVal( 'action', 'view' );

		$htmlTitle = $defaultHTMLTitle;
		if ( $isMainPage ) {
			$htmlTitle = $wgSitename . ' - ' . $this->msg( 'main_title' )->text();
		} elseif ( $namespace == NS_MAIN && $theRealTitle->exists() && $action == 'view' ) {
			if ( class_exists( 'TitleTests' ) ) {
				$titleTest = TitleTests::newFromTitle( $theRealTitle );
				if ( $titleTest ) {
					$htmlTitle = $titleTest->getTitle();
				}
			} else {
				$howto = $this->msg( 'howto', $title )->text();
				$htmlTitle = $this->msg( 'pagetitle', $howto )->text();
			}
		} elseif ( $namespace == NS_USER || $namespace == NS_USER_TALK ) {
			$username = $theRealTitle->getText();
			$htmlTitle = $this->getLanguage()->getNsText( $namespace ) . ": $username - $wgSitename";
		} elseif ( $namespace == NS_CATEGORY ) {
			$htmlTitle = $this->msg( 'category_title_tag', $theRealTitle->getText() )->text();
		}

		return $htmlTitle;
	}

	/**
	 * Get the login menu box, which contains the form that allows the user to
	 * log in.
	 *
	 * @param bool $isHead If true, certain HTML elements' IDs are suffixed with "_head"
	 */
	private function getUserLoginBox( $isHead = false ) {
		if ( class_exists( 'UserLoginBox' ) ) {
			return UserLoginBox::getLogin( $isHead );
		} else {
			// Bah, we have to reimplement UserLoginBox's logic here.
			$actionURL = SpecialPage::getTitleFor( 'Userlogin' )->getFullURL( array(
				'action' => 'submitlogin',
				'type' => 'login',
				'autoredirect' => urlencode( $this->getTitle()->getPrefixedURL() ),
				'sitelogin' => '1',
				'wpLoginToken' => ( !LoginForm::getLoginToken() ) ? LoginForm::setLoginToken() : LoginForm::getLoginToken()
			) );

			// wikiHow's SSL_LOGIN_DOMAIN constant is not supported intentionally
			// as it was always quite a hack
			// I don't know what would be the proper way to handle this, actually.
			// LoginForm::execute() has some HTTPS-related code, but...I dunno.
			// @todo FIXME in any case

			if ( $isHead ) {
				$headSuffix = '_head';
			} else {
				$headSuffix = '';
			}

			require_once( 'templates/userloginbox.tmpl.php' );
			$template = new UserLoginBoxTemplate;
			$variables = array(
				// Took out the social login stuff for the time being.
				// It works for wikiHow because it's a part of their "platform",
				// but on a (more) vanilla MW installation we can't guarantee
				// the availability of one or more social login extensions, and
				// implementing such an extension or extensions here wouldn't
				// make sense (as opposed to (re)implementing the wH Notifications
				// or UserLoginBox extensions -- those are rather essential parts
				// of this skin).
				'social_buttons' => '',//self::getSocialLogin( $headSuffix ),
				'suffix' => $headSuffix,
				'action_url' => htmlspecialchars( $actionURL ),
			);

			foreach ( $variables as $variable => $value ) {
				$template->set( $variable, $value );
			}

			return $template->getHTML();
		}
	}

	/**
	 * Gets the social login buttons (Facebook Connect & Google+).
	 *
	 * This merely fetches the HTML, styling and JS "back-end" is up to you to
	 * implement.
	 *
	 * @param string $suffix Optional HTML element ID suffix (_head) or empty
	 * @return string HTML
	 */
	public static function getSocialLogin( $suffix = '' ) {
		$html = '<div id="fb_connect' . $suffix . '"><a id="fb_login' . $suffix .
			'" href="#"><span></span></a></div>
				<div id="gplus_connect' . $suffix . '"><a id="gplus_login' . $suffix .
				'" href="#"><span></span></a></div>';

		return $html;
	}

	/**
	 * Output the "Page last edited X days Y hours ago" string for pages in
	 * content namespaces.
	 *
	 * @param Title $title
	 * @return string "edited X ago" string on success, empty string on failure
	 */
	public function getPageLastEdit( Title $title ) {
		global $wgContentNamespaces;

		$msg = '';

		if ( $title->exists() && in_array( $title->getNamespace(), $wgContentNamespaces ) ) {
			// First construct a Revision object from the current Title...
			$revision = Revision::newFromTitle( $title );
			if ( $revision instanceof Revision ) {
				// ...then get its timestamp...
				$timestamp = $revision->getTimestamp();
				// ...turn it into a UNIX timestamp...
				$unixTS = wfTimestamp( TS_UNIX, $timestamp );
				// ..and pass everything to MediaWiki's crazy formatter
				// function.
				$formattedTS = $this->getLanguage()->formatTimePeriod(
					time() - $unixTS,
					array(
						'noabbrevs' => true,
						// There doesn't appear to be an 'avoidhours'; if there
						// were, we'd use it so that this'd match the mockup.
						'avoid' => 'avoidminutes'
					)
				);

				// Get the last editor's username (if any), too
				$author = $revision->getUserText();

				// Pick the correct internationalization message, depending on if
				// the current user is allowed to access the revision's last author's
				// name or not (hey, it could be RevisionDeleted, as Revision::getUserText()'s
				// documentation states)
				if ( $author ) {
					$msg = $this->msg( 'bluesky-page-edited-user', $formattedTS, $author )->parse();
				} else {
					$msg = $this->msg( 'bluesky-page-edited', $formattedTS )->parse();
				}
			}
		}

		return $msg;
	}
}

class BlueSkyTemplate extends BaseTemplate {

	/**
	 * Template filter callback for BlueSky skin.
	 * Takes an associative array of data set from a SkinTemplate-based
	 * class, and a wrapper for MediaWiki's localization database, and
	 * outputs a formatted page.
	 */
	public function execute() {
		global $wgStylePath, $wgSitename, $wgForumLink, $blueSkyTOC;

		$tocHTML = '';
		if ( $blueSkyTOC != '' ) {
			if ( sizeof( $blueSkyTOC ) > 6 ) {
				$tocHTML .= "<div class='toc_long'>";
			} else {
				$tocHTML .= "<div class='toc_short'>";
			}
			$i = 0;
			foreach ( $blueSkyTOC as $tocpart ) {
				$class = "toclevel-{$tocpart['toclevel']}";
				$href = "#{$tocpart['anchor']}";
				$tocHTML .= "<a href='$href' data-to='$href' data-numid='$i' class='$class'><span class='toc_square'></span>{$tocpart['line']}</a>";
				$i++;
			}
			$tocHTML .= '</div>';
		}

		if ( class_exists( 'MobileWikihow' ) ) {
			$mobileWikihow = new MobileWikihow();
			$result = $mobileWikihow->controller();
			// false means we stop processing template
			if ( !$result ) {
				return;
			}
		}

		$this->data['pageLanguage'] = $this->getSkin()->getTitle()->getPageViewLanguage()->getHtmlCode();

		$sk = $this->getSkin();
		$out = $sk->getOutput();
		$request = $sk->getRequest();
		$title = $sk->getTitle();
		$user = $sk->getUser();

		$action = $request->getVal( 'action', 'view' );
		if ( count( $request->getVal( 'diff' ) ) > 0 ) {
			$action = 'diff';
		}

		$isMainPage = ( $title->isMainPage() && $action == 'view' );

		$isArticlePage = !$isMainPage &&
			$title->getNamespace() == NS_MAIN &&
			$action == 'view';

		$isDocViewer = $title->isSpecial( 'DocViewer' );

		// determine whether or not the user is logged in
		$isLoggedIn = $user->getID() > 0;

		$isTool = false;
		wfRunHooks( 'getToolStatus', array( &$isTool ) );

		$isIndexed = class_exists( 'RobotPolicy' ) && RobotPolicy::isIndexable( $title );

		$pageTitle = $sk->getHTMLTitle( $out->getHTMLTitle(), $this->data['title'], $isMainPage );

		$heading = '';
		if ( !$sk->suppressH1Tag() && $this->data['title'] ) {
			$heading = '<h1 id="firstHeading" class="firstHeading" lang="' . $this->data['pageLanguage'] .
				'"><span dir="auto">' . $this->data['title'] . '</span></h1>';
		}

		// get the breadcrumbs / category links at the top of the page
		$catLinksTop = $sk->getCategoryLinks( true );
		wfRunHooks( 'getBreadCrumbs', array( &$catLinksTop ) );
		$mainPageObj = Title::newMainPage();

		$isPrintable = false;
		if ( MWNamespace::isTalk( $title->getNamespace() ) && $action == 'view' ) {
			$isPrintable = $request->getVal( 'printable' ) == 'yes';
		}

		$siteNotice = '';
		if ( !$isMainPage && !$isDocViewer && ( !isset( $_COOKIE['sitenoticebox'] ) || !$_COOKIE['sitenoticebox'] ) ) {
			$siteNotice = $sk->getSiteNotice();
		}

		// search
		$searchTitle = SpecialPage::getTitleFor( 'Search' );
		$top_search = '
			<form id="bubble_search" name="search_site" action="' . $searchTitle->getFullURL() . '" method="get">
				<input type="text" id="searchInput" class="search_box" name="search" x-webkit-speech />
				<input type="submit" value="Search" id="search_site_bubble" class="search_button" />
			</form>';

		$body = '';

		// @todo FIXME: when, oh when, pray tell, is this useful?
		// It currently clashes pretty badly with social tools and looks more
		// out-of-place than anything else. Besides, we already have an "edit"
		// link in like basically all circumstances anyway...
		$editLink = '';
		if (
			$title->userCan( 'edit' ) &&
			$action != 'edit' &&
			$action != 'diff' &&
			$action != 'history' &&
			( ( $isLoggedIn && !in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK, NS_FILE, NS_CATEGORY ) ) ) ||
				!in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK, NS_FILE, NS_CATEGORY ) ) )
		)
		{
			// INTL: Need bigger buttons for non-English sites
			$editLinkText = ( $title->getNamespace() == NS_MAIN ) ? $sk->msg( 'bluesky-edit-page' )->plain() : $sk->msg( 'edit' )->plain();
			$editLink = Linker::link(
				$title,
				$editLinkText,
				array( 'class' => 'editsection' ),
				$sk->editUrlOptions()
			);
			$heading = $editLink . $heading;
		}

		if ( $isArticlePage || ( $title->getNamespace() == NS_PROJECT && $action == 'view' ) || ( $title->getNamespace() == NS_CATEGORY && !$title->exists() ) ) {
			if ( $title->getNamespace() == NS_PROJECT && ( $title->getDBkey() == 'RSS-feed' || $title->getDBkey() == 'Rising-star-feed' ) ) {
				$list_page = true;
				$sticky = false;
			} else {
				$list_page = false;
				$sticky = true;
			}
			$body .= $heading . ( class_exists( 'ArticleAuthors' ) ? ArticleAuthors::getAuthorHeader() : '' ) . $this->data['bodytext'];
			$body = '<div id="bodycontents" class="minor_section">' . $body . '</div>';
			if ( class_exists( 'WikihowArticleHTML' ) ) {
				$wikitext = ContentHandler::getContentText( $sk->getContext()->getWikiPage()->getContent( Revision::RAW ) );
				$magic = WikihowArticleHTML::grabTheMagic( $wikitext );
				$this->data['bodytext'] = WikihowArticleHTML::processArticleHTML(
					$body,
					array(
						'sticky-headers' => $sticky,
						'ns' => $title->getNamespace(),
						'list-page' => $list_page,
						'magic-word' => $magic
					)
				);
			}
		} else {
			if ( $action == 'edit' && class_exists( 'WikihowArticleEditor' ) ) {
				$heading .= WikihowArticleEditor::grabArticleEditLinks( $request->getVal( 'guidededitor' ) );
			}
			$this->data['bodyheading'] = $heading;
			$body = '<div id="bodycontents" class="minor_section">' . $this->data['bodytext'] . '</div>';
			if ( !$isTool && class_exists( 'WikihowArticleHTML' ) ) {
				$this->data['bodytext'] = WikihowArticleHTML::processHTML(
					$body,
					$action,
					array( 'show-gray-container' => $sk->showGrayContainer() )
				);
			} else {
				// a little hack to style the no such special page messages for special pages that actually
				// exist
				if ( false !== strpos( $body, 'You have arrived at a "special page"' ) ) {
					$body = "<div class='minor_section'>$body</div>";
				}
				$this->data['bodytext'] = $body;
			}
		}

		// post-process the Steps section HTML to get the numbers working
		if (
			$title->getNamespace() == NS_MAIN &&
			!$isMainPage &&
			( $action == 'view' || $action == 'purge' )
		)
		{
			// for preview article after edit, you have to munge the
			// steps of the previewHTML manually
			$body = $this->data['bodytext'];
			$opts = array();
			// $this->data['bodytext'] = WikihowArticleHTML::postProcess($body, $opts);
		}

		$navTabs = $sk->genNavigationTabs();

		$showBreadCrumbs = $sk->showBreadCrumbs();
		$showSideBar = $sk->showSideBar();
		$showArticleTabs = $title->getNamespace() != NS_SPECIAL && !$isMainPage;
		if (
			in_array( $title->getNamespace(), array( NS_FILE ) ) &&
			( empty( $action ) || $action == 'view' ) &&
			!$isLoggedIn
		)
		{
			$showArticleTabs = false;
		}

		$showRCWidget =
			class_exists( 'RCWidget' ) &&
			$title->getNamespace() != NS_USER &&
			( !$isLoggedIn || $user->getOption( 'recent_changes_widget_show', true ) == 1 ) &&
			( $isLoggedIn || $isMainPage ) &&
			!$isDocViewer &&
			$action != 'edit';

		$showFollowWidget = !$sk->msg( 'bluesky-follow-table' )->isDisabled();

		$showSocialSharing =
			$title->exists() &&
			$title->getNamespace() == NS_MAIN &&
			$action == 'view' &&
			class_exists( 'WikihowShare' );

		$showSliderWidget =
			class_exists( 'Slider' ) &&
			$title->exists() &&
			$title->getNamespace() == NS_MAIN &&
			!$title->isProtected() &&
			!$isPrintable &&
			!$isMainPage &&
			$isIndexed &&
			// $showSocialSharing &&
			$request->getVal( 'oldid' ) == '' &&
			( $request->getVal( 'action' ) == '' || $request->getVal( 'action' ) == 'view' );

		$isSpecialPage = $title->getNamespace() == NS_SPECIAL
			|| ( $title->getNamespace() == NS_MAIN && $request->getVal( 'action' ) == 'protect' )
			|| ( $title->getNamespace() == NS_MAIN && $request->getVal( 'action' ) == 'delete' );

		$showStaffStats = !$isMainPage
			&& $isLoggedIn
			&& ( in_array( 'staff', $user->getGroups() ) || in_array( 'staff_widget', $user->getGroups() ) )
			&& $title->getNamespace() == NS_MAIN
			&& class_exists( 'Pagestats' );

		$tabsArray = $sk->getTabsArray( $showArticleTabs );

		wfRunHooks( 'JustBeforeOutputHTML', array( &$this ) );

		// Output the doctype element and everything that goes before the HTML
		// <body> tag
		$this->html( 'headelement' );

		wfRunHooks( 'PageHeaderDisplay', array( $sk->isUserAgentMobile() ) ); ?>

		<div id="header_outer"><div id="header">
			<ul id="actions">
				<?php foreach ( $navTabs as $tabid => $tab ): ?>
					<li id="<?php echo $tabid ?>_li">
						<div class="nav_icon"></div>
						<a id="<?php echo $tabid ?>" class="nav" href="<?php echo $tab['link'] ?>"><?php echo $tab['text'] ?></a>
						<?php echo $tab['menu'] ?>
					</li>
				<?php endforeach; ?>
			</ul><!--end actions-->
			<?php if ( isset( $sk->notifications_count ) && (int)$sk->notifications_count > 0 ): ?>
				<div id="notification_count" class="notice"><?php echo $sk->notifications_count ?></div>
			<?php endif; ?>
			<?php
				$holidayLogo = SkinBlueSky::getHolidayLogo();
				$customLogo = wfFindFile( 'BlueSky-logo.png' );
				/**
				 * Pick a logo if we can find one. Otherwise just show the
				 * sitename in its place in the fixed header.
				 */
				if ( is_object( $customLogo ) ) {
					$logoElement = Html::element( 'img', array(
						'src' => $customLogo->getUrl(),
						'class' => 'logo',
						'alt' => ''
					) );
				} elseif ( $holidayLogo ) {
					$logoElement = Html::element( 'img', array(
						'src' => $holidayLogo,
						'class' => 'logo',
						'alt' => ''
					) );
				} else {
					$logoElement = Html::element( 'h2', array(
						'class' => 'logo-text'
					), $wgSitename );
				}
			?>
			<a href="<?php echo $mainPageObj->getLocalURL(); ?>" id="logo_link"><?php echo $logoElement ?></a>
			<?php echo $top_search ?>
			<?php wfRunHooks( 'EndOfHeader', array( &$out ) ); ?>
		</div></div><!--end #header-->
		<?php wfRunHooks( 'AfterHeader', array( &$out ) ); ?>
		<div id="main_container" class="<?php echo ( $isMainPage ? 'mainpage' : '' ) ?>">
			<div id="header_space"></div>

		<div id="main">
		<?php wfRunHooks( 'BeforeActionbar', array( &$out ) ); ?>
		<div id="actionbar" class="<?php echo ( $isTool ? 'isTool' : '' ) ?>">
			<?php if ( $showBreadCrumbs ): ?>
				<div id="gatBreadCrumb">
					<ul id="breadcrumb" class="Breadcrumbs">
						<?php echo $catLinksTop ?>
					</ul>
				</div>
			<?php endif; ?>
			<?php
			if ( count( $tabsArray ) > 0 ) {
				echo $sk->getTabsHtml( $tabsArray );
			}
			?>

		</div><!--end #actionbar-->
		<div id="container"<?php echo !$showSideBar ? ' class="no_sidebar"' : '' ?>>
		<div id="article_shell">
		<div id="article"<?php if ( class_exists( 'Microdata' ) ) { echo Microdata::genSchemaHeader(); } ?> class="mw-body">
			<?php if ( $this->data['newtalk'] ) { ?><div class="usermessage"><?php $this->html( 'newtalk' ) ?></div><?php } ?>
			<?php
			wfRunHooks( 'BeforeTabsLine', array( &$out ) );
			if ( !$isArticlePage && $this->data['bodyheading'] ) {
				echo '<div class="wh_block">' . $this->data['bodyheading'] . '</div>';
			}
			?>
			<div class="section sticky" id="intro">
				<?php echo $editLink ?>
				<?php if ( !$sk->suppressH1Tag() && $this->data['title'] && !isset( $this->data['bodyheading'] ) ) { ?><h1 id="firstHeading" class="firstHeading" lang="<?php $this->text( 'pageLanguage' ) ?>"><span dir="auto"><?php $this->html( 'title' ) ?></span></h1><?php }?>
				<div id="info"><?php
					if ( isset( $this->data['viewcount'] ) && $this->data['viewcount'] ) {
						echo '<span id="view_count">';
						$this->html( 'viewcount' );
						echo '</span>';
					}
					echo $sk->msg( 'word-separator' )->escaped();
					$lastEdit = $sk->getPageLastEdit( $title );
					if ( !empty( $lastEdit ) ) {
						echo '<span id="originators">' . $lastEdit . '</span>';
					}
				?>
				</div>
				<?php if ( $this->data['undelete'] ) { ?><div id="contentSub"><?php $this->html( 'undelete' ) ?></div><?php } ?>
				<div id="contentSub2"><?php $this->html( 'subtitle' ) ?></div>
				<div class="clearall"></div>
				<?php if ( $tocHTML != '' ) { ?>
				<div id="header_toc">
					<span id="header-toc-header"><?php echo $sk->msg( 'bluesky-toc-sections' ) ?></span><?php echo $tocHTML; ?>
				</div>
				<?php } ?>
			</div>

			<?php
			echo "<!-- start content -->\n";
			echo $this->html( 'bodytext' );
			echo "<!-- end content -->\n";

			if ( $this->data['dataAfterContent'] ) {
				$this->html( 'dataAfterContent' );
			}

			$showingArticleInfo = 0;
			if ( in_array( $title->getNamespace(), array( NS_MAIN, NS_PROJECT ) ) && $action == 'view' ) {
				$catLinks = $sk->getCategoryLinks( false );
				$authors = class_exists( 'ArticleAuthors' ) ? ArticleAuthors::getAuthorFooter() : false;
				if ( $authors || is_array( $this->data['language_urls'] ) || $catLinks ) {
					$showingArticleInfo = 1;
				}
				?>

				<div class="section noprint">
					<?php if ( $showingArticleInfo ): ?>
						<h2 class="section_head" id="article_info_header"><span><?php echo $sk->msg( 'bluesky-article-info' )->plain() ?></span></h2>
						<div id="article_info" class="section_text">
					<?php else : ?>
						<h2 class="section_head" id="article_tools_header"><span><?php echo $sk->msg( 'bluesky-article-tools' )->plain() ?></span></h2>
						<div id="article_tools" class="section_text">
					<?php endif ?>
						<?php if ( $catLinks ): ?>
							<p class="info"> <?php echo $sk->msg( 'categories' )->text() ?>: <?php echo $catLinks ?></p>
						<?php endif; ?>
						<p><?php echo $authors ?></p>
						<?php if ( is_array( $this->data['language_urls'] ) ) { ?>
							<p class="info"><?php $this->msg( 'otherlanguages' ) ?>:</p>
							<p class="info"><?php
								$links = array();
								foreach ( $this->data['language_urls'] as $langlink ) {
									$links[] = htmlspecialchars( trim( $langlink['language'] ) ) . '&nbsp;<span><a href="' . htmlspecialchars( $langlink['href'] ) . '">' . $langlink['text'] . '</a><span>';
								}
								echo implode( '&#44;&nbsp;', $links );
								?>
							</p>
						<?php }
						// talk link
						if ( $action == 'view' && MWNamespace::isTalk( $title->getNamespace() ) ) {
							$talk_link = '#postcomment';
						} else {
							$talk_link = $title->getTalkPage()->getLocalURL();
						}
						?>
						<ul id="end_options">
							<li class="endop_discuss"><span></span><a href="<?php echo $talk_link ?>" id="gatDiscussionFooter"><?php echo $sk->msg( 'bluesky-discuss' )->plain() ?></a></li>
							<li class="endop_print"><span></span><a href="<?php echo $title->getLocalURL( 'printable=yes' ) ?>" id="gatPrintView"><?php echo $sk->msg( 'print' )->text() ?></a></li>
							<?php
							/* Commented out for the time being, because:
							 * 1) "share via email" is not a core MW functionality,
							 * and as such, we need to address it here somehow,
							 * 2) the related JS needs refactoring, and
							 * 3) the i18n message is currently AWOL
							<li class="endop_email"><span></span><a href="#" onclick="return emailLink();" id="gatSharingEmail"><?php echo $sk->msg( 'at_email' )->text() ?></a></li>
							*/
							?>
							<?php if ( $isLoggedIn ) {
								if ( $title->userIsWatching() ) {
									$watchAction = 'unwatch';
								} else {
									$watchAction = 'watch';
								}
								// i18n messages used here: bluesky-watch,
								// bluesky-unwatch
								?>
									<li class="endop_watch"><span></span><a class="mw-watchlink" href="<?php echo $title->getLocalURL( 'action=' . $watchAction ); ?>"><?php echo $sk->msg( 'bluesky-' . $watchAction )->plain() ?></a></li>
							<?php } ?>
							<li class="endop_edit"><span></span><a href="<?php echo $title->getEditURL(); ?>" id="gatEditFooter"><?php echo $sk->msg( 'edit' )->plain(); ?></a></li>
							<?php if ( $title->getNamespace() == NS_MAIN && class_exists( 'ThankAuthors' ) ) { ?>
								<li class="endop_fanmail"><span></span><a href="/Special:ThankAuthors?target=<?php echo $title->getPrefixedURL(); ?>" id="gatThankAuthors"><?php echo $sk->msg( 'at_fanmail' )->text() ?></a></li>
							<?php } ?>
						</ul> <!--end #end_options -->
						<div class="clearall"></div>
					</div><!--end #article_info .section_text-->
					<?php if ( $sk->pageStats() != '' ) { ?>
						<p class="page_stats">
						<?php echo $sk->pageStats() ?>
						</p>
					<?php } ?>
				</div><!--end .section-->

			<?php }

			if ( in_array( $title->getNamespace(), array( NS_USER, NS_MAIN, NS_PROJECT ) ) && $action == 'view' ) {
			?>

		</div> <!-- end #article -->
		<div id="">

			<?php } ?>
		</div> <!--end last_question-->
		<div class="clearall"></div>

		</div> <!--end #article_shell-->

		<?php if ( $showSideBar ):
			$loggedOutClass = '';
			if ( false && $title->getText() != 'Userlogin' && $title->getNamespace() == NS_MAIN ) {
				$loggedOutClass = ' logged_out';
			}
		?>
			<div id="sidebar">
				<?php echo $siteNotice ?>

				<!-- Sidebar Top Widgets -->
				<?php
					foreach ( $sk->mSidebarTopWidgets as $sbWidget ) {
						echo $sbWidget;
					}
				?>
				<!-- END Sidebar Top Widgets -->

				<?php
				// "Write an article" button
				$writeMsgContents = $sk->msg( 'bluesky-write-article-url' )->text();
				if ( preg_match( '/^(?:' . wfUrlProtocols() . ')/', $writeMsgContents ) ) {
					$write = Linker::makeExternalLink(
						$writeMsgContents,
						$sk->msg( 'bluesky-write-article' )->plain(),
						/* escape */true,
						/* link type */'',
						/* array of extra attributes to <a> */array(
							'id' => 'gatWriteAnArticle',
							'class' => 'button secondary'
						)
					);
				} else {
					$write = Linker::link(
						Title::newFromText( $writeMsgContents ),
						$sk->msg( 'bluesky-write-article' )->plain(),
						array(
							'id' => 'gatWriteAnArticle',
							'class' => 'button secondary'
						)
					);
				}

				if ( !$isDocViewer ) {
				?>
				<div id="top_links" class="sidebox<?php echo $loggedOutClass ?>" <?php echo is_numeric( $sk->msg( 'top_links_padding' )->text() ) ? ' style="padding-left:' . $sk->msg( 'top_links_padding' )->text() . 'px;padding-right:' . $sk->msg( 'top_links_padding' )->text() . 'px;"' : '' ?>>
					<a href="<?php echo SpecialPage::getTitleFor( 'Randompage' )->getFullURL() ?>" id="gatRandom" accesskey="x" class="button secondary"><?php echo $sk->msg( 'randompage' )->plain(); ?></a>
					<?php echo $write ?>
				</div><!--end #top_links-->
				<?php } ?>
				<?php if ( $showStaffStats ): ?>
					<div class="sidebox" style="padding-top:10px" id="staff_stats_box"></div>
				<?php endif; ?>

				<?php $userLinks = $sk->getUserLinks(); ?>
				<?php if ( $userLinks ) { ?>
				<div class="sidebox">
					<?php echo $userLinks ?>
				</div>
				<?php } ?>

				<?php if ( $showSocialSharing ): ?>
					<div class="sidebox<?php echo $loggedOutClass ?>" id="sidebar_share">
						<h3><?php echo $sk->msg( 'social_share' )->text() ?></h3>
						<?php
						if ( $isMainPage ) {
							echo WikihowShare::getMainPageShareButtons();
						} else {
							echo WikihowShare::getTopShareButtons( $isIndexed );
						}
						?>
						<div style="clear:both; float:none;"></div>
					</div>
				<?php endif; ?>

				<!-- Sidebar Widgets -->
				<?php foreach ( $sk->mSidebarWidgets as $sbWidget ): ?>
					<?php echo $sbWidget ?>
				<?php endforeach; ?>
				<!-- END Sidebar Widgets -->

				<?php if ( $showRCWidget ): ?>
					<div class="sidebox" id="side_rc_widget">
						<?php RCWidget::showWidget(); ?>
						<p class="bottom_link">
							<?php
							if ( $isLoggedIn ) {
								echo $sk->msg( 'welcome', $user->getName(), $user->getUserPage()->getLocalURL() )->text();
							} else {
								echo Linker::link(
									SpecialPage::getTitleFor( 'Userlogin' ),
									$sk->msg( 'rcwidget_join_in' )->text(),
									array( 'id' => 'gatWidgetBottom' )
								);
							}
							?>
							<a href="" id="play_pause_button" onclick="rcTransport(this); return false;"></a>
						</p>
					</div><!--end #side_rc_widget-->
				<?php endif; ?>

				<?php if ( ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_USER ) && !$isDocViewer ) { ?>
					<div id="side_featured_contributor" class="sidebox">
						<?php $this->showFeaturedContributorWidget(); ?>
						<?php if ( !$isLoggedIn ) { ?>
							<p class="bottom_button"><?php echo Linker::link(
								SpecialPage::getTitleFor( 'Userlogin' ),
								$sk->msg( 'bluesky-featured-contributor-action' )->plain(),
								array( 'id' => 'gatFCWidgetBottom' )
							); ?>
							</p>
						<?php } ?>
					</div><!--end #side_featured_contributor-->
				<?php } ?>

				<?php if ( $showFollowWidget ) { ?>
					<div class="sidebox">
						<h3><?php echo $sk->msg( 'bluesky-follow-header' )->plain() ?></h3>
						<?php echo $sk->msg( 'bluesky-follow-table' )->text() ?>
					</div>
				<?php } ?>
			</div><!--end #sidebar-->
		<?php endif; // end if $showSideBar ?>
		<div class="clearall"></div>
		</div><!--end #container -->
		</div><!--end #main-->
			<div id="clear_footer"></div>
		</div><!--end #main_container-->
		<div id="footer_outer">
			<div id="footer">
				<div id="footer_side">
					<?php
						if ( $isLoggedIn ) {
							$footerMessage = $sk->msg( 'bluesky-site-footer' );
						} else {
							$footerMessage = $sk->msg( 'bluesky-site-footer-anon' );
						}
						if ( !$footerMessage->isDisabled() ) {
							echo $footerMessage->parse();
						}
					?>
				</div><!--end #footer_side-->

				<div id="footer_main">
					<div id="sub_footer">
						<?php
						if ( isset( $this->data['copyright'] ) && $this->data['copyright'] ) {
							echo '<div id="creative_commons">';
							// This is probably useless, I already left it out
							// for the "powered by MW" stuff below, but...whatever
							echo Html::element( 'a', array(
								'href' => Title::newFromText( $sk->msg( 'copyrightpage' )->inContentLanguage()->text() )->getFullURL(),
								'class' => 'imglink sub_footer_link footer_creative_commons footer_sprite'
							) );
							echo $this->data['copyright'];
							echo '</div>';
						}

						foreach ( $this->getFooterIcons( 'nocopyright' ) as $blockName => $footerIcons ) {
							echo '<div id="' . htmlspecialchars( $blockName ) . '">';
							foreach ( $footerIcons as $icon ) {
								echo $sk->makeFooterIcon( $icon, 'withoutImage' );
							}
							echo '</div>';
						}
						?>
					</div>
				</div><!--end #footer_main-->
			</div>
			<br class="clearall" />
		</div><!--end #footer_outer-->
		<div id="dialog-box" title=""></div>

		<?php
		// Quick note/edit popup
		if ( $action == 'diff' && class_exists( 'QuickNoteEdit' ) ) {
			echo QuickNoteEdit::displayQuicknote();
			echo QuickNoteEdit::displayQuickedit();
		}

		// Slider box -- for non-logged in users on articles only
		if ( $showSliderWidget ) {
			echo Slider::getBox();
			echo '<div id="slideshowdetect"></div>';
		}
		?>

		<div id="fb-root"></div>

		<?php
		if ( $showRCWidget ) {
			RCWidget::showWidgetJS();
		}

		// Load event listeners all pages
		if ( class_exists( 'CTALinks' ) && trim( $sk->msg( 'cta_feature' )->inContentLanguage()->text() ) == 'on' ) {
			echo CTALinks::getBlankCTA();
		}

		wfRunHooks( 'ArticleJustBeforeBodyClose' );

		if ( $showStaffStats ) {
			echo Pagestats::getJSsnippet( 'article' );
		}

		if ( class_exists( 'GoodRevision' ) ) {
			$grevid = $title ? GoodRevision::getUsedRev( $title->getArticleID() ) : '';
			$latestRev = $title->getNamespace() == NS_MAIN ? $title->getLatestRevID() : '';
			echo '<!-- shown patrolled revid=' . $grevid . ', latest=' . $latestRev . ' -->';
		}

		echo wfReportTime();

		$this->printTrail();
?>
</body>
</html>
<?php
	}

	/**
	 * Show the "featured contributor" widget if
	 * [[MediaWiki:Bluesky-featured-contributor-list]] has some content.
	 *
	 * @param bool $top Only get the first user listed on the message?
	 */
	private function showFeaturedContributorWidget( $top = false ) {
		global $wgParser;

		$sk = $this->getSkin();
		$msg = $sk->msg( 'bluesky-featured-contributor-list' )->inContentLanguage();
		if ( $msg->isDisabled() ) {
			// message is empty, aborting
			return;
		}

		$rec = '';
		$list = preg_split( '/\n==/', $msg->text() );

		if ( $top ) {
			$rec = $list[0];
		} else {
			$r = rand( 0, ( count( $list ) - 1 ) );
			if ( $r == 0 ) {
				$rec = $list[0];
			} else {
				$rec = '== ' . $list[$r];
			}
		}

		preg_match( '/== (.*?) ==/', $rec, $matches );
		$fc_user = $matches[1];
		preg_match( '/==\n(.*)/', $rec, $matches );
		$fc_blurb = $matches[1];

		$u = User::newFromName( $fc_user );

		if ( !$u ) {
			return;
		}

		$u->load();
		if ( class_exists( 'Avatar' ) ) { // wikiHow avatar extension
			$avatar = Avatar::getPicture( $u->getName(), true, true );
		} elseif ( class_exists( 'wAvatar' ) ) { // SocialProfile extension
			$avatarObject = new wAvatar( $u->getId(), 'l' );
			$avatar = $avatarObject->getAvatarURL();
		} else { // no avatar extension of any kind (or at least one that we support)
			$avatar = '';
		}

		$output = $wgParser->parse( $fc_blurb, $sk->getTitle(), new ParserOptions() );
		$fc_blurb = preg_replace( '/\n/', '', strip_tags( $output->getText(), '<p><b><a><br>' ) );

		$fc_blurb = str_replace( '$1', $u->getName(), $fc_blurb );
		$regYear = gmdate( 'Y', wfTimestamp( TS_UNIX, $u->getRegistration() ) );
		$fc_blurb = str_replace( '$2', $regYear, $fc_blurb );

?>
	<div>
		<h3><?php echo $sk->msg( 'bluesky-featured-contributor-title' )->plain(); ?></h3>
		<div class="featuredContrib_id">
			<?php if ( $avatar != '' ) { ?>
				<span id="fc_id_img" class="fc_id_img"><a href="<?php echo $u->getUserPage()->getFullURL(); ?>"><?php echo $avatar ?></a></span>
			<?php } ?>
			<span id="fc_id" class="fc_id"><?php echo $fc_blurb ?></span>
		</div>
		<div class="clearall"></div>
	</div>
<?php
	}

}

