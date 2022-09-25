<?php

namespace Fatty\Exceptions;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;

class FattyException extends \Exception
{
	protected $names = [];

	public function getError(): Error
	{
		$error = new Error($this->getMessage());

		foreach ($this->names as $name) {
			$error->addParam(new Param($name));
		}

		return $error;
	}
}
