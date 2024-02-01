<?php
/**
 * Class AllInOneCleanerHandler.
 *
 * @package all_in_one_cleaner
 */

namespace all_in_one_cleaner;

use byteperfect\WPBackgroundProcess;
use Exception;

/**
 * Class AllInOneCleanerHandler.
 *
 * @package all_in_one_cleaner
 */
class AllInOneCleanerHandler extends WPBackgroundProcess {
	/**
	 * Prefix.
	 *
	 * @var string
	 */
	protected string $prefix = 'all_in_one_cleaner';

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected string $action = 'clean';

	/**
	 * Perform task with queued item.
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 */
	protected function task( $item ) {
		try {
			$item = apply_filters( 'all_in_one_cleaner_task', $item );
		} catch ( Exception $exception ) {
			all_in_one_cleaner()->log( $exception->getMessage() );

			$item = false;
		}

		return $item;
	}

	/**
	 * Get the version of the handler.
	 *
	 * @return int
	 */
	public function get_handler_version(): int {
		return 1;
	}
}
