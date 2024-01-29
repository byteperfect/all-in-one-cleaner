<?php
/**
 * Plugin Name:       All-In-One Cleaner
 * Plugin URI:        https://wordpress.org/plugins/all-in-one-cleaner/
 * Description:       The plugin allows you to clean up your WordPress site.
 * Version:           0.1.0
 * Author:            Aleksandr Levashov <aleksandr@byteperfect.dev>
 * Author URI:        https://byteperfect.dev/
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Text Domain:       all-in-one-cleaner
 * Domain Path:       /languages/
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

use all_in_one_cleaner\AllInOneCleaner;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ALL_IN_ONE_CLEANER_PLUGIN_FILE' ) ) {
	define( 'ALL_IN_ONE_CLEANER_PLUGIN_FILE', __FILE__ );
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main function responsible for returning the one true All-In-One Cleaner Instance to functions everywhere.
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php $all_in_one_cleaner = all_in_one_cleaner(); ?>
 *
 * @return AllInOneCleaner
 */
function all_in_one_cleaner(): AllInOneCleaner {
	return AllInOneCleaner::instance();
}

/** Initialization of the plugin. */
all_in_one_cleaner();
