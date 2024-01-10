<?php
/**
 * Core Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;

/**
 * Core Module.
 *
 * @package all_in_one_cleaner
 */
class Core extends AbstractModule {
	/**
	 * Get the slug of the plugin for which the module is registered.
	 *
	 * @return string
	 */
	protected function get_plugin_slug(): string {
		return 'all-in-one-cleaner/all-in-one-cleaner.php';
	}

	/**
	 * Get settings field prefix.
	 *
	 * @return string
	 */
	protected function get_settings_field_prefix(): string {
		return 'module_core_';
	}

	/**
	 * Register settings fields.
	 *
	 * @param Settings $settings Settings.
	 *
	 * @return void
	 */
	public function register_settings_fields( Settings $settings ): void {
		$settings->add_tab(
			__( 'Core', 'all_in_one_cleaner' ),
			array(
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_posts',
					__( 'Delete posts', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_pages',
					__( 'Delete pages', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'clear_options',
					__( 'Clear options', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Handle data deletion.
	 *
	 * @param Settings $settings Settings.
	 *
	 * @return void
	 */
	public function handle( Settings $settings ): void {
	}
}
