<?php

namespace all_in_one_cleaner\interfaces;

/**
 * Abstract Module.
 *
 * @package all_in_one_cleaner
 */
interface Module {
	/**
	 * Initialize.
	 *
	 * @return void
	 */
	public function initialize(): void;
}
