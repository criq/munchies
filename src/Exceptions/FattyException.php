<?php

namespace Fatty\Exceptions;

use Katu\Errors\Error;

class FattyException extends \Exception
{
	protected $names = [];

	public function getError(): Error
	{
		return new Error($this->getMessage());
	}
}
