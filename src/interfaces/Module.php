<?php

namespace all_in_one_cleaner\interfaces;

use all_in_one_cleaner\Settings;

/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */
interface Module {
	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function initialize(): void;

	/**
	 * Register settings fields.
	 *
	 * @param Settings $settings Settings.
	 *
	 * @return void
	 */
	public function register_settings_fields( Settings $settings ): void;

	/**
	 * Get the version of supported handler.
	 *
	 * @return int
	 */
	public function get_handler_version(): int;

	/**
	 * Show notice that the handler is not compatible with the module.
	 *
	 * @return void
	 */
	public function handler_is_not_compatible_notice(): void;
}
