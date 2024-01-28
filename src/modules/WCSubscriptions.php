<?php
/**
 * WooCommerce Subscriptions Module.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;
use all_in_one_cleaner\Utils;
use WP_Post;

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
	 */
	public function task_shop_subscription( int $subscription_id ): void {
		if ( true === $this->get_option( 'delete_subscriptions' ) ) {
			$this->delete_post( $subscription_id );
		}
	}

	/**
	 * Check if the post can be deleted.
	 *
	 * @param WP_Post $post Post.
	 *
	 * @return bool
	 */
	public function can_be_deleted( WP_Post $post ): bool {
		if ( Utils::is_orphaned_post( $post ) ) {
			return true;
		}

		$customer_id = get_post_meta( $post->ID, '_customer_user', true );

		if ( in_array( $customer_id, array( false, '' ), true ) ) {
			all_in_one_cleaner()->log(
				'Customer ID not found.',
				array(
					'caller'   => __METHOD__,
					'order_id' => $post->ID,
				)
			);

			return true;
		}

		return ! user_can( $customer_id, 'manage_options' );
	}
}
