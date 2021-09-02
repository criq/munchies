<?php

namespace Fatty\Exceptions;

class FattyException extends \Exception
{
	protected $names = [];

	public function __toString()
	{
		return static::class;
	}

	public function addName(string $name) : FattyException
	{
		return $this->names[] = $name;

		return $this;
	}

	public function getNames() : array
	{
		return $this->names;
	}
}
