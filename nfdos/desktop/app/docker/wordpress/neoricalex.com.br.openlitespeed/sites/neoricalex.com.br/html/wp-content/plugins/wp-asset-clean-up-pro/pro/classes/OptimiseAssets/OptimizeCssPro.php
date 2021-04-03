<?php
namespace WpAssetCleanUpPro\OptimiseAssets;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Misc;
use WpAssetCleanUp\OptimiseAssets\OptimizeCss;
use WpAssetCleanUp\Plugin;
use WpAssetCleanUpPro\Positions;

/**
 * Class OptimizeCssPro
 * @package WpAssetCleanUpPro
 */
class OptimizeCssPro
{
	/**
	 *
	 */
	const CRITICAL_CSS_MARKER = '<meta data-name=wpacu-delimiter data-content="ASSET CLEANUP CRITICAL CSS" />';

	/**
	 *
	 */
	public function init()
	{
		add_action('wp_head', static function() {
			if ( Plugin::preventAnyChanges() || Main::isTestModeActive() || ! has_filter('wpacu_critical_css') ) { return; }
			echo self::CRITICAL_CSS_MARKER;
		}, -PHP_INT_MAX);

		add_filter('wpacu_local_fonts_display_css_output',   array($this, 'updateCssOutputFontDisplay'), 10, 2);
		add_filter('wpacu_local_fonts_display_style_inline', array($this, 'updateInlineCssOutputFontDisplay'), 10, 2); // alters $htmlSource
		add_filter('wpacu_change_css_position',              array($this, 'changeCssPosition'), 10, 1);
		add_filter('wpacu_alter_source_for_critical_css',    array($this, 'alterHtmlSourceForCriticalCss'));
		add_filter('wpacu_media_queries_load_for_css',       function($htmlSource) { return MatchMediaLoadPro::alterHtmlSourceForMediaQueriesLoadCss($htmlSource); });
	}

	/**
	 * @param $cssContent
	 * @param $enable
	 *
	 * @return mixed
	 */
	public function updateCssOutputFontDisplay($cssContent, $enable)
	{
		if (! $enable || ! preg_match('/@font-face(\s+|){/i', $cssContent)) {
			return $cssContent;
		}

		// "font-display" is enabled in "Settings" - "Local Fonts"
		return FontsLocalPro::alterLocalFontFaceFromCssContent($cssContent);
	}

