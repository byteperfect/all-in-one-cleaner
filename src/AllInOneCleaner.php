<?php
/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner;

/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */
class AllInOneCleaner {
	/**
	 * Get instance of AllInOneCleaner.
	 *
	 * @return AllInOneCleaner
	 */
	public static function instance(): AllInOneCleaner {
		static $instance;

		// Instantiate only once.
		if ( is_null( $instance ) ) {
			$instance = new AllInOneCleaner();

			$instance->initialize();
		}

		return $instance;
	}

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	protected function initialize(): void {
		static $initialized;

		if ( true !== $initialized ) {
			$initialized = true;
		}
	}
}
