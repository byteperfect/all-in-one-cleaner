<?php
/**
 * Class WooCommerce
 *
 * Represents a module for WooCommerce plugin integration.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner\modules;

use all_in_one_cleaner\Settings;
use Exception;

/**
 * Class WooCommerce
 *
 * Represents a module for WooCommerce plugin integration.
 *
 * @package all_in_one_cleaner
 */
class WooCommerce extends AbstractModule {
	/**
	 * Get the slug of the plugin for which the module is registered.
	 *
	 * @return string
	 */
	protected function get_plugin_slug(): string {
		return 'woocommerce/woocommerce.php';
	}

	/**
	 * Get settings field prefix.
	 *
	 * @return string
	 */
	protected function get_settings_field_prefix(): string {
		return 'module_woocommerce_';
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
			__( 'WooCommerce', 'all_in_one_cleaner' ),
			array(
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_products',
					__( 'Delete products', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_orders',
					__( 'Delete orders', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_coupons',
					__( 'Delete coupons', 'all_in_one_cleaner' )
				),
				$settings->make_field(
					'checkbox',
					$this->get_settings_field_prefix() . 'delete_customers',
					__( 'Delete customers', 'all_in_one_cleaner' )
				),
			)
		);
	}

	/**
	 * Executes the task for a product.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return void
	 */
	public function task_product( int $product_id ): void {
		if ( true === $this->get_option( 'delete_products' ) ) {
			wp_delete_post( $product_id, true );
		}
	}

	/**
	 * Executes the task for a product variation.
	 *
	 * @param int $product_variation_id Product variation ID.
	 *
	 * @return void
	 */
	public function task_product_variation( int $product_variation_id ): void {
		if ( true === $this->get_option( 'delete_products' ) ) {
			wp_delete_post( $product_variation_id, true );
		}
	}

	/**
	 * Executes the task for a shop order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 * @throws Exception If customer ID not found.
	 */
	public function task_shop_order( int $order_id ): void {
		if ( user_can( $this->get_customer_id( $order_id ), 'manage_options' ) ) {
			return;
		}

		if ( true === $this->get_option( 'delete_orders' ) ) {
			wp_delete_post( $order_id, true );
		}
	}

	/**
	 * Executes the task for a shop order refund.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 * @throws Exception If customer ID not found.
	 */
	public function task_shop_order_refund( int $order_id ): void {
		if ( user_can( $this->get_customer_id( $order_id ), 'manage_options' ) ) {
			return;
		}

		if ( true === $this->get_option( 'delete_orders' ) ) {
			wp_delete_post( $order_id, true );
		}
	}

	/**
	 * Executes the task for a shop coupon.
	 *
	 * @param int $coupon_id Coupon ID.
	 *
	 * @return void
	 */
	public function task_shop_coupon( int $coupon_id ): void {
		if ( true === $this->get_option( 'delete_coupons' ) ) {
			wp_delete_post( $coupon_id, true );
		}
	}

	/**
	 * Executes the task for a shop customer.
	 *
	 * @return void
	 */
	public function task_shop_customer(): void {
		if ( true === $this->get_option( 'delete_customers' ) ) {
			// @todo: Implement this.
			// wp_delete_user( $user_id, $reassign );
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
