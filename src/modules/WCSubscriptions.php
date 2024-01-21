<?php
/**
 * WooCommerce Subscriptions Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;
use Exception;

/**
 * WooCommerce Subscriptions Module.
 *
 * @package all_in_one_cleaner
 */
class WCSubscriptions extends AbstractModule {
	/**
	 * Get the slug of the plugin for which the module is registered.
	 *
	 * @return string
	 */
	protected function get_plugin_slug(): string {
		return 'woocommerce-subscriptions/woocommerce-subscriptions.php';
	}

	/**
	 * Get settings field prefix.
	 *
	 * @return string
	 */
	protected function get_settings_field_prefix(): string {
		return 'module_wc_subscriptions_';
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
			__( 'WooCommerce Subscriptions', 'all_in_one_cleaner' ),
			array(
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_subscriptions',
					__( 'Delete subscriptions', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Delete subscription.
	 *
	 * @param int $subscription_id Subscription ID.
	 *
	 * @return void
	 * @throws Exception If customer ID not found.
	 */
	public function task_shop_subscription( int $subscription_id ): void {
		if ( user_can( $this->get_customer_id( $subscription_id ), 'manage_options' ) ) {
			return;
		}

		if ( true === $this->get_option( 'delete_subscriptions' ) ) {
			wp_delete_post( $subscription_id, true );
		}
	}

	/**
	 * Get customer ID by order ID.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return int
	 *
	 * @throws Exception If customer ID not found.
	 */
	protected function get_customer_id( int $order_id ): int {
		$customer_id = get_post_meta( $order_id, '_customer_user', true );

		if ( false === $customer_id || '' === $customer_id ) {
			throw new Exception( 'Customer ID not found.' );
		}

		return (int) $customer_id;
	}
}
