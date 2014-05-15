<?php

namespace SS6\ShopBundle\Model\Customer\Exception;

use Exception;

class DuplicateEmailException extends Exception implements CustomerException {
	/**
	 * @param string $message
	 * @param Exception $previous
	 */
	public function __construct($message = null, $previous = null) {
		parent::__construct($message, 0, $previous);
	}
}