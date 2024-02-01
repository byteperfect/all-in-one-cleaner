<?php
/**
 * Core Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;
use all_in_one_cleaner\Utils;
use Exception;
use WP_Post;

/**
 * Core Module.
 *
 * @package all_in_one_cleaner
 */
class Core extends AbstractModule {
	/**
	 * Module name.
	 *
	 * @var string
	 */
	protected string $module_name = 'Core';

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
					$this->get_settings_field_prefix() . 'delete_orphaned_users',
					__( 'Delete orphaned users', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'quick_deletion',
					__( 'Perform a quick deletion', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Set handler queue.
	 *
	 * @return string[]
	 */
	public function push_to_queue(): array {
		return array(
			'posts',
			'orphaned_posts',
			'orphaned_meta',
			'orphaned_users',
		);
	}

	/**
	 * Perform the task.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $item ) {
		if ( is_string( $item ) ) {
			if ( str_starts_with( $item, 'posts' ) ) {
				$item = $this->clear_posts( $item );
			} elseif ( str_starts_with( $item, 'orphaned_posts' ) ) {
				$item = $this->clear_orphaned_posts( $item );
			} elseif ( str_starts_with( $item, 'orphaned_meta' ) ) {
				$item = $this->clear_orphaned_meta();
			} elseif ( str_starts_with( $item, 'orphaned_users' ) ) {
				$item = $this->clear_orphaned_users();
			}
		}

		return $item;
	}

	/**
	 * Clear posts table.
	 *
	 * @param string $item Queue item to iterate over.
	 *
	 * @return string|false
	 */
	protected function clear_posts( string $item ) {
		$post_id = (int) substr( $item, 5 );

		$post = Utils::get_post( $post_id );
		if ( ! isset( $post->ID ) ) {
			all_in_one_cleaner()->log( 'No posts found.', __METHOD__ );

			return false;
		}

		$child_posts = Utils::get_child_posts( (int) $post->ID );
		foreach ( $child_posts as $child_post ) {
			try {
				do_action( 'all_in_one_cleaner_task_' . $child_post->post_type, (int) $child_post->ID, $post );
			} catch ( Exception $exception ) {
				all_in_one_cleaner()->log( $exception->getMessage(), __METHOD__ );

				break;
			}
		}

		try {
			do_action( 'all_in_one_cleaner_task_' . $post->post_type, (int) $post->ID, null );
		} catch ( Exception $exception ) {
			all_in_one_cleaner()->log( $exception->getMessage(), __METHOD__ );

			return false;
		}

		return 'posts' . $post->ID;
	}

	/**
	 * Clear orphaned posts.
	 *
	 * @param string $item Queue item to iterate over.
	 *
	 * @return string|false
	 */
	protected function clear_orphaned_posts( string $item ) {
		$post_id = (int) substr( $item, 14 );

		$orphaned_post = Utils::get_orphaned_post( $post_id );
		if ( ! isset( $orphaned_post->ID ) ) {
			all_in_one_cleaner()->log( 'No posts found.', __METHOD__ );

			return false;
		}

		try {
			do_action( 'all_in_one_cleaner_task_' . $orphaned_post->post_type, (int) $orphaned_post->ID, (int) $orphaned_post->post_parent );
		} catch ( Exception $exception ) {
			all_in_one_cleaner()->log( $exception->getMessage(), __METHOD__ );

			return false;
		}

		return 'orphaned_posts' . $orphaned_post->ID;
	}

	/**
	 * Clear orphaned meta.
	 *
	 * @return false
	 */
	protected function clear_orphaned_meta(): bool {
		Utils::delete_orphaned_meta();

		return false;
	}

	/**
	 * Clear orphaned users.
	 *
	 * @return false
	 */
	protected function clear_orphaned_users(): bool {
		if ( true === $this->get_option( 'delete_orphaned_users' ) ) {
			Utils::delete_orphaned_users();
		}

		return false;
	}

	/**
	 * Delete post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function task_post( int $post_id ): void {
		if ( true === $this->get_option( 'delete_posts' ) ) {
			$this->delete_post( $post_id );
		}
	}

	/**
	 * Delete post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function task_page( int $post_id ): void {
		if ( true === $this->get_option( 'delete_pages' ) ) {
			$this->delete_post( $post_id );
		}
	}

	/**
	 * Delete post.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return void
	 */
	public function task_revision( int $post_id ): void {
		if ( true === $this->get_option( 'delete_revisions' ) ) {
			$this->delete_post( $post_id );
		}
	}

	/**
	 * Check if the post can be deleted.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return bool
	 */
	protected function can_be_deleted( WP_Post $post ): bool {
		return true;
	}

	/**
	 * Get the version of supported handler.
	 *
	 * @return int
	 */
	public function get_handler_version(): int {
		return 1;
	}
}
