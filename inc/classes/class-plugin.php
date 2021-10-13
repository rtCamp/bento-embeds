<?php
/**
 * Plugin manifest class.
 *
 * @package bento-embeds
 */

namespace Bento_Embeds\Features\Inc;

use \Bento_Embeds\Features\Inc\Traits\Singleton;

/**
 * Class Plugin
 */
class Plugin {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {

		add_filter( 'render_block', [ $this, 'bento_twitter' ], 10, 2 );
		add_filter( 'render_block', [ $this, 'bento_youtube' ], 10, 2 );
		add_filter( 'render_block', [ $this, 'bento_soundcloud' ], 10, 2 );

	}

	/**
	 * Use Twitter bento component for WordPress twitter embed block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function bento_twitter( $block_content, $block ) {

		$load_bento_components = get_post_meta( get_the_ID(), 'load_bento_components', true );

		$load_bento_components = boolval( $load_bento_components );

		// use blockName to only affect the desired block.
		if ( false === $load_bento_components || 'core/embed' !== $block['blockName'] || 'twitter' !== $block['attrs']['providerNameSlug'] ) {
			return $block_content;
		}

		preg_match( '/^http(s|):\/\/twitter\.com(\/\#\!\/|\/)([a-zA-Z0-9_]{1,20})\/status(es)*\/(\d+)$/', $block['attrs']['url'], $urlbits );

		$tweet_id = ! empty( $urlbits[5] ) ? $urlbits[5] : false;

		if ( empty( $tweet_id ) ) {
			return $block_content;
		}

		$output = '';

		ob_start()
		?>
		<script src="https://cdn.ampproject.org/custom-elements-polyfill.js"></script>
		<script async custom-element="amp-twitter" src="https://cdn.ampproject.org/v0/amp-twitter-1.0.js"></script>
		<link rel="stylesheet" href="https://cdn.ampproject.org/v0/amp-twitter-1.0.css">
		<style>
			bento-twitter {
			width: 375px;
			height: 620px;
			}
		</style>
		<figure class="wp-block-embed is-type-rich is-provider-twitter wp-block-embed-twitter"><div class="wp-block-embed__wrapper">
			<div class="twitter-tweet twitter-tweet-rendered" style="display: flex; max-width: 550px; width: 100%; margin-top: 10px; margin-bottom: 10px;">
				<bento-twitter id="my-tweet" data-tweetid="<?php echo esc_attr( $tweet_id ); ?>">
				</bento-twitter>
			</div>
		</figure>
		<?php
		$output = ob_get_clean();

		// return the new content of the block.
		return $output;
	}

	/**
	 * Use Twitter bento component for WordPress twitter embed block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function bento_youtube( $block_content, $block ) {

		$load_bento_components = get_post_meta( get_the_ID(), 'load_bento_components', true );

		$load_bento_components = boolval( $load_bento_components );

		// use blockName to only affect the desired block.
		if ( false === $load_bento_components || 'core/embed' !== $block['blockName'] || 'youtube' !== $block['attrs']['providerNameSlug'] ) {
			return $block_content;
		}

		$video_id = $this->get_youtube_id( $block['attrs']['url'] );

		$output = '';

		ob_start()
		?>
		<script src="https://cdn.ampproject.org/custom-elements-polyfill.js"></script>
		<script async custom-element="amp-youtube" src="https://cdn.ampproject.org/v0/amp-youtube-1.0.js"></script>
		<link rel="stylesheet" href="https://cdn.ampproject.org/v0/amp-youtube-1.0.css">

		<figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
			<div class="wp-block-embed__wrapper">
				<bento-youtube
				data-videoid="<?php echo esc_attr( $video_id ); ?>"
				layout="responsive"
				width="480"
				height="270"
				></bento-youtube>
			</div>
		</figure>
		<?php
		$output = ob_get_clean();

		// return the new content of the block.
		return $output;
	}

	/**
	 * Use Twitter bento component for WordPress twitter embed block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function bento_soundcloud( $block_content, $block ) {

		$load_bento_components = get_post_meta( get_the_ID(), 'load_bento_components', true );

		$load_bento_components = boolval( $load_bento_components );

		// use blockName to only affect the desired block.
		if ( false === $load_bento_components || 'core/embed' !== $block['blockName'] || 'soundcloud' !== $block['attrs']['providerNameSlug'] ) {
			return $block_content;
		}

		preg_match( '/src="([^"]+)"/', $block_content, $match );

		$url = urldecode( $match[1] );

		$track_id = $this->extract_params_from_iframe_src( $url );

		$output = '';

		ob_start()
		?>
		<script src="https://cdn.ampproject.org/custom-elements-polyfill.js"></script>
		<style data-bento-boilerplate>
			bento-soundcloud {
			display: block;
			overflow: hidden;
			position: relative;
			}
		</style>
		<script async src="https://cdn.ampproject.org/v0/bento-soundcloud-1.0.js"></script>
		<style>
			bento-soundcloud {
			aspect-ratio: 16/9;
			}
		</style>
		<link rel="stylesheet" href="https://cdn.ampproject.org/v0/amp-soundcloud-1.0.css">

		<figure class="wp-block-embed is-type-rich is-provider-soundcloud wp-block-embed-soundcloud">
			<div class="wp-block-embed__wrapper">
				<bento-soundcloud
				width="480"
				height="480"
				layout="responsive"
				data-trackid="<?php echo esc_attr( $track_id['track_id'] ); ?>"
				data-visual="true"
				></bento-soundcloud>
			</div>
		</figure>
		<?php
		$output = ob_get_clean();

		// return the new content of the block.
		return $output;
	}

	/**
	 * Get params from Soundcloud iframe src.
	 *
	 * @param string $url URL.
	 * @return array Params extracted from URL.
	 */
	private function extract_params_from_iframe_src( $url ) {

		$query = [];

		parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $query );

