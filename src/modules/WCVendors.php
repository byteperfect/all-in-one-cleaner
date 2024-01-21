<?php
/**
 * Core Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;
use stdClass;
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
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_commissions',
					__( 'Delete commissions', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Delete vendor order.
	 *
	 * @param int     $order_id    Order ID.
	 * @param WP_Post $parent_post WooCommerce order.
	 *
	 * @return void
	 */
	public function task_shop_order_vendor( int $order_id, WP_Post $parent_post ): void {
		if ( $this->get_option( 'delete_commissions' ) ) {
			$this->delete_commissions( $parent_post->ID );
		}

		if ( $this->get_option( 'delete_orders' ) ) {
			wp_delete_post( $order_id, true );
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
	protected function delete_commissions( int $order_id ): void {
		global $wpdb;

		$commissions = $this->get_commissions( $order_id );

		foreach ( $commissions as $commission ) {
			if ( user_can( (int) $commission->vendor_id, 'manage_options' ) ) {
				continue;
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->delete(
				$wpdb->prefix . 'pv_commission',
				array(
					'id' => $commission->id,
				)
			);
		}
	}

	/**
	 * Get commissions.
	 *
	 * This method retrieves commissions for a given order ID.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array<stdClass> An array of commissions.
	 */
	protected function get_commissions( int $order_id ): array {
		global $wpdb;

		$prefix = $wpdb->get_blog_prefix();

		$query = "SELECT * FROM {$prefix}pv_commission WHERE order_id = %d";

		$commissions = $wpdb->get_results( $wpdb->prepare( $query, $order_id ) ); // phpcs:ignore WordPress.DB

		return (array) $commissions;
	}
}
