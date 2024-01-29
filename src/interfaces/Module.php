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

	/**
	 * Get the slug of the module.
	 *
	 * @return string
	 */
	public function get_module_slug(): string;
}