	/**
	 * @param $htmlSource
	 * @param $status
	 *
	 * @return mixed
	 */
	public function updateInlineCssOutputFontDisplay($htmlSource, $status)
	{
		if (! $status) {
			return $htmlSource;
		}

		return FontsLocalPro::alterLocalFontFaceFromInlineStyleTags($htmlSource);
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed|void
	 */
	public function changeCssPosition($htmlSource)
	{
		return Positions::doChanges($htmlSource);
	}

	/**
	 * @param $htmlSource
	 *
	 * @return mixed
	 */
	public static function alterHtmlSourceForCriticalCss($htmlSource)
	{
		// The marker needs to be there
		if (strpos($htmlSource, self::CRITICAL_CSS_MARKER) === false) {
			return $htmlSource;
		}

		$criticalCssData = apply_filters('wpacu_critical_css', array('content' => false, 'minify' => false));

		if ( ! (isset($criticalCssData['content']) && $criticalCssData['content']) ) {
			// No critical CSS set? Return the HTML source as it is with the critical CSS location marker stripped
			return str_replace(self::CRITICAL_CSS_MARKER, '', $htmlSource);
		}

		$keepRenderBlockingList = ( isset( $criticalCssData['keep_render_blocking'] ) && $criticalCssData['keep_render_blocking'] ) ? $criticalCssData['keep_render_blocking'] : array();

		// If just a string was added (one in the list), convert it as an array with one item
		if (! is_array($keepRenderBlockingList)) {
			$keepRenderBlockingList = array($keepRenderBlockingList);
		}

		$doCssMinify            = isset( $criticalCssData['minify'] ) && $criticalCssData['minify']; // leave no room for any user errors in case the 'minify' parameter is unset by mistake
		$criticalCssContent     = OptimizeCss::maybeAlterContentForCssFile( $criticalCssData['content'], $doCssMinify, array( 'alter_font_face' ) );

		$criticalCssStyleTag = '<style id="wpacu-critical-css">'.$criticalCssContent.'</style>';

		/*
		 * By default the page will have the critical CSS applied as well as non-render blocking LINK tags (non-critical)
		 * For development purposes only, you can append:
		 * 1) /?wpacu_only_critical_css to ONLY load the critical CSS
		 * 2) /?wpacu_no_critical_css to ONLY load the non-render blocking LINK tags (non-critical)
		 * For a cleaner load, &wpacu_no_admin_bar can be added to avoid loading the top admin bar
		*/
		if (array_key_exists('wpacu_only_critical_css', $_GET)) {
			// For debugging purposes: preview how the page would load only with the critical CSS loaded (all LINK/STYLE CSS tags are stripped)
			$htmlSource = preg_replace('#<link[^>]*(stylesheet|(as(\s+|)=(\s+|)(|"|\')style(|"|\')))[^>]*(>)#Umi', '', $htmlSource);
			$htmlSource = preg_replace('@(<style[^>]*?>).*?</style>@si', '', $htmlSource);
			$htmlSource = str_replace(Misc::preloadAsyncCssFallbackOutput(true), '', $htmlSource);
		} else {
			// Convert render-blocking LINK CSS tags into non-render blocking ones
			$cleanerHtmlSource = preg_replace( '/<!--(.|\s)*?-->/', '', $htmlSource );
			$cleanerHtmlSource = preg_replace( '@<(noscript)[^>]*?>.*?</\\1>@si', '', $cleanerHtmlSource );

			preg_match_all( '#<link[^>]*(stylesheet|(as(\s+|)=(\s+|)(|"|\')style(|"|\')))[^>]*(>)#Umi',
				$cleanerHtmlSource, $matchesSourcesFromTags, PREG_SET_ORDER );

			if ( empty( $matchesSourcesFromTags ) ) {
				return $htmlSource;
			}

			foreach ( $matchesSourcesFromTags as $results ) {
				$matchedTag = $results[0];

				if (! empty($keepRenderBlockingList) && preg_match('#('.implode('|', $keepRenderBlockingList).')#Usmi', $matchedTag)) {
					continue;
				}

				// Marked for no alteration or for loading based on the media query match? Then, it's already non-render blocking and it has to be skipped!
				if (preg_match('#data-wpacu-skip([=>/ ])#i', $matchedTag)
				    || strpos($matchedTag, 'data-wpacu-apply-media-query=') !== false) {
					continue;
				}

				if ( preg_match( '#rel(\s+|)=(\s+|)([\'"])preload([\'"])#i', $matchedTag ) ) {
					if ( strpos( $matchedTag, 'data-wpacu-preload-css-basic=\'1\'' ) !== false ) {
						$htmlSource = str_replace( $matchedTag, '', $htmlSource );
					} elseif ( strpos( $matchedTag, 'data-wpacu-preload-it-async=\'1\'' ) !== false ) {
						continue; // already async preloaded
					} elseif ( strpos ($matchedTag, 'data-wpacu-skip-preload=\'1\'') !== false  ) {
						continue; // skip async preloaded (for debugging purposes)
					}
				} elseif ( preg_match( '#rel(\s+|)=(\s+|)([\'"])stylesheet([\'"])#i', $matchedTag ) ) {
					$matchedTagAlteredForPreload = str_ireplace(
						array(
							'<link ',
							'rel=\'stylesheet\'',
							'rel="stylesheet"',
							'id=\'',
							'id="',
							'data-wpacu-to-be-preloaded-basic=\'1\''
						),
						array(
							'<link rel=\'preload\' as=\'style\' data-wpacu-preload-it-async=\'1\' ',
							'onload="this.onload=null;this.rel=\'stylesheet\'"',
							'onload="this.onload=null;this.rel=\'stylesheet\'"',
							'id=\'wpacu-preload-',
							'id="wpacu-preload-',
							''
						),
						$matchedTag
					);

					$htmlSource = str_replace( $matchedTag, $matchedTagAlteredForPreload, $htmlSource );
				}
			}
		}

		// For debugging purposes: preview how the page would load without critical CSS & all the non-render blocking CSS files loaded
		// It should show a flash of unstyled content: https://en.wikipedia.org/wiki/Flash_of_unstyled_content
		if (array_key_exists('wpacu_no_critical_css', $_GET)) {
			$criticalCssStyleTag = '';
		}

		return str_replace(
			self::CRITICAL_CSS_MARKER,
			$criticalCssStyleTag . Misc::preloadAsyncCssFallbackOutput(),
			$htmlSource
		);
	}
}
