<?php

namespace Fatty\Exceptions;

use Katu\Errors\Error;

class FattyException extends \Exception
{
	protected $names = [];

	public function addName(string $name): FattyException
	{
		return $this->names[] = $name;

		return $this;
	}

	public function getNames(): array
	{
		return $this->names;
	}

	public function getError(): Error
	{
		return new Error($this->getMessage());
	}
}
