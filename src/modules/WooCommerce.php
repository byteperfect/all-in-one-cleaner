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
		if ( $this->get_option( 'delete_products' ) ) {
			// wp_delete_post( $product_id, true );
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
		if ( $this->get_option( 'delete_products' ) ) {
			// wp_delete_post( $product_variation_id, true );
		}
	}

	/**
	 * Executes the task for a shop order.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function task_shop_order( int $order_id ): void {
		if ( $this->get_option( 'delete_orders' ) ) {
			// wp_delete_post( $order_id, true );
		}
	}

	/**
	 * Executes the task for a shop order refund.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return void
	 */
	public function task_shop_order_refund( int $order_id ) {
		if ( $this->get_option( 'delete_orders' ) ) {
			// wp_delete_post( $order_id, true );
		}
	}

	/**
	 * Executes the task for a shop coupon.
	 *
	 * @return void
	 */
	public function task_shop_coupon() {
		if ( $this->get_option( 'delete_coupons' ) ) {
			// wp_delete_post( $coupon_id, true );
		}
	}

	/**
	 * Executes the task for a shop customer.
	 *
	 * @return void
	 */
	public function task_shop_customer() {
		if ( $this->get_option( 'delete_customers' ) ) {
			// @todo: Implement this.
		}
	}

	/**
	 * Executes the task for a shop order note.
	 *
	 * @return void
	 */
	public function task_shop_order_note() {
		if ( ! $this->get_settings()->get( $this->get_settings_field_prefix() . 'delete_orders' ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'shop_order_note'" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})" );
	}

	/**
	 * Executes the task for a shop web hook.
	 *
	 * @return void
	 */
	public function task_shop_webhook() {
		if ( ! $this->get_settings()->get( $this->get_settings_field_prefix() . 'delete_orders' ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'shop_webhook'" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})" );
	}

	/**
	 * Executes the task for a shop web hook delivery.
	 *
	 * @return void
	 */
	public function task_shop_webhook_delivery() {
		if ( ! $this->get_settings()->get( $this->get_settings_field_prefix() . 'delete_orders' ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'shop_webhook_delivery'" );
		$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT id FROM {$wpdb->posts})" );
	}
}
