<?php

namespace Fatty\Exceptions;

class InvalidSportDurationsAnaerobicException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná doba cvičení anaaerobních sportů.";
	}
}
