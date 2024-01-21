<?php
/**
 * WooCommerce Subscriptions Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;

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
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_subscribers',
					__( 'Delete subscribers', 'all_in_one_cleaner' )
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
	 */
	public function task_shop_subscription( int $subscription_id ): void {
		if ( $this->get_option( 'delete_subscriptions' ) ) {
			wp_delete_post( $subscription_id, true );
		}
	}

	/**
	 * Delete subscriber.
	 *
	 * @param int $subscriber_id Subscriber ID.
	 *
	 * @return void
	 */
	public function task_subscriber( int $subscriber_id ): void {
		if ( $this->get_option( 'delete_subscribers' ) ) {
			// @todo: Implement this.
		}
	}
}
