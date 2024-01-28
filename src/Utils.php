<?php
/**
 * Class Utils.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

// phpcs:disable WordPress.DB.DirectDatabaseQuery

namespace all_in_one_cleaner;

use stdClass;
use WP_Post;

/**
 * Class Utils.
 *
 * @package all_in_one_cleaner
 */
class Utils {
	/**
	 * Trashes or deletes a post or page.
	 *
	 *  When the post and page is deleted, everything that is tied to it is deleted also.
	 *  This includes comments, post meta fields, and terms associated with the post.
	 *
	 * @param int $post_id The ID of the post to be deleted.
	 *
	 * @return void
	 */
	public static function delete_post( int $post_id ): void {
		global $wpdb;

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		self::delete_object_term_relationships( $post );

		if ( 'attachment' === $post->post_type ) {
			self::delete_attachment_files( $post_id );
		} else {
			self::delete_post_revisions( $post_id );
		}

		self::delete_post_comments( $post_id );

		self::delete_post_meta( $post_id );

		$wpdb->delete( $wpdb->posts, array( 'ID' => $post_id ) );

		clean_post_cache( $post );
	}

	/**
	 * Deletes all term relationships associated with the given post.
	 *
	 * This method removes all term relationships for the given post. This includes relationships
	 * with categories, tags, and any other taxonomies that the post type is associated with.
	 *
	 * @param WP_Post $post The post object whose term relationships are to be deleted.
	 *
	 * @return void
	 */
	protected static function delete_object_term_relationships( WP_Post $post ): void {
		$taxonomies = array_unique(
			array_merge(
				get_object_taxonomies( $post->post_type ),
				array( 'category', 'post_tag' )
			)
		);

		wp_delete_object_term_relationships( $post->ID, $taxonomies );
	}

	/**
	 * Deletes all attachment files associated with the given post.
	 *
	 * This method removes all attachment files for the given post.
	 * If the site is a multisite, the directory size cache is cleaned.
	 *
	 * @param int $post_id The ID of the post whose attachment files are to be deleted.
	 *
	 * @return void
	 */
	protected static function delete_attachment_files( int $post_id ): void {
		$meta         = wp_get_attachment_metadata( $post_id );
		$backup_sizes = get_post_meta( $post_id, '_wp_attachment_backup_sizes', true );
		$file         = get_attached_file( $post_id );
		if ( false !== $meta && false !== $file ) {
			wp_delete_attachment_files( $post_id, $meta, $backup_sizes, $file );

			if ( is_multisite() ) {
				clean_dirsize_cache( $file );
			}
		}
	}

	/**
	 * Deletes all revisions of a given post.
	 *
	 * This method retrieves all revisions of the specified post using a direct database query.
	 * It then iterates over each revision and deletes it by calling the delete_post method.
	 *
	 * @param int $post_id The ID of the post whose revisions are to be deleted.
	 *
	 * @return void
	 */
	protected static function delete_post_revisions( int $post_id ): void {
		global $wpdb;

		$post_revision_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'revision'", $post_id )
		);

