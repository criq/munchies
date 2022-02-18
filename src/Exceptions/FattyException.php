<?php

namespace Fatty\Exceptions;

class FattyException extends \Exception
{
	protected $names = [];

	public function __toString()
	{
		$class = static::class;

		return "$class {$this->getMessage()}, file {$this->getFile()}, line {$this->getLine()}";
	}

	public function addName(string $name): FattyException
	{
		return $this->names[] = $name;

		return $this;
	}

	public function getNames(): array
	{
		return $this->names;
	}
}
