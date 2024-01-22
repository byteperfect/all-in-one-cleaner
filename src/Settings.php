<?php
/**
 * Class Settings.
 *
 * @package all_in_one_cleaner
 */

declare( strict_types=1 );

namespace all_in_one_cleaner;

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container\Container;
use Carbon_Fields\Field\Field;
use Exception;
use function all_in_one_cleaner;

/**
 * Class Settings.
 *
 * @package all_in_one_cleaner
 */
class Settings {
	const PREFIX = 'all_in_one_cleaner_';

	/**
	 * Options container.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function initialize(): void {
		static $initialized;

		if ( true !== $initialized ) {
			$this->register_hooks();

			Carbon_Fields::boot();

			$initialized = true;
		}
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	protected function register_hooks(): void {
		add_action( 'carbon_fields_register_fields', array( $this, 'register_fields' ) );
		add_action( 'carbon_fields_container_all_in_one_cleaner_before_sidebar', array( $this, 'output_alert' ) );
		add_filter( 'carbon_fields_all_in_one_cleaner_button_label', array( $this, 'set_button_label' ) );
		add_action( 'carbon_fields_theme_options_container_saved', array( $this, 'on_save_fields' ), 10, 2 );
	}

	/**
	 * Register settings fields.
	 *
	 * @return void
	 */
	public function register_fields(): void {
		$this->container = Container::make(
			'theme_options',
			'all_in_one_cleaner',
			__( 'All-In-One Cleaner', 'all_in_one_cleaner' )
		)->set_page_file( 'all_in_one_cleaner' )->set_page_parent( 'tools.php' );

		do_action( 'all_in_one_cleaner_register_settings_fields', $this );
	}

	/**
	 * Output alert.
	 *
	 * @return void
	 */
	public function output_alert(): void {
		$alert = <<<EOA
Attention!<br/>
After clicking on the "Clear" button, the data will be permanently deleted from the WordPress database according to the selected options.<br/>
You will not be able to restore the deleted data.
EOA;

		_e( $alert, 'all_in_one_cleaner' );
	}

	/**
	 * Set button label.
	 *
	 * @return string
	 */
	public function set_button_label(): string {
		if ( all_in_one_cleaner()->get_handler()->is_active() ) {
			return __( 'Stop', 'all_in_one_cleaner' );
		} else {
			return __( 'Start', 'all_in_one_cleaner' );
		}
	}

	/**
	 * Add tab with fields.
	 *
	 * @param string       $tab_name Tab name.
	 * @param array<Field> $fields   Fields.
	 *
	 * @return void
	 */
	public function add_tab( string $tab_name, array $fields ): void {
		$this->container->add_tab( $tab_name, $fields );
	}

	/**
	 * Create a new field of type $raw_type and name $name and label $label.
	 *
	 * @param string      $raw_type Field raw type.
	 * @param string      $name     lower case and underscore-delimited.
	 * @param string|null $label    (optional) Automatically generated from $name if not present.
	 *
	 * @return Field
	 */
	public function make_field( string $raw_type, string $name, string $label = null ): Field {
		return Field::make( $raw_type, self::PREFIX . $name, $label );
	}

	/**
	 * Get option field value.
	 *
	 * @param string $name Field name.
	 *
	 * @return mixed
	 */
	public function get( string $name ) {
		$option_name  = self::PREFIX . $name;
		$container_id = (
			isset( $this->container )
			&&
			is_a( $this->container, Container::class )
		) ? $this->container->get_id() : '';

		return carbon_get_theme_option( $option_name, $container_id );
	}

	/**
	 * On save fields.
	 *
	 * This hook is triggered when the options are saved.
	 * It allows you to perform actions when the options are saved.
	 *
	 * @param mixed     $user_data User data.
	 * @param Container $container Container.
	 *
	 * @return void
	 */
	public function on_save_fields( $user_data, Container $container ): void {
		if ( 'carbon_fields_container_all_in_one_cleaner' === $container->id ) {
			try {
				// phpcs:ignore WordPress.Security
				if ( __( 'Start', 'all_in_one_cleaner' ) === $_POST['publish'] ) {
					do_action( 'all_in_one_cleaner_on_save_fields', $this );
				} else {
					all_in_one_cleaner()->get_handler()->cancel();
				}
			} catch ( Exception $exception ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions
				error_log( $exception->getMessage() );
			}
		}
	}
}
