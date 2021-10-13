<?php
/**
 * Theme widgets.
 *
 * @package Amp-Wp-Org
 */

namespace Bento_Embeds\Features\Inc;

use Bento_Embeds\Features\Inc\Traits\Singleton;

/**
 * Class Assets
 */
class MetaBox {

	use Singleton;

	/**
	 * Construct method.
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * To register action/filter.
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		/**
		 * Action
		 */
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post', [ $this, 'save_post_options' ] );

	}

	/**
	 * To add meta box.
	 *
	 * @return void
	 */
	public function add_meta_boxes() {

		add_meta_box(
			'load_bento_components',
			'Page options',
			[ $this, 'render_meta_box' ],
			null,
			'side',
			'default'
		);

	}

	/**
	 * Renders the page options metabox content.
	 */
	public function render_meta_box() {

		$load_bento_components = get_post_meta( get_the_ID(), 'load_bento_components', true );
		$load_bento_components = boolval( $load_bento_components );

		?>
		<input type="checkbox" id="show-title" name="load_bento_components" value="1" <?php checked( $load_bento_components ); ?>>
		<label for="show-title" ><?php esc_html_e( 'Load Bento Embeds', 'bento-embeds' ); ?></label>
		<?php
	}

	/**
	 * Saves page options field data to the post meta.
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_post_options( $post_id ) {

		$load_bento = filter_input( INPUT_POST, 'load_bento_components', FILTER_SANITIZE_STRING );
		$load_bento = empty( $load_bento ) ? false : $load_bento;

		update_post_meta(
			$post_id,
			'load_bento_components',
			$load_bento
		);
	}

}
