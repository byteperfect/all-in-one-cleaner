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
					$this->get_settings_field_prefix() . 'quick_deletion',
					__( 'Perform a quick deletion', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function register_hooks() {
		parent::register_hooks();

		add_filter( 'all_in_one_cleaner_push_to_queue', array( $this, 'push_to_queue' ), - PHP_INT_MAX );
		add_filter( 'all_in_one_cleaner_task', array( $this, 'task' ) );
	}

	/**
	 * Set handler queue.
	 *
	 * @return string[]
	 */
	public function push_to_queue(): array {
		return array(
			'posts',
			'options',
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
			} elseif ( str_starts_with( $item, 'options' ) ) {
				$item = $this->clear_options( $item );
			}
		} else {
			$item = false;
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

		$parent_post = $this->get_post( $post_id );
		if ( ! isset( $parent_post->ID ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			error_log( 'No posts found.' );

			return false;
		}

		$child_posts = $this->get_child_posts( (int) $parent_post->ID );
		foreach ( $child_posts as $child_post ) {
			try {
				do_action( 'all_in_one_cleaner_task_' . $child_post->post_type, $child_post->ID, $parent_post );
			} catch ( Exception $exception ) {
				all_in_one_cleaner()->log( $exception->getMessage(), __METHOD__ );

				break;
			}
		}

		try {
			do_action( 'all_in_one_cleaner_task_' . $parent_post->post_type, $parent_post->ID );
		} catch ( Exception $exception ) {
			all_in_one_cleaner()->log( $exception->getMessage(), __METHOD__ );

			return false;
		}

		return 'posts' . $parent_post->ID;
	}

	/**
	 * Get post.
	 *
	 * @param int $post_id ID of the previously processed post.
	 *
	 * @return object
	 */
	protected function get_post( int $post_id ): object {
		global $wpdb;

		if ( 0 === $post_id ) {
			$query = <<<EOQ
SELECT ID, post_type
FROM $wpdb->posts
WHERE post_parent = 0
ORDER BY ID DESC
LIMIT 1;
EOQ;
		} else {
			$query = <<<EOQ
SELECT ID, post_type
FROM $wpdb->posts
WHERE post_parent = 0 AND ID < $post_id
ORDER BY ID DESC
LIMIT 1;
EOQ;
		}

		// phpcs:ignore WordPress.DB
		return (object) $wpdb->get_row( $query );
	}

	/**
	 * Get child posts.
	 *
	 * @param int $parent_post_id Parent post ID.
	 *
	 * @return stdClass[]
	 */
	protected function get_child_posts( int $parent_post_id ): array {
		global $wpdb;

		// phpcs:ignore WordPress.DB
		$query = <<<EOQ
SELECT ID, post_type
FROM $wpdb->posts
WHERE post_parent = $parent_post_id
ORDER BY ID DESC;
EOQ;

		// phpcs:ignore WordPress.DB
		return (array) $wpdb->get_results( $query );
	}

	/**
	 * Clear options.
	 *
	 * @param string $item Queue item to iterate over.
	 *
	 * @return string|false
	 */
	protected function clear_options( string $item ) {
		if ( true === $this->get_option( 'clear_options' ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions
			error_log( 'clear_options.' );
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
	 * Check if the post can be deleted.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return bool
	 */
	protected function can_be_deleted( WP_Post $post ): bool {
		return true;
	}
}