		foreach ( $post_revision_ids as $revision_id ) {
			self::delete_post( (int) $revision_id );
		}
	}

	/**
	 * Deletes all comments associated with the given post.
	 *
	 * This method retrieves all comments of the specified post using a direct database query.
	 * It then iterates over each comment and deletes it. After deleting the comment, it also
	 * deletes the associated comment metadata. If there are any comments associated with the post,
	 * the comment cache is cleaned.
	 *
	 * @param int $post_id The ID of the post whose comments are to be deleted.
	 *
	 * @return void
	 */
	protected static function delete_post_comments( int $post_id ): void {
		global $wpdb;

		$comment_ids = $wpdb->get_col(
			$wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_post_ID = %d", $post_id )
		);

		if ( count( $comment_ids ) > 0 ) {
			foreach ( $comment_ids as $comment_id ) {
				$wpdb->delete(
					$wpdb->commentmeta,
					array(
						'comment_id' => $comment_id,
					)
				);
				$wpdb->delete(
					$wpdb->comments,
					array(
						'comment_ID' => $comment_id,
					)
				);
			}

			clean_comment_cache( $comment_ids );
		}
	}

	/**
	 * Deletes all metadata associated with the given post.
	 *
	 * This method removes all metadata for the given post. This includes all custom fields
	 * and other meta information associated with the post. The deletion is performed using a direct
	 * database query on the WordPress postmeta table.
	 *
	 * @param int $post_id The ID of the post whose metadata are to be deleted.
	 *
	 * @return void
	 */
	protected static function delete_post_meta( int $post_id ): void {
		global $wpdb;

		$wpdb->delete(
			$wpdb->postmeta,
			array(
				'post_id' => $post_id,
			)
		);
	}

	/**
	 * Get post.
	 *
	 * @param int $post_id ID of the previously processed post.
	 *
	 * @return stdClass
	 */
	public static function get_post( int $post_id ): stdClass {
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
	public static function get_child_posts( int $parent_post_id ): array {
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
	 * Get orphaned post.
	 *
	 * @param int $post_id ID of the previously processed post.
	 *
	 * @return stdClass
	 */
	public static function get_orphaned_post( int $post_id ): stdClass {
		global $wpdb;

		if ( 0 === $post_id ) {
			$query = <<<EOQ
SELECT p.ID, p.post_type, p.post_parent
FROM hp_posts AS p
LEFT JOIN hp_posts AS pp ON p.post_parent = pp.ID
WHERE p.post_parent <> 0 AND pp.ID IS NULL
ORDER BY p.ID DESC
LIMIT 1;
EOQ;
		} else {
			$query = <<<EOQ
SELECT p.ID, p.post_type, p.post_parent
FROM hp_posts AS p
LEFT JOIN hp_posts AS pp ON p.post_parent = pp.ID
WHERE p.post_parent <> 0 AND p.ID < $post_id AND pp.ID IS NULL
ORDER BY p.ID DESC
LIMIT 1;
EOQ;
		}

		// phpcs:ignore WordPress.DB
		return (object) $wpdb->get_row( $query );
	}

	/**
	 * Deletes orphaned metadata from the WordPress database.
	 *
	 * This method executes a direct SQL query on the WordPress database to delete orphaned metadata.
	 * Orphaned metadata is defined as metadata that is associated with a post that no longer exists in the database.
	 *
	 * @return void
	 */
	public static function delete_orphaned_meta(): void {
		global $wpdb;

		$wpdb->query(
			"DELETE pm FROM $wpdb->postmeta pm LEFT JOIN $wpdb->posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL"
		);
	}

	/**
	 * Deletes orphaned users from the WordPress database.
	 *
	 * This method executes a direct SQL query on the WordPress database to delete orphaned users.
	 * Orphaned users are defined as users that do not have any posts associated with them.
	 *
	 * @return void
	 */
	public static function delete_orphaned_users(): void {
		global $wpdb;

		$wpdb->query(
			"DELETE FROM $wpdb->users WHERE ID NOT IN (SELECT DISTINCT post_author FROM $wpdb->posts)"
		);

		$wpdb->query(
			"DELETE FROM $wpdb->usermeta WHERE user_id NOT IN (SELECT DISTINCT ID FROM $wpdb->users)"
		);

		$wpdb->query(
			"DELETE FROM $wpdb->links WHERE link_owner NOT IN (SELECT DISTINCT ID FROM $wpdb->users)"
		);
	}

	/**
	 * Checks if a post is orphaned in the WordPress database.
	 *
	 * This method executes a direct SQL query on the WordPress database to check if a post is orphaned.
	 * An orphaned post is a post that has a parent post specified, but the parent post does not exist in the database.
	 * The SQL query counts the number of posts with the given parent ID in the posts table.
	 * If the count is zero, the post is considered orphaned and the method returns true.
	 * Otherwise, the method returns false.
	 *
	 * @param WP_Post $post The post to be checked.
	 *
	 * @return bool True if the post is orphaned, false otherwise.
	 */
	public static function is_orphaned_post( WP_Post $post ): bool {
		global $wpdb;

		// If the post does not have a parent, it cannot be orphaned, so return false.
		if ( 0 === $post->post_parent ) {
			return false;
		}

		// Prepare a SQL query to count the number of posts with the given parent ID.
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(ID) FROM $wpdb->posts WHERE ID=%d",
				$post->post_parent
			)
		);

		// If the count is zero, the post is considered orphaned and the method returns true.
		// Otherwise, the method returns false.
		return ! $result;
	}
}
