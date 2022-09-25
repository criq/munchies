<?php

namespace Fatty\Exceptions;

use Katu\Errors\Error;
use Katu\Tools\Validation\Param;

class FattyException extends \Exception
{
	protected $paramKeys = [];

	public function getError(): Error
	{
		$error = new Error($this->getMessage());

		foreach ($this->paramKeys as $paramKey) {
			$error->addParam(new Param($paramKey));
		}

		return $error;
	}
}
