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

	}

	/**
	 * Use Twitter bento component for WordPress twitter embed block.
	 *
	 * @param string $block_content Block content.
	 * @param array  $block Block attributes.
	 * @return string
	 */
	public function bento_twitter( $block_content, $block ) {

		// use blockName to only affect the desired block.
		if ( 'core/embed' !== $block['blockName'] || 'twitter' !== $block['attrs']['providerNameSlug'] ) {
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
				<bento-twitter id="my-tweet" data-tweetid="885634330868850689">
				</bento-twitter>
			</div>
		</figure>
		<?php
		$output = ob_get_clean();

		// return the new content of the block.
		return $output;
	}

}
