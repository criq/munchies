<?php

namespace Fatty\Exceptions;

class FattyException extends \Exception
{
	protected $abbr;
	protected $names = [];

	public static function createFromAbbr(string $abbr)
	{
		return (new static)->setAbbr($abbr);
	}

	public function setAbbr(?string $abbr) : FattyException
	{
		$this->abbr = $abbr;

		return $this;
	}

	public function getAbbr() : ?string
	{
		return $this->abbr;
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
