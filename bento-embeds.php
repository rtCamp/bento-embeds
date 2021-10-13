<?php
/**
 * Plugin Name: Blocks Embeds
 * Description: Adds new block variations of existing blocks, which will utilize the Bento components.
 * Plugin URI:  https://rtcamp.com
 * Author:      rtCamp
 * Author URI:  https://rtcamp.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Version:     1.0
 * Text Domain: bento-embeds
 *
 * @package bento-embeds
 */

define( 'BENTO_EMBEDS_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'BENTO_EMBEDS_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'BENTO_EMBEDS_BUILD_URI', BENTO_EMBEDS_PATH . '/assets/build' );

require_once BENTO_EMBEDS_PATH . '/inc/helpers/autoloader.php';
require_once BENTO_EMBEDS_PATH . '/inc/helpers/helper-functions.php';

/**
 * To load plugin manifest class.
 *
 * @return void
 */
function bento_embeds_features_plugin_loader() {
	\Bento_Embeds\Features\Inc\MetaBox::get_instance();
	\Bento_Embeds\Features\Inc\Plugin::get_instance();
}

bento_embeds_features_plugin_loader();
