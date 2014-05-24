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

	var $skinname = 'bluesky', $stylename = 'bluesky',
		$template = 'BlueSkyTemplate', $useHeadElement = true;

	public $mSidebarWidgets = array();
	public $mSidebarTopWidgets = array();

	function addWidget( $html, $class = '' ) {
		$class = htmlspecialchars( $class ); // Healthy paranoia, just in case.
		$display = "
	<div class='sidebox $class'>
		$html
	</div>\n";

		array_push( $this->mSidebarWidgets, $display );
		return;
	}

	/**
	 * A mild hack to allow for the language appropriate 'How to' to be added to
	 * interwiki link titles. Note: German (de) is a straight pass-through
	 * since the 'How to' is already stored in the de database
	 */
	function getInterWikiLinkText( $linkText, $langCode ) {
		static $formatting = array(
			'ar' => '$1 كيفية',
			'de' => '$1',
			'es' => 'Cómo $1',
			'en' => 'How to $1',
			'fa' => '$1 چگونه',
			'fr' => 'Comment $1',
			'he' => '$1 איך',
			'it' => 'Come $1',
			'ja' => '$1（する）方法',
			'nl' => '$1',
			'pt' => 'Como $1',
		);

		$result = $linkText;
		if ( isset( $formatting[$langCode] ) ) {
			$format = $formatting[$langCode];
		}
		if ( !empty( $format ) ) {
			$result = preg_replace( "@(\\$1)@", $linkText, $format );
		}
		return $result;
	}

	function getInterWikiCTA( $langCode, $linkText, $linkHref ) {
		static $cta = array(
			'es' => '<a href="<link>">¿Te gustaría saber <title>? ¡Lee acerca de eso en español!</a>',
			'de' => '<a href="<link>">Lies auch unseren deutschen Artikel: <title>.</a>',
			'pt' => '<a href="<link>">Gostaria de aprender <title>? Leia sobre o assunto em português!</a>',
			'it' => '<a href="<link>">Ti piacerebbe sapere <title>? Leggi come farlo, in italiano!</a>',
			'fr' => '<a href="<link>">Voudriez-vous apprendre <title>? Découvrez comment le faire en le lisant en français!</a>',
			'nl' => '<a href="<link>">Wil je graag leren <title>? Lees erover in het Nederlands</a>',
		);
		$title = $this->getInterWikiLinkText( $linkText, $langCode );
		$result = '';
		$linkHref .= '?utm_source=enwikihow&utm_medium=translatedcta&utm_campaign=translated';
		if ( $title && isset( $cta[$langCode] ) ) {
			$title = '<i>' . $title . '</i>';
			$result = $cta[$langCode];
			$result = str_replace( '<title>', $title, $result );
			$result = str_replace( '<link>', $linkHref, $result );
		}
		return $result;
	}

	function pageStats() {
		global $wgOut, $wgLang, $wgRequest;
		global $wgDisableCounters;

		$context = $this->getSkin()->getContext();
		$oldid = $wgRequest->getVal( 'oldid' );
		$diff = $wgRequest->getVal( 'diff' );

		if ( !$wgOut->isArticle() ) {
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
			$count = $wgLang->formatNum( $context->getWikiPage()->getCount() );
			if ( $count ) {
				if ( $this->getTitle()->getNamespace() == NS_USER ) {
					$s = wfMessage( 'viewcountuser', $count )->text();
				} else {
					$s = wfMessage( 'viewcount', $count )->text();
				}
			}
		}

		return $s;
	}

	/**
	 * @param $userId Integer: user id in database.
	 * @param $userText String: user name in database.
	 * @return string HTML fragment with talk and/or block links
	 * @private
	 */
	function userToolLinks( $userId, $userText ) {
		global $wgUser, $wgDisableAnonTalk, $wgSysopUserBans, $wgTitle, $wgLanguageCode, $wgRequest, $wgServer;
		$talkable = !( $wgDisableAnonTalk && 0 == $userId );
		$blockable = ( $wgSysopUserBans || 0 == $userId );

		$items = array();
		if ( $talkable ) {
			$items[] = $this->userTalkLink( $userId, $userText );
		}

		// XXMOD Added for quick note feature
		if ( $wgTitle->getNamespace() != NS_SPECIAL &&
			$wgLanguageCode == 'en' &&
			$wgRequest->getVal( 'diff', '' ) )
		{
			$items[] = QuickNoteEdit::getQuickNoteLink( $wgTitle, $userId, $userText );
		}

		$contribsPage = SpecialPage::getTitleFor( 'Contributions', $userText );
		$items[] = Linker::linkKnown( $contribsPage, wfMsgHtml( 'contribslink' ) );

		if ( $wgTitle->isSpecial( 'Recentchanges' ) && $wgUser->isAllowed( 'patrol' ) ) {
			$contribsPage = SpecialPage::getTitleFor( 'Bunchpatrol', $userText );
			$items[] = Linker::linkKnown( $contribsPage , 'bunch' );
		}
		if ( $blockable && $wgUser->isAllowed( 'block' ) ) {
			$items[] = $this->blockLink( $userId, $userText );
		}

		if ( $items ) {
			return ' (' . implode( ' | ', $items ) . ')';
		} else {
			return '';
		}
	}

	/**
	 * User links feature: users can get a list of their own links by specifying
	 * a list in User:username/Mylinks
	 *
	 * @return string
	 */
	function getUserLinks() {
		global $wgUser, $wgParser, $wgTitle;

		$ret = '';

		if ( $wgUser->getID() > 0 ) {
			$t = Title::makeTitle( NS_USER, $wgUser->getName() . '/Mylinks' );
			if ( $t->getArticleID() > 0 ) {
				$r = Revision::newFromTitle( $t );
				$text = $r->getText();
				if ( $text != '' ) {
					$ret = '<h3>' . wfMessage( 'mylinks' )->text() . '</h3>';
					$ret .= '<div id="my_links_list">';
					$options = new ParserOptions();
					$output = $wgParser->parse( $text, $wgTitle, $options );
					$ret .= $output->getText();
					$ret .= '</div>';
				}
			}
		}

		return $ret;
	}

	private function needsFurtherEditing( &$title ) {
		$cats = $title->getParentCategories();
		if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
			$keys = array_keys( $cats );
			$templates = wfMessage( 'templates_further_editing' )->inContentLanguage()->text();
			$templates = explode( "\n", $templates );
			$templates = array_flip( $templates ); // switch all key/value pairs
			for ( $i = 0; $i < sizeof( $keys ); $i++ ) {
				$t = Title::newFromText( $keys[$i] );
				if ( isset( $templates[$t->getText()] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	function getRelatedArticlesBox( $e, $isBoxShape = false ) {
		global $wgTitle, $wgContLang, $wgRequest, $wgMemc;

		if ( !$wgTitle
			|| $wgTitle->getNamespace() != NS_MAIN
			|| $wgTitle->getFullText() == wfMessage( 'mainpage' )->text()
			|| $wgRequest->getVal( 'action' ) != '' )
		{
			return '';
		}

		$cachekey = wfMemcKey( 'relarticles_box', intval( $isBoxShape ), $wgTitle->getArticleID() );
		$val = $wgMemc->get( $cachekey );
		if ( $val ) {
			return $val;
		}

		$cats = Categoryhelper::getCurrentParentCategories();
		$cat = '';
		if ( is_array( $cats ) && sizeof( $cats ) > 0 ) {
			$keys = array_keys( $cats );
			$templates = wfMessage( 'categories_to_ignore' )->inContentLanguage()->text();
			$templates = explode( "\n", $templates );
			$templates = str_replace( 'http://www.wikihow.com/Category:', '', $templates );
			$templates = array_flip( $templates ); // make the array associative.
			for ( $i = 0; $i < sizeof( $keys ); $i++ ) {
				$t = Title::newFromText( $keys[$i] );
				if ( isset( $templates[urldecode( $t->getPartialURL() )] ) ) {
					continue;
				} else {
					$cat = $t->getDBKey();
					break;
				}
			}
		}

		// Populate related articles box with other articles in the category,
		// displaying the featured articles first
		$result = '';
		if ( !empty( $cat ) ) {
			$dbr = wfGetDB( DB_SLAVE );
			$num = intval( wfMessage( 'num_related_articles_to_display' )->inContentLanguage()->text() );
			$res = $dbr->select(
				array( 'categorylinks', 'page' ),
				array( 'cl_from', 'page_is_featured, page_title' ),
				array(
					'cl_from = page_id',
					'cl_to' => $cat,
					'page_namespace' => 0,
					'page_is_redirect' => 0,
					'(page_is_featured = 1 OR page_random > ' . wfRandom() . ')'
				),
				__METHOD__,
				array( 'ORDER BY' => 'page_is_featured DESC' )
			);

			if ( $isBoxShape ) {
				$result .= '<div class="related_square_row">';
			}

			$count = 0;
			foreach ( $res as $row ) {
				if ( $count >= $num ) {
					break;
				}
				if ( $row->cl_from == $wgTitle->getArticleID() ) {
					continue;
				}

				$t = Title::newFromDBkey( $row->page_title );
				if ( !$t || $this->needsFurtherEditing( $t ) ) {
					continue;
				}

				if ( $isBoxShape ) {
					// exit if there's a word that will be too long
					$word_array = explode( ' ', $t->getText() );
					foreach ( $word_array as $word ) {
						if ( strlen( $word ) > 7 ) {
							continue;
						}
					}

					$data = self::featuredArticlesAttrs( $t, $t->getFullText(), 200, 162 );
					$result .= $this->relatedArticlesBox( $data, $num_cols );
					if ( $count == 1 ) {
						$result .= '</div><div class="related_square_row">';
					}
				} else {
					// $data = self::featuredArticlesAttrs($t, $t->getFullText());
					$result .= self::getArticleThumb( $t, 127, 140 );
				}

				$count++;
			}

			if ( $isBoxShape ) $result .= '</div>';

			if ( !empty( $result ) ) {
				if ( $isBoxShape ) {
					$result = "<div id='related_squares'>$result\n</div>";
				} else {
					$result = "<h3>" . wfMessage( 'relatedarticles' )->text() . "</h3>$result<div class='clearall'></div>\n";
				}
			}
		}
		$wgMemc->set( $cachekey, $result );
		return $result;
	}

	static function getGalleryImage( $title, $width, $height, $skip_parser = false ) {
		global $wgMemc, $wgLanguageCode, $wgContLang;

		$cachekey = wfMemcKey( 'gallery1', $title->getArticleID(), $width, $height );
		$val = $wgMemc->get( $cachekey );
		if ( $val ) {
			return $val;
		}

		if ( ( $title->getNamespace() == NS_MAIN ) || ( $title->getNamespace() == NS_CATEGORY ) ) {
			if ( $title->getNamespace() == NS_MAIN ) {
				$file = Wikitext::getTitleImage( $title, $skip_parser );

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
						$wgMemc->set( $cachekey, wfGetPad( $thumb->getUrl() ), 2 * 3600 ); // 2 hours
						return wfGetPad( $thumb->getUrl() );
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
						$wgMemc->set( $cachekey, wfGetPad( $thumb->getUrl() ), 2 * 3600 ); // 2 hours
						return wfGetPad( $thumb->getUrl() );
					}
				}
			} else {
				$image = Title::makeTitle( NS_IMAGE, 'Book_266.png' );
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
					$wgMemc->set( $cachekey, wfGetPad( $thumb->getUrl() ), 2 * 3600 ); // 2 hours
					return wfGetPad( $thumb->getUrl() );
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

	function getArticleThumbWithPath( $t, $width, $height, $file ) {
		global $wgContLang, $wgLanguageCode;

		$sourceWidth = $file->getWidth();
		$sourceHeight = $file->getHeight();
		$xScale = $width / $sourceWidth;
		if ( $height > $xScale * $sourceHeight ) {
			$heightPreference = true;
		} else {
			$heightPreference = false;
		}
		$thumb = WatermarkSupport::getUnwatermarkedThumbnail( $file, $width, $height, true, true, $heightPreference );
		// removed the fixed width for now
		$articleName = $t->getText();
		if ( $wgLanguageCode == 'zh' ) {
			$articleName = $wgContLang->convert( $articleName );
		}
		$html = "<div class='thumbnail'><a href='{$t->getFullUrl()}'><img src='" . $thumb->getUrl() . "' alt='' /><div class='text'><p>" . wfMessage( 'Howto', '' )->text() . "<br /><span>{$articleName}</span></p></div></a></div>";

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

	function relatedArticlesBox( $data ) {
		if ( !is_array( $data ) ) { // $data is actually a Title obj
			$data = self::featuredArticlesAttrs( $data, $data->getText() );
		}

		if ( strlen( $data['text'] ) > 35 ) {
			// too damn long
			$the_title = substr( $data['text'], 0, 32 ) . '...';
		} else {
			// we're good
			$the_title = $data['text'];
		}

		$html = '<a class="related_square" href="' . $data['url'] . '" style="background-image:url(' . $data['img'] . ')">
				<p><span>' . wfMessage( 'howto', '' )->text() . '</span>' . $the_title . '</p></a>';

		return $html;
	}

	function getNewArticlesBox() {
		global $wgMemc;

		$cachekey = wfMemcKey( 'newarticlesbox' );
		$cached = $wgMemc->get( $cachekey );

		if ( $cached ) {
			return $cached;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$ids = array();
		$res = $dbr->select(
			'pagelist',
			'pl_page',
			array( 'pl_list' => 'risingstar' ),
			__METHOD__,
			array( 'ORDER BY' => 'pl_page DESC', 'LIMIT' => 5 )
		);

		foreach ( $res as $row ) {
			$ids[] = $row->pl_page;
		}

		$html = '<div id="side_new_articles"><h3>' . wfMessage( 'newarticles' )->text() . "</h3>\n<table>";

		if ( $ids ) {
			$res = $dbr->select(
				array( 'page' ),
				array( 'page_namespace', 'page_title' ),
				array( 'page_id IN (' . implode( ',', $ids ) . ')' ),
				__METHOD__,
				array( 'ORDER BY' => 'page_id DESC', 'LIMIT' => 5 )
			);
			foreach ( $res as $row ) {
				$t = Title::makeTitle( NS_MAIN, $row->page_title );
				if ( !$t ) {
					continue;
				}
				$html .= $this->featuredArticlesRow( $t );
			}
		}

		$html .= '</table></div>';
		$one_hour = 60 * 60;

		$wgMemc->set( $cachekey, $html, $one_hour );

		return $html;
	}

	function getFeaturedArticlesBox( $daysLimit = 11, $linksLimit = 4 ) {
		global $wgServer, $wgTitle, $IP, $wgMemc, $wgProdHost;

		$cachekey = wfMemcKey( 'featuredbox', $daysLimit, $linksLimit );
		$result = $wgMemc->get( $cachekey );
		if ( $result ) {
			return $result;
		}

		$feeds = FeaturedArticles::getFeaturedArticles( $daysLimit );

		$html = "<h3><span onclick=\"location='" . wfMessage( 'featuredarticles_url' )->text() . "';\" style=\"cursor:pointer;\">" . wfMessage( 'featuredarticles' )->text() . "</span></h3>\n";

		$now = time();
		$popular = Title::makeTitle( NS_SPECIAL, 'Popularpages' );
		$randomizer = Title::makeTitle( NS_SPECIAL, 'Randomizer' );
		$count = 0;
		foreach ( $feeds as $item ) {
			$url = $item[0];
			$date = $item[1];
			if ( $date > $now ) {
				continue;
			}
			$url = str_replace( "$wgServer/", '', $url );
			if ( $wgServer != 'http://www.wikihow.com' ) {
				$url = str_replace( 'http://www.wikihow.com/', '', $url );
			}
			$url = str_replace( "http://$wgProdHost/", '', $url );

			$title = Title::newFromURL( urldecode( $url ) );
			if ( $title ) {
				// $html .= $this->featuredArticlesRow($title);
				$html .= self::getArticleThumb( $title, 126, 120 );
			}
			$count++;
			if ( $count >= $linksLimit ) {
				break;
			}
		}

		// main page stuff
		if ( $daysLimit > 8 ) {
			$data = self::featuredArticlesAttrs( $popular, wfMessage( 'populararticles' )->text() );
			$html .= $this->featuredArticlesRow( $data );
			$data = self::featuredArticlesAttrs( $randomizer, wfMessage( 'or_a_random_article' )->text() );
			$html .= $this->featuredArticlesRow( $data );
		}
		$html .= '<div class="clearall"></div>';

		// expires every 5 minutes
		$wgMemc->set( $cachekey, $html, 5 * 60 );

		return $html;
	}

	// overloaded from Skin class
	function drawCategoryBrowser( $tree, &$skin, $count = 0 ) {
		$return = '';
		//$viewMode = WikihowCategoryViewer::getViewModeArray( $this->getContext() );
		foreach ( $tree as $element => $parent ) {
			/*
			if ($element == "Category:WikiHow" ||
				$element == "Category:Featured-Articles" ||
				$element == "Category:Honors") {
					continue;
			}
			*/

			$count++;
			$start = ' ' . self::BREADCRUMB_SEPARATOR;

			/*
			//not too many...
			if ($count > self::BREADCRUMB_LIMIT && !self::$bShortened) {
				$return .= '<li class="bread_ellipsis"><span>'.$start.'</span> ... </li>';
				self::$bShortened = true;
				break;
			}
			*/

			$eltitle = Title::newFromText( $element );
			if ( empty( $parent ) ) {
				# element start a new list
				$return .= "\n";
			} else {
				# grab the others elements
				$return .= $this->drawCategoryBrowser( $parent, $skin, $count );
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
	const BREADCRUMB_LIMIT = 2;
	static $bShortened = false;

	function getCategoryLinks( $usebrowser ) {
		global $wgOut, $wgContLang;

		if ( !$usebrowser && empty( $wgOut->mCategoryLinks['normal'] ) ) {
			return '';
		}

		// Use Unicode bidi embedding override characters,
		// to make sure links don't smash each other up in ugly ways.
		$dir = $wgContLang->isRTL() ? 'rtl' : 'ltr';
		$embed = "<span dir='$dir'>";
		$pop = '</span>';

		if ( empty( $wgOut->mCategoryLinks['normal'] ) ) {
			$t = $embed . '' . $pop;
		} else {
			$t = $embed . implode( "{$pop} | {$embed}" , $wgOut->mCategoryLinks['normal'] ) . $pop;
		}
		if ( !$usebrowser ) {
			return $t;
		}

		$mainPageObj = Title::newMainPage();
		$sk = $this->getSkin();

		$sep = self::BREADCRUMB_SEPARATOR;

		//$viewMode = WikihowCategoryViewer::getViewModeArray( $this->getContext() );
		$categories = Linker::link(
			SpecialPage::getTitleFor( 'Categories' ),
			wfMessage( 'categories' )->text()/*,
			array(),
			$viewMode*/
		);
		$s = '<li class="home">' . Linker::link( $mainPageObj, wfMessage( 'home' )->text() ) . "</li> <li>$sep $categories</li>";

		# optional 'dmoz-like' category browser. Will be shown under the list
		# of categories an article belong to
		if ( $usebrowser ) {
			$s .= ' ';

			# get a big array of the parents tree
			$parentTree = Categoryhelper::getCurrentParentCategoryTree();
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
	 * vuln...
	 *
	 * @return bool
	 */
	function suppressH1Tag() {
		global $wgTitle, $wgLang;

		if ( $wgTitle->isMainPage() ) {
			return true;
		}

		if ( $wgTitle->isSpecial( 'Userlogin' ) ) {
			return true;
		}

		return false;
	}

	function getSiteNotice() {
		global $wgUser, $wgRequest, $wgReadOnly;

		$hasCookies = $wgUser && $wgUser->hasCookies();
		if ( $hasCookies && $wgReadOnly ) {
			$siteNotice = $wgReadOnly;
		} elseif ( !$hasCookies && $wgRequest->getVal( 'c' ) == 't' ) {
			$siteNotice = wfMessage( 'sitenotice_cachedpage' )->parse();
		} elseif ( !$wgUser->isAnon() ) {
			if ( wfMessage( 'sitenotice_loggedin' )->text() == '-' || wfMessage( 'sitenotice_loggedin' )->text() == '' ) {
				return '';
			}
			$siteNotice = wfMessage( 'sitenotice_loggedin' )->parse();
		} else {
			if ( wfMessage( 'sitenotice' )->text() == '-' || wfMessage( 'sitenotice' )->text() == '' ) {
				return '';
			}
			$siteNotice = wfMessage( 'sitenotice' )->parse();
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
		global $wgTitle;
		$result = true;
		wfRunHooks( 'ShowSideBar', array( &$result ) );
		return $result;
	}

	/**
	 * Calls any hooks in place to see if a module has requested that the
	 * bread crumb (category) links at the top of the article shouldn't
	 * be displayed.
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
	 * @return bool
	 */
	static function showGrayContainer() {
		global $wgTitle, $wgRequest;

		$result = true;
		wfRunHooks( 'ShowGrayContainer', array( &$result ) );

		$action = $wgRequest ? $wgRequest->getVal( 'action' ) : '';

		if ( $wgTitle->exists() || $wgTitle->getNamespace() == NS_USER ) {
			if (
				$wgTitle->getNamespace() == NS_USER ||
				$wgTitle->getNamespace() == NS_IMAGE ||
				$wgTitle->getNamespace() == NS_CATEGORY ||
				( $wgTitle->getNamespace() == NS_MAIN ) && ( $action == 'edit' || $action == 'submit2' )
			)
			{
				$result = false;
			}
		}
		return $result;
	}

	function getTabsArray( $showArticleTabs ) {
		global $wgTitle, $wgUser, $wgRequest;

		$action = $wgRequest->getVal( 'action', 'view' );
		if ( $wgRequest->getVal( 'diff' ) ) {
			$action = 'diff';
		}
		$skin = $this->getSkin();

		$tabs = array();

		wfRunHooks( 'pageTabs', array( &$tabs ) );

		if ( count( $tabs ) > 0 ) {
			return $tabs;
		}

		if ( !$showArticleTabs ) {
			return;
		}

		// article
		if ( $wgTitle->getNamespace() != NS_CATEGORY ) {
			$articleTab->href = $wgTitle->isTalkPage() ? $wgTitle->getSubjectPage()->getFullURL():$wgTitle->getFullURL();
			$articleTab->text = $wgTitle->getSubjectPage()->getNamespace() == NS_USER ? wfMessage( 'user' )->text() : wfMessage( 'article' )->text();
			$articleTab->class = ( !MWNamespace::isTalk( $wgTitle->getNamespace() ) && $action != 'edit' && $action != 'history' ) ? 'on' : '';
			$articleTab->id = 'tab_article';
			$tabs[] = $articleTab;
		}

		// edit
		if (
			$wgTitle->getNamespace() != NS_CATEGORY &&
			(
				!in_array( $wgTitle->getNamespace(), array( NS_USER, NS_USER_TALK, NS_FILE ) ) ||
				$action == 'edit' || $wgUser->getID() > 0
			)
		)
		{
			$editTab->href = $wgTitle->getLocalURL( $skin->editUrlOptions() );
			$editTab->text = wfMessage( 'edit' )->text();
			$editTab->class = ( $action == 'edit' ) ? 'on' : '';
			$editTab->id = 'tab_edit';
			$tabs[] = $editTab;
		}

		// talk
		if ( $wgTitle->getNamespace() != NS_CATEGORY ) {
			if ( $action == 'view' && MWNamespace::isTalk( $wgTitle->getNamespace() ) ) {
				$talklink = '#postcomment';
			} else {
				$talklink = $wgTitle->getTalkPage()->getLocalURL();
			}
			if ( in_array( $wgTitle->getNamespace(), array( NS_USER, NS_USER_TALK ) ) ) {
				$msg = wfMessage( 'talk' )->text();
			} else {
				$msg = wfMessage( 'discuss' )->text();
			}
			$talkTab->href = $talklink;
			$talkTab->text = $msg;
			$talkTab->class = ( $wgTitle->isTalkPage() && $action != 'edit' && $action != 'history' ) ? 'on' : '';
			$talkTab->id = 'tab_discuss';
			$tabs[] = $talkTab;
		}

		// history
		if ( !$wgUser->isAnon() && $wgTitle->getNamespace() != NS_CATEGORY ) {
			$historyTab->href = $wgTitle->getLocalURL( 'action=history' );
			$historyTab->text = wfMessage( 'history' )->text();
			$historyTab->class = ( $action == 'history' ) ? 'on' : '';
			$historyTab->id = 'tab_history';
			$tabs[] = $historyTab;
		}

		// for category page: link for image view
		if ( !$wgUser->isAnon() && $wgTitle->getNamespace() == NS_CATEGORY ) {
			$imageViewTab->href = $wgTitle->getLocalURL();
			$imageViewTab->text = wfMessage( 'image_view' )->text();
			$imageViewTab->class = $wgRequest->getVal( 'viewMode', 0 ) ? '' : 'on';
			$imageViewTab->id = 'tab_image_view';
			$tabs[] = $imageViewTab;
		}

		// For category page: link for text view
		if ( !$wgUser->isAnon() && $wgTitle->getNamespace() == NS_CATEGORY ) {
			$textViewTab->href = $wgTitle->getLocalURL( 'viewMode=text' );
			$textViewTab->text = wfMessage( 'text_view' )->text();
			$textViewTab->class = $wgRequest->getVal( 'viewMode', 0 ) ? 'on' : '';
			$textViewTab->id = 'tab_text_view';
			$tabs[] = $textViewTab;
		}

		// admin
		if ( $wgUser->isSysop() && $wgTitle->userCan( 'delete' ) ) {
			$adminTab->href = '#';
			$adminTab->text = wfMessage( 'admin_admin' )->text();
			$adminTab->class = '';
			$adminTab->id = 'tab_admin';
			$adminTab->hasSubMenu = true;
			$adminTab->subMenuName = 'AdminOptions';

			$adminTab->subMenu = array();
			$admin1->href = $wgTitle->getLocalURL( 'action=protect' );
			$admin1->text = !$wgTitle->isProtected() ? wfMessage( 'protect' )->text() : wfMessage( 'unprotect' )->text();
			$adminTab->subMenu[] = $admin1;
			if ( $wgTitle->getNamespace() != NS_IMAGE ) {
				$admin2->href = SpecialPage::getTitleFor( 'Movepage', $wgTitle )->getLocalURL();
			} else {
				$admin2->href = SpecialPage::getTitleFor( 'Movepage' )->getLocalURL()
					. '?target=' . $wgTitle->getPrefixedURL() . '&_=' . time();
			}
			$admin2->text = wfMessage( 'admin_move' )->text();
			$adminTab->subMenu[] = $admin2;
			$admin3->href = $wgTitle->getLocalURL( 'action=delete' );
			$admin3->text = wfMessage( 'admin_delete' )->text();
			$adminTab->subMenu[] = $admin3;

			$tabs[] = $adminTab;
		}

		return $tabs;
	}

	function getTabsHtml( $tabs ) {
		$html = '';

		if ( count( $tabs ) > 0 ) {
			$html .= '<div id="article_tabs">';
			$html .= '<ul id="tabs">';

			foreach ( $tabs as $tab ) {
				$attributes = '';

				foreach ( $tab as $attribute => $value ) {
					if ( $attribute != 'text' ) {
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
	 * Calls any hooks in place to see if a module has requested that the
	 * bread crumb (category) links at the top of the article shouldn't
	 * be displayed.
	 */
	static function showHeadSection() {
		global $wgTitle;
		$result = true;
		wfRunHooks( 'ShowHeadSection', array( &$result ) );

		// Don't show head section in wikiHow:Tour pages
		if ( $wgTitle->getNamespace() == NS_PROJECT
			&& stripos( $wgTitle->getPrefixedText(), 'wikiHow:Tour' ) !== false )
		{
			$result = false;
		}
		return $result;
	}

	function genNavigationTabs() {
		global $wgUser;

		$sk = $this->getSkin();
		$title = $sk->getTitle();

		$isLoggedIn = $wgUser->getID() > 0;

		$navTabs = array(
			'nav_messages' => array(
				'menu' => $sk->getHeaderMenu( 'messages' ),
				'link' => '#',
				'text' => wfMessage( 'navbar_messages' )->text()
			),
			'nav_profile' => array(
				'menu' => $sk->getHeaderMenu( 'profile' ),
				'link' => $isLoggedIn ? $wgUser->getUserPage()->getLocalURL() : '#',
				'text' => $isLoggedIn ? strtoupper( wfMessage( 'navbar_profile' )->text() ) : strtoupper( wfMessage( 'login' )->text() )
			),
			'nav_explore' => array(
				'menu' => $sk->getHeaderMenu( 'explore' ),
				'link' => '#',
				'text' => wfMessage( 'navbar_explore' )->text()
			),
			'nav_help' => array(
				'menu' => $sk->getHeaderMenu( 'help' ),
				'link' => '#',
				'text' => wfMessage( 'navbar_help' )->text()
			)
		);

		if (
			$title->getNamespace() == NS_MAIN &&
			!$title->isMainPage() &&
			$title->userCan( 'edit' )
		)
		{
			$editPage = $title->getLocalURL( $sk->editUrlOptions() );
			$navTabs['nav_edit'] = array(
				'menu' => $sk->getHeaderMenu( 'edit' ),
				'link' => $editPage,
				'text' => strtoupper( wfMessage( 'edit' )->text() )
			);
		}

		return $navTabs;
	}

	function getHeaderMenu( $menu ) {
		global $wgLanguageCode, $wgTitle, $wgUser, $wgForumLink;

		$html = '';
		$menu_css = 'menu';
		$sk = $this->getSkin();
		$isLoggedIn = $wgUser->getID() > 0;

		switch ( $menu ) {
			case 'edit':
				$html = '<a href="' . $wgTitle->getLocalURL( $sk->editUrlOptions() ) . '">' . wfMessage( 'edit-this-article' )->text() . '</a>';
				if ( !$isLoggedIn ) {
					break;
				}
				$html .= Linker::link( SpecialPage::getTitleFor( 'Importvideo', $wgTitle->getText() ), wfMessage( 'importvideo' )->text() );
				if ( $wgLanguageCode == 'en' ) {
					$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'RelatedArticle' ), wfMessage( 'manage_related_articles' )->text(), array(), array( "target" => $wgTitle->getPrefixedURL() ) ) .
							Linker::link( SpecialPage::getTitleFor( 'Articlestats', $wgTitle->getText() ), wfMessage( 'articlestats' )->text() );
				}
				$html .= Linker::link(
					SpecialPage::getTitleFor( 'Whatlinkshere', $wgTitle->getPrefixedURL() ),
					wfMessage( 'whatlinkshere' )->plain()
				);
				break;
			case 'profile':
				if ( $isLoggedIn ) {
					$html = Linker::link( Title::makeTitle( NS_SPECIAL, 'Mytalk', 'post' ), wfMessage( 'mytalkpage' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Mypage' ), wfMessage( 'myauthorpage' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Notifications' ), wfMessage( 'mynotifications' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Watchlist' ), wfMessage( 'watchlist' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Drafts' ), wfMessage( 'mydrafts' )->text() ) .
							Linker::link( SpecialPage::getTitleFor( 'Mypages', 'Contributions' ), wfMessage( 'mycontris' )->text() ) .
							Linker::link( SpecialPage::getTitleFor( 'Mypages', 'Fanmail' ), wfMessage( 'myfanmail' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Preferences' ), wfMessage( 'mypreferences' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'Userlogout' ), wfMessage( 'logout' )->text() );
				} else {
					$html = UserLoginBox::getLogin( true );
					$menu_css = 'menu_login';
				}
				break;
			case 'explore':
				$dashboardPage = $wgLanguageCode == 'en' ? Title::makeTitle( NS_SPECIAL, "CommunityDashboard" ) : Title::makeTitle( NS_PROJECT, wfMessage( "community" )->text() );
				$html = Linker::link( $dashboardPage, wfMessage( 'community_dashboard' )->text() );
				if ( $isLoggedIn ) {
					$html .= "<a href='$wgForumLink'>" . wfMessage( 'forums' )->text() . "</a>";
				}
				$html .= Linker::link(
					SpecialPage::getTitleFor( 'Randompage' ),
					wfMessage( 'randompage' )->plain()
				);
				if ( !$isLoggedIn ) {
					$html .= Linker::link(
						Title::newFromText( wfMessage( 'aboutpage' )->text() ),
						wfMessage( 'navmenu_aboutus' )->text()
					);
				}
				$html .= Linker::link( SpecialPage::getTitleFor( 'Categories' ), wfMessage( 'navmenu_categories' )->text() ) .
						Linker::link( SpecialPage::getTitleFor( 'Recentchanges' ), wfMessage( 'recentchanges' )->text() );
				if ( $isLoggedIn ) {
					$html .= Linker::link( SpecialPage::getTitleFor( 'Specialpages' ), wfMessage( 'specialpages' )->text() );
					$html .= Linker::link( Title::newFromText( wfMessage( 'helppage' )->text() ), wfMessage( 'help' )->text() );
				}
				break;
			case 'help':
				$html = Linker::link( Title::makeTitle( NS_SPECIAL, 'CreatePage' ), wfMessage( 'Write-an-article' )->text() );
				if ( $wgLanguageCode == 'en' ) {
					$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'RequestTopic' ), wfMessage( 'requesttopic' )->text() ) .
							Linker::link( Title::makeTitle( NS_SPECIAL, 'ListRequestedTopics' ), wfMessage( 'listrequtestedtopics' )->text() );
				}

				if ( $isLoggedIn ) {
					if ( $wgLanguageCode == 'en' ) {
						$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'TipsPatrol' ), wfMessage( 'navmenu_tipspatrol' )->text() );
					}
					$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'RCPatrol' ), wfMessage( 'PatrolRC' )->text() );
					if ( $wgLanguageCode == 'en' ) {
						$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'Categorizer' ), wfMessage( 'categorize_articles' )->text() );
					}
				}

				if ( $wgLanguageCode == 'en' ) {
					$html .= '<a href="/Special:CommunityDashboard">' . wfMessage( 'more-ideas' )->text() . '</a>';
				} else {
					$html .= Linker::link( Title::makeTitle( NS_SPECIAL, 'Uncategorizedpages' ), wfMessage( 'categorize_articles' )->text() ) .
							'<a href="/Contribute-to-wikiHow">' . wfMessage( 'more-ideas' )->text() . '</a>';
				}
				break;
			case 'messages':
				if ( class_exists( 'EchoEvent' ) && $wgUser->hasCookies() ) {
					$maxNotesShown = 5;
					$notif = ApiEchoNotifications::getNotifications( $wgUser, 'html', $maxNotesShown );

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
						$notifUser = MWEchoNotifUser::newFromUser( $wgUser );
						$this->notifications_count = $notifUser->getNotificationCount();

						if ( $this->notifications_count > $maxNotesShown ) {
							$unshown = '<br />' . Linker::link(
								$notificationsPage,
								wfMessage( 'parentheses',
									( $this->notifications_count - $maxNotesShown ) . ' unread' // @todo FIXME: proper i18n
								)->text()
							);
						} else {
							$unshown = '';
						}

						// add view all link
						$html .= '<div class="menu_message_morelink">';
						$html .= Linker::link( $notificationsPage, wfMessage( 'more-notifications-link' )->plain() );
						$html .= $unshown . '</div>';
					} else {
						// no notifications
						$html .= '<div class="menu_message_morelink">' . wfMessage( 'no-notifications' )->parse() . '</div>';
					}

				} else {
					// old school
					list( $html, $this->notifications_count ) = Notifications::loadNotifications();
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
	 * This is only available in MediaWiki 1.23+, but...
	 *
	 * @param OutputPage|null $out
	 * @return QuickTemplate
	 */
	protected function prepareQuickTemplate( OutputPage $out = null ) {
		global $wgContLang, $wgHideInterlanguageLinks, $wgUser;
		wfProfileIn( __METHOD__ );

		$tpl = parent::prepareQuickTemplate( $out );

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
			$out->getTitle()->getNamespace() == NS_USER &&
			$wgUser->getId() == 0 &&
			class_exists( 'UserPagePolicy' ) &&
			!UserPagePolicy::isGoodUserPage( $out->getTitle()->getDBkey() )
		)
		{
			$txt = wfMessage( 'noarticletext_user' )->parse();
			$tpl->setRef( 'bodytext', $txt );
			header( 'HTTP/1.1 404 Not Found' );
		}

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

	static function getHTMLTitle( $defaultHTMLTitle, $title, $isMainPage ) {
		global $wgTitle, $wgRequest, $wgLanguageCode, $wgLang, $wgSitename;

		$namespace = $wgTitle->getNamespace();
		$action = $wgRequest->getVal( 'action', 'view' );

		$htmlTitle = $defaultHTMLTitle;
		if ( $isMainPage ) {
			$htmlTitle = $wgSitename . ' - ' . wfMessage( 'main_title' )->text();
		} elseif ( $namespace == NS_MAIN && $wgTitle->exists() && $action == 'view' ) {
			if ( $wgLanguageCode == 'en' ) {
				$titleTest = TitleTests::newFromTitle( $wgTitle );
				if ( $titleTest ) {
					$htmlTitle = $titleTest->getTitle();
				}
			} else {
				$howto = wfMessage( 'howto', $title )->text();
				$htmlTitle = wfMessage( 'pagetitle', $howto )->text();
			}
		} elseif ( $namespace == NS_USER || $namespace == NS_USER_TALK ) {
			$username = $wgTitle->getText();
			$htmlTitle = $wgLang->getNsText( $namespace ) . ": $username - $wgSitename";
		} elseif ( $namespace == NS_CATEGORY ) {
			$htmlTitle = wfMessage( 'category_title_tag', $wgTitle->getText() )->text();
		}

		return $htmlTitle;
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
		global $wgUser, $wgLang, $wgRequest;
		global $wgOut, $wgScript, $wgStylePath, $wgLanguageCode, $wgForumLink;
		global $wgContLang, $wgXhtmlDefaultNamespace, $wgContLanguageCode;
		global $IP, $wgServer, $wgIsDomainTest;
		global $wgSSLsite;

		$prefix = '';

		if ( class_exists( 'MobileWikihow' ) ) {
			$mobileWikihow = new MobileWikihow();
			$result = $mobileWikihow->controller();
			// false means we stop processing template
			if ( !$result ) {
				return;
			}
		}

		$action = $wgRequest->getVal( 'action', 'view' );
		if ( count( $wgRequest->getVal( 'diff' ) ) > 0 ) {
			$action = 'diff';
		}

		$title = $this->getSkin()->getTitle();
		$isMainPage = ( $title->isMainPage() && $action == 'view' );

		$isArticlePage = !$isMainPage &&
			$title->getNamespace() == NS_MAIN &&
			$action == 'view';

		$isDocViewer = $title->isSpecial( 'DocViewer' );

		$isBehindHttpAuth = !empty( $_SERVER['HTTP_AUTHORIZATION'] );

		// determine whether or not the user is logged in
		$isLoggedIn = $wgUser->getID() > 0;

		$isTool = false;
		wfRunHooks( 'getToolStatus', array( &$isTool ) );

		$sk = $this->getSkin();

		wikihowAds::setCategories();
		if ( !$isLoggedIn && $action == 'view' ) {
			wikihowAds::getGlobalChannels();
		}

		$showAds = wikihowAds::isEligibleForAds();

		$isIndexed = RobotPolicy::isIndexable( $title );

		$pageTitle = SkinBlueSky::getHTMLTitle( $wgOut->getHTMLTitle(), $this->data['title'], $isMainPage );

		// set the title and what not
		$avatar = '';
		$namespace = $title->getNamespace();
		if ( $namespace == NS_USER || $namespace == NS_USER_TALK ) {
			$username = $title->getText();
			$usernameKey = $title->getDBKey();
			$avatar = ( class_exists( 'Avatar' ) ) ? Avatar::getPicture( $usernameKey ) : '';
			$h1 = $username;
			if ( $title->getNamespace() == NS_USER_TALK ) {
				$h1 = $wgLang->getNsText( NS_USER_TALK ) . ": $username";
			} elseif ( $username == $wgUser->getName() ) {
				// user's own page
				$profileBoxName = wfMessage( 'profilebox-name' )->text();
				$h1 .= "<div id='gatEditRemoveButtons'>
								<a href='/Special:Profilebox' id='gatProfileEditButton'>Edit</a>
								 | <a href='#' onclick='removeUserPage(\"$profileBoxName\");'>Remove $profileBoxName</a>
								 </div>";
			}
			$this->set( 'title', $h1 );
		}

		$logoutPage = $wgLang->specialPage( 'Userlogout' );
		$returnTarget = $title->getPrefixedURL();
		$returnto = strcasecmp( urlencode( $logoutPage ), $returnTarget ) ? "returnto={$returnTarget}" : '';

		$login = '';
		if ( !$wgUser->isAnon() ) {
			$uname = $wgUser->getName();
			if ( strlen( $uname ) > 16 ) {
				$uname = substr( $uname, 0, 16 ) . '...';
			}
			$login = wfMessage( 'welcome_back', $wgUser->getUserPage()->getFullURL(), $uname )->text();

			if ( method_exists( $wgUser, 'isFacebookUser' ) && $wgUser->isFacebookUser() ) {
				$login = wfMessage( 'welcome_back_fb', $wgUser->getUserPage()->getFullURL() , $wgUser->getName() )->text();
			} elseif ( method_exists( $wgUser, 'isGPlusUser' ) && $wgUser->isGPlusUser() ) {
				$gname = $wgUser->getName();
				if ( substr( $gname, 0, 3 ) == 'GP_' ) {
					$gname = substr( $gname, 0, 12 ) . '...';
				}
				$login = wfMessage( 'welcome_back_gp', $wgUser->getUserPage()->getFullURL(), $gname )->text();
			}
		} else {
			if ( $wgLanguageCode == 'en' ) {
				$login = wfMessage( 'signup_or_login', $returnto )->text() . ' ' . wfMessage( 'social_connect_header' )->text();
			} else {
				$login = wfMessage( 'signup_or_login', $returnto )->text();
			}
		}

		// XX PROFILE EDIT/CREAT/DEL BOX DATE - need to check for pb flag in order to display this.
		$pbDate = '';
		if ( $title->getNamespace() == NS_USER ) {
			$u = User::newFromName( $title->getDBkey() );
			if ( $u ) {
				if ( UserPagePolicy::isGoodUserPage( $title->getDBkey() ) ) {
					$pbDate = ProfileBox::getPageTop( $u );
				}
			}
		}

		if ( !$sk->suppressH1Tag() ) {
			if ( $title->getNamespace() == NS_MAIN && $title->exists() && $action == 'view' ) {
				if (
					class_exists( 'Microdata' ) &&
					Microdata::showRecipeTags() &&
					Microdata::showhRecipeTags()
				)
				{
					$itemprop_name1 = ' fn"';
					$itemprop_name2 = '';
				} else {
					$itemprop_name1 = '" itemprop="name"';
					$itemprop_name2 = ' itemprop="url"';
				}

				$heading = '<h1 class="firstHeading' . $itemprop_name1 . '><a href="' . $title->getFullURL() . '"' . $itemprop_name2 . '>' . wfMessage( 'howto', $this->data['title'] )->text() . '</a></h1>';
			} else {
				if (
					(
						(
							$title->getNamespace() == NS_USER &&
							class_exists( 'UserPagePolicy' ) &&
							UserPagePolicy::isGoodUserPage( $title->getDBKey() )
						) ||
						$title->getNamespace() == NS_USER_TALK
					)
				)
				{
					$heading = '<h1 class="firstHeading">' . $this->data['title'] . '</h1> ' . $pbDate;
					if ( $avatar ) {
						$heading = $avatar . '<div id="avatarNameWrap">' . $heading . '</div><div style="clear: both;"> </div>';
					}
				} else {
					if ( $this->data['title'] && ( strtolower( substr( $title->getText(), 0, 9 ) ) != 'userlogin' ) ) {
						$heading = "<h1 class='firstHeading'>" . $this->data['title'] . "</h1>";
					}
				}
			}
		}

		// get the breadcrumbs / category links at the top of the page
		$catLinksTop = $sk->getCategoryLinks( true );
		wfRunHooks( 'getBreadCrumbs', array( &$catLinksTop ) );
		$mainPageObj = Title::newMainPage();

		$isPrintable = false;
		if ( MWNamespace::isTalk( $title->getNamespace() ) && $action == 'view' ) {
			$isPrintable = $wgRequest->getVal( 'printable' ) == 'yes';
		}

		$otherLanguageLinks = array();
		$translationData = array();
		if ( $this->data['language_urls'] ) {
			foreach ( $this->data['language_urls'] as $lang ) {
				if ( $lang['code'] == $wgLanguageCode ) {
					continue;
				}
				$otherLanguageLinks[ $lang['code'] ] = $lang['href'];
				$langMsg = $sk->getInterWikiCTA( $lang['code'], $lang['text'], $lang['href'] );
				if ( !$langMsg ) {
					continue;
				}
				$encLangMsg = json_encode( $langMsg );
				$translationData[] = "'{$lang['code']}': {'msg':$encLangMsg}";
			}
		}

		if ( !$isMainPage && !$isDocViewer && ( !isset( $_COOKIE['sitenoticebox'] ) || !$_COOKIE['sitenoticebox'] ) ) {
			$siteNotice = $sk->getSiteNotice();
		}

		// Right-to-left languages
		$dir = $wgContLang->isRTL() ? 'rtl' : 'ltr';
		$head_element = "<html xmlns:fb=\"https://www.facebook.com/2008/fbml\" xmlns=\"{$wgXhtmlDefaultNamespace}\" xml:lang=\"$wgContLanguageCode\" lang=\"$wgContLanguageCode\" dir='$dir'>\n";

		$rtl_css = '';
		if ( $wgContLang->isRTL() ) {
			$rtl_css = "<style type=\"text/css\" media=\"all\">/*<![CDATA[*/ @import \a" . wfGetPad( "/extensions/min/f/skins/WikiHow/rtl.css" ) . "\"; /*]]>*/</style>";
			$rtl_css .= "
	<!--[if IE]>
	<style type=\"text/css\">
	BODY { margin: 25px; }
	</style>
	<![endif]-->";
		}

		$printable_media = 'print';
		if ( $wgRequest->getVal( 'printable' ) == 'yes' ) {
			$printable_media = 'all';
		}

		// search
		$searchTitle = SpecialPage::getTitleFor( 'Search' );
		$top_search = '
			<form id="bubble_search" name="search_site" action="' . $searchTitle->getFullURL() . '" method="get">
				<input type="text" id="searchInput" class="search_box" name="search" x-webkit-speech />
				<input type="submit" value="Search" id="search_site_bubble" class="search_button" />
			</form>';

		$text = $this->data['bodytext'];
		// Remove stray table under video section. Probably should eventually do it at
		// the source, but then have to go through all articles.
		if ( strpos( $text, '<a name="Video">' ) !== false ) {
			$vidpattern = "<p><br /></p>\n<center>\n<table width=\"375px\">\n<tr>\n<td><br /></td>\n<td align=\"left\"></td>\n</tr>\n</table>\n</center>\n<p><br /></p>";
			$text = str_replace( $vidpattern, "", $text );
		}
		$this->data['bodytext'] = $text;

		// hack to get the FA template working, remove after we go live
		$fa = '';
		if ( $wgLanguageCode != 'nl' && strpos( $this->data['bodytext'], 'featurestar' ) !== false ) {
			$fa = '<p id="feature_star">' . wfMessage( 'featured_article' )->text() . '</p>';
			// $this->data['bodytext'] = preg_replace("@<div id=\"featurestar\">(.|\n)*<div style=\"clear:both\"></div>@mU", '', $this->data['bodytext']);
		}

		$body = '';

		if ( $title->userCan( 'edit' ) &&
			$action != 'edit' &&
			$action != 'diff' &&
			$action != 'history' &&
			( ( $isLoggedIn && !in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK, NS_IMAGE, NS_CATEGORY ) ) ) ||
				!in_array( $title->getNamespace(), array( NS_USER, NS_USER_TALK, NS_IMAGE, NS_CATEGORY ) ) ) ) {
				// INTL: Need bigger buttons for non-english sites
				$editlink_text = ( $title->getNamespace() == NS_MAIN ) ? wfMessage( 'editarticle' )->text() : wfMessage( 'edit' )->text();
				$heading = '<a href="' . $title->getLocalURL( $sk->editUrlOptions() ) . '" class="editsection">' . $editlink_text . '</a>' . $heading;
		}

		if ( $isArticlePage || ( $title->getNamespace() == NS_PROJECT && $action == 'view' ) || ( $title->getNamespace() == NS_CATEGORY && !$title->exists() ) ) {
			if ( $title->getNamespace() == NS_PROJECT && ( $title->getDBkey() == 'RSS-feed' || $title->getDBkey() == 'Rising-star-feed' ) ) {
				$list_page = true;
				$sticky = false;
			} else {
				$list_page = false;
				$sticky = true;
			}
			$body .= $heading . ArticleAuthors::getAuthorHeader() . $this->data['bodytext'];
			$body = '<div id="bodycontents">' . $body . '</div>';
			$wikitext = ContentHandler::getContentText( $this->getSkin()->getContext()->getWikiPage()->getContent( Revision::RAW ) );
			$magic = WikihowArticleHTML::grabTheMagic( $wikitext );
			$this->data['bodytext'] = WikihowArticleHTML::processArticleHTML( $body, array( 'sticky-headers' => $sticky, 'ns' => $title->getNamespace(), 'list-page' => $list_page, 'magic-word' => $magic ) );
		} else {
			if ( $action == 'edit' ) $heading .= WikihowArticleEditor::grabArticleEditLinks( $wgRequest->getVal( "guidededitor" ) );
			$this->data['bodyheading'] = $heading;
			$body = '<div id="bodycontents">' . $this->data['bodytext'] . '</div>';
			if ( !$isTool ) {
				$this->data['bodytext'] = WikihowArticleHTML::processHTML( $body, $action, array( 'show-gray-container' => $sk->showGrayContainer() ) );
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
		if ( $title->getNamespace() == NS_MAIN
			&& !$isMainPage
			&& ( $action == 'view' || $action == 'purge' )
		) {
			// for preview article after edit, you have to munge the
			// steps of the previewHTML manually
			$body = $this->data['bodytext'];
			$opts = array();
			if ( !$showAds )
				$opts['no-ads'] = true;
			// $this->data['bodytext'] = WikihowArticleHTML::postProcess($body, $opts);
		}

		// insert avatars into discussion, talk, and kudos pages
		if ( MWNamespace::isTalk( $title->getNamespace() ) || $title->getNamespace() == NS_USER_KUDOS ) {
			$this->data['bodytext'] = Avatar::insertAvatarIntoDiscussion( $this->data['bodytext'] );
		}

		// $navMenu = $sk->genNavigationMenu();

		$navTabs = $sk->genNavigationTabs();

		$optimizelyJS = false;
		if ( class_exists( 'OptimizelyPageSelector' ) ) {
			if ( OptimizelyPageSelector::isArticleEnabled( $title ) && OptimizelyPageSelector::isUserEnabled( $wgUser ) ) {
				$optimizelyJS = OptimizelyPageSelector::getOptimizelyTag();
			}
		}

		$showSpotlightRotate =
			$isMainPage &&
			$wgLanguageCode == 'en';

		$showBreadCrumbs = $sk->showBreadCrumbs();
		$showSideBar = $sk->showSideBar();
		$showHeadSection = $sk->showHeadSection();
		$showArticleTabs = $title->getNamespace() != NS_SPECIAL && !$isMainPage;
		if ( in_array( $title->getNamespace(), array( NS_IMAGE ) )
			&& ( empty( $action ) || $action == 'view' )
			&& !$isLoggedIn )
		{
			$showArticleTabs = false;
		}

		$showWikiTextWidget = false;
		if ( class_exists( 'WikitextDownloader' ) ) {
			$showWikiTextWidget = WikitextDownloader::isAuthorized() && !$isDocViewer;
		}

		$showRCWidget =
			class_exists( 'RCWidget' ) &&
			$title->getNamespace() != NS_USER &&
			( !$isLoggedIn || $wgUser->getOption( 'recent_changes_widget_show', true ) == 1 ) &&
			( $isLoggedIn || $isMainPage ) &&
			!in_array( $title->getPrefixedText(),
				array( 'Special:Avatar', 'Special:ProfileBox', 'Special:IntroImageAdder' ) ) &&
			strpos( $title->getPrefixedText(), 'Special:Userlog' ) === false &&
			!$isDocViewer &&
			$action != 'edit';

		$showFollowWidget =
			class_exists( 'FollowWidget' ) &&
			!$isDocViewer &&
			in_array( $wgLanguageCode, array( 'en', 'de', 'es', 'pt' ) );

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
			$wgLanguageCode == 'en' &&
			// $showSocialSharing &&
			$wgRequest->getVal( 'oldid' ) == '' &&
			( $wgRequest->getVal( 'action' ) == '' || $wgRequest->getVal( 'action' ) == 'view' );

		$showTopTenTips =
			$title->exists() &&
			$title->getNamespace() == NS_MAIN &&
			$wgLanguageCode == 'en' &&
			!$isPrintable &&
			!$isMainPage &&
			$wgRequest->getVal( 'oldid' ) == '' &&
			( $wgRequest->getVal( 'action' ) == '' || $wgRequest->getVal( 'action' ) == 'view' );

		$showAltMethod = false;
		if ( class_exists( 'AltMethodAdder' ) ) {
			$showAltMethod = true;
		}

		$showExitTimer = class_exists( 'BounceTimeLogger' );

		$isLiquid = false;// !$isMainPage && ( $title->getNameSpace() == NS_CATEGORY );

		$showFeaturedArticlesSidebar = $action == 'view'
			&& !$isMainPage
			&& !$isDocViewer
			&& !$wgSSLsite
			&& $title->getNamespace() != NS_USER;

		$isSpecialPage = $title->getNamespace() == NS_SPECIAL
			|| ( $title->getNamespace() == NS_MAIN && $wgRequest->getVal( 'action' ) == 'protect' )
			|| ( $title->getNamespace() == NS_MAIN && $wgRequest->getVal( 'action' ) == 'delete' );

		$showTextScroller =
			class_exists( 'TextScroller' ) &&
			$title->exists() &&
			$title->getNamespace() == NS_MAIN &&
			!$isPrintable &&
			!$isMainPage &&
			strpos( $this->data['bodytext'], 'textscroller_outer' ) !== false;

		$showImageFeedback =
			class_exists( 'ImageFeedback' ) &&
			ImageFeedback::isValidPage();

		$showWikivideo =
			class_exists( 'WHVid' ) &&
			( ( $title->exists() && $title->getNamespace() == NS_MAIN && strpos( $this->data['bodytext'], 'whvid_cont' ) !== false )
				|| $title->getNamespace() == NS_SPECIAL ) &&
			!$isPrintable &&
			!$isMainPage;

		$showStaffStats = !$isMainPage
			&& $isLoggedIn
			&& ( in_array( 'staff', $wgUser->getGroups() ) || in_array( 'staff_widget', $wgUser->getGroups() ) )
			&& $title->getNamespace() == NS_MAIN
			&& class_exists( 'Pagestats' );

		$showThumbsUp = class_exists( 'ThumbsNotifications' );

		$postLoadJS = $isArticlePage;

		// add present JS files to extensions/min/groupsConfig.php
		$fullJSuri = '/extensions/min/g/whjs' .
			( !$isArticlePage ? ',jqui' : '' ) .
			( $showExitTimer ? ',stu' : '' ) .
			( $showRCWidget ? ',rcw' : '' ) .
			( $showSpotlightRotate ? ',sp' : '' ) .
			( $showFollowWidget ? ',fl' : '' ) .
			( $showSliderWidget ? ',slj' : '' ) .
			( $showThumbsUp ? ',thm' : '' ) .
			( $showWikiTextWidget ? ',wkt' : '' ) .
			( $showAltMethod ? ',altj' : '' ) .
			( $showTopTenTips ? ',tpt' : '' ) .
			( $isMainPage ? ',hp' : '' ) .
			( $showWikivideo ? ',whv' : '' ) .
			( $showImageFeedback ? ',ii' : '' ) .
			( $showTextScroller ? ',ts' : '' );

		if ( $wgOut->mJSminCodes ) {
			$fullJSuri .= ',' . join( ',', $wgOut->mJSminCodes );
		}
		$cachedParam = $wgRequest && $wgRequest->getVal( 'c' ) == 't' ? '&c=t' : '';
		$fullJSuri .= '&r=' . WH_SITEREV . $cachedParam . '&e=.js';

		$fullCSSuri = '/extensions/min/g/whcss' .
			( !$isArticlePage ? ',jquic,nona' : '' ) .
			( $isLoggedIn ? ',li' : '' ) .
			( $showSliderWidget ? ',slc' : '' ) .
			( $showAltMethod ? ',altc' : '' ) .
			( $showTopTenTips ? ',tptc' : '' ) .
			( $showWikivideo ? ',whvc' : '' ) .
			( $showTextScroller ? ',tsc' : '' ) .
			( $isMainPage ? ',hpc' : '' ) .
			( $showImageFeedback ? ',iic' : '' ) .
			( $isSpecialPage ? ',spc' : '' );

		if ( $wgOut->mCSSminCodes ) {
			$fullCSSuri .= ',' . join( ',', $wgOut->mCSSminCodes );
		}
		$fullCSSuri .= '&r=' . WH_SITEREV . $cachedParam . '&e=.css';

		$tabsArray = $sk->getTabsArray( $showArticleTabs );

		wfRunHooks( 'JustBeforeOutputHTML', array( &$this ) );

?>
<!DOCTYPE html>
<?php echo $head_element ?><head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# article: http://ogp.me/ns/article#">
	<title><?php echo $pageTitle ?></title>
	<?php /*Hack to add variable WH as a global variable before loading script. This is need because load.php creates a closure when loading wikibits.js
			Add by Gershon Bialer on 12/2/2013*/?><script>
<!--
var WH = WH || {};
//-->
</script>

	<?php if ( $wgIsDomainTest ): ?>
	<base href="http://www.wikihow.com/" />
	<?php endif; ?>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<meta name="verify-v1" content="/Ur0RE4/QGQIq9F46KZyKIyL0ZnS96N5x1DwQJa7bR8=" />
	<meta name="google-site-verification" content="Jb3uMWyKPQ3B9lzp5hZvJjITDKG8xI8mnEpWifGXUb0" />
	<meta name="msvalidate.01" content="CFD80128CAD3E726220D4C2420D539BE" />
	<meta name="y_key" content="1b3ab4fc6fba3ab3" />
	<meta name="p:domain_verify" content="bb366527fa38aa5bc27356b728a2ec6f" />
	<?php if ( $isArticlePage || $isMainPage ): ?>
	<link rel="alternate" media="only screen and (max-width: 640px)" href="http://<?php if ( $wgLanguageCode != 'en' ) { echo ( $wgLanguageCode . "." ); } ?>m.wikihow.com/<?php echo $title->getPartialUrl() ?>">
	<?php endif; ?>
	<?php // add CSS files to extensions/min/groupsConfig.php ?>
	<style type="text/css" media="all">/*<![CDATA[*/ @import "<?php echo $fullCSSuri ?>"; /*]]>*/</style>
	<?php // below is the minified /resources/css/printable.css ?>
	<style type="text/css" media="<?php echo$printable_media?>">/*<![CDATA[*/ body{background-color:#FFF;font-size:1.2em}#header_outer{background:0 0;position:relative}#header{text-align:center;height:63px!important;width:242px!important;background:url(/skins/owl/images/logo_lightbg_242.jpg) no-repeat center center;margin-top:15px}#article_shell{margin:0 auto;float:none;padding-bottom:2em}.sticking{position:absolute!important;top:0!important}#actions,#article_rating,#article_tabs,#breadcrumb,#bubble_search,#cse-search-box,#end_options,#footer_outer,#header_space,#logo_link,#notification_count,#originators,#sidebar,#sliderbox,.edit,.editsection,.mwimg,.section.relatedwikihows,.section.video,.whvid_cont,.altadder_section{display:none!important} /*]]>*/</style>
		<?php
		// Bootstapping certain javascript functions:
		// A function to merge one object with another; stub funcs
		// for button swapping (this should be done in CSS anyway);
		// initialize the timer for bounce stats tracking.
		?>
		<script>
			<!--
			var WH = WH || {};
			WH.lang = WH.lang || {};
			button_swap = button_unswap = function(){};
			WH.exitTimerStartTime = (new Date()).getTime();
			WH.mergeLang = function(A){for(i in A){v=A[i];if(typeof v==='string'){WH.lang[i]=v;}}};
			//-->
		</script>
		<?php if ( !$postLoadJS ): ?>
			<?php echo $this->html( 'headscripts' ) ?>
			<script type="text/javascript" src="<?php echo $fullJSuri ?>"></script>
		<?php endif ?>

		<?php $this->html( 'headlinks' ) ?>

	<?php if ( !$wgIsDomainTest ) { ?>
			<link rel='canonical' href='<?php echo $title->getFullURL() ?>'/>
			<link href="https://plus.google.com/102818024478962731382" rel="publisher" />
		<?php } ?>
	<?php if ( $sk->isUserAgentMobile() ): ?>
			<link media="only screen and (max-device-width: 480px)" href="<?php echo wfGetPad( '/extensions/min/f/skins/WikiHow/iphone.css' ) ?>" type="text/css" rel="stylesheet" />
		<?php else : ?>
			<!-- not mobile -->
		<?php endif; ?>
	<!--<![endif]-->
	<?php echo $rtl_css ?>
	<link rel="alternate" type="application/rss+xml" title="wikiHow: How-to of the Day" href="http://www.wikihow.com/feed.rss"/>
	<link rel="apple-touch-icon" href="<?php echo wfGetPad( '/skins/WikiHow/safari-large-icon.png' ) ?>" />
	<?php
	if ( class_exists( 'CTALinks' ) && trim( wfMessage( 'cta_feature' )->inContentLanguage()->text() ) == "on" ) {
		echo CTALinks::getGoogleControlScript();
	}

	echo $wgOut->getHeadItems();

	$userdir = $wgLang->getDir();
	$sitedir = $wgContLang->getDir();
	?>
	<?php foreach ( $otherLanguageLinks as $lang => $url ): ?>
			<link rel="alternate" hreflang="<?php echo $lang ?>" href="<?php echo htmlspecialchars( $url ) ?>" />
		<?php endforeach; ?>
		</head>
		<body <?php if ( isset( $this->data['body_ondblclick'] ) && $this->data['body_ondblclick'] ) { ?>ondblclick="<?php $this->text( 'body_ondblclick' ) ?>"<?php } ?>
			<?php if ( isset( $this->data['body_onload'] ) && $this->data['body_onload'] ) { ?>onload="<?php $this->text( 'body_onload' ) ?>"<?php } ?>
			class="mediawiki <?php echo $userdir ?> sitedir-<?php echo $sitedir ?>">
		<?php wfRunHooks( 'PageHeaderDisplay', array( $sk->isUserAgentMobile() ) ); ?>

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
				$logoPath = $holidayLogo ? $holidayLogo : $wgStylePath . '/BlueSky/resources/images/wikihow_logo.png';
				if ( $wgLanguageCode != 'en' ) {
					$logoPath = $wgStylePath . '/BlueSky/resources/images/wikihow_logo_intl.png';
				}
			?>
			<a href="<?php echo $mainPageObj->getLocalURL(); ?>" id="logo_link"><img src="<?php echo $logoPath ?>" class="logo" alt="" /></a>
			<?php echo $top_search ?>
			<?php wfRunHooks( 'EndOfHeader', array( &$wgOut ) ); ?>
		</div></div><!--end header-->
		<?php wfRunHooks( 'AfterHeader', array( &$wgOut ) ); ?>
		<div id="main_container" class="<?php echo ( $isMainPage ? 'mainpage' : '' ) ?>">
			<div id="header_space"></div>

		<div id="main">
		<?php wfRunHooks( 'BeforeActionbar', array( &$wgOut ) ); ?>
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

		</div><!--end actionbar-->
		<script>
		<!--
		WH.translationData = {<?php echo join( ',', $translationData ) ?>};
		//-->
		</script>
		<?php
		$sidebar = !$showSideBar ? 'no_sidebar' : '';

		// INTL: load mediawiki messages for sidebar expand and collapse for later use in sidebar boxes
		$langKeys = array( 'navlist_collapse', 'navlist_expand', 'usernameoremail', 'password' );
		print Wikihow_i18n::genJSMsgs( $langKeys );
		?>
		<div id="container" class="<?php echo $sidebar ?>">
		<div id="article_shell">
		<div id="article"<?php if ( class_exists( 'Microdata' ) ) { echo Microdata::genSchemaHeader(); } ?>>

			<?php wfRunHooks( 'BeforeTabsLine', array( &$wgOut ) ); ?>
			<?php
			if ( !$isArticlePage && !$isMainPage && $this->data['bodyheading'] ) {
				echo '<div class="wh_block">' . $this->data['bodyheading'] . '</div>';
			}
			echo $this->html( 'bodytext' );

			$showingArticleInfo = 0;
			if ( in_array( $title->getNamespace(), array( NS_MAIN, NS_PROJECT ) ) && $action == 'view' && !$isMainPage ) {
				$catLinks = $sk->getCategoryLinks( false );
				$authors = class_exists( 'ArticleAuthors' ) ? ArticleAuthors::getAuthorFooter() : false;
				if ( $authors || is_array( $this->data['language_urls'] ) || $catLinks ) {
					$showingArticleInfo = 1;
				}
				?>

				<div class="section">
					<?php if ( $showingArticleInfo ): ?>
						<h2 class="section_head" id="article_info_header"><span><?php echo wfMessage( 'article_info' )->text() ?></span></h2>
						<div id="article_info" class="section_text">
					<?php else : ?>
						<h2 class="section_head" id="article_tools_header"><span><?php echo wfMessage( 'article_tools' )->text() ?></span></h2>
						<div id="article_tools" class="section_text">
					<?php endif ?>
						<?php echo $fa ?>
						<?php if ( $catLinks ): ?>
							<p class="info"> <?php echo wfMessage( 'categories' )->text() ?>: <?php echo $catLinks ?></p>
						<?php endif; ?>
						<p><?php echo $authors ?></p>
						<?php if ( is_array( $this->data['language_urls'] ) ) { ?>
							<p class="info"><?php $this->msg( 'otherlanguages' ) ?>:</p>
							<p class="info"><?php
								$links = array();
								$sk = $this->getSkin();
								foreach ( $this->data['language_urls'] as $langlink ) {
									$linkText = $langlink['text'];
									preg_match( '@interwiki-(..)@', $langlink['class'], $langCode );
									if ( !empty( $langCode[1] ) ) {
										$linkText = $sk->getInterWikiLinkText( $linkText, $langCode[1] );
									}
									$links[] = htmlspecialchars( trim( $langlink['language'] ) ) . '&nbsp;<span><a href="' . htmlspecialchars( $langlink['href'] ) . '">' . $linkText . '</a><span>';
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
							<li class="endop_discuss"><span></span><a href="<?php echo $talk_link ?>" id="gatDiscussionFooter"><?php echo wfMessage( 'at_discuss' )->text() ?></a></li>
							<li class="endop_print"><span></span><a href="<?php echo $title->getLocalURL( 'printable=yes' ) ?>" id="gatPrintView"><?php echo wfMessage( 'print' )->text() ?></a></li>
							<li class="endop_email"><span></span><a href="#" onclick="return emailLink();" id="gatSharingEmail"><?php echo wfMessage( 'at_email' )->text() ?></a></li>
							<?php if ( $isLoggedIn ): ?>
								<?php if ( $title->userIsWatching() ) { ?>
									<li class="endop_watch"><span></span><a href="<?php echo $title->getLocalURL( 'action=unwatch' ); ?>"><?php echo wfMessage( 'at_remove_watch' )->text()?></a></li>
								<?php } else { ?>
									<li class="endop_watch"><span></span><a href="<?php echo $title->getLocalURL( 'action=watch' ); ?>"><?php echo wfMessage( 'at_watch' )->text()?></a></li>
								<?php } ?>
							<?php endif; ?>
							<li class="endop_edit"><span></span><a href="<?php echo $title->getEditUrl(); ?>" id="gatEditFooter"><?php echo wfMessage( 'edit' )->text(); ?></a></li>
							<?php if ( $title->getNamespace() == NS_MAIN ) { ?>
								<li class="endop_fanmail"><span></span><a href="/Special:ThankAuthors?target=<?php echo $title->getPrefixedURL(); ?>" id="gatThankAuthors"><?php echo wfMessage( 'at_fanmail' )->text()?></a></li>
							<?php } ?>
						</ul> <!--end end_options -->
						<div class="clearall"></div>
					</div><!--end article_info section_text-->
					<p class='page_stats'><?php echo $sk->pageStats() ?></p>
				</div><!--end section-->

			<?php }

			if ( in_array( $title->getNamespace(), array( NS_USER, NS_MAIN, NS_PROJECT ) ) && $action == 'view' && !$isMainPage ) {
			?>

		</div> <!-- article -->
		<div id="">

			<?php } ?>
		</div> <!--end last_question-->
		<div class="clearall"></div>

		</div> <!--end article_shell-->


		<?php if ( $showSideBar ):
			$loggedOutClass = '';
			if ( $showAds && $title->getText() != 'Userlogin' && $title->getNamespace() == NS_MAIN ) {
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

				<?php if ( !$isDocViewer ) { ?>
				<div id="top_links" class="sidebox<?php echo $loggedOutClass ?>" <?php echo is_numeric( wfMessage( 'top_links_padding' )->text() ) ? ' style="padding-left:' . wfMessage( 'top_links_padding' )->text() . 'px;padding-right:' . wfMessage( 'top_links_padding' )->text() . 'px;"' : '' ?>>
					<a href="<?php echo SpecialPage::getTitleFor( 'Randompage' )->getFullURL() ?>" id="gatRandom" accesskey="x" class="button secondary"><?php echo wfMessage( 'randompage' )->text(); ?></a>
					<a href="/Special:Createpage" id="gatWriteAnArticle" class="button secondary"><?php echo wfMessage( 'writearticle' )->text(); ?></a>
				</div><!--end top_links-->
				<?php } ?>
				<?php if ( $showStaffStats ): ?>
					<div class="sidebox" style="padding-top:10px" id="staff_stats_box"></div>
				<?php endif; ?>

				<?php if ( $showWikiTextWidget ) { ?>
					<div class="sidebox" id="side_rc_widget">
						<a id='wikitext_downloader' href='#'>Download WikiText</a>
					</div><!--end sidebox-->
				<?php } ?>

				<?php $userLinks = $sk->getUserLinks(); ?>
				<?php if ( $userLinks ) { ?>
				<div class="sidebox">
					<?php echo $userLinks ?>
				</div>
				<?php } ?>

				<?php
				$related_articles = $sk->getRelatedArticlesBox( $this );
				if ( $action == 'view' && $related_articles != '' ) {
					$related_articles = '<div id="side_related_articles" class="sidebox">'
						. $related_articles . '</div><!--end side_related_articles-->';

					echo $related_articles;
				}
				?>

				<?php if ( $showSocialSharing ): ?>
					<div class="sidebox<?php echo $loggedOutClass ?>" id="sidebar_share">
						<h3><?php echo wfMessage( 'social_share' )->text() ?></h3>
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

				<?php if ( $showFeaturedArticlesSidebar ): ?>
					<div id="side_featured_articles" class="sidebox">
						<?php echo $sk->getFeaturedArticlesBox( 4, 4 ) ?>
					</div>
				<?php endif; ?>

				<?php if ( $showRCWidget ): ?>
					<div class="sidebox" id="side_rc_widget">
						<?php RCWidget::showWidget(); ?>
						<p class="bottom_link">
							<?php if ( $isLoggedIn ) { ?>
								<?php echo wfMessage( 'welcome', $wgUser->getName(), $wgUser->getUserPage()->getLocalURL() )->text(); ?>
							<?php } else { ?>
								<a href="/Special:Userlogin" id="gatWidgetBottom"><?php echo wfMessage( 'rcwidget_join_in' )->text()?></a>
							<?php } ?>
							<a href="" id="play_pause_button" onclick="rcTransport(this); return false;" ></a>
						</p>
					</div><!--end side_recent_changes-->
				<?php endif; ?>

				<?php if ( class_exists( 'FeaturedContributor' ) && ( $title->getNamespace() == NS_MAIN || $title->getNamespace() == NS_USER ) && !$isMainPage && !$isDocViewer ): ?>
					<div id="side_featured_contributor" class="sidebox">
						<?php FeaturedContributor::showWidget(); ?>
						<?php if ( !$isLoggedIn ): ?>
							<p class="bottom_button">
								<a href="/Special:Userlogin" class="button secondary" id="gatFCWidgetBottom" onclick='gatTrack("Browsing","Feat_contrib_cta","Feat_contrib_wgt");'><? echo wfMessage( 'fc_action' )->text() ?></a>
							</p>
						<?php endif; ?>
					</div><!--end side_featured_contributor-->
				<?php endif; ?>

				<?php // if (!$isLoggedIn) echo $navMenu; ?>

				<?php if ( $showFollowWidget ): ?>
					<div class="sidebox">
						<?php FollowWidget::showWidget(); ?>
					</div>
				<?php endif; ?>
			</div><!--end sidebar-->
		<?php endif; // end if $showSideBar ?>
		<div class="clearall" ></div>
		</div> <!--end container -->
		</div><!--end main-->
			<div id="clear_footer"></div>
		</div><!--end main_container-->
		<div id="footer_outer">
			<div id="footer">
				<div id="footer_side">
					<?php
						if ( $isLoggedIn ) {
							echo wfMessage( 'site_footer_owl' )->parse();
						} else {
							echo wfMessage( 'site_footer_owl_anon' )->parse();
						}
					?>
				</div><!--end footer_side-->

				<div id="footer_main">

					<div id="sub_footer">
						<?php
							if ( $isLoggedIn || $isMainPage ) {
								echo wfMessage( 'sub_footer_new', wfGetPad(), wfGetPad() )->text();
							} else {
								echo wfMessage( 'sub_footer_new_anon', wfGetPad(), wfGetPad() )->text();
							}
						?>
					</div>
				</div><!--end footer_main-->
			</div>
				<br class="clearall" />
		</div><!--end footer-->
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

		<?php if ( $postLoadJS ): ?>
			<?php echo $this->html( 'headscripts' ) ?>
			<script type="text/javascript" src="<?php echo $fullJSuri ?>"></script>
		<?php endif; ?>
		<?php
		if ( $optimizelyJS ) {
			echo $optimizelyJS;
		}
		?>

		<?php if ( $showExitTimer ): ?>
			<script>
				<!--
				if (WH.ExitTimer) {
					WH.ExitTimer.start();
				}
				//-->
			</script>
		<?php endif; ?>

		<?php
		if ( $showRCWidget ) {
			RCWidget::showWidgetJS();
		}

		// Load event listeners all pages
		if ( class_exists( 'CTALinks' ) && trim( wfMessage( 'cta_feature' )->inContentLanguage()->text() ) == 'on' ) {
			echo CTALinks::getBlankCTA();
		}

		wfRunHooks( 'ArticleJustBeforeBodyClose' );

		if ( $showStaffStats ) {
			echo Pagestats::getJSsnippet( 'article' );
		}

		echo $wgOut->getBottomScripts();

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

}

