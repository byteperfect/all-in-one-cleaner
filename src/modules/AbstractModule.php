<?php
/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;

/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */
abstract class AbstractModule {
	/**
	 * Whether the module is initialized.
	 *
	 * @var bool
	 */
	protected bool $initialized = false;

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function initialize(): void {
		if ( $this->is_active() && true !== $this->initialized ) {
			$this->register_hooks();

			$this->initialized = true;
		}
	}

	/**
	 * Check if the required plugin is active.
	 *
	 * @return bool
	 */
	protected function is_active(): bool {
		return in_array(
			$this->get_plugin_slug(),
			apply_filters( 'active_plugins', get_option( 'active_plugins' ) ),
			true
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function register_hooks() {
		add_action( 'all_in_one_cleaner_register_settings_fields', array( $this, 'register_settings_fields' ) );
	}

	/**
	 * Register settings fields.
	 *
	 * @param Settings $settings Settings.
	 *
	 * @return void
	 */
	abstract public function register_settings_fields( Settings $settings ): void;

	/**
	 * Get the slug of the plugin for which the module is registered.
	 *
	 * @return string
	 */
	abstract protected function get_plugin_slug(): string;

	/**
	 * Get settings field prefix.
	 *
	 * @return string
	 */
	abstract protected function get_settings_field_prefix(): string;
}
