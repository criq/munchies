<?php

namespace Fatty\Exceptions;

class InvalidSportDurationsAerobicException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná doba cvičení aerobních sportů.";
	}
}
