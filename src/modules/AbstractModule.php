<?php
/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\interfaces\Module;
use all_in_one_cleaner\Settings;
use all_in_one_cleaner\Utils;
use WP_Post;

/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */
abstract class AbstractModule implements Module {
	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected string $module_name;

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

		$class_methods = get_class_methods( $this );
		foreach ( $class_methods as $class_method ) {
			if ( 0 === strpos( $class_method, 'task_' ) ) {
				$post_type = substr( $class_method, 5 );

				add_action( 'all_in_one_cleaner_task_' . $post_type, array( $this, $class_method ), 10, 2 );
			}

			if ( 'push_to_queue' === $class_method ) {
				add_action( 'all_in_one_cleaner_push_to_queue', array( $this, 'push_to_queue' ) );
			}

			if ( 'task' === $class_method ) {
				add_filter( 'all_in_one_cleaner_task', array( $this, 'task' ) );
			}
		}
	}

	/**
	 * Get the value of an option.
	 *
	 * @param string $option_name Option name.
	 *
	 * @return mixed
	 */
	protected function get_option( string $option_name ) {
		$option_name = $this->get_settings_field_prefix() . $option_name;

		return all_in_one_cleaner()->get_settings()->get( $option_name );
	}

	/**
	 * Deletes the given post.
	 *
	 * This method permanently deletes the specified post based on the post ID.
	 * If the 'all_in_one_cleaner_quick_deletion' filter returns true, it uses the Utils::delete_post method
	 * for deletion. Otherwise, it uses the WordPress function wp_delete_post.
	 *
	 * @param int $post_id The ID of the post to be deleted.
	 *
	 * @return void
	 */
	protected function delete_post( int $post_id ): void {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( ! $this->can_be_deleted( $post ) ) {
			return;
		}

		if ( apply_filters( 'all_in_one_cleaner_quick_deletion', true ) ) {
			Utils::delete_post( $post_id );
		} else {
			wp_delete_post( $post_id, true );
		}
	}

	/**
	 * Show notice that the handler is not compatible with the module.
	 *
	 * @return void
	 */
	public function handler_is_not_compatible_notice(): void {
		$message = sprintf(
		/* translators: %s: module name */
			__( 'The "%s" module will not be activated due to a version mismatch between the handler and the main module. Please update the add-on.', 'all_in_one_cleaner' ),
			$this->module_name
		);

		echo '<div class="error"><p>' . esc_html( $message ) . '</p></div>';
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

	/**
	 * Check if the post can be deleted.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return bool
	 */
	abstract protected function can_be_deleted( WP_Post $post ): bool;

	/**
	 * Get the version of supported handler.
	 *
	 * @return int
	 */
	abstract public function get_handler_version(): int;
}
