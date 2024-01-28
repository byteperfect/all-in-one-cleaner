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
use WP_Post;

/**
 * Class WCVendors
 *
 * This class extends the AbstractModule class and provides functionalities for WC Vendors plugin.
 */
class WCVendors extends AbstractModule {
	/**
	 * Get the slug of the plugin for which the module is registered.
	 *
	 * @return string
	 */
	protected function get_plugin_slug(): string {
		return 'wc-vendors/class-wc-vendors.php';
	}

	/**
	 * Get settings field prefix.
	 *
	 * @return string
	 */
	protected function get_settings_field_prefix(): string {
		return 'module_wc_vendors_';
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
			__( 'WooCommerce Vendors', 'all_in_one_cleaner' ),
			array(
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_orders',
					__( 'Delete orders', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Set handler queue.
	 *
	 * @param array<string> $queue Queue.
	 *
	 * @return array<string>
	 */
	public function push_to_queue( array $queue ): array {
		$queue[] = 'orphaned_commissions';

		return $queue;
	}

	/**
	 * Perform the task.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	public function task( $item ) {
		if ( 'orphaned_commissions' === $item ) {
			$item = $this->delete_orphaned_commissions();
		}

		return $item;
	}

	/**
	 * Delete vendor order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function task_shop_order_vendor( int $order_id ): void {
		if ( true === $this->get_option( 'delete_orders' ) ) {
			$this->delete_post( $order_id );
		}

		/**
		 * @todo: Нужно удалить запись из таблицы hp_actionscheduler_actions, где args содержит order_id: args LIKE '%117882%'.
		 * action_id, hook, status, scheduled_date_gmt, scheduled_date_local, args, schedule, group_id, attempts, last_attempt_gmt, last_attempt_local, claim_id, extended_args, modified_gmt, modified_local, failed_at_gmt, failed_at_local, completed_at_gmt, completed_at_local, claim_id, extended_args, modified_gmt, modified_local, failed_at_gmt, failed_at_local, completed_at_gmt, completed_at_local
		 * 251079,woocommerce_deliver_webhook_async,complete,2023-12-19 15:59:10,2023-12-19 10:59:10,"{""webhook_id"":26,""arg"":117882}","O:30:""ActionScheduler_SimpleSchedule"":2:{s:22:"" * scheduled_timestamp"";i:1703001550;s:41:"" ActionScheduler_SimpleSchedule timestamp"";i:1703001550;}",2,1,2023-12-19 15:59:25,2023-12-19 10:59:25,0,,10
		 */
	}

	/**
	 * Delete commissions.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function task_shop_order( int $order_id ): void {
		$this->delete_commissions( $order_id );
	}

	/**
	 * Delete commissions.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function task_shop_order_refund( int $order_id ): void {
		$this->delete_commissions( $order_id );
	}

	/**
	 * Delete commissions.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function delete_commissions( int $order_id ): void {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->delete(
			$wpdb->prefix . 'pv_commission',
			array( 'order_id' => $order_id ),
			array( '%d' )
		);

		if ( false === $result ) {
			all_in_one_cleaner()->log(
				$wpdb->last_error,
				array(
					'caller'   => __METHOD__,
					'order_id' => $order_id,
				)
			);
		}
	}

	/**
	 * Delete orphaned commissions.
	 *
	 * @return bool
	 *
	 * @todo: How to correctly call this method in context of background process?
	 */
	protected function delete_orphaned_commissions(): bool {
		global $wpdb;

		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$result = $wpdb->query(
			"DELETE FROM {$wpdb->prefix}pv_commission WHERE order_id NOT IN (SELECT ID FROM {$wpdb->posts})"
		);

		if ( false === $result ) {
			all_in_one_cleaner()->log(
				$wpdb->last_error,
				array(
					'caller' => __METHOD__,
				)
			);
		}

		return false;
	}

	/**
	 * Check if the post can be deleted.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return bool
	 */
	protected function can_be_deleted( WP_Post $post ): bool {
		if ( Utils::is_orphaned_post( $post ) ) {
			return true;
		}

		return ! user_can( (int) $post->post_author, 'manage_options' );
	}
}
