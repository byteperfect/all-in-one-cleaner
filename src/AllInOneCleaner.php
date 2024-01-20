<?php
/**
 * Class AllInOneCleaner.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner;

use all_in_one_cleaner\modules\AbstractModule;
use all_in_one_cleaner\modules\Core;
use all_in_one_cleaner\modules\WCVendors;

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
			$this->register_hooks();

			$this->get_settings()->initialize();

			foreach ( $this->get_modules() as $module ) {
				$module->initialize();
			}

			$this->get_handler();

			$initialized = true;
		}
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function register_hooks() {
		add_action( 'all_in_one_cleaner_on_save_fields', array( $this, 'dispatch_cleaner' ) );
	}

	/**
	 * Get settings.
	 *
	 * @return Settings
	 */
	public function get_settings(): Settings {
		static $settings;

		if ( is_null( $settings ) ) {
			$settings = new Settings();
		}

		return $settings;
	}

	/**
	 * Get list modules.
	 *
	 * @return array<AbstractModule>
	 */
	public function get_modules(): array {
		static $modules;

		if ( is_null( $modules ) ) {
			$modules = array(
				'Core'      => new Core(),
				'WCVendors' => new WCVendors(),
			);
		}

		return $modules;
	}

	/**
	 * Get handler.
	 *
	 * @return AllInOneCleanerHandler
	 */
	public function get_handler(): AllInOneCleanerHandler {
		static $handler;

		if ( is_null( $handler ) ) {
			$handler = new AllInOneCleanerHandler();
		}

		return $handler;
	}

	/**
	 * Dispatch cleaner.
	 *
	 * @return void
	 */
	public function dispatch_cleaner() {
		$data = (array) apply_filters( 'all_in_one_cleaner_push_to_queue', array() );

		if ( count( $data ) > 0 ) {
			$this->get_handler()->data( $data );
			$this->get_handler()->save();
			$this->get_handler()->dispatch();
		} else {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'The cleaner has not been dispatched because the queue is empty.' );
		}
	}
}
