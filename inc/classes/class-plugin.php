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

		add_filter( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );
		add_filter( 'script_loader_tag', [ $this, 'make_script_async' ], 10, 2 );

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
		<style data-bento-boilerplate>
			bento-soundcloud {
			display: block;
			overflow: hidden;
			position: relative;
			}
		</style>
		<style>
			bento-soundcloud {
				aspect-ratio: 16/9;
			}
		</style>
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

	/**
	 * Load scripts
	 *
	 * @return void
	 */
	public function load_scripts() {

		$load_bento_components = get_post_meta( get_the_ID(), 'load_bento_components', true );

		$load_bento_components = boolval( $load_bento_components );

		if ( ! $load_bento_components || is_amp_request() ) {
			return;
		}

		$scripts = [
			'twitter'    => 'bento-twitter-1.0',
			'youtube'    => 'bento-youtube-1.0',
			'soundcloud' => 'bento-soundcloud-1.0',
		];

		foreach ( $scripts as $embed => $script_name ) {
			if ( $this->has_block( 'core/embed', $embed ) ) {
				wp_enqueue_style( sprintf( 'bento-embeds-amp-%1$s-style', $embed ), sprintf( '/dist/v0/%1$s.css', $script_name ), [], null );
				wp_enqueue_script( sprintf( 'bento-embeds-amp-%1$s', $embed ), sprintf( '/dist/v0/%1$s.js', $script_name ), [], null, true );
			}
		}
		wp_enqueue_script( 'bento-js', '/dist/bento.js', [], null, true );

	}

	/**
	 * Load bento embed scripts async.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 *
	 * @return string Script loader tag.
	 */
	public function make_script_async( $tag, $handle ) {

		$scripts = [
			'twitter',
			'soundcloud',
			'youtube',
		];

		foreach ( $scripts as $script_name ) {

			if ( sprintf( 'bento-embeds-amp-%1$s', $script_name ) === $handle ) {
				return str_replace( '<script', sprintf( '<script async custom-element="amp-%1$s"', $script_name ), $tag );
			}
		}

		return $tag;
	}

	/**
	 * Checks if the post has a block with given embed provider.
	 *
	 * @param string                  $block_name     Block name.
	 * @param string                  $embed_provider Embed provider.
	 * @param int|string|WP_Post|null $post Optional. Post content, post ID, or post object. Defaults to global $post.
	 * @return boolean
	 */
	public function has_block( $block_name, $embed_provider, $post = null ) {

		if ( ! has_blocks( $post ) ) {
			return false;
		}

		if ( ! is_string( $post ) ) {
			$wp_post = get_post( $post );

			if ( $wp_post instanceof \WP_Post ) {
				$post = $wp_post->post_content;
			}
		}

		/*
		 * Normalize block name to include namespace, if provided as non-namespaced.
		 * This matches behavior for WordPress 5.0.0 - 5.3.0 in matching blocks by
		 * their serialized names.
		 */
		if ( false === strpos( $block_name, '/' ) ) {
			$block_name = 'core/' . $block_name;
		}

		// Test for existence of block by its fully qualified name.
		$has_block = false !== strpos( $post, '<!-- wp:' . $block_name . ' ' );

		if ( ! $has_block ) {
			/*
			 * If the given block name would serialize to a different name, test for
			 * existence by the serialized form.
			 */
			$serialized_block_name = strip_core_block_namespace( $block_name );

			if ( $serialized_block_name !== $block_name ) {
				$has_block = false !== strpos( $post, '<!-- wp:' . $serialized_block_name . ' ' );
				$has_type  = false !== strpos( $post, sprintf( '"providerNameSlug":"%1$s', $embed_provider ) );
			}
		}

		return ( $has_block && $has_type );
	}

}