		$parsed_url = wp_parse_url( $query['url'] );

		if ( preg_match( '#tracks/(?P<track_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'track_id' => $matches['track_id'],
			];
		}
		if ( preg_match( '#playlists/(?P<playlist_id>\d+)#', $parsed_url['path'], $matches ) ) {
			return [
				'playlist_id' => $matches['playlist_id'],
			];
		}
		return [];
	}

	/**
	 * Get youtube id from youtube url.
	 *
	 * @param string $video_source Youtube URL.
	 *
	 * @return bool|string|string[]|null
	 */
	protected function get_youtube_id( $video_source ) {

		$video_id = '';

		if ( ! empty( $video_source ) ) {

			$url = trim( $video_source, ' "' );
			$url = trim( $url );
			$url = str_replace( array( 'youtu.be/', '/v/', '#!v=', '&amp;', '&#038;', 'playlist' ), array( 'youtu.be/?v=', '/?v=', '?v=', '&', '&', 'videoseries' ), $url );

			// Replace any extra question marks with ampersands - the result of a URL like "http://www.youtube.com/v/9FhMMmqzbD8?fs=1&hl=en_US" being passed in.
			$query_string_start = strpos( $url, '?' );

			if ( false !== $query_string_start ) {
				$url = substr( $url, 0, $query_string_start + 1 ) . str_replace( '?', '&', substr( $url, $query_string_start + 1 ) );
			}

			$url = wp_parse_url( $url );

			if ( isset( $url['query'] ) ) {

				parse_str( $url['query'], $qargs );

				if ( isset( $qargs['list'] ) ) {
					$video_id = preg_replace( '|[^_a-z0-9-]|i', '', $qargs['list'] );
				}

				if ( empty( $video_id ) ) {
					$video_id = preg_replace( '|[^_a-z0-9-]|i', '', $qargs['v'] );
				}
			}
		}

		return $video_id;
	}

}
